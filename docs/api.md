# API externa — Nager.Date

[← Volver al índice](index.md)

---

## ¿Qué es Nager.Date?

[Nager.Date](https://date.nager.at) es una API pública y gratuita que devuelve los festivos nacionales de cualquier país por año. No requiere autenticación ni registro.

TimeTrack la usa para obtener automáticamente los festivos de España del año en curso y mostrarlos en varias pantallas de la aplicación.

---

## Endpoint utilizado

```
GET https://date.nager.at/api/v3/PublicHolidays/{año}/ES
```

**Ejemplo de respuesta:**

```json
[
  {
    "date": "2026-01-01",
    "localName": "Año Nuevo",
    "name": "New Year's Day",
    "countryCode": "ES",
    "global": true,
    "counties": null,
    "types": ["Public"]
  },
  {
    "date": "2026-06-24",
    "localName": "Sant Joan",
    "name": "Saint John's Day",
    "countryCode": "ES",
    "global": false,
    "counties": ["ES-VC"],
    "types": ["Public"]
  }
]
```

---

## Integración en TimeTrack

La llamada a la API se realiza desde **PHP en el servidor**, no desde el navegador, por dos razones: permite filtrar los resultados antes de enviarlos al cliente y permite guardarlos en la base de datos para usarlos en otras funcionalidades como los días especiales de los trabajadores.

### Importación automática

Al cargar el panel del administrador, jQuery lanza automáticamente una petición AJAX a `ajax/importar_festivos.php`. Este archivo consulta la API, filtra los festivos nacionales y de la Comunitat Valenciana (`ES-VC`), y guarda en la base de datos los que no existan todavía. Solo muestra un aviso si se han importado festivos nuevos.

```javascript
$.ajax({
    url:    "/ajax/importar_festivos.php",
    method: "GET",
    success: function(respuesta) {
        var datos = JSON.parse(respuesta);
        if (datos.importados > 0) {
            $("#importar-festivos").fadeIn("slow").html(datos.mensaje);
        }
    }
});
```

### Visualización

Los festivos se muestran como tarjetas de calendario en tres pantallas:

- Panel del administrador (`admin/index.php`)
- Gestión de horarios (`admin/horarios.php`)
- Vacaciones del trabajador (`trabajador/vacaciones.php`)

El archivo `ajax/get_festivos.php` devuelve los festivos almacenados en la base de datos en formato JSON. jQuery los recibe y los renderiza dinámicamente con `$.each()`:

```javascript
$.each(festivos, function(i, festivo) {
    var partes = festivo.date.split("-");
    var dia    = partes[2];
    var mes    = meses[parseInt(partes[1]) - 1];

    html += "<div class='festivo-card'>" +
                "<div class='festivo-card-mes'>" + mes + "</div>" +
                "<div class='festivo-card-dia'>" + dia + "</div>" +
                "<div class='festivo-card-nombre'>" + festivo.localName + "</div>" +
            "</div>";
});

$("#festivos").hide().html(html).fadeIn("slow");
```

### Uso en días especiales

Cuando el administrador asigna un día especial de tipo "festivo" a un trabajador, puede basarse en el calendario de festivos importados desde la API. Esto garantiza que los festivos nacionales y autonómicos están siempre actualizados sin necesidad de introducirlos manualmente.

---

## Archivos relacionados

| Archivo | Función |
|---|---|
| `ajax/importar_festivos.php` | Consulta la API e importa los festivos nuevos a la BD |
| `ajax/get_festivos.php` | Devuelve los festivos de la BD en formato JSON |
| `js/main.js` | Lanza las peticiones AJAX y renderiza las tarjetas |
