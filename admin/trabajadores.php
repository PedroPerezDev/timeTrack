<?php
/*
 * Gestión de trabajadores - Panel de administrador
 * Permite buscar, crear, modificar y borrar trabajadores
 * Solo accesible para usuarios con rol 'admin'
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "admin") {
    header("Location: ../index.php");
    exit;
}

include "../config.php";

$conexion = conectar();

//-----------------------------------------------------|
//------- FUNCIÓN PARA PINTAR LA TABLA --------------- |
//-----------------------------------------------------|

function mostrarTabla($resultado) {

    echo "<div class='tabla-wrapper'>
    <table>
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
        </tr>";

    while ($fila = $resultado->fetch_assoc()) {

        $foto_html = !empty($fila['foto'])
            ? "<img src='/timetrack/uploads/fotos_trabajadores/" . $fila['foto'] . "' width='40' style='border-radius:50%'>"
            : "Sin foto";

        echo "<tr>
            <td>" . $fila['id'] . "</td>
            <td>" . $foto_html . "</td>
            <td>" . $fila['nombre'] . "</td>
            <td>" . $fila['apellidos'] . "</td>
            <td>" . $fila['email'] . "</td>
            <td>" . $fila['dni'] . "</td>
            <td>" . $fila['telefono'] . "</td>
            <td>" . $fila['departamento'] . "</td>
            <td>" . $fila['puesto'] . "</td>
            <td>" . $fila['dias_vacaciones_totales'] . " días</td>
            <td class='acciones'>

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

    echo "</table></div>";
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
    //------------------- ALTA TRABAJADOR -----------------|
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
            echo "<p style='color:red'>Nombre, apellidos, email y contraseña son obligatorios</p>";

        } else {

            $check = $conexion->query("SELECT * FROM usuarios WHERE email = '$email'");

            if ($check->num_rows > 0) {
                echo "<p style='color:red'>Ya existe un trabajador con ese email</p>";

            } else {

                $foto = "";
                if (!empty($_FILES['foto']['name'])) {
                    $nombre_foto = time() . "_" . $_FILES['foto']['name'];
                    $ruta_foto   = "../uploads/fotos_trabajadores/" . $nombre_foto;
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_foto)) {
                        $foto = $nombre_foto;
                    } else {
                        echo "<p style='color:red'>Error al subir la foto</p>";
                    }
                }

                $insertar = $conexion->query("INSERT INTO usuarios 
                    (nombre, apellidos, email, password, rol, dni, telefono, direccion, 
                    fecha_nacimiento, fecha_incorporacion, foto, departamento, puesto, dias_vacaciones_totales)
                    VALUES 
                    ('$nombre', '$apellidos', '$email', '$password', 'trabajador', '$dni', '$telefono', 
                    '$direccion', '$fecha_nac', '$fecha_inc', '$foto', '$departamento', '$puesto', '$dias_vac')");

                if ($insertar) {
                    echo "<p style='color:green'>Trabajador <b>$nombre $apellidos</b> dado de alta correctamente</p>";
                } else {
                    echo "<p style='color:red'>Error al dar de alta: " . $conexion->error . "</p>";
                }
            }
        }
    }

    //-----------------------------------------------------|
    //----------------- BORRADO TRABAJADOR ----------------|
    //-----------------------------------------------------|

    if (isset($_POST['borrar'])) {

        $id = $_POST['id'];

        $resultado_foto = $conexion->query("SELECT foto FROM usuarios WHERE id = '$id'");
        $fila_foto      = $resultado_foto->fetch_assoc();

        if (!empty($fila_foto['foto'])) {
            unlink("../uploads/fotos_trabajadores/" . $fila_foto['foto']);
        }

        $borrar = $conexion->query("DELETE FROM usuarios WHERE id = '$id'");

        if ($borrar) {
            echo "<p style='color:green'>Trabajador borrado correctamente</p>";
        } else {
            echo "<p style='color:red'>Error al borrar: " . $conexion->error . "</p>";
        }
    }

    //-----------------------------------------------------|
    //---------------- MODIFICAR TRABAJADOR ---------------|
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
            echo "<p style='color:red'>Nombre, apellidos y email son obligatorios</p>";

        } else {

            $sql_foto = "";
            if (!empty($_FILES['foto']['name'])) {

                $resultado_foto = $conexion->query("SELECT foto FROM usuarios WHERE id = '$id'");
                $fila_foto      = $resultado_foto->fetch_assoc();

                if (!empty($fila_foto['foto'])) {
                    unlink("../uploads/fotos_trabajadores/" . $fila_foto['foto']);
                }

                $nombre_foto = time() . "_" . $_FILES['foto']['name'];
                $ruta_foto   = "../uploads/fotos_trabajadores/" . $nombre_foto;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_foto)) {
                    $sql_foto = ", foto = '$nombre_foto'";
                }
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
                echo "<p style='color:green'>Trabajador actualizado correctamente</p>";
            } else {
                echo "<p style='color:red'>Error al actualizar: " . $conexion->error . "</p>";
            }
        }
    }

    //-----------------------------------------------------|
    //------------ BUSCADOR DE TRABAJADORES ---------------|
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
    //------------ RESULTADO DE LA BÚSQUEDA --------------|
    //-----------------------------------------------------|

    if (isset($_POST['buscar'])) {

        $resultado = null;

        if (!empty($_POST['buscar_id'])) {
            $id_buscar = $_POST['buscar_id'];
            $resultado = $conexion->query("SELECT * FROM usuarios 
                WHERE id = '$id_buscar' AND rol = 'trabajador'");

        } elseif (!empty($_POST['buscar_nombre'])) {
            $nombre_buscar = $_POST['buscar_nombre'];
            $resultado = $conexion->query("SELECT * FROM usuarios 
                WHERE (nombre LIKE '%$nombre_buscar%' OR apellidos LIKE '%$nombre_buscar%') 
                AND rol = 'trabajador'");

        } else {
            echo "<p style='color:red'>Introduce un nombre o selecciona un trabajador</p>";
        }

        if ($resultado && $resultado->num_rows > 0) {
            mostrarTabla($resultado);
        } elseif ($resultado) {
            echo "<p style='color:red'>No se ha encontrado ningún trabajador</p>";
        }
    }

    //-----------------------------------------------------|
    //------------- VER TODOS PAGINADO -------------------|
    //-----------------------------------------------------|

    if (isset($_POST['ver_todos']) || isset($_GET['pagina'])) {

        $regxpag = 5;

        $total          = $conexion->query("SELECT * FROM usuarios WHERE rol = 'trabajador'");
        $totalRegistros = $total->num_rows;
        $totalPaginas   = ceil($totalRegistros / $regxpag);

        if (isset($_GET['pagina'])) {
            $pagina = intval($_GET['pagina']);
        } else {
            $pagina = 1;
        }

        $posInicial = ($pagina - 1) * $regxpag;

        $resultado = $conexion->query("SELECT * FROM usuarios 
            WHERE rol = 'trabajador' 
            ORDER BY apellidos ASC
            LIMIT $posInicial, $regxpag");

        if ($resultado->num_rows > 0) {
            mostrarTabla($resultado);
        } else {
            echo "<p style='color:red'>No hay trabajadores registrados</p>";
        }

        if ($pagina <= 1) {
            $anterior = $totalPaginas;
        } else {
            $anterior = $pagina - 1;
        }

        echo "<div class='paginacion'>";
        echo "<a href='trabajadores.php?pagina=" . $anterior . "'> &lt; </a>";

        for ($i = 1; $i <= $totalPaginas; $i++) {
            echo "<a href='trabajadores.php?pagina=" . $i . "'>" . $i . "</a>";
        }

        if ($pagina >= $totalPaginas) {
            $siguiente = 1;
        } else {
            $siguiente = $pagina + 1;
        }

        echo "<a href='trabajadores.php?pagina=" . $siguiente . "'> &gt; </a>";
        echo "</div>";
    }

    //-----------------------------------------------------|
    //--------- FORMULARIO DE MODIFICACIÓN ---------------|
    //-----------------------------------------------------|

    if (isset($_POST['ver_modificar'])) {

        $id            = $_POST['id_modificar'];
        $resultado_mod = $conexion->query("SELECT * FROM usuarios WHERE id = '$id'");

        if ($resultado_mod->num_rows == 1) {

            $fila = $resultado_mod->fetch_assoc();

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

            if (!empty($fila['foto'])) {
                echo "<img src='/timetrack/uploads/fotos_trabajadores/" . $fila['foto'] . "' width='80' style='border-radius:var(--radio-mediano);display:block;margin-top:6px'>";
            } else {
                echo "<p>Sin foto</p>";
            }

            echo "
            <label>Cambiar foto (opcional)</label>
            <input type='file' name='foto' accept='image/*'>

            <input type='submit' name='actualizar' value='Actualizar trabajador'>
            </fieldset>
            </form>";
        }
    }

    //-----------------------------------------------------|
    //--------- FORMULARIO DE ALTA (AL FINAL) ------------|
    //-----------------------------------------------------|

    desconectar($conexion);
    ?>

    <?php if (isset($_GET['nuevo'])): ?>

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

    <?php endif; ?>

    <!-- Botón de nuevo trabajador al final de la página -->
    <a href="trabajadores.php?nuevo=1">
        <button type="button">+ Nuevo trabajador</button>
    </a>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>