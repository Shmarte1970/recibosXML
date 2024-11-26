<?php
session_start();
require_once 'DbfConverter.class.php';

// Headers para AJAX y JSON
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Logging para debug
error_log("Petición recibida en conver.php");
error_log("POST data: " . print_r($_POST, true));

try {
    if (!isset($_POST['tables']) || !is_array($_POST['tables'])) {
        throw new Exception('No se seleccionaron tablas');
    }

    $dbfPath = BASE_PATH . '\\data';
    $sqlOutputPath = BASE_PATH . '\\exports';
    $dropTables = true;

    $converter = new DbfConverter($dbfPath, $sqlOutputPath, $dropTables);
    
    // Validar tablas seleccionadas
    $selectedTables = array_filter($_POST['tables'], function($table) {
        return !empty(trim($table)) && preg_match('/^[a-zA-Z0-9_]+$/', $table);
    });

    if (empty($selectedTables)) {
        throw new Exception('No se seleccionaron tablas válidas');
    }

    // Guardar el número total de tablas para el progreso
    $_SESSION['total_tables'] = count($selectedTables);

    // Realizar la conversión
    $result = $converter->convertSelected($selectedTables);
    
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Error en conver.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}