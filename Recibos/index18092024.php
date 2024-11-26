<?php

require_once 'conexion.php';

$data = array();

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


$covellog = connectCovellog();

$zarca = connectZarca();




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
        echo "<script>console.log('0 resultados de rebuts');</script>";
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
    echo "<script>('0 resultados de Bco');</script>";
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


// Mostrar el array de valores únicos
echo "<script>console.log('Valores unicos del campo Compe');</script>";  
/*echo "<script>('$uniqueCompteArray');</script>";*/


// Array para almacenar NOM y COMPTE
$nomCompteArray = array();

// Obtener todos los registros de la tabla empresa
$empresaQuery = "SELECT NOM, CCORRENT, CIF FROM empresa";
$empresaResult = $covellog->query($empresaQuery);
if ($empresaResult === false) {
    die('Error en la consulta de empresa: ' . $covellog->error);
}

$empresaData = array();
while ($empresaRow = $empresaResult->fetch_assoc()) {
    $empresaData[] = $empresaRow;
}

$numeroComprado = 1;

// Imprimir cada registro almacenado en $empresaData
echo "<script>('Registros de la tabla empresa');</script>";
for ($i = 0; $i < count($empresaData); $i++) {    
   /* echo "<script>($empresaData[$i]['NOM'] . $empresaData[$i]['CCORRENT'] .  $empresaData[$i]['CIF']');</script>";*/
}

// Imprimir el cómputo total de registros almacenados en $empresaData
/*echo "<script>('Total de registros en la tabla empresa:.count($empresaData)');</script>";*/

// Comparar los valores de COMPTE en $data con CCORRENT en empresa y almacenar NOM y COMPTE en el nuevo array
for ($i = 0; $i < count($data); $i++) {
  /*  print_r($data[$i]['COMPTE']);
    echo ' Valor del data:'."\n";         */
for ($j = 0; $j < count($empresaData); $j++) {   
 /*   echo "Entro en el segundo for"."\n";
    print_r('Valor de J '.$j."\n");*/
 

    $compteData = trim ($data[$i]['COMPTE']);
    $compteEmpresa = trim ($empresaData[$j]['CCORRENT']);
    $nombreEmpresa = trim ($empresaData[$j]['NOM']);
    $cifEmpresa = trim ($empresaData[$j]['CIF']);

    /*echo 'Que esta comparando ' . "\n";
    print_r('Valor de COMPTE $data '.$compteData."\n");
    print_r('Valor de CCORRENT $empresaData '.$compteEmpresa."\n");
    print_r('Valor de NOM $empresaData '.$nombreEmpresa."\n");
    print_r('Cif empresa no esta en Rebuts y hay que añadirlo de empresa $empresaData '.$cifEmpresa."\n");
*/

    if ($compteData == $compteEmpresa) {
  /*      echo 'Entro en el if '."\n";          
        print_r($compteData == $compteEmpresa."\n");
        print_r('Registros encontrados '.$numeroComprado."\n");  */
        $nomCompteArray[] = array(
            'NOM' => $empresaData[$j]['NOM'],
            'COMPTE' => $empresaData[$j]['CCORRENT'],
            'CIF' => $empresaData[$j]['CIF']
        );
        $numeroComprado++;

    }
   /* echo 'No he encontrado coincidencias en el if'."\n";*/   
}
    

}

// Vaciar el array después de la inserción
$data = array();

foreach ($nomCompteArray as $entry) {
    $nom = $entry['NOM'];
    $compte = $entry['COMPTE'];
    $cif = $entry['CIF'];
    
    $updateSql = "UPDATE temporary SET NOM = ?, CIF = ? WHERE COMPTE = ?";
    $stmtUpdate = $covellog->prepare($updateSql);
    if ($stmtUpdate === false) {
        die('Error en la preparación de la consulta de actualización: ' . $covellog->error);
    }
    
    $stmtUpdate->bind_param('sss', $nom, $cif, $compte);
    $stmtUpdate->execute();
    /*echo "Actualización realizada para COMPTE: $compte con NOM: $nom y CIF: $cif\n";*/
}

// Consulta a la base de datos
$sql = "SELECT * FROM temporary where DATE >= ? ";
// $result = $conn->query($sql);
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
    echo "<script>('0 resultados de temporary');</script>";
}


$errorMessages = [];
$hasErrors = false;

foreach ($data as $row) {
    $errorMsg = "";
    
    if (empty($row['compte']) || strlen($row['compte']) != 24) {
        $errorMsg .= "Cuenta incorrecta. ";
    }
    if (empty($row['cif']) || strlen($row['cif']) < 9) {
        $errorMsg .= "CIF incorrecto. ";
    }
    if (empty($row['numfactura'])) {
        $errorMsg .= "Falta número de factura. ";
    }
    if (empty($row['numrebut'])) {
        $errorMsg .= "Falta número de recibo. ";
    }
    if (empty($row['import'])) {
        $errorMsg .= "Falta importe. ";
    }
    if (empty($row['date'])) {
        $errorMsg .= "Falta fecha. ";
    }
    if (empty($row['datevenciment'])) {
        $errorMsg .= "Falta fecha de vencimiento. ";
    }
    if (empty($row['nom'])) {
        $errorMsg .= "Falta nombre. ";
    }
    
    if (!empty($errorMsg)) {
        $hasErrors = true;
        $errorMessages[] = "Factura " . ($row['numfactura'] ?? 'Desconocida') . ": " . $errorMsg;
    }
}

