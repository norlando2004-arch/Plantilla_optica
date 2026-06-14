# ✅ Testing y Verificación - Pagos Optica

## Verificación Post-Deployment

### 1. Verificar que Logs se Generan

#### Buscar pagos procesados recientemente
```bash
# Últimos 50 logs de pago
grep -E "PagosWebhookController|PagosRetornoController|PagoFulfillmentService|PagoPostApprovalService" storage/logs/laravel.log | tail -50

# Últimos 20 minutos
grep -E "2026-06-11 2[0-4]" storage/logs/laravel.log | grep -E "PagosWebhookController|PagosRetornoController" | tail -20
```

#### Buscar un pago específico
```bash
# Por referencia
grep "PAY-ABC123" storage/logs/laravel.log

# Por ID de pago
grep "pago_id.*:.*42" storage/logs/laravel.log
```

#### Buscar errores
```bash
# Errores en el último día
grep "2026-06-11" storage/logs/laravel.log | grep "error\|Error\|ERROR" | grep -E "PagosRetorno|PagoFulfillment|PagoPostApproval"
```

### 2. Verificar Estados en BD

#### Pagos que se procesaron correctamente
```sql
SELECT 
  p.id,
  p.referencia,
  p.estado,
  p.monto,
  p.meta->>'fulfillment' as fulfillment_status,
  p.meta->>'post_approval_processed_at' as post_approval_at,
  p.created_at
FROM pagos p
WHERE p.estado = 'aprobado'
  AND p.meta->>'fulfillment'->>'completed_at' IS NOT NULL
  AND p.meta->>'post_approval_processed_at' IS NOT NULL
ORDER BY p.created_at DESC
LIMIT 10;
```

#### Pagos pendientes de procesar
```sql
SELECT 
  p.id,
  p.referencia,
  p.estado,
  CASE 
    WHEN p.meta->>'fulfillment'->>'completed_at' IS NULL THEN 'Fulfillment incompleto'
    WHEN p.meta->>'post_approval_processed_at' IS NULL THEN 'Post-approval incompleto'
    ELSE 'OK'
  END as status,
  p.created_at
FROM pagos p
WHERE p.estado = 'aprobado'
  AND (
    p.meta->>'fulfillment'->>'completed_at' IS NULL 
    OR p.meta->>'post_approval_processed_at' IS NULL
  )
ORDER BY p.created_at DESC;
```

### 3. Ejecutar Comando de Verificación

```bash
# Ver qué pagos necesitarían recuperación (sin hacer cambios)
php artisan payments:retry-inconsistent --dry-run

# Ver detalles
php artisan payments:retry-inconsistent --dry-run --verbose

# Ejecutar recuperación si hay pagos incompletos
php artisan payments:retry-inconsistent
```

### 4. Flujo de Testing Manual

#### Test Case 1: Simulación de Webhook
```bash
# En tu test o usando tinker:
php artisan tinker

# Simular un webhook de Bold
$pago = Pago::find(1);
event(new \App\Events\BoldPaymentCompleted($pago));

# Ver logs
```

#### Test Case 2: Simulación de Return URL
```bash
# Acceder directamente a la URL de retorno
GET /pagos/retorno/bold?ref=PAY-ABC123&status=success

# Debe:
# 1. Loguear "Usuario retornando de pasarela"
# 2. Reintental fulfillment si no completado
# 3. Reintental post-approval si no completado
# 4. Redirigir a pagos.approved
```

#### Test Case 3: Error Simulado
```bash
# En PagoPostApprovalService, agregar error temporal:
// return false; // Simular error

$pago = Pago::find(1);
app(PagoPostApprovalService::class)->processApproved($pago);

# Debe:
# 1. Loguear el error completo
# 2. NOT romper la transacción si es posible
# 3. Permitir reintento
```

## Monitoreo Continuo

### Checklist Diario

- [ ] Ver que no haya errores nuevos en logs de pago
- [ ] Verificar que no haya pagos aprobados incompletos (comando con --dry-run)
- [ ] Confirmar que los emails de confirmación se enviaron a clientes

### Checklist Semanal

```bash
# Ejecutar comando de recuperación (modo seguro)
php artisan payments:retry-inconsistent --dry-run --verbose

# Si hay pagos incompletos, ver detalles
php artisan payments:retry-inconsistent --dry-run --verbose

# Luego ejecutar sin --dry-run si todo se ve bien
php artisan payments:retry-inconsistent --verbose
```

### Checklist Mensual

