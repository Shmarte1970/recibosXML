<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

try {
    $logFile = 'C:\\CovellogZarca\\dbf2sql.log';
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
        
        // Calcular progreso basado en las líneas "Exporting table"
        if (preg_match_all('/Exporting table/', $log, $matches)) {
            $tablesProcessed = count($matches[0]);
            // Asumimos que cada tabla es aproximadamente el mismo porcentaje del total
            $response['progress'] = min(round(($tablesProcessed / max(1, $_SESSION['total_tables'])) * 100), 100);
        }

        // Verificar si el proceso está completado
        if (strpos($log, 'DONE') !== false) {
            $response['completed'] = true;
            $response['progress'] = 100;
        }
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode([
        'completed' => false,
        'progress' => 0,
        'log' => '',
        'error' => $e->getMessage()
    ]);
}