if ($hasErrors) {
    echo "<script>console.log('Se encontraron registros con datos incorrectos:');</script>";
    foreach ($errorMessages as $error) {
        echo "<script>console.log(" . json_encode($error) . ");</script>";
    }
    
    // Aquí puedes decidir cómo manejar los errores en la interfaz de usuario
    echo "<script>addPhpMessage('Se encontraron registros con datos incorrectos. Revise la consola para más detalles.');</script>";
    
    // Si necesitas una decisión del usuario, podrías usar JavaScript para mostrar un diálogo de confirmación
    echo "<script>
    if (!confirm('Se encontraron registros con datos incorrectos. ¿Desea continuar?')) {
        window.location.href = 'pagina_de_error.php'; // Redirige a una página de error si el usuario no quiere continuar
    }
    </script>";
} else {
    echo "<script>console.log('Todos los registros están correctos.');</script>";
    echo "<script>addPhpMessage('Todos los registros están correctos.');</script>";
}

if (!empty($data)) {
    echo "<script>console.log('Estructura de la primera fila de \$data:');</script>";
    echo "<script>console.log(" . json_encode($data[0]) . ");</script>";
}


$dataList = array();


if (!empty($data)) {
    foreach ($data as $row) {
        // Usamos isset para verificar la existencia de cada campo
        // y accedemos a los campos de manera case-insensitive
        $dataList[] = [
            'nom' => isset($row['NOM']) ? $row['NOM'] : (isset($row['nom']) ? $row['nom'] : 'N/A'),
            'NUMFACTURA' => isset($row['NUMFACTURA']) ? $row['NUMFACTURA'] : (isset($row['numfactura']) ? $row['numfactura'] : 'N/A'),
            'IMPORT' => isset($row['IMPORT']) ? $row['IMPORT'] : (isset($row['import']) ? $row['import'] : 'N/A'),
            'DATE' => isset($row['DATE']) ? $row['DATE'] : (isset($row['date']) ? $row['date'] : 'N/A'),
            'DATEVENCIMENT' => isset($row['DATEVENCIMENT']) ? $row['DATEVENCIMENT'] : (isset($row['datevenciment']) ? $row['datevenciment'] : 'N/A'),
            'is_selected' => false // Mantenemos este campo como estaba
        ];
    }
    echo "<script>addPhpMessage('Se encontraron " . count($dataList) . " registros.');</script>";
    
    // Depuración: Imprimir la estructura de la primera fila de $dataList
    echo "<script>console.log('Estructura de la primera fila de \$dataList:');</script>";
    echo "<script>console.log(" . json_encode($dataList[0]) . ");</script>";
} else {
    echo "<script>addPhpMessage('No se encontraron registros en \$data.');</script>";
}

$stmt->close();
$covellog->close();
$zarca->close();




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
            <label for="fecha">Introduce la fecha desde (YYYY-MM-DD):</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo $fechaRebuts; ?>" required>
        </div>        
        <div class="form-group">
            <button type="submit">Enviar</button>
        </div>
        <h2>Listado de Recibos con fecha: <?php echo "$fechaRebuts"?></h2>
        <div class="table-container">
            <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Número de Factura</th>
                    <th>Importe</th>
                    <th>Fecha Facturacion</th>
                    <th>F. Vencimiento</th>
                    <th class="checkbox-column">Seleccion</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dataList)): ?>
                    <?php foreach ($dataList as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nom']); ?></td>
                            <td><?php echo htmlspecialchars($row['NUMFACTURA']); ?></td>
                            <td><?php echo htmlspecialchars($row['IMPORT']); ?></td>
                            <td><?php echo htmlspecialchars($row['DATE']); ?></td>
                            <td><?php echo htmlspecialchars($row['DATEVENCIMENT']); ?></td>
                            <td class="checkbox-column">
                            <!-- Checkbox asociado a cada registro, con valor igual al número de factura -->
                            <input class="checkbox-column" type="checkbox" name="selectedInvoices[]" value="<?php echo htmlspecialchars($row['is_selected']); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No hay registros disponibles</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

        <div class="form-group inline">
            <label for="banco">Selecciona un banco:</label>
            <select id="banco" name="banco" required>
                <?php foreach ($dataBco as $banco): ?>
                    <option value="<?php echo $banco['concatenado']; ?>">
                        <?php echo $banco['nomBco']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="xmlButton" class="xml-button">XML</button>
        </div>      

        
    </form>


    <script src="main.js"></script> <!-- Asegúrate de que main.js existe y está enlazado correctamente -->
</body>
</html>
