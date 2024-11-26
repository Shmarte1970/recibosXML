<?php

session_start();
require_once 'DbfConverter.class.php';
// Establecer headers para prevenir caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");



set_time_limit(0);
ini_set('max_execution_time', 0);



$dbfPath = BASE_PATH . '\\data';
$sqlOutputPath = BASE_PATH . '\\exports';
$dropTables = true;

$converter = new DbfConverter($dbfPath, $sqlOutputPath, $dropTables);
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                    <strong>Total de tablas:</strong> <span data-total-tables><?= count($tables) ?></span>
                </div>
                <div class="col-md-4">
                    <strong>Directorio:</strong> <span data-current-path><?= htmlspecialchars($converter->getDbfPath()) ?></span>
                </div>
                <div class="col-md-4">
                    <strong>Tamaño total:</strong> <span data-total-size><?= $converter->formatSize(array_sum(array_column($tables, 'rawsize'))) ?></span>
                </div>
            </div>
        </div>

        <form method="post" class="mb-4" id="convertForm">
            <input type="hidden" name="timestamp" value="<?php echo time(); ?>">
            <div class="card">
                <div class="card mb-4">
                    <div class="card-header">
                        Configuración de rutas
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="dbfPath" class="form-label">Directorio de archivos DBF:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dbfPath" name="dbfPath"
                                    value="C:\CovellogZarca\data" required>
                                <button class="btn btn-outline-secondary" type="button" id="browseDbfPath">
                                    Explorar
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="outputPath" class="form-label">Directorio de salida SQL:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="outputPath" name="outputPath"
                                    value="C:\CovellogZarca\exports" required>
                                <button class="btn btn-outline-secondary" type="button" id="browseOutputPath">
                                    Explorar
                                </button>
                            </div>
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
            </div>
        </form>

        <script src="main.js"></script>
</body>

</html>