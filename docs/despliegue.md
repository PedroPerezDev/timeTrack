# Despliegue

[← Volver al índice](index.md)

---

## Entornos

La aplicación funciona en dos entornos con la misma configuración de rutas, por lo que no es necesario cambiar ningún archivo al pasar de local a producción.

### Local — XAMPP

El entorno de desarrollo usa XAMPP en Windows con un VirtualHost configurado en Apache:

```apache
<VirtualHost *:80>
    ServerName timetrack.local
    DocumentRoot "C:/xampp/htdocs/timetrack"

    <Directory "C:/xampp/htdocs/timetrack">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

El archivo `hosts` de Windows apunta el dominio local al servidor:

```
127.0.0.1 timetrack.local
```

La aplicación se accede en `http://timetrack.local`.

### Producción — Hosting con Plesk

La aplicación está desplegada en `https://mytimetrack.es` en el hosting de loading.es, gestionado mediante el panel Plesk.

El archivo `config.php` se crea manualmente en cada entorno con las credenciales correspondientes y está excluido del repositorio mediante `.gitignore`. En el repositorio se incluye `config.ejemplo.php` como plantilla.

---

## Flujo de actualización

El proceso para actualizar la aplicación en producción es:

1. Desarrollar y probar en local (`http://timetrack.local`)
2. Hacer commit de los cambios en la rama `desarrollo`
3. Hacer merge a `main` cuando la funcionalidad está estable
4. Push a GitHub
5. Desde Plesk, hacer Pull del repositorio

No es necesario tocar rutas, configuraciones ni archivos al subir los cambios.

---

## Control de versiones

### Ramas

| Rama | Uso |
|---|---|
| `main` | Versión estable. Siempre es lo que hay en producción |
| `desarrollo` | Trabajo activo. Aquí se desarrollan las nuevas funcionalidades |

### Etiquetas

| Tag | Descripción |
|---|---|
| `v1.0` | Primera versión funcional: login, fichaje con AJAX y gestión básica |
| `v2.0` | Versión con responsive completo, modo oscuro y refactoring del código |

### Archivos excluidos del repositorio

El archivo `.gitignore` excluye:

- `config.php` — credenciales de la base de datos
- `uploads/fotos_trabajadores/` — fotos subidas por los usuarios

---

## Documentación

La documentación del proyecto está publicada en GitHub Pages y accesible en:

[https://pedroperezdev.github.io/timeTrack](https://pedroperezdev.github.io/timeTrack)

Los archivos de documentación están en la carpeta `docs/` del repositorio en formato Markdown. GitHub Pages los renderiza automáticamente con el tema configurado en `docs/_config.yml`.
