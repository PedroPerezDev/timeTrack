# Arquitectura

[← Volver al índice](index.md)

---

## Estructura de carpetas

```
timetrack/
├── admin/                  ← Páginas del panel de administrador
│   ├── index.php           ← Dashboard con KPIs y fichajes del día
│   ├── trabajadores.php    ← CRUD de trabajadores
│   ├── horarios.php        ← Gestión de horarios y días especiales
│   ├── informes.php        ← Informes de fichajes por período
│   └── pdf.php             ← Generación del PDF con FPDF
├── ajax/                   ← Endpoints que reciben peticiones AJAX
│   ├── fichar.php          ← Registra un fichaje y devuelve JSON
│   ├── get_festivos.php    ← Devuelve festivos desde la BD en JSON
│   └── importar_festivos.php ← Importa festivos desde la API Nager.Date
├── css/
│   └── style.css           ← Hoja de estilos principal (Mobile First)
├── docs/                   ← Documentación del proyecto (GitHub Pages)
├── img/                    ← Logo, frames del reloj animado y slideshow
├── includes/               ← Componentes reutilizables
│   ├── header.php          ← Cabecera con navegación y modo oscuro
│   ├── footer.php          ← Pie de página
│   ├── funciones.php       ← Funciones PHP de uso común
│   └── cerrar_sesion.php   ← Destruye la sesión y redirige al login
├── js/
│   └── main.js             ← Toda la lógica JavaScript del cliente
├── libs/
│   └── fpdf/               ← Librería FPDF para generación de PDFs
├── trabajador/             ← Páginas del panel del trabajador
│   ├── index.php           ← Dashboard con reloj y botones de fichaje
│   ├── perfil.php          ← Datos personales y laborales
│   └── vacaciones.php      ← Días de vacaciones y festivos
├── uploads/
│   └── fotos_trabajadores/ ← Fotos de perfil subidas al servidor
├── config.php              ← Credenciales de base de datos (no en Git)
├── config.ejemplo.php      ← Plantilla de configuración sin credenciales
└── index.php               ← Pantalla de login
```

---

## Base de datos

La base de datos se llama `timetrack_db` y contiene las siguientes tablas:

| Tabla | Descripción |
|---|---|
| `usuarios` | Trabajadores y administradores con todos sus datos |
| `horarios` | Horario semanal de cada trabajador (lunes a viernes) |
| `horarios_especiales` | Días especiales: vacaciones, festivos, libres y cambios |
| `fichajes` | Registro de cada fichaje con hora real y diferencia en minutos |
| `incidencias` | Incidencias generadas automáticamente o por el admin |
| `festivos_locales` | Festivos importados desde la API Nager.Date |

---

## Roles de usuario

La aplicación tiene dos roles gestionados mediante sesiones PHP:

### Trabajador
- Accede a su panel personal en `trabajador/`
- Puede fichar, ver su perfil y consultar sus vacaciones
- No puede acceder a ninguna URL de `admin/`

### Administrador
- Accede al panel de administración en `admin/`
- Gestiona trabajadores, horarios, informes y festivos
- Puede ver los fichajes de todos los trabajadores

La verificación de rol se realiza en cada página mediante la función `verificarSesion($rol)` definida en `includes/funciones.php`. Si el rol no corresponde, la función redirige automáticamente al login.

---

## Funciones comunes — funciones.php

Para evitar duplicar código en todas las páginas, las operaciones más repetidas están centralizadas en `includes/funciones.php`:

| Función | Descripción |
|---|---|
| `verificarSesion($rol)` | Inicia sesión y verifica el rol. Redirige al login si no corresponde |
| `mostrarMensaje($texto, $tipo)` | Muestra un mensaje de éxito (verde) o error (rojo) |
| `mostrarFoto($foto, $ancho, $estilo)` | Devuelve el HTML de la foto de un trabajador |
| `diasVacacionesDisponibles($total, $gastados)` | Calcula los días de vacaciones disponibles |
| `formatearFecha($fecha)` | Convierte una fecha de `Y-m-d` a `d/m/Y` |
| `subirFoto($file, $ruta)` | Sube una foto al servidor y devuelve el nombre del archivo |
| `eliminarFoto($foto, $ruta)` | Elimina una foto del servidor si existe |
