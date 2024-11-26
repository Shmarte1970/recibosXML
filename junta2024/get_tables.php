<?php
session_start();
require_once 'DbfConverter.class.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['path'])) {
        throw new Exception('No se especificó ruta');
    }

    $path = rtrim($_GET['path'], '\\') . '\\';
    
    if (!is_dir($path)) {
        throw new Exception("El directorio no existe: " . $path);
    }

    // Actualizar la ruta en la sesión
    $_SESSION['dbf_path'] = $path;

    // Crear una instancia temporal del convertidor para obtener la lista de tablas
    $converter = new DbfConverter($path, BASE_PATH . '\\exports', true);
    $tables = $converter->getDbfTables();

    echo json_encode([
        'success' => true,
        'path' => $path,
        'tables' => $tables
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}