<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApplyDatabaseTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $conn = DB::connection();
            $driver = (string) $conn->getDriverName();
            $timeoutMs = (int) env('DB_STATEMENT_TIMEOUT_MS', 2500);
            if ($timeoutMs < 500) {
                $timeoutMs = 500;
            }

            if ($driver === 'pgsql') {
                $conn->statement('SET statement_timeout TO '.$timeoutMs);
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                $conn->statement('SET SESSION MAX_EXECUTION_TIME='.$timeoutMs);
            }
        } catch (\Throwable $e) {
            // noop
        }

        return $next($request);
    }
}
