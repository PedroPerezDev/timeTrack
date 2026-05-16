<?php
/*
 * Gestión de trabajadores - Panel de administrador
 * Permite buscar, crear, modificar y borrar trabajadores
 */

include "../includes/funciones.php";
verificarSesion('admin');

include "../config.php";

$conexion = conectar();


/*
 * Genera la tabla HTML con el listado de trabajadores.
 */
function mostrarTabla($resultado) {

    echo "<div class='tabla-wrapper'>
    <table class='tabla-apilable'>
        <thead>
        <tr>
            <th>ID</th>
            <th>Foto</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>DNI</th>
            <th>Teléfono</th>
            <th>Departamento</th>
            <th>Puesto</th>
            <th>Vacaciones</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>";

    while ($fila = $resultado->fetch_assoc()) {

        echo "<tr>
            <td data-label='ID'>" . $fila['id'] . "</td>
            <td data-label='Foto'>" . mostrarFoto($fila['foto'], 40) . "</td>
            <td data-label='Nombre'>" . $fila['nombre'] . "</td>
            <td data-label='Apellidos'>" . $fila['apellidos'] . "</td>
            <td data-label='Email'>" . $fila['email'] . "</td>
            <td data-label='DNI'>" . $fila['dni'] . "</td>
            <td data-label='Teléfono'>" . $fila['telefono'] . "</td>
            <td data-label='Departamento'>" . $fila['departamento'] . "</td>
            <td data-label='Puesto'>" . $fila['puesto'] . "</td>
            <td data-label='Vacaciones'>" . $fila['dias_vacaciones_totales'] . " días</td>
            <td data-label='Acciones' class='acciones'>

                <a href='horarios.php?id=" . $fila['id'] . "'>
                    <input type='button' value='Horario' class='btn-horario'>
                </a>

                <form action='trabajadores.php' method='POST' style='display:inline'>
                    <input type='hidden' name='id_modificar' value='" . $fila['id'] . "'>
                    <input type='submit' name='ver_modificar' value='Modificar'>
                </form>

                <form action='trabajadores.php' method='POST' style='display:inline'>
                    <input type='hidden' name='id' value='" . $fila['id'] . "'>
                    <input type='submit' name='borrar' value='Borrar'
                        onclick='return confirm(\"¿Seguro que quieres borrar este trabajador?\")'>
                </form>

            </td>
        </tr>";
    }

    echo "</tbody></table></div>";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión de Trabajadores - TimeTrack</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Gestión de Trabajadores</h2>

    <?php

    //-----------------------------------------------------|
    //---------- ALTA DE TRABAJADOR ----------------------|
    //-----------------------------------------------------|

    if (isset($_POST['alta'])) {

        $nombre       = $_POST['nombre'];
        $apellidos    = $_POST['apellidos'];
        $email        = $_POST['email'];
        $password     = $_POST['password'];
        $dni          = $_POST['dni'];
        $telefono     = $_POST['telefono'];
        $direccion    = $_POST['direccion'];
        $fecha_nac    = $_POST['fecha_nacimiento'];
        $fecha_inc    = $_POST['fecha_incorporacion'];
        $departamento = $_POST['departamento'];
        $puesto       = $_POST['puesto'];
        $dias_vac     = $_POST['dias_vacaciones_totales'];

        if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
            mostrarMensaje("Nombre, apellidos, email y contraseña son obligatorios", 'error');

        } else {

            $check = $conexion->query("SELECT id FROM usuarios WHERE email = '$email'");

            if ($check->num_rows > 0) {
                mostrarMensaje("Ya existe un trabajador con ese email", 'error');

            } else {

                $foto     = subirFoto($_FILES['foto']);
                $insertar = $conexion->query("INSERT INTO usuarios 
                    (nombre, apellidos, email, password, rol, dni, telefono, direccion, 
                    fecha_nacimiento, fecha_incorporacion, foto, departamento, puesto, dias_vacaciones_totales)
                    VALUES 
                    ('$nombre', '$apellidos', '$email', '$password', 'trabajador', '$dni', '$telefono', 
                    '$direccion', '$fecha_nac', '$fecha_inc', '$foto', '$departamento', '$puesto', '$dias_vac')");

                if ($insertar) {
                    mostrarMensaje("Trabajador <b>$nombre $apellidos</b> dado de alta correctamente");
                } else {
                    mostrarMensaje("Error al dar de alta: " . $conexion->error, 'error');
                }
            }
        }
    }

    //-----------------------------------------------------|
    //---------- BORRADO DE TRABAJADOR -------------------|
    //-----------------------------------------------------|

    if (isset($_POST['borrar'])) {

        $id        = $_POST['id'];
        $fila_foto = $conexion->query("SELECT foto FROM usuarios WHERE id = '$id'")->fetch_assoc();

        eliminarFoto($fila_foto['foto']);

        $borrar = $conexion->query("DELETE FROM usuarios WHERE id = '$id'");

        if ($borrar) {
            mostrarMensaje("Trabajador borrado correctamente");
        } else {
            mostrarMensaje("Error al borrar: " . $conexion->error, 'error');
        }
    }

    //-----------------------------------------------------|
    //---------- MODIFICACIÓN DE TRABAJADOR --------------|
    //-----------------------------------------------------|

    if (isset($_POST['actualizar'])) {

        $id           = $_POST['id'];
        $nombre       = $_POST['nombre'];
        $apellidos    = $_POST['apellidos'];
        $email        = $_POST['email'];
        $dni          = $_POST['dni'];
        $telefono     = $_POST['telefono'];
        $direccion    = $_POST['direccion'];
        $fecha_nac    = $_POST['fecha_nacimiento'];
        $fecha_inc    = $_POST['fecha_incorporacion'];
        $departamento = $_POST['departamento'];
        $puesto       = $_POST['puesto'];
        $dias_vac     = $_POST['dias_vacaciones_totales'];

        if (empty($nombre) || empty($apellidos) || empty($email)) {
            mostrarMensaje("Nombre, apellidos y email son obligatorios", 'error');

        } else {

            $sql_foto = "";
            if (!empty($_FILES['foto']['name'])) {
                $fila_foto = $conexion->query("SELECT foto FROM usuarios WHERE id = '$id'")->fetch_assoc();
                eliminarFoto($fila_foto['foto']);
                $nombre_foto = subirFoto($_FILES['foto']);
                if ($nombre_foto) $sql_foto = ", foto = '$nombre_foto'";
            }

            $update = $conexion->query("UPDATE usuarios SET
                nombre              = '$nombre',
                apellidos           = '$apellidos',
                email               = '$email',
                dni                 = '$dni',
                telefono            = '$telefono',
                direccion           = '$direccion',
                fecha_nacimiento    = '$fecha_nac',
                fecha_incorporacion = '$fecha_inc',
                departamento        = '$departamento',
                puesto              = '$puesto',
                dias_vacaciones_totales = '$dias_vac'
                $sql_foto
                WHERE id = '$id'");

            if ($update) {
                mostrarMensaje("Trabajador actualizado correctamente");
            } else {
                mostrarMensaje("Error al actualizar: " . $conexion->error, 'error');
            }
        }
    }

    //-----------------------------------------------------|
    //---------- BUSCADOR DE TRABAJADORES ----------------|
    //-----------------------------------------------------|

    ?>

    <form action="trabajadores.php" method="POST">
        <fieldset>
            <legend>Buscar trabajador</legend>

            <label>Buscar por nombre</label>
            <input type="text" name="buscar_nombre" placeholder="Escribe el nombre..."
                value="<?php echo isset($_POST['buscar_nombre']) ? $_POST['buscar_nombre'] : ''; ?>">

            <label>O selecciona de la lista</label>
            <select name="buscar_id">
                <option value="">-- Selecciona un trabajador --</option>
                <?php
                $todos = $conexion->query("SELECT id, nombre, apellidos FROM usuarios WHERE rol = 'trabajador' ORDER BY apellidos ASC");
                while ($t = $todos->fetch_assoc()) {
                    $selected = (isset($_POST['buscar_id']) && $_POST['buscar_id'] == $t['id']) ? "selected" : "";
                    echo "<option value='" . $t['id'] . "' $selected>" . $t['apellidos'] . ", " . $t['nombre'] . "</option>";
                }
                ?>
            </select>

            <input type="submit" name="buscar" value="Buscar">
            <input type="submit" name="ver_todos" value="Ver todos">
        </fieldset>
    </form>

    <?php

    //-----------------------------------------------------|
    //---------- RESULTADO DE BÚSQUEDA -------------------|
    //-----------------------------------------------------|

    if (isset($_POST['buscar'])) {

        $resultado = null;

        if (!empty($_POST['buscar_id'])) {
            $resultado = $conexion->query("SELECT * FROM usuarios 
                WHERE id = '" . $_POST['buscar_id'] . "' AND rol = 'trabajador'");

        } elseif (!empty($_POST['buscar_nombre'])) {
            $nombre_buscar = $_POST['buscar_nombre'];
            $resultado = $conexion->query("SELECT * FROM usuarios 
                WHERE (nombre LIKE '%$nombre_buscar%' OR apellidos LIKE '%$nombre_buscar%') 
                AND rol = 'trabajador'");

        } else {
            mostrarMensaje("Introduce un nombre o selecciona un trabajador", 'error');
        }

        if ($resultado && $resultado->num_rows > 0) {
            mostrarTabla($resultado);
        } elseif ($resultado) {
            mostrarMensaje("No se ha encontrado ningún trabajador", 'error');
        }
    }

    //-----------------------------------------------------|
    //---------- LISTADO PAGINADO ------------------------|
    //-----------------------------------------------------|

    if (isset($_POST['ver_todos']) || isset($_GET['pagina'])) {

        $regxpag        = 5;
        $total          = $conexion->query("SELECT id FROM usuarios WHERE rol = 'trabajador'");
        $totalRegistros = $total->num_rows;
        $totalPaginas   = ceil($totalRegistros / $regxpag);
        $pagina         = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $posInicial     = ($pagina - 1) * $regxpag;

        $resultado = $conexion->query("SELECT * FROM usuarios 
            WHERE rol = 'trabajador' 
            ORDER BY apellidos ASC
            LIMIT $posInicial, $regxpag");

        if ($resultado->num_rows > 0) {
            mostrarTabla($resultado);
        } else {
            mostrarMensaje("No hay trabajadores registrados", 'error');
        }

        $anterior  = $pagina <= 1 ? $totalPaginas : $pagina - 1;
        $siguiente = $pagina >= $totalPaginas ? 1 : $pagina + 1;

        echo "<div class='paginacion'>";
        echo "<a href='trabajadores.php?pagina=$anterior'> &lt; </a>";
        for ($i = 1; $i <= $totalPaginas; $i++) {
            echo "<a href='trabajadores.php?pagina=$i'>$i</a>";
        }
        echo "<a href='trabajadores.php?pagina=$siguiente'> &gt; </a>";
        echo "</div>";
    }

    //-----------------------------------------------------|
    //---------- FORMULARIO DE MODIFICACIÓN --------------|
    //-----------------------------------------------------|

    if (isset($_POST['ver_modificar'])) {

        $id            = $_POST['id_modificar'];
        $resultado_mod = $conexion->query("SELECT * FROM usuarios WHERE id = '$id'");

        if ($resultado_mod->num_rows == 1) {

            $fila = $resultado_mod->fetch_assoc();

            echo "<div id='form-modificar' style='display:none'>";
            echo "
            <h3>Modificar trabajador</h3>
            <form action='trabajadores.php' method='POST' enctype='multipart/form-data'>
            <fieldset>
            <legend>MODIFICAR TRABAJADOR</legend>

            <input type='hidden' name='id' value='" . $fila['id'] . "'>

            <label>Nombre *</label>
            <input type='text' name='nombre' value='" . $fila['nombre'] . "'>

            <label>Apellidos *</label>
            <input type='text' name='apellidos' value='" . $fila['apellidos'] . "'>

            <label>Email *</label>
            <input type='email' name='email' value='" . $fila['email'] . "'>

            <label>DNI</label>
            <input type='text' name='dni' value='" . $fila['dni'] . "'>

            <label>Teléfono</label>
            <input type='text' name='telefono' value='" . $fila['telefono'] . "'>

            <label>Dirección</label>
            <input type='text' name='direccion' value='" . $fila['direccion'] . "'>

            <label>Fecha de nacimiento</label>
            <input type='date' name='fecha_nacimiento' value='" . $fila['fecha_nacimiento'] . "'>

            <label>Fecha de incorporación</label>
            <input type='date' name='fecha_incorporacion' value='" . $fila['fecha_incorporacion'] . "'>

            <label>Departamento</label>
            <input type='text' name='departamento' value='" . $fila['departamento'] . "'>

            <label>Puesto</label>
            <input type='text' name='puesto' value='" . $fila['puesto'] . "'>

            <label>Días de vacaciones</label>
            <input type='number' name='dias_vacaciones_totales' value='" . $fila['dias_vacaciones_totales'] . "'>

            <label>Foto actual</label>";

            echo mostrarFoto($fila['foto'], 80, 'border-radius:var(--radio-mediano);display:block;margin-top:6px');

            echo "
            <label>Cambiar foto (opcional)</label>
            <input type='file' name='foto' accept='image/*'>

            <input type='submit' name='actualizar' value='Actualizar trabajador'>
            </fieldset>
            </form>";
            echo "</div>";
        }
    }

    desconectar($conexion);
    ?>

    <!-- Formulario de alta oculto por defecto -->
    <div id="form-nuevo" style="display:none">
        <h3>Nuevo trabajador</h3>
        <form id="form-alta" action="trabajadores.php" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>NUEVO TRABAJADOR</legend>

            <label>Nombre *</label>
            <input type="text" name="nombre" placeholder="Nombre">

            <label>Apellidos *</label>
            <input type="text" name="apellidos" placeholder="Apellidos">

            <label>Email *</label>
            <input type="email" name="email" placeholder="email@ejemplo.com">

            <label>Contraseña *</label>
            <input type="password" name="password" placeholder="Contraseña">

            <label>DNI</label>
            <input type="text" name="dni" placeholder="12345678A">

            <label>Teléfono</label>
            <input type="text" name="telefono" placeholder="600000000">

            <label>Dirección</label>
            <input type="text" name="direccion" placeholder="Calle, número, ciudad">

            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento">

            <label>Fecha de incorporación</label>
            <input type="date" name="fecha_incorporacion">

            <label>Departamento</label>
            <input type="text" name="departamento" placeholder="Departamento">

            <label>Puesto</label>
            <input type="text" name="puesto" placeholder="Puesto de trabajo">

            <label>Días de vacaciones</label>
            <input type="number" name="dias_vacaciones_totales" value="22">

            <label>Foto</label>
            <input type="file" name="foto" accept="image/*">

            <input type="submit" name="alta" value="Dar de alta">
        </fieldset>
        </form>
    </div>

    <button type="button" id="btn-nuevo-trabajador">+ Nuevo trabajador</button>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>
