<?php
/*
 * Obtiene los festivos nacionales y de la Comunidad Valenciana
 * usando la API pública Nager.Date
 * Se llama desde jQuery con AJAX
 * Devuelve los festivos filtrados en formato JSON
 */

session_start();

// Solo accesible si hay sesión activa
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtengo el año actual para la consulta
$anyo = date('Y');

// URL de la API de festivos nacionales de España
$url = "https://date.nager.at/api/v3/PublicHolidays/" . $anyo . "/ES";

/*
 * Uso file_get_contents para llamar a la API externa
 * y obtener los festivos en formato JSON
 */
$respuesta = file_get_contents($url);

if ($respuesta === false) {
    echo json_encode(['error' => 'No se ha podido conectar con la API de festivos']);
    exit;
}

// Convierto el JSON a array de PHP para poder filtrarlo
$todos = json_decode($respuesta, true);

/*
 * Filtro los festivos para quedarme solo con:
 * - Los nacionales (sin counties, aplican a toda España)
 * - Los específicos de la Comunidad Valenciana (ES-VC)
 */
$filtrados = array_filter($todos, function($festivo) {
    // Si no tiene counties es nacional, lo incluyo siempre
    if (empty($festivo['counties'])) {
        return true;
    }
    // Si tiene counties compruebo si está ES-VC entre ellos
    return in_array('ES-VC', $festivo['counties']);
});

// Devuelvo el array filtrado como JSON
// array_values resetea los índices del array
echo json_encode(array_values($filtrados));
?>