<?php

session_start();
// Establecer headers para prevenir caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


// Archivo: c:\wamp64\www\junta2024\index.php
set_time_limit(0); // 0 significa sin límite de tiempo
ini_set('max_execution_time', 0);




class DbfConverter
{
    private $dbfPath;
    private $iniFile;
    private $outputPath;
    private $dropTables;

    public function __construct($dbfPath, $iniFile, $outputPath, $dropTables)
    {
        $this->dbfPath = $dbfPath;
        $this->iniFile = $iniFile;
        $this->outputPath = $outputPath;
        $this->dropTables = $dropTables;
    }



    // Obtener lista de tablas DBF
    public function getDbfTables()
    {
        $tables = [];
        $log = "Buscando DBFs en: " . $this->dbfPath . "\n";

        // Asegurarse de que el directorio existe
        if (!is_dir($this->dbfPath)) {
            error_log("El directorio no existe: " . $this->dbfPath);
            return $tables;
        }

        // Listar todos los archivos DBF (ignorando mayúsculas/minúsculas)
        $pattern = $this->dbfPath . '\\*.{dbf,DBF}';
        $files = glob($pattern, GLOB_BRACE);

        $log .= "Archivos encontrados: " . count($files) . "\n";

        foreach ($files as $file) {
            $tableName = pathinfo($file, PATHINFO_FILENAME);
            $filesize = filesize($file);
            $modified = filemtime($file);

            $log .= sprintf(
                "Archivo: %s, Tamaño: %s, Modificado: %s\n",
                $file,
                $this->formatSize($filesize),
                date('Y-m-d H:i:s', $modified)
            );

            $tables[$tableName] = [
                'name' => $tableName,
                'size' => $this->formatSize($filesize),
                'modified' => date('Y-m-d H:i:s', $modified),
                'path' => $file,
                'rawsize' => $filesize // Para ordenar por tamaño si es necesario
            ];
        }

        // Ordenar las tablas por nombre
        ksort($tables);

        // Guardar el log para diagnóstico
        file_put_contents($this->outputPath . 'dbf_scan.log', $log);

        return $tables;
    }

    // Leer estructura del archivo DBF
    public function getTableStructure($tableName)
    {
        try {
            $filePath = $this->dbfPath . '\\' . $tableName . '.dbf';

            if (!file_exists($filePath)) {
                return false;
            }

            // Abrir archivo en modo binario
            $handle = fopen($filePath, "rb");
            if (!$handle) {
                return false;
            }

            // Leer cabecera DBF
            $header = fread($handle, 32);
            if (strlen($header) != 32) {
                fclose($handle);
                return false;
            }

            // Obtener número de registros (bytes 4-7)
            $recordCount = unpack("V", substr($header, 4, 4))[1];

            // Obtener longitud de la cabecera (bytes 8-9)
            $headerLength = unpack("v", substr($header, 8, 2))[1];

            // Calcular número de campos
            $fieldCount = floor(($headerLength - 32) / 32);

            // Leer definiciones de campos
            $structure = [];
            for ($i = 0; $i < $fieldCount; $i++) {
                $fieldInfo = fread($handle, 32);
                if (strlen($fieldInfo) != 32) {
                    break;
                }

                // Nombre del campo (bytes 0-10)
                $fieldName = rtrim(substr($fieldInfo, 0, 11), "\x00");
                // Tipo de campo (byte 11)
                $fieldType = substr($fieldInfo, 11, 1);
                // Longitud del campo (byte 16)
                $fieldLength = ord(substr($fieldInfo, 16, 1));
                // Decimales (byte 17)
                $fieldDecimals = ord(substr($fieldInfo, 17, 1));

                $structure[] = [
                    'name' => $fieldName,
                    'type' => $fieldType,
                    'length' => $fieldLength,
                    'precision' => ($fieldType == 'N' || $fieldType == 'F') ? $fieldDecimals : null
                ];
            }

            fclose($handle);

            return [
                'fields' => $structure,
                'recordCount' => $recordCount
            ];
        } catch (Exception $e) {
            return false;
        }
    }