- [ ] Analizar patrones en logs de error
- [ ] Ver si hay productos específicos que causan problemas
- [ ] Revisar si hay que mejorar timeouts o retry logic
- [ ] Verificar que Bold webhooks están llegando

## Señales de Que Todo Está Funcionando ✅

### Logs que Debes Ver

#### Webhook llegó y procesó
```
[2026-06-11 20:28:30] PagosWebhookController: Webhook recibido de Bold
[2026-06-11 20:28:31] PagosWebhookController: Firma verificada ✓
[2026-06-11 20:28:32] PagosWebhookController: Evento: TRANSACTION_COMPLETED
[2026-06-11 20:28:33] PagoFulfillmentService: iniciando
[2026-06-11 20:28:34] PagoFulfillmentService: completado exitosamente
[2026-06-11 20:28:35] PagoPostApprovalService: iniciando
[2026-06-11 20:28:38] PagoPostApprovalService: completado
```

#### Return URL procesó correctamente
```
[2026-06-11 21:00:00] PagosRetornoController: Usuario retornando de pasarela
[2026-06-11 21:00:01] PagosRetornoController: Pago encontrado (estado: aprobado)
[2026-06-11 21:00:02] PagosRetornoController: Ya procesado, verificar logs
[2026-06-11 21:00:03] PagosRetornoController: Redirigiendo a pagos.approved
```

#### Error fue manejado correctamente
```
[2026-06-11 22:00:00] PagoPostApprovalService: iniciando
[2026-06-11 22:00:05] error: "Timeout sending email"
[2026-06-11 22:00:05] warning: "Email falló pero transacción completada"
[2026-06-11 22:00:06] info: "Usuario puede reintentar desde return URL"
```

## Señales de Problemas ⚠️

### Problemas Frecuentes y Soluciones

#### Problema: Pago aprobado pero no hay logs
```bash
# Solución: Verificar que los servicios se están llamando
grep "PagoFulfillmentService\|PagoPostApprovalService" storage/logs/laravel.log | wc -l

# Si 0, verificar que los cambios están en producción
grep -r "handleApproved" app/Services/Pagos/
```

#### Problema: Comando encuentra muchos pagos incompletos
```bash
# Ejecutar con --dry-run primero
php artisan payments:retry-inconsistent --dry-run --verbose

# Revisar logs para ver qué salió mal
# Luego ejecutar recuperación
php artisan payments:retry-inconsistent --verbose
```

#### Problema: Neon pooler timeout
```bash
# Los logs mostrarán: "SQLSTATE[25P02]"
grep "25P02" storage/logs/laravel.log

# Solución ya implementada: reconnect automático
# Si sigue pasando, contactar a Neon support
```

## Generación de Reportes

### Reporte de Pagos del Día

```bash
# Pagos procesados exitosamente hoy
php artisan tinker
Pago::whereDate('created_at', today())->where('estado', 'aprobado')->count()

# Pagos fallidos hoy
Pago::whereDate('created_at', today())->where('estado', 'rechazado')->count()

# Pagos incompletos hoy
Pago::whereDate('created_at', today())
  ->where('estado', 'aprobado')
  ->where(function($q) {
    $q->whereRaw("meta->>'fulfillment'->>'completed_at' IS NULL")
      ->orWhereRaw("meta->>'post_approval_processed_at' IS NULL");
  })
  ->count()
```

### Reporte de Errores

```bash
# Errores de la última hora
grep "$(date +'%Y-%m-%d %H'):" storage/logs/laravel.log | grep -i error | wc -l

# Errores más comunes
grep "error" storage/logs/laravel.log | grep -o 'Error: [^"]*' | sort | uniq -c | sort -rn | head -10
```

## Escalación

Si encontras un problema:

1. **Recolectar información**:
   ```bash
   # ID del pago problemático
   grep "PAY-XXX" storage/logs/laravel.log > pago_logs.txt
   
   # Estado en BD
   SELECT * FROM pagos WHERE referencia = 'PAY-XXX';
   
   # Información de usuario
   SELECT * FROM usuarios WHERE id = (SELECT usuario_id FROM pagos WHERE referencia = 'PAY-XXX');
   ```

2. **Intentar recuperación**:
   ```bash
   # Si es un pago, intentar recuperarlo
   php artisan payments:retry-inconsistent --dry-run --verbose
   ```

3. **Si persiste**:
   - Revisar PAYMENT_FIXES_README.md
   - Revisar PAYMENT_FLOW_TECHNICAL_DETAILS.md
   - Contactar a soporte de Bold (verificar webhook signature)

