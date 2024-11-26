<?php
session_start();

// Headers para asegurar respuesta JSON
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

try {
    $logFile = BASE_PATH . '\\logs\\dbf2sql.log';
    $response = [
        'completed' => false,
        'progress' => 0,
        'log' => '',
        'error' => null
    ];

    if (file_exists($logFile)) {
        $log = file_get_contents($logFile);
        if ($log === false) {
            throw new Exception("No se pudo leer el archivo de log");
        }

        $response['log'] = mb_convert_encoding($log, 'UTF-8', 'auto');
        
        // Contar tablas procesadas
        $totalTables = isset($_SESSION['total_tables']) ? $_SESSION['total_tables'] : 1;
        $processedTables = substr_count($log, 'Procesando tabla');
        
        // Calcular progreso
        $response['progress'] = min(round(($processedTables / $totalTables) * 100), 100);
        
        // Verificar si está completado
        if (strpos($log, 'DONE') !== false) {
            $response['completed'] = true;
            $response['progress'] = 100;
        }
    }

    // Asegurar que la salida es JSON válido
    $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonResponse === false) {
        throw new Exception("Error al convertir respuesta a JSON: " . json_last_error_msg());
    }
    
    echo $jsonResponse;

} catch (Exception $e) {
    // En caso de error, asegurar que también devolvemos JSON válido
    echo json_encode([
        'completed' => false,
        'progress' => 0,
        'log' => '',
        'error' => $e->getMessage()
    ]);
}