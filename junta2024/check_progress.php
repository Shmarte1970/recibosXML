<?php
session_start();

// Asegurar que solo enviamos headers de JSON
header('Content-Type: application/json');

try {
    $logFile = BASE_PATH . '\\logs\\dbf2sql.log';
    $response = [
        'completed' => false,
        'progress' => 0,
        'log' => '',
        'error' => null
    ];

    if (file_exists($logFile)) {
        $log = @file_get_contents($logFile);
        
        if ($log !== false) {
            // Limpiar cualquier carácter que pueda causar problemas con JSON
            $log = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $log);
            $response['log'] = $log;
            
            // Calcular progreso
            $totalTables = isset($_SESSION['total_tables']) ? $_SESSION['total_tables'] : 1;
            $processedTables = substr_count($log, 'Procesando tabla');
            $response['progress'] = min(round(($processedTables / $totalTables) * 100), 100);
            
            // Verificar si está completado
            $response['completed'] = (strpos($log, 'DONE') !== false);
            if ($response['completed']) {
                $response['progress'] = 100;
            }
        }
    }

    // Asegurar que el JSON es válido antes de enviarlo
    $jsonResponse = json_encode($response);
    if ($jsonResponse === false) {
        throw new Exception("Error codificando JSON: " . json_last_error_msg());
    }

    echo $jsonResponse;

} catch (Exception $e) {
    echo json_encode([
        'completed' => false,
        'progress' => 0,
        'log' => '',
        'error' => $e->getMessage()
    ]);
}