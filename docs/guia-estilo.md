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

## 2. Color

### Colores principales
<div align="center">
<img src="timetrack_coloresprincipales.png" alt="Paleta de colores TimeTrack" width="600">
</div>
La aplicación usa una gama de verdes oscuros como color corporativo,
transmitiendo seriedad y confianza. Se definen tres niveles:

- **Color principal** (`#0F6E56`): es el color de marca. Se usa en la
  cabecera, el pie de página, los títulos de sección, los botones
  primarios y los bordes de elementos destacados.
- **Color acento** (`#1D9E75`): versión más clara del principal. Se usa
  en los estados hover de botones y enlaces de navegación.
- **Color acento suave** (`#5DCAA5`): versión muy clara. Se usa en
  fondos de badges, textos secundarios de la cabecera y detalles suaves.

### Colores de fondo y superficie

<div align="center">
<img src="timetrack_colores_fondo_superficie.png" alt="Paleta de colores de fondo de TimeTrack" width="600">
</div>

Se distinguen dos niveles de fondo para crear profundidad visual:

- **Color fondo** (`#F4F7F5`): fondo general de toda la aplicación.
  Es un blanco roto con un toque verde muy suave.
- **Color superficie** (`#FFFFFF`): fondo de tarjetas, formularios y
  tablas. El contraste entre superficie y fondo ayuda a separar
  visualmente los bloques de contenido.

### Colores de texto

<div align="center">
<img src="timetrack_colores_texto.png" alt="Paleta de colores para los textos en TimeTrack" width="600">
</div>

- **Color texto** (`#1A2E26`): texto principal. Es un negro verdoso
  oscuro, más suave que el negro puro.
- **Color texto apagado** (`#6A8F82`): texto secundario, etiquetas de
  formulario y placeholders.


### Colores de estado

<div align="center">
<img src="timetrack_colores_estado.png" alt="Paleta de colores de estados de TimeTrack" width="600">
</div>

Para comunicar el resultado de las acciones del usuario se usan cuatro
colores de estado estándar, independientes de la paleta corporativa:

- **Información** (`#2563EB`): mensajes informativos y el botón de
  acceso al horario del trabajador.
- **Advertencia** (`#D97706`): alertas y avisos, como solicitudes
  pendientes de aprobar en el dashboard.
- **Error** (`#DC2626`): errores de validación, mensajes negativos
  y el botón de borrar trabajadores.
- **Neutro** (`#6B7280`): estados desactivados o sin relevancia.

## 3. Tipografía

TimeTrack usa dos fuentes cargadas desde Google Fonts, declaradas al
inicio de `css/style.css` mediante `@import`:

### Fuentes utilizadas

**Inter** es la fuente principal de la aplicación. Se usa en todo el
texto de la interfaz: menús, formularios, tablas, botones y párrafos.
Es una fuente sans-serif diseñada específicamente para pantallas,
con una legibilidad muy alta en tamaños pequeños. Se cargan los pesos
400 (normal) y 500 (medio).

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=JetBrains+Mono&display=swap');
</style>

<div style="padding: 1.5rem; background: #F4F7F5; border-radius: 8px; margin-bottom: 16px;">

  <p style="font-size: 11px; color: #6A8F82; letter-spacing: 1px; margin-bottom: 12px;">INTER — PESO 400 · TEXTO GENERAL</p>
  <p style="font-family: 'Inter', sans-serif; font-weight: 400; font-size: 22px; color: #1A2E26; margin: 0 0 6px;">El control horario de tu empresa</p>
  <p style="font-family: 'Inter', sans-serif; font-weight: 400; font-size: 13px; color: #6A8F82; margin: 0;">Texto secundario · etiquetas · descripciones de formulario</p>

  <hr style="border: none; border-top: 1px solid #C8DDD5; margin: 1.5rem 0;">

  <p style="font-size: 11px; color: #6A8F82; letter-spacing: 1px; margin-bottom: 12px;">INTER — PESO 500 · TÍTULOS Y BOTONES</p>
  <p style="font-family: 'Inter', sans-serif; font-weight: 500; font-size: 22px; color: #1A2E26; margin: 0 0 6px;">Trabajadores · Informes · Horarios</p>
  <p style="font-family: 'Inter', sans-serif; font-weight: 500; font-size: 13px; color: #6A8F82; margin: 0;">Cabeceras de tabla · navegación · botones de acción</p>

</div>

**JetBrains Mono** Se usa en el reloj en
tiempo real, las horas de fichaje y los números del dashboard. Se eligió
porque sus dígitos tienen un aspecto similar al de un reloj digital,
lo que encaja visualmente con el propósito de la aplicación.

<div style="padding: 1.5rem; background: #F4F7F5; border-radius: 8px;">

  <p style="font-size: 11px; color: #6A8F82; letter-spacing: 1px; margin-bottom: 12px;">JETBRAINS MONO · DATOS NUMÉRICOS Y RELOJ</p>
  <p style="font-family: 'JetBrains Mono', monospace; font-size: 36px; color: #1A2E26; margin: 0 0 6px;">08:32:47</p>
  <p style="font-family: 'JetBrains Mono', monospace; font-size: 16px; color: #1A2E26; margin: 0 0 6px;">09:00 · 14:00 · 18:30</p>
  <p style="font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #6A8F82; margin: 0;">Horas de fichaje · números del dashboard · datos de jornada</p>

</div>



### Tamaño base

El tamaño de texto base se define en el `body` y varía según el
dispositivo siguiendo la estrategia Mobile First:

- **Móvil:** 12px
- **Tablet** (desde 768px): 13px
- **Escritorio** (desde 1024px): 14px

El resto de elementos usan tamaños relativos (`rem` o `px`) partiendo
de esta base, de forma que toda la tipografía escala proporcionalmente
según el dispositivo.

### Pesos tipográficos

Se usan únicamente dos pesos para mantener la coherencia visual:

- **400 (normal):** texto general, descripciones, contenido de tablas.
- **500 (medio):** títulos, etiquetas importantes, botones y datos
  destacados. Aporta jerarquía sin necesidad de usar negrita completa.

### Variables CSS

Las fuentes están referenciadas mediante variables para facilitar
posibles cambios futuros:

- `--fuente-cuerpo`: aplicada al `body` y heredada por todos los
  elementos de la interfaz.
- `--fuente-mono`: aplicada explícitamente a los elementos que
  muestran datos numéricos o de tiempo.