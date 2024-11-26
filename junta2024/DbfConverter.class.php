<?php

define('BASE_PATH', 'C:\\CovellogZarca');
define('WEB_PATH', 'C:\\wamp64\\www\\junta2024');

class DbfConverter
{
    private $dbfPath;
    private $outputPath;
    private $logsPath;
    private $dropTables;

    public function __construct($dbfPath, $outputPath, $dropTables)
    {
        $this->dbfPath = rtrim($dbfPath, '\\') . '\\';
        $this->outputPath = rtrim($outputPath, '\\') . '\\';
        $this->logsPath = rtrim(BASE_PATH . '\\logs', '\\') . '\\';
        $this->dropTables = $dropTables;
    
   /* 
    // Verificar que los directorios existen (ya deberían estar creados)
    if (!is_dir($this->dbfPath)) {
        throw new Exception("No existe el directorio de datos: " . $this->dbfPath);
    }
    if (!is_dir($this->outputPath)) {
        throw new Exception("No existe el directorio de salida: " . $this->outputPath);
    }
    if (!is_dir($this->logsPath)) {
        throw new Exception("No existe el directorio de logs: " . $this->logsPath);
    }*/
}

    // Obtener lista de tablas DBF
    public function getDbfTables()
    {
        $tables = [];
        $log = "Buscando DBFs en: " . $this->dbfPath . "\n";

        if (!is_dir($this->dbfPath)) {
            error_log("El directorio no existe: " . $this->dbfPath);
            return $tables;
        }

        $pattern = $this->dbfPath . '*.{dbf,DBF}';
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
                'rawsize' => $filesize
            ];
        }

        ksort($tables);

        // Guardar el log en el directorio de logs
        $logFile = $this->logsPath . 'dbf_scan.log';
        file_put_contents($logFile, $log);

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

            $handle = fopen($filePath, "rb");
            if (!$handle) {
                return false;
            }

            $header = fread($handle, 32);
            if (strlen($header) != 32) {
                fclose($handle);
                return false;
            }

            $recordCount = unpack("V", substr($header, 4, 4))[1];
            $headerLength = unpack("v", substr($header, 8, 2))[1];
            $fieldCount = floor(($headerLength - 32) / 32);

            $structure = [];
            for ($i = 0; $i < $fieldCount; $i++) {
                $fieldInfo = fread($handle, 32);
                if (strlen($fieldInfo) != 32) break;

                $fieldName = rtrim(substr($fieldInfo, 0, 11), "\x00");
                $fieldType = substr($fieldInfo, 11, 1);
                $fieldLength = ord(substr($fieldInfo, 16, 1));
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
            'log' => '',
            'sqlFile' => ''
        ];

        try {
            // Generar nombre de archivo con timestamp
            $timestamp = date('Y-m-d_His');
            $outputFileName = "sqldump_{$timestamp}.sql";
            $fullOutputPath = $this->outputPath . $outputFileName;

            // Crear lista de tablas separadas por coma
            $tableList = implode(',', array_map('trim', $tableNames));

            // Crear el comando con los tres parámetros para dbf2sql_selective.exe
            $cmd = "C:\\CovellogZarca\\dbf2sql_selective.exe " . 
                   escapeshellarg($this->dbfPath) . " " . 
                   escapeshellarg($fullOutputPath) . " " .
                   escapeshellarg($tableList);

            // Ejecutar el comando
            $output = [];
            $returnCode = 0;
            exec($cmd . " 2>&1", $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception("Error ejecutando dbf2sql_selective.exe: " . implode("\n", $output));
            }

            // Verificar si se generó el archivo SQL
            if (!file_exists($fullOutputPath)) {
                throw new Exception("No se generó el archivo SQL de salida.");
            }

            $result['sqlFile'] = $fullOutputPath;
            
            // Leer el log si existe
            $logFile = $this->logsPath . 'dbf2sql.log';
            if (file_exists($logFile)) {
                $result['log'] = file_get_contents($logFile);
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
        $logFile = $this->logsPath . 'dbf2sql.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            
            // Calcular progreso basado en las tablas procesadas
            $totalTables = isset($_SESSION['total_tables']) ? $_SESSION['total_tables'] : 1;
            $tablesProcessed = substr_count($content, 'Exportar tabla');
            $progress = min(round(($tablesProcessed / $totalTables) * 100), 100);

            return [
                'completed' => strpos($content, 'DONE') !== false,
                'current' => $tablesProcessed,
                'total' => $totalTables,
                'progress' => $progress,
                'log' => $content
            ];
        }
        return [
            'completed' => false, 
            'current' => 0, 
            'total' => 0,
            'progress' => 0,
            'log' => ''
        ];
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
}

// Crear instancia del convertidor
$dbfPath = $_POST['dbfPath'] ?? BASE_PATH . '\\data';
$outputPath = $_POST['outputPath'] ?? BASE_PATH . '\\exports';
$dropTables = true;

$converter = new DbfConverter($dbfPath, $outputPath, $dropTables);
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