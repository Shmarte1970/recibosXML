<?php
// Permitir acceso desde cualquier origen
header("Access-Control-Allow-Origin: *");
// Permitir los métodos necesarios
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
// Permitir los encabezados necesarios
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar la solicitud OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el dato enviado en el cuerpo de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);
    $dato = $input['dato'];

    // Devolver el mismo dato en la respuesta
    echo json_encode(array("dato" => $dato));
} else {
    echo json_encode(array("error" => "Método no permitido"));
}
?>

