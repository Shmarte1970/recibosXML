<?php
// Variables de conexión a la base de datos
$servername = 'localhost';
$username = 'admin';
$password = 'admin2023';
$dbcovellog = 'covellog';
$dbzarca = 'zarca';

// Eliminar el fichero XML
$filename = "rebuts.xml";
$message = "";

if (file_exists($filename)){
    if(unlink($filename)){
        $message = "El fichero $filename ha sido borrado.";
    } else {
        $message = "No se ha podido eliminar el fichero $filename";
    }
} else {
    $message = "El fichero $filename no existe.";
}

echo "<script>document.addEventListener('DOMContentLoaded', function() { showMessage('$message'); });</script>";

// Creación de conexión con Covellog
$covellog = new mysqli($servername, $username, $password, $dbcovellog);

if ($covellog->connect_error) {
    die('Error connecting: ' . $covellog->connect_error);
} else {
    echo "<script>console.log('Conexión establecida con BBDD Covellog');</script>";
}

// Conexión con la base de datos Zarca
$zarca = new mysqli($servername, $username, $password, $dbzarca);
if ($zarca->connect_error) {
    die('Error connecting to Zarca: ' . $zarca->connect_error);
} else {
    echo "<script>console.log('Conexión establecida con BBDD Zarca');</script>";
}

// Inicializar la variable $fechaRebuts con la fecha actual del sistema
$fechaRebuts = date('Y-m-d'); // Formato 'YYYY-MM-DD'

// Vaciar tabla temporary
$truncateSql = "TRUNCATE TABLE temporary";
if (!$covellog->query($truncateSql)) {
    die('Error al vaciar la tabla temporal: ' . $covellog->error);
}

// Solo ejecutar la consulta si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha'])) {
    $fechaRebuts = $_POST['fecha'];

    // Consulta a la base de datos
    $sql = "SELECT * FROM rebut WHERE DATA >= ?";
    $stmt = $covellog->prepare($sql);
    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $covellog->error);
    }

    // Enlazar el parámetro y ejecutar la consulta
    $stmt->bind_param('s', $fechaRebuts);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();

    if ($result->num_rows > 0) {
        // Salida de datos de cada fila
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        echo "<script>console.log('0 resultados');</script>";
    }

    if (empty($data)) {
        echo "<script>console.log('No se encontraron resultados.');</script>";
    } else {
        $numRegistros = count($data);
        // Mostrar mensaje flotante con el número de registros
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showMessage('Se han añadido $numRegistros registros al array.'); });</script>";
        
        // Depuración en la consola
        echo "<script>console.log('Registros obtenidos:');</script>";
        foreach ($data as $row) {
            echo "<script>console.log('Fecha: " . $row['DATA'] . "');</script>";
            echo "<script>console.log('Importe: " . $row['IMPORT'] . "');</script>";
            echo "<script>console.log('Factura: " . $row['NUMFACTURA'] . "');</script>";
            echo "<script>console.log('Rebut: " . $row['NUMREBUT'] . "');</script>";
            echo "<script>console.log('Fecha Vencimiento: " . $row['DATAVENCIMENT'] . "');</script>";
            echo "<script>console.log('Cuenta: " . $row['COMPTE'] . "');</script>";
            echo "<script>console.log('Estado: " . $row['ESTAT'] . "');</script>";
            echo "<script>console.log('Observaciones: " . $row['OBSERVACIONS'] . "');</script>";
            echo "<script>console.log('----------------------');</script>";
        }
    }

    if (empty($data)) {
        echo "<script>console.log('No se encontraron Bancos');</script>".PHP_EOL;
    } else {
        // Preparar la consulta de inserción utilizando los nombres correctos de las columnas 'DATE' y 'DATAVENCIMENT'
        $insertSql = "INSERT INTO temporary (NUMFACTURA, NUMREBUT, IMPORT, DATE, DATEVENCIMENT, COMPTE) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $covellog->prepare($insertSql);
        if ($stmtInsert === false) {
            die('Error en la preparación de la consulta de inserción: ' . $covellog->error);
        }

        // Insertar los datos en la tabla temporal
        foreach ($data as $row) {
            $stmtInsert->bind_param(
                'ssssss',
                $row['NUMFACTURA'],
                $row['NUMREBUT'],
                $row['IMPORT'],
                $row['DATA'],
                $row['DATAVENCIMENT'],
                $row['COMPTE']             
            );
            $stmtInsert->execute();
        }
        
        echo "<script>console.log('Datos insertados correctamente en la tabla Temporary.');</script>.".PHP_EOL;
    }
}

// Consulta a la tabla Bancos de Zarca
$sql = "SELECT * FROM zcctabancariaszarca";
$stmt = $zarca->prepare($sql);
if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $zarca->error);
}

$stmt->execute();
$resultBancos = $stmt->get_result();

$dataBco = array();

if ($resultBancos->num_rows > 0) {
    // Salida de datos de cada fila
    while ($row = $resultBancos->fetch_assoc()) {
        // concatenando campos de cta
        $concatenado = $row['codigoPais'] . $row['entidad'] . $row['oficina'] . $row['dc'] . $row['cta'];
        $dataBco[] = array(
            'concatenado' => $concatenado,
            'nomBco' => $row['nombreBanco'],
            'idSepa' => $row['idSepa']
        );
    }
} else {
    echo "0 resultados";
}

if (empty($dataBco)) {    
    echo "<script>console.log('No se encontraron Bancos');</script>".PHP_EOL;  
} else {
    $count = 0;
    foreach ($dataBco as $row) {
        $count++;
    }    
    echo "<script>console.log('total datos insertados en dataBco..:.$count');</script>";    
}


// Obtener valores únicos del campo COMPTE
$uniqueCompteSql = "SELECT DISTINCT COMPTE FROM temporary";
$resultUniqueCompte = $covellog->query($uniqueCompteSql);

$uniqueCompteArray = array();

if ($resultUniqueCompte->num_rows > 0) {
    while ($row = $resultUniqueCompte->fetch_assoc()) {
        $uniqueCompteArray[] = $row['COMPTE'];        
    }
}








?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Fecha y Banco</title>
    <link rel="stylesheet" href="main.css"> <!-- Enlace al archivo CSS -->
</head>
<body>
    <h1>Recibos</h1>

    <!-- Contenedor para mensajes flotantes -->
    <div id="message-container" class="message-container"></div>

    <form method="post">
        <div class="form-group">
            <label for="fecha">Introduce la fecha (YYYY-MM-DD):</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo $fechaRebuts; ?>" required>
        </div>
        <div class="form-group">
            <label for="banco">Selecciona un banco:</label>
            <select id="banco" name="banco" required>
                <?php foreach ($dataBco as $banco): ?>
                    <option value="<?php echo $banco['concatenado']; ?>">
                        <?php echo $banco['nomBco']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <button type="submit">Enviar</button>
        </div>
    </form>

    <script src="main.js"></script> <!-- Asegúrate de que main.js existe y está enlazado correctamente -->
</body>
</html>
