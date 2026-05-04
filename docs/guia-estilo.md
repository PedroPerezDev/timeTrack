# Guía de Estilo Visual

## 1. Estructura

TimeTrack sigue una estructura de página clásica dividida en tres zonas:
**cabecera**, **contenido principal** y **pie de página**.

### Cabecera

La cabecera está definida en `includes/header.php` y se incluye en todas
las páginas de la aplicación tras iniciar sesión. Contiene tres elementos:

- **Logo y nombre:** imagen del logotipo junto al texto "TimeTrack".
- **Usuario conectado:** muestra un saludo con el nombre de la sesión activa.
- **Navegación:** menú de enlaces que cambia según el rol del usuario.

En móvil los tres elementos se apilan en columna. A partir de 768px
se reorganizan en una sola fila con el logo a la izquierda y el menú
a la derecha.

### Contenido principal

El elemento `<main>` ocupa todo el espacio disponible entre la cabecera
y el pie de página gracias a `flex: 1`. Tiene un padding que aumenta
progresivamente según el tamaño de pantalla: 16px en móvil, 24px en
tablet y 32px en escritorio. En pantallas grandes el contenido queda
centrado con un ancho máximo de 1400px.

### Pie de página

El pie de página está definido en `includes/footer.php` y se incluye
en todas las páginas. Muestra el nombre de la aplicación, el año actual
generado dinámicamente con PHP y el nombre del desarrollador. Siempre
queda pegado al fondo de la pantalla gracias a la estrategia
`flex-direction: column` y `min-height: 100vh` aplicada al `<body>`.

### Páginas sin cabecera

La página de inicio de sesión (`index.php`) no incluye la cabecera ni
el pie de la aplicación, ya que el usuario todavía no ha autenticado.
Tiene su propio layout centrado verticalmente en pantalla completa.