    public function convertSelected($tableNames)
{
    $result = [
        'success' => true,
        'error' => null,
        'log' => ''
    ];

    try {
        // Primero, generar el archivo INI con las tablas seleccionadas
        $iniResult = $this->generarIni($tableNames);
        if (!$iniResult['success']) {
            throw new Exception("Error generando archivo INI: " . $iniResult['error']);
        }

        // Crear el comando con los parámetros correctos
        // Nota: Los parámetros ahora se pasan como "./data", "sqldump.sql"
        $cmd = "C:\\CovellogZarca\\dbf2sqlSinCreate.exe " . 
               "\"./data\" " . 
               "\"sqldump.sql\"";

        // Ejecutar el comando
        $output = [];
        $returnCode = 0;
        exec($cmd . " 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("Error ejecutando dbf2sqlSinCreate.exe: " . implode("\n", $output));
        }

        // Verificar si se generó el archivo log
        $logFile = 'C:\\CovellogZarca\\dbf2sql.log';
        if (file_exists($logFile)) {
            $result['log'] = file_get_contents($logFile);
        } else {
            $result['log'] = "Proceso completado, pero no se generó archivo de log.";
        }

        // Verificar si se generó el archivo SQL
        $sqlFile = 'C:\\CovellogZarca\\sqldump.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("No se generó el archivo SQL de salida.");
        }

    } catch (Exception $e) {
        $result['success'] = false;
        $result['error'] = $e->getMessage();
        $result['log'] = isset($output) ? implode("\n", $output) : $e->getMessage();
    }

    return $result;
}

    public function getConversionProgress($timestamp)
    {
        $logFile = 'C:\\CovellogZarca\\dbf2sql.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            // Analizar el contenido para determinar el progreso
            return [
                'completed' => strpos($content, 'DONE') !== false,
                'current' => substr_count($content, 'Exporting table'),
                'log' => $content
            ];
        }
        return ['completed' => false, 'current' => 0, 'log' => ''];
    }

    public function getDbfPath()
    {
        return $this->dbfPath;
    }

    public function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }


    // Generar fichero param.ini
    public function generarIni($selectedTables)
    {
        // Definir ruta del archivo INI
        $iniFile = 'C:\\CovellogZarca\\param.ini';

        // Comprobar si existe y eliminar
        if (file_exists($iniFile)) {
            if (!unlink($iniFile)) {
                throw new Exception("No se pudo eliminar el archivo param.ini existente");
            }
        }

        $timestamp = date('Y-m-d_H-i-s');
        $iniContent = "; Archivo de configuración generado: {$timestamp}\n\n";

        // Sección Config
        $iniContent .= "[Config]\n";
        $iniContent .= "FechaHora=" . date('Y-m-d H:i:s') . "\n";
        $iniContent .= "RutaOrigen={$this->dbfPath}\n";
        $iniContent .= "RutaDestino={$this->outputPath}dump_{$timestamp}.sql\n\n";

        // Sección Tablas
        $iniContent .= "[Tablas]\n";
        $iniContent .= "lista=" . implode(',', $selectedTables) . "\n";

        // Guardar el nuevo archivo
        if (file_put_contents($iniFile, $iniContent) === false) {
            throw new Exception("No se pudo crear el archivo param.ini");
        }

        return [
            'success' => true,
            'iniFile' => $iniFile,
            'timestamp' => $timestamp
        ];
    }
}


// Crear instancia del convertidor
$dbfPath = 'C:\\CovellogZarca\\data\\';
$iniFile = 'C:\\CovellogZarca\\param.ini';
$sqlOutputPath = 'C:\\CovellogZarca\\sqldump.sql';
$dropTables = true;

$converter = new DbfConverter($dbfPath, $iniFile, $sqlOutputPath, $dropTables);
$tables = $converter->getDbfTables();

