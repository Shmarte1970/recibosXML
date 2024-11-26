<?php
require 'vendor/autoload.php'; // Asegúrate de que esta ruta es correcta para cargar SendGrid

function generarInforme($ruta_completa, $datosDefectuosos) {
    $directorio = dirname($ruta_completa);
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }

    $archivo = fopen($ruta_completa, "w");
    if ($archivo === false) {
        return "Error: No se pudo crear el archivo de informe.";
    }
    
    if (empty($datosDefectuosos)) {
        fwrite($archivo, "No se encontraron datos defectuosos.\n");
    } else {
        foreach ($datosDefectuosos as $row) {
            foreach ($row as $campo => $valor) {
                fwrite($archivo, "$campo: $valor\n");
            }
            fwrite($archivo, "\n");
        }
    }
    
    $cantidad_registros = count($datosDefectuosos);
    fwrite($archivo, "Cantidad de registros: $cantidad_registros\n");
    fclose($archivo);
    
    return "Informe generado correctamente en: $ruta_completa";
}

function enviarInforme($ruta_completa, $datosDefectuosos = null) {
    if (!file_exists($ruta_completa) && $datosDefectuosos !== null) {
        $resultado = generarInforme($ruta_completa, $datosDefectuosos);
        if (strpos($resultado, "Error") === 0) {
            return $resultado;
        }
    } elseif (!file_exists($ruta_completa)) {
        return "Error: El archivo de informe no existe y no se proporcionaron datos para generarlo.";
    }

    // ... (resto del código de enviarInforme sin cambios)
}

// Si se llama directamente a este script
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $directorio = __DIR__ . '/informes/';
    $fecha_actual = date("Y-m-d");
    $ruta_completa = $directorio . "RecibosErroneos_" . $fecha_actual . ".txt";
    
    if (isset($_POST['action']) && $_POST['action'] === 'generarInforme') {
        // Asumimos que $datosDefectuosos se pasa via POST
        $datosDefectuosos = isset($_POST['datosDefectuosos']) ? json_decode($_POST['datosDefectuosos'], true) : [];
        $resultado = generarInforme($ruta_completa, $datosDefectuosos);
        echo $resultado;
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'enviarInforme') {
        // Asumimos que $datosDefectuosos se pasa via POST si es necesario
        $datosDefectuosos = isset($_POST['datosDefectuosos']) ? json_decode($_POST['datosDefectuosos'], true) : null;
        $resultado = enviarInforme($ruta_completa, $datosDefectuosos);
        echo $resultado;
        exit;
    }
}
?>