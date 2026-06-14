<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Carrito;
use App\Services\ProductDetailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InformacionController extends Controller
{
    public function index(): View
    {
        $productDetailSettings = ProductDetailSettings::load();
        $successfulPaymentStates = ['aprobado', 'completado'];

        $visitantesActivos = $this->countActiveUsers();

        // Contar empleados conectados (rol_id = 2)
        $empleadosConectados = $this->getConnectedEmployees();
        $usuariosConectados = count($empleadosConectados);
        $nombresEmpleados = $empleadosConectados->pluck('nombre')->implode(', ');

        // Ingresos totales
        $ingresosTotales = Pago::whereIn('estado', $successfulPaymentStates)->sum('monto');

        // Cantidad de pagos completados
        $cantidadPagos = Pago::whereIn('estado', $successfulPaymentStates)->count();

        // Ingresos del mes actual
        $ingresosEstesMes = Pago::whereIn('estado', $successfulPaymentStates)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('monto');

        // Cantidad de usuarios
        $totalUsuarios = Usuario::count();

        // Datos para gráfica (dinámica): desde el primer pago exitoso hasta el mes actual.
        // Esto evita estar limitado al año actual y va creciendo mes a mes.
        $firstSuccessfulPaymentAt = Pago::whereIn('estado', $successfulPaymentStates)->min('created_at');

        $rangeStart = $firstSuccessfulPaymentAt
            ? Carbon::parse($firstSuccessfulPaymentAt)->startOfMonth()
            : now()->startOfMonth();
        $rangeEnd = now()->startOfMonth();

        $monthKeys = [];
        $ingresosPorMes = [];
        $pagosPorMes = [];

        $cursor = $rangeStart->copy();
        while ($cursor->lte($rangeEnd)) {
            $key = $cursor->format('Y-m');
            $monthKeys[] = $key;
            $ingresosPorMes[$key] = 0.0;
            $pagosPorMes[$key] = 0;
            $cursor->addMonth();
        }

        $successfulPaymentsRaw = Pago::whereIn('estado', $successfulPaymentStates)
            ->where('created_at', '>=', $rangeStart)
            ->where('created_at', '<=', $rangeEnd->copy()->endOfMonth())
            ->get(['created_at', 'monto']);

        foreach ($successfulPaymentsRaw as $row) {
            $key = Carbon::parse($row->created_at)->format('Y-m');
            if (!array_key_exists($key, $ingresosPorMes)) {
                continue;
            }

            $ingresosPorMes[$key] += (float) ($row->monto ?? 0);
            $pagosPorMes[$key] += 1;
        }

        // Productos más vendidos (tabla carrito no existe aún)
        $productosMasVendidos = collect([]);

        // Gráfica de distribución de productos por categoría
        $productosPorCategoria = collect([]);

        // Estado de pagos (solo los que interesan en el dashboard)
        // Nota: el sistema usa 'aprobado' como exitoso; 'completado' se mantiene por compatibilidad.
        $estadoPagos = collect([
            [
                'estado' => 'aprobado',
                'cantidad' => (int) Pago::whereIn('estado', $successfulPaymentStates)->count(),
            ],
            [
                'estado' => 'rechazado',
                'cantidad' => (int) Pago::where('estado', 'rechazado')->count(),
            ],
        ]);

        // Preparar datos para Chart.js
        $labelsMeses = [];
        $ingresosPorMesData = [];
        $visitantesPorMesData = [];

        foreach ($monthKeys as $key) {
            $labelsMeses[] = $key;
            $ingresosPorMesData[] = round((float) ($ingresosPorMes[$key] ?? 0), 2);
            $visitantesPorMesData[] = (int) ($pagosPorMes[$key] ?? 0);
        }

        return view('admin.informacion', [
            'usuariosConectados' => $usuariosConectados,
            'nombresEmpleados' => $nombresEmpleados,
            'visitantesActivos' => $visitantesActivos,
            'hideLoveCtaForClients' => (bool) ($productDetailSettings['hide_love_cta_for_clients'] ?? false),
            'ingresosTotales' => round($ingresosTotales, 2),
            'cantidadPagos' => $cantidadPagos,
            'ingresosEstesMes' => round($ingresosEstesMes, 2),
            'totalUsuarios' => $totalUsuarios,
            'mesesNombres' => json_encode($labelsMeses),
            'ingresosPorMesData' => json_encode($ingresosPorMesData),
            'visitantesPorMesData' => json_encode($visitantesPorMesData),
            'productosMasVendidos' => $productosMasVendidos,
            'estadoPagos' => $estadoPagos,
            'productosPorCategoria' => $productosPorCategoria,
            'tieneProductos' => false,
        ]);
    }

    public function updateLoveCtaVisibility(Request $request): RedirectResponse
    {
        $settings = ProductDetailSettings::load();
        $settings['hide_love_cta_for_clients'] = $request->boolean('hide_love_cta_for_clients');

        ProductDetailSettings::upsert($settings);

        return redirect()
            ->route('admin.informacion')
            ->with('status', $settings['hide_love_cta_for_clients']
                ? 'El boton "Me encanta, lo quiero" quedo oculto para los clientes.'
                : 'El boton "Me encanta, lo quiero" quedo visible para los clientes.');
    }

    /**
     * Obtiene los empleados conectados en este momento (rol_id = 2)
     */
    private function getConnectedEmployees()
    {
        try {
            $cacheDriver = config('cache.default');
            
            // Si es database cache (SQLite por defecto)
            if ($cacheDriver === 'database') {
                $cacheEntries = DB::table('cache')
                    ->where('key', 'like', '%user_activity:%')
                    ->where('expiration', '>', now()->timestamp)
                    ->pluck('value')
                    ->toArray();
                
                $userIds = [];
                foreach ($cacheEntries as $value) {
                    $identifier = null;
                    if (is_string($value)) {
                        $decoded = @unserialize($value, ['allowed_classes' => false]);
                        if (is_string($decoded)) {
                            $identifier = $decoded;
                        } elseif (preg_match('/s:\d+:"(.*?)"/', $value, $matches)) {
                            $identifier = $matches[1];
                        }
                    }

                    if (is_string($identifier) && str_starts_with($identifier, 'auth:')) {
                        $userId = (int) substr($identifier, 5);
                        if ($userId > 0) {
                            $userIds[] = $userId;
                        }
                    }
                }
                
                if (empty($userIds)) {
                    return collect([]);
                }
                
                // Obtener usuarios staff (empleados/admins)
                return Usuario::whereIn('id', $userIds)
                    ->whereIn('rol_id', [2, 3])
                    ->select('id', 'nombre', 'correo')
                    ->get();
            }
            
            return collect([]);
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Cuenta usuarios activos en este momento desde Cache
     * Busca todas las claves user_activity:* que no han expirado
     */
    private function countActiveUsers(): int
    {
        try {
            $cacheDriver = config('cache.default');
            
            // Si es database cache (SQLite por defecto)
            if ($cacheDriver === 'database') {
                $cacheEntries = DB::table('cache')
                    ->where('key', 'like', '%user_activity:%')
                    ->where('expiration', '>', now()->timestamp)
                    ->pluck('value')
                    ->toArray();

                if (empty($cacheEntries)) {
                    return 0;
                }

                $nonAuthCount = 0;
                $authUserIds = [];

                foreach ($cacheEntries as $value) {
                    $identifier = null;
                    if (is_string($value)) {
                        $decoded = @unserialize($value, ['allowed_classes' => false]);
                        if (is_string($decoded)) {
                            $identifier = $decoded;
                        } elseif (preg_match('/s:\d+:"(.*?)"/', $value, $matches)) {
                            $identifier = $matches[1];
                        }
                    }

                    if (!is_string($identifier) || $identifier === '') {
                        continue;
                    }

                    if (str_starts_with($identifier, 'auth:')) {
                        $userId = (int) substr($identifier, 5);
                        if ($userId > 0) {
                            $authUserIds[] = $userId;
                        }
                        continue;
                    }

                    // guest:* y visitor:* cuentan como no registrados
                    if (str_starts_with($identifier, 'guest:') || str_starts_with($identifier, 'visitor:')) {
                        $nonAuthCount++;
                    }
                }

                $authUserIds = array_values(array_unique($authUserIds));
                if (empty($authUserIds)) {
                    return $nonAuthCount;
                }

                // Excluir rol_id=4 del conteo de visitantes activos
                $roleByUserId = Usuario::whereIn('id', $authUserIds)
                    ->pluck('rol_id', 'id')
                    ->toArray();

                $authCount = 0;
                foreach ($authUserIds as $userId) {
                    $roleId = (int) ($roleByUserId[$userId] ?? 0);
                    if ($roleId === 4) {
                        continue;
                    }
                    $authCount++;
                }

                return $nonAuthCount + $authCount;
            }
            
            // Si es Redis
            if ($cacheDriver === 'redis') {
                try {
                    $redis = Cache::connection('redis');
                    if (method_exists($redis, 'connection')) {
                        $conn = $redis->connection();
                        if (method_exists($conn, 'keys')) {
                            $keys = $conn->keys('*user_activity:*');
                            if (!is_array($keys) || empty($keys)) {
                                return 0;
                            }

                            // Intentar leer valores para poder excluir rol 4
                            if (method_exists($conn, 'mget')) {
                                $values = $conn->mget($keys);
                                if (!is_array($values)) {
                                    return count($keys);
                                }

                                $nonAuthCount = 0;
                                $authUserIds = [];

                                foreach ($values as $identifier) {
                                    if (!is_string($identifier) || $identifier === '') {
                                        continue;
                                    }

                                    if (str_starts_with($identifier, 'auth:')) {
                                        $userId = (int) substr($identifier, 5);
                                        if ($userId > 0) {
                                            $authUserIds[] = $userId;
                                        }
                                        continue;
                                    }

                                    if (str_starts_with($identifier, 'guest:') || str_starts_with($identifier, 'visitor:')) {
                                        $nonAuthCount++;
                                    }
                                }

                                $authUserIds = array_values(array_unique($authUserIds));
                                if (empty($authUserIds)) {
                                    return $nonAuthCount;
                                }

                                $roleByUserId = Usuario::whereIn('id', $authUserIds)
                                    ->pluck('rol_id', 'id')
                                    ->toArray();

                                $authCount = 0;
                                foreach ($authUserIds as $userId) {
                                    $roleId = (int) ($roleByUserId[$userId] ?? 0);
                                    if ($roleId === 4) {
                                        continue;
                                    }
                                    $authCount++;
                                }

                                return $nonAuthCount + $authCount;
                            }

                            return count($keys);
                        }
                    }
                } catch (\Exception $e) {
                    return 0;
                }
            }
            
            // Si es file cache
            if ($cacheDriver === 'file') {
                $cachePath = storage_path('framework/cache/data');
                if (is_dir($cachePath)) {
                    $files = glob($cachePath . '/*user_activity*');
                    return count($files);
                }
            }
            
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