// Añadir aquí el procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es un refresco o nueva submisión
    if (!isset($_SESSION['last_submit']) || $_SESSION['last_submit'] !== $_POST['timestamp']) {
        $_SESSION['last_submit'] = $_POST['timestamp'];
        // Procesar el formulario normalmente
        if (isset($_POST['convert']) && isset($_POST['tables'])) {
            $result = $converter->convertSelected($_POST['tables']);

            echo '<div class="alert ' . ($result['success'] ? 'alert-success' : 'alert-danger') . '">';
            if ($result['success']) {
                echo '<h4>✓ Conversión completada con éxito</h4>';
                echo '<p>Archivo SQL generado: ' . htmlspecialchars($result['sqlFile']) . '</p>';
                $downloadPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $result['sqlFile']);
                echo '<a href="' . $downloadPath . '" class="btn btn-primary" download>Descargar SQL</a>';
            } else {
                echo '<h4>✗ Error en la conversión</h4>';
            }
            echo '</div>';

            if (isset($result['log'])) {
                echo '<div class="card mt-3"><div class="card-body">';
                echo '<h5 class="card-title">Log de la conversión:</h5>';
                echo '<pre class="mb-0">' . htmlspecialchars($result['log']) . '</pre>';
                echo '</div></div>';
            }
        }
    } else {
        // Es un refresco, redirigir a la página limpia
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>



<?php

$dbfPath = 'C:\\CovellogZarca\\data\\';
$iniFile = 'C:\\CovellogZarca\\param.ini';
$sqlOutputPath = 'C:\\CovellogZarca\\sqldump.sql';
$dropTables = true;

$converter = new DbfConverter($dbfPath, $iniFile, $sqlOutputPath, $dropTables);
$tables = $converter->getDbfTables();

// Debug info
echo "<!-- Debug Info: -->\n";
echo "<!-- Directorio DBF: " . htmlspecialchars($converter->getDbfPath()) . " -->\n";
echo "<!-- Tablas encontradas: " . count($tables) . " -->\n";
if (count($tables) == 0) {
    echo '<div class="alert alert-warning">
            No se encontraron archivos DBF en el directorio especificado.<br>
            Directorio: ' . htmlspecialchars($converter->getDbfPath()) . '
          </div>';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Conversor DBF a SQL - CovellogZarca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="main.css" rel="stylesheet">
</head>

<body class="container py-4">
    <h1 class="mb-4">Conversor DBF a SQL - CovellogZarca</h1>

    <!-- Resumen de tablas -->
    <div class="card mb-4">
        <div class="card-header">
            Resumen de tablas
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Total de tablas:</strong> <?= count($tables) ?>
                </div>
                <div class="col-md-4">
                    <strong>Directorio:</strong> <?= htmlspecialchars($converter->getDbfPath()) ?>
                </div>
                <div class="col-md-4">
                    <strong>Tamaño total:</strong>
                    <?= $converter->formatSize(array_sum(array_column($tables, 'rawsize'))) ?>
                </div>
            </div>
        </div>
    </div>

    <form method="post" class="mb-4" id="convertForm">
        <input type="hidden" name="timestamp" value="<?php echo time(); ?>">
        <div class="card">
            <div class="card-header">
                <div class="table-actions">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" id="selectAll">Seleccionar Todo</button>
                        <button type="button" class="btn btn-secondary btn-sm" id="deselectAll">Deseleccionar Todo</button>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="text" id="tableSearch" class="form-control form-control-sm"
                            placeholder="Buscar tabla..." style="width: 200px;">
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sel.</th>
                            <th>Tabla</th>
                            <th>Tamaño</th>
                            <th>Última Modificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                            <tr data-table-name="<?= htmlspecialchars($table['name']) ?>">
                                <td>
                                    <input type="checkbox" name="tables[]"
                                        value="<?= htmlspecialchars($table['name']) ?>"
                                        class="form-check-input">
                                </td>
                                <td><?= htmlspecialchars($table['name']) ?></td>
                                <td><?= htmlspecialchars($table['size']) ?></td>
                                <td><?= htmlspecialchars($table['modified']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm"
                                        onclick="showStructure('<?= htmlspecialchars($table['name']) ?>')">
                                        Ver Estructura
                                    </button>
                                </td>
                            </tr>
                            <tr id="preview-<?= htmlspecialchars($table['name']) ?>" class="table-preview">
                                <td colspan="5">
                                    <div class="loading">Cargando estructura...</div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <!-- Barra de progreso -->
                <div id="conversionProgress" style="display:none;">
                    <div class="progress mb-3">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="progressText" class="text-center mb-2">Preparando conversión...</div>
                    <div id="logOutput" class="bg-light p-2 mb-2" style="max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;">
                        <!-- Aquí se mostrarán los mensajes de log -->
                    </div>
                </div>

                <button type="submit" name="convert" id="convertButton" class="btn btn-primary">
                    Convertir Seleccionadas
                </button>
            </div>
    </form>

    <script src="main.js"></script>
</body>

</html>