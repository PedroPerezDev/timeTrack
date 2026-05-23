# Funcionalidades

[← Volver al índice](index.md)

---

## Login

La pantalla de acceso es la primera que ve cualquier usuario. Muestra el formulario de usuario y contraseña con el logotipo de TimeTrack.

![Login en escritorio](img/login_escritorio.png)

---

## Panel del administrador

### Dashboard

Al entrar al panel el administrador ve cuatro indicadores en tiempo real:

- **Trabajadores activos** — total de empleados en plantilla
- **Han fichado hoy** — trabajadores con al menos un fichaje del día
- **Sin fichar hoy** — trabajadores que no han fichado ningún turno
- **Incidencias hoy** — incidencias generadas en el día actual

Al cargar el panel se ejecuta automáticamente un proceso que revisa si el día anterior hubo trabajadores con fichajes incompletos. Por cada fichaje no realizado se genera una incidencia en la base de datos.

![Dashboard del administrador](img/admin_dashboard_escritorio.png)

---

### Fichajes del día

Desde el panel el administrador puede ver qué trabajadores han fichado hoy y en qué hora lo han hecho.

![Fichajes de hoy](img/admin_fichajes_hoy_escritorio.png)

---

### Gestión de trabajadores

Permite buscar trabajadores por nombre o seleccionarlos de una lista. El resultado se muestra en una tabla paginada de 5 registros por página con las acciones de horario, modificar y borrar.

Desde esta pantalla el administrador puede:

- **Dar de alta** un nuevo trabajador con todos sus datos y foto
- **Modificar** los datos de un trabajador existente
- **Borrar** un trabajador con confirmación

El formulario de alta valida los campos en el cliente antes de enviar los datos al servidor, usando expresiones regulares para nombre, email, DNI, teléfono y contraseña.

![Gestión de trabajadores](img/admin_trabajadores_escritorio.png)

El formulario de modificación muestra todos los campos del trabajador y permite actualizar la foto de perfil:

![Formulario de modificar trabajador](img/modificar_trabajador_claro_escritorio.png)

---

### Horarios

Cada trabajador tiene su propio horario semanal editable de lunes a viernes con jornada partida: entrada y salida de mañana, y entrada y salida de tarde.

Además se pueden registrar **días especiales** para fechas concretas:

- **Vacaciones** — el trabajador ve el mensaje de vacaciones
- **Festivo** — el trabajador ve el mensaje de festivo
- **Día libre** — el trabajador ve el mensaje de día libre
- **Cambio de horario** — el trabajador ficha con un horario diferente al habitual

![Días especiales registrados](img/admin_dias_especiales_registrados_escritorio.png)

![Formulario de día especial](img/admin_dia_especial_escritorio.png)

---

### Informes

El administrador puede generar informes de cualquier trabajador en un rango de fechas. El informe muestra para cada fichaje la hora prevista, la hora real y la diferencia en minutos. Al final aparece un resumen con el balance total del período.

![Informe de fichajes](img/informes_fichajes_claro_escritorio.png)

Desde el informe se puede generar un **PDF** con la librería FPDF que incluye la cabecera de TimeTrack, la tabla de fichajes y el resumen del período:

![PDF generado con FPDF](img/informe_pdf_generado.png)

---

### Solicitudes de vacaciones

El administrador puede ver todas las solicitudes de vacaciones enviadas por los trabajadores, aprobarlas o denegarlas.

![Solicitudes de vacaciones](img/01_admin_solicitudes_light_desktop.png)

---

## Panel del trabajador

El trabajador accede a la aplicación con su usuario y contraseña desde cualquier dispositivo. Una vez dentro ve su jornada del día actual con un reloj en tiempo real y cuatro botones de fichaje.

### Fichaje

La pantalla principal muestra la fecha, el reloj en tiempo real y los cuatro tipos de fichaje:

- **Entrada mañana**
- **Salida mañana**
- **Entrada tarde**
- **Salida tarde**

Cada botón muestra la hora prevista según el horario asignado. Al pulsar FICHAR, la acción se registra en el servidor mediante AJAX sin recargar la página, de forma que el reloj no se interrumpe. Una vez fichado, el botón muestra la hora real registrada y la diferencia en minutos respecto a la hora prevista.

Los cuatro botones están siempre disponibles e independientes entre sí. No es obligatorio seguir un orden.

![Jornada de hoy](img/09_trabajador_jornada_hoy_light.png)

### Estados especiales

La aplicación detecta automáticamente situaciones en las que el trabajador no debe fichar y muestra un mensaje en lugar de los botones:

| Situación | Mensaje mostrado |
|---|---|
| Fin de semana | Hoy es fin de semana, ¡descansa! |
| Vacaciones | Hoy estás de vacaciones |
| Festivo | Hoy es festivo |
| Día libre | Hoy tienes el día libre |
| Sin horario asignado | Contacta con el administrador |
| Jornada completada | ¡Jornada completada, hasta mañana! |

### Perfil

El trabajador puede consultar sus datos personales y laborales: nombre, email, DNI, teléfono, dirección, fecha de nacimiento, departamento, puesto y fecha de incorporación.

![Mi perfil](img/11_trabajador_perfil_light.png)

### Vacaciones

Muestra los días de vacaciones totales, gastados y disponibles, junto con el formulario para enviar nuevas solicitudes y el calendario de festivos nacionales del año en curso obtenido de la API Nager.Date.

![Vacaciones — solicitud](img/13_trabajador_vacaciones_solicitud.png)

![Vacaciones — historial y festivos nacionales](img/14_trabajador_vacaciones_historial_festivos.png)

---

## Diseño responsive

La aplicación está construida con un enfoque **Mobile First**. Todos los paneles se adaptan a cualquier tamaño de pantalla. A continuación se muestran algunos ejemplos comparativos entre escritorio y dispositivos más pequeños.

**Dashboard del administrador:**

![Dashboard — escritorio](img/admin_dashboard_escritorio.png)

![Dashboard — tablet](img/admin_dashboard_movil.png)

![Dashboard — móvil](img/admin_dashboard_movil_pequeno.png)

**Gestión de trabajadores:**

![Trabajadores — escritorio](img/admin_trabajadores_escritorio.png)

![Trabajadores — tablet](img/admin_trabajadores_tablet.png)

![Trabajadores — móvil](img/admin_trabajadores_apilado_movil_claro.png)

**Menú de navegación en móvil** — se accede mediante el botón hamburguesa en la cabecera:

![Menú hamburguesa abierto](img/07_admin_menu_hamburguesa_abierto.png)

---

## Modo oscuro

La aplicación incluye un modo oscuro activable desde cualquier pantalla. A continuación se muestran algunos ejemplos comparativos.

**Dashboard:**

![Dashboard — modo claro](img/admin_dashboard_escritorio.png)

![Dashboard — modo oscuro](img/admin_dashboard_oscuro_escritorio.png)

**Solicitudes de vacaciones:**

![Solicitudes — modo claro](img/01_admin_solicitudes_light_desktop.png)

![Solicitudes — modo oscuro](img/02_admin_solicitudes_dark_desktop.png)

**Jornada del trabajador:**

![Jornada — modo claro](img/09_trabajador_jornada_hoy_light.png)

![Jornada — modo oscuro](img/10_trabajador_jornada_hoy_dark.png)
