# Migracion de pedidos: puntos criticos

## Pedidos en proceso y campo vendedor vacio

### Problema encontrado

En la app movil, la vista **Pedidos En Proceso** si mostraba pedidos, pero en la web no aparecian aunque el pedido se finalizara con mensaje de exito.

Al revisar directamente la base externa `xxxxvpex`, se confirmo que el pedido si se estaba guardando. Ejemplo real encontrado:

```txt
id_vtaped: 261
nit: 75036432
fecha: 2026-05-19
hdigita: 20:56:30
operario: A008
vendedor: ''
titular: RAUL RAMOS
ped_estado: 0
```

La causa fue que algunos operarios en `xxxxciao` tienen el campo `ciao_vend` vacio. Por ejemplo:

```txt
clave: A008
nombre: FACTURA NORMAL
ciao_vend: ''

clave: A005
nombre: FACTURA TOUCH
ciao_vend: ''
```

El pedido se crea en `xxxxvpex.vendedor` con ese mismo valor vacio.

### Diferencia movil vs web

El movil consulta los pedidos del vendedor asi:

```sql
SELECT * FROM xxxxvpex
WHERE vendedor = '{idVendedor}'
ORDER BY id_vtaped DESC;
```

Si `idVendedor` esta vacio, la consulta se convierte en:

```sql
SELECT * FROM xxxxvpex
WHERE vendedor = ''
ORDER BY id_vtaped DESC;
```

La web tenia una proteccion que devolvia lista vacia cuando `$vendedor === ''`. Eso rompia la compatibilidad con la base vieja, porque en este sistema un vendedor vacio puede ser un valor valido.

### Regla para futuras correcciones

No asumir que `vendedor` o `ciao_vend` siempre tienen valor.

Para replicar el comportamiento movil, cuando `vendedor` venga vacio se debe consultar explicitamente:

```php
->where('vendedor', '')
```

No se debe hacer:

```php
if ($vendedor === '') {
    return [];
}
```

en listados que deban comportarse igual al movil.

### Archivos relacionados

- `app/Services/PedidoFlowService.php`
  - `listarPedidosVendedor(...)`
  - `filtrarPedidos(...)`
  - `listarPedidosAprobados(...)`
  - `obtenerPedido(...)`
- `app/Http/Controllers/PedidoController.php`
  - `continuar(...)`
  - `pedidosProceso(...)`
- `app/Http/Controllers/AuthController.php`
  - `validateOperario(...)`

### Comportamiento esperado

La vista web **Pedidos en proceso** debe mostrar los pedidos igual que el movil:

```sql
SELECT * FROM xxxxvpex
WHERE vendedor = :vendedor
ORDER BY id_vtaped DESC;
```

Si `:vendedor` es cadena vacia, debe buscar `vendedor = ''`.

### Como diagnosticar si vuelve a pasar

1. Confirmar si el pedido existe en `xxxxvpex`.
2. Revisar los campos:
   - `id_vtaped`
   - `nit`
   - `fecha`
   - `hdigita`
   - `operario`
   - `vendedor`
   - `ped_estado`
3. Revisar el operario en `xxxxciao`:
   - `nombre`
   - `clave`
   - `ciao_vend`
4. Si `xxxxciao.ciao_vend` esta vacio, entonces `xxxxvpex.vendedor` puede quedar vacio y la consulta debe permitir ese caso.

### Nota importante

En esta migracion se debe priorizar el comportamiento real del movil y de la base existente, incluso cuando parezca raro desde Laravel. En este caso, `vendedor = ''` no significa necesariamente "dato invalido"; significa "asi lo guarda y consulta el sistema viejo para ciertos operarios".
