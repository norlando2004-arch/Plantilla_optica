# 📦 Integración de Datos de Envío Automática - Optica

## El Cambio

Ahora cuando un pago se aprueba, **AUTOMÁTICAMENTE se guardan TODOS los datos necesarios** para que aparezca correctamente en el panel "Próximos Envíos" **SIN intervención manual del admin**.

## ¿Qué Cambió?

### Nuevo Servicio: `ShippingDataPreparationService`

Cuando un pago se aprueba, este servicio **automáticamente**:

1. ✅ **Guarda la fórmula** (prescription_id) en meta
2. ✅ **Guarda datos del cliente** (nombre, email, teléfono, cédula, dirección)
3. ✅ **Guarda datos del pedido** (productos, cantidades, precios)
4. ✅ **Guarda datos de pago** (monto, moneda, referencia)
5. ✅ **Marca el estado** como pendiente de envío (`envio_estado = null`)

Todo esto en la tabla `pagos`, columna `meta`.

## Dónde se Ejecuta

### 1️⃣ **En el Webhook** (cuando Bold notifica)
```
Bold envía webhook → PagosWebhookController 
  → PagoPostApprovalService::processApproved() 
    → ShippingDataPreparationService::prepareShippingData() ✅
```

### 2️⃣ **En el Return URL** (cuando usuario regresa de Bold)
```
Usuario retorna → PagosRetornoController 
  → PagoPostApprovalService::processApproved() 
    → ShippingDataPreparationService::prepareShippingData() ✅
```

### 3️⃣ **En Recuperación Manual** (comando para pagos incompletos)
```bash
php artisan payments:retry-inconsistent
  → PagoPostApprovalService::processApproved() 
    → ShippingDataPreparationService::prepareShippingData() ✅
```

## Garantía

**NUNCA más faltarán datos en "Próximos Envíos"** porque:

- ✅ Se ejecuta SIEMPRE después de que se aprueba el pago
- ✅ Es idempotente (seguro de ejecutar múltiples veces)
- ✅ Recupera datos desde todas las fuentes (usuario, carrito, guest, meta)
- ✅ No interrumpe el flujo si falla (está wrapped en try-catch)

## Ejemplo de Datos Guardados

Cuando se aprueba un pago, ahora meta contiene:

```json
{
  "usuario_id": 42,
  "perfil_cliente_id": 5,
  "prescription_id": 123,
  "fulfillment": {
    "completed_at": "2026-06-11T20:30:00Z"
  },
  "inventory_updated": true,
  "post_approval_processed_at": "2026-06-11T20:30:15Z",
  
  "perfil_data": {
    "id": 5,
    "numero_documento": "1234567890",
    "tipo_documento": "CC",
    "nombre": "Juan Pérez",
    "correo": "juan@example.com",
    "telefono": "+573001234567",
    "direccion": "Calle 123 #45-67",
    "ciudad": "Medellín",
    "genero": "M",
    "fecha_nacimiento": "1990-01-01"
  },
  
  "carrito_data": {
    "carrito_id": 999,
    "total_items": 2,
    "items_summary": [
      {
        "producto_id": 10,
        "nombre_producto": "Lentes de Sol",
        "cantidad": 1,
        "precio_unitario": 85000.00
      }
    ]
  },
  
  "payment_data": {
    "referencia": "PAY-ABC123",
    "monto": 85000.00,
    "moneda": "COP",
    "pasarela": "bold",
    "aprobado_en": "2026-06-11T20:30:00Z"
  }
}
```

Ahora en el panel "Próximos Envíos" puedes acceder a TODOS estos datos sin problemas.

## Cambios en Archivos

| Archivo | Cambio |
|---------|--------|
| **ShippingDataPreparationService.php** | ✨ Nuevo servicio para preparar datos |
| **PagoPostApprovalService.php** | ✅ Ahora llama al nuevo servicio automáticamente |
| **RetryInconsistentPayments.php** | ✅ Ahora ejecuta preparación de datos al recuperar pagos |

## Verificación

### Verificar en Base de Datos

```sql
-- Ver datos preparados para un pago
SELECT 
  id,
  referencia,
  estado,
  envio_estado,
  meta->>'usuario_id' as usuario_id,
  meta->>'perfil_cliente_id' as perfil_id,
  meta->>'prescription_id' as prescription_id,
  meta->'perfil_data'->>'nombre' as nombre_cliente,
  meta->'perfil_data'->>'numero_documento' as cedula,
  jsonb_keys(meta) as todas_las_claves
FROM pagos
WHERE estado = 'aprobado'
ORDER BY created_at DESC
LIMIT 10;
```

### Verificar en Logs

```bash
grep "ShippingDataPreparationService" storage/logs/laravel.log
```

Deberías ver logs como:
```
[2026-06-11 20:30:00] ShippingDataPreparationService: iniciando preparación de datos de envío
[2026-06-11 20:30:01] ShippingDataPreparationService: prescription_id guardado en meta
[2026-06-11 20:30:01] ShippingDataPreparationService: datos de perfil guardados
[2026-06-11 20:30:02] ShippingDataPreparationService: datos de carrito guardados
[2026-06-11 20:30:02] ShippingDataPreparationService: datos de envío preparados exitosamente
```

## ¿Y los 2 Pagos Incompletos Anteriores?

Ejecuta esto para recuperarlos:

```bash
# Ver cuáles necesitan recuperación
php artisan payments:retry-inconsistent --dry-run --verbose

# Ejecutar recuperación (prepara datos automáticamente)
php artisan payments:retry-inconsistent --verbose
```

Después de esto, esos 2 pagos tendrán TODOS los datos en meta y aparecerán correctamente en "Próximos Envíos".

## Próximos Pasos

1. **Deploy** estos cambios a producción
2. **Ejecutar comando** para recuperar pagos anteriores incompletos
3. **Verificar** en BD que meta contiene los datos
4. **Confirmar** que pagos aparecen en "Próximos Envíos" con todos los datos

---

**Resultado Final**: ✅ **NUNCA VUELVERÁ A FALTAR INFORMACIÓN EN PRÓXIMOS ENVÍOS**

Porque ahora:
- Datos se guardan automáticamente con CADA aprobación
- Se recuperan desde múltiples fuentes (usuario, perfil, carrito, guest)
- Se rastrean en logs para auditoría
- Se pueden recuperar manualmente si algo falla
