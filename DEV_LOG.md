## 📅 Registro de Cambios - 07/02/2026

### 🐛 BUG FIX: Error Crítico en Canje de Premios (Club Vilcanet)

**Estado:** ✅ Resuelto
**Archivos Afectados:**
* `app/Models/Redemption.php`
* `app/Http/Controllers/Api/ClubController.php`

**Descripción del Problema:**
La aplicación móvil fallaba al intentar canjear un premio, mostrando dos errores dependiendo del intento:
1.  `SQLSTATE[HY000]: General error: 1364 Field 'reward_name' doesn't have a default value`.
2.  `Connection closed while receiving data` (Error de conexión fatal).

**Diagnóstico Técnico:**
1.  **Discrepancia en Base de Datos:** La columna en la tabla `redemptions` se llama `points_spent`, pero el controlador estaba enviando `points_used`. Esto causaba el cierre inesperado de la conexión.
2.  **Protección Mass Assignment:** El modelo `Redemption` no tenía autorizado el campo `reward_name` en la propiedad `$fillable`, por lo que Laravel eliminaba el dato antes de guardar, provocando el error SQL 1364.

**Solución Implementada:**
1.  **Modelo (`Redemption.php`):** Se actualizaron los campos permitidos en `$fillable`:
    * Se agregó `'reward_name'` para persistir el nombre del premio en el historial.
    * Se agregó `'points_spent'` coincidiendo con el esquema real de la base de datos.
2.  **Controlador (`ClubController.php`):** Se corrigió el método `redeem` para construir el objeto de canje correctamente:
    * Ahora envía `points_spent` (en lugar de `points_used`).
    * Incluye explícitamente `'reward_name' => $reward->name`.

---