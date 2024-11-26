<?php
header('Content-Type: application/json');

if (!isset($_GET['table'])) {
    echo json_encode(['error' => 'No se especificó tabla']);
    exit;
}

$table = $_GET['table'];
$dbfPath = 'C:\\CovellogZarca\\data\\' . $table . '.dbf';

if (!file_exists($dbfPath)) {
    echo json_encode(['error' => 'Archivo no encontrado']);
    exit;
}

try {
    $handle = fopen($dbfPath, "rb");
    if (!$handle) {
        throw new Exception('No se pudo abrir el archivo');
    }

    // Leer cabecera DBF
    $header = fread($handle, 32);
    if (strlen($header) != 32) {
        throw new Exception('Error leyendo cabecera');
    }

    $recordCount = unpack("V", substr($header, 4, 4))[1];
    $headerLength = unpack("v", substr($header, 8, 2))[1];
    $fieldCount = floor(($headerLength - 32) / 32);

    // Leer definiciones de campos
    $fields = [];
    for ($i = 0; $i < $fieldCount; $i++) {
        $fieldInfo = fread($handle, 32);
        if (strlen($fieldInfo) != 32) break;
        
        $name = rtrim(substr($fieldInfo, 0, 11), "\x00");
        $type = substr($fieldInfo, 11, 1);
        $length = ord(substr($fieldInfo, 16, 1));
        $decimals = ord(substr($fieldInfo, 17, 1));
        
        $fields[] = [
            'name' => $name,
            'type' => $type,
            'length' => $length,
            'precision' => ($type == 'N' || $type == 'F') ? $decimals : null
        ];
    }

    fclose($handle);

    echo json_encode([
        'success' => true,
        'fields' => $fields,
        'recordCount' => $recordCount
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}