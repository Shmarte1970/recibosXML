<?php
require_once 'index.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['tables']) || !is_array($_POST['tables'])) {
        throw new Exception('No se seleccionaron tablas');
    }

    $dbfPath = 'C:\\CovellogZarca\\data\\';
    $iniFile = 'C:\\CovellogZarca\\param.ini';
    $sqlOutputPath = 'C:\\CovellogZarca\\sqldump.sql';
    $dropTables = true;

    $converter = new DbfConverter($dbfPath, $iniFile, $sqlOutputPath, $dropTables);

    // Limpiar y validar las tablas seleccionadas
    $selectedTables = array_filter($_POST['tables'], function($table) {
        return !empty(trim($table)) && preg_match('/^[a-zA-Z0-9_]+$/', $table);
    });

    if (empty($selectedTables)) {
        throw new Exception('No se seleccionaron tablas válidas');
    }

    // Intentar la conversión
    $result = $converter->convertSelected($selectedTables);
    
    // Agregar información adicional al resultado
    $result['tables'] = $selectedTables;
    
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Error en convert.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'tables' => isset($selectedTables) ? $selectedTables : []
    ]);
}