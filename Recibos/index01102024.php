<?php


require 'vendor/autoload.php';

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



$datosDefectuosos = array(); // Nuevo array para almacenar datos incorrectos

foreach ($data as $key => $row) {
    $errorMsg = "";
    $isDefectuoso = false;
    
    if (empty($row['compte']) || strlen($row['compte']) != 24) {
        $errorMsg .= "Cuenta incorrecta. ";
        $isDefectuoso = true;
    }
    if (empty($row['cif']) || strlen($row['cif']) < 9) {
        $errorMsg .= "CIF incorrecto. ";
        $isDefectuoso = true;
    }
    if (empty($row['numfactura'])) {
        $errorMsg .= "Falta número de factura. ";
        $isDefectuoso = true;
    }
    if (empty($row['numrebut'])) {
        $errorMsg .= "Falta número de recibo. ";
        $isDefectuoso = true;
    }
    if (empty($row['import'])) {
        $errorMsg .= "Falta importe. ";
        $isDefectuoso = true;
    }
    if (empty($row['date'])) {
        $errorMsg .= "Falta fecha. ";
        $isDefectuoso = true;
    }
    if (empty($row['datevenciment'])) {
        $errorMsg .= "Falta fecha de vencimiento. ";
        $isDefectuoso = true;
    }
    if (empty($row['nom'])) {
        $errorMsg .= "Falta nombre. ";
        $isDefectuoso = true;
    }
    
    if ($isDefectuoso) {
        $row['error'] = $errorMsg;
        $datosDefectuosos[] = $row;
        unset($data[$key]);
    }
}

if (!empty($datosDefectuosos)) {
    echo "<script>addPhpMessage('Se encontraron " . count($datosDefectuosos) . " registros con datos incorrectos.');</script>";
} else {
    echo "<script>addPhpMessage('Todos los registros están correctos.');</script>";
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
 
   
} 

echo "<script>console.log('Número de registros correctos: " . count($dataList) . "');</script>";
echo "<script>console.log('Número de registros defectuosos: " . count($datosDefectuosos) . "');</script>";

// Si necesitas ver una muestra de los datos
if (!empty($dataList)) {
    echo "<script>console.log('Muestra de datos correctos:');</script>";
    echo "<script>console.log(" . json_encode(array_slice($dataList, 0, 5)) . ");</script>";
}

if (!empty($datosDefectuosos)) {
    echo "<script>console.log('Muestra de datos defectuosos:');</script>";
    echo "<script>console.log(" . json_encode(array_slice($datosDefectuosos, 0, 5)) . ");</script>";
   
}

$fecha_actual = date("Y-m-d"); // Formato: YYYY-MM-DD
$archivo_txt = "RecibosErroneos_" . $fecha_actual . ".txt";

if (isset($_POST['generateReport'])) {
   

    $archivo = fopen($archivo_txt, "w");

    // Verificar si $datosDefectuosos está vacío
    if (empty($datosDefectuosos)) {
        fwrite($archivo, "No se encontraron datos defectuosos.\n");
        echo "<script>console.log('No hay datos defectuosos para escribir en el archivo.');</script>";
    } else {
        foreach ($datosDefectuosos as $row) {
            foreach ($row as $campo => $valor) {
                fwrite($archivo, "$campo: $valor\n");
            }
            fwrite($archivo, "\n");
        }
    }

    $cantidad_registros = count($datosDefectuosos);
    fwrite($archivo, "Cantidad de registros: $cantidad_registros\n");
    fclose($archivo);

    echo "<script>console.log('Informe de errores guardado en $archivo_txt');</script>";
    echo "<script>console.log('Cantidad de registros escritos: $cantidad_registros');</script>";

    // Añadir una verificación adicional
    if (file_exists($archivo_txt)) {
        $contenido = file_get_contents($archivo_txt);
        echo "<script>console.log('Contenido del archivo:', " . json_encode($contenido) . ");</script>";
    } else {
        echo "<script>console.log('El archivo no se pudo crear o leer.');</script>";
    }



}


function enviarInforme($archivo_txt) {
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("pedro.rios@zarca.es", "ERP Zarca");
    $email->setSubject("Informe de Recibos Erróneos - " . date("Y-m-d"));  
    $email->addTo("erpzarca@gmail.com", "Pedro Rios");
    $email->addTo("pedro.rios@zarca.es", "Pedro Rios");
    $email->addContent("text/plain", "Adjunto encontrará el informe de recibos erróneos generado el " . date("Y-m-d"));

    // Adjuntar el archivo
    $file_encoded = base64_encode(file_get_contents($archivo_txt));
    $email->addAttachment(
        $file_encoded,
        "application/text",
        basename($archivo_txt),
        "attachment"
    );

    $sendgrid = new \SendGrid('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');
    
    // Configurar el cliente para ignorar la verificación SSL
    $sendgrid->client->setCurlOptions([
        CURLOPT_CAINFO => 'C:/cert/cacert.pem',
    ]);

    try {
        $response = $sendgrid->send($email);
        return "Mensaje enviado correctamente. Código de estado: " . $response->statusCode();
    } catch (Exception $e) {
        return "Error al enviar el mensaje: " . $e->getMessage();
    }
}

// Uso de la función
if (isset($_POST['generateReport'])) {
    // ... (código para generar el archivo)
    $archivo_txt = $datosDefectuosos;
    $resultado = enviarInforme($archivo_txt);
    echo "<script>alert('$resultado');</script>";
}




$stmt->close();
$covellog->close();
$zarca->close();

$numRegistros = $numRegistros ?? 0;
$dataList = $dataList ?? [];
$datosDefectuosos = $datosDefectuosos ?? [];

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Fecha y Banco</title>
    <link rel="stylesheet" href="main.css"> <!-- Enlace al archivo CSS -->  
    <link rel="stylesheet" href="popup.css">
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
            <button type="submit">Generar</button>
        </div>
        <h2>Listado de Recibos con fecha: <?php echo "$fechaRebuts"?></h2>
        <h3>recibos generados <?php echo "$numRegistros " ?> correctos <span class="correct-count"> <?php echo count($dataList)?> </span> erroneos <span class="error-count"> <?php echo count($datosDefectuosos)?></span></h3>
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

        <?php if (!empty($datosDefectuosos)): ?>
<div class="datos-defectuosos-container">
    <div class="datos-defectuosos">
        <h3>Recibos con Errores</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nº Factura</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datosDefectuosos as $row): ?>
                        <tr>
                            <td><span class="num-factura-error"><?php echo htmlspecialchars($row['numfactura'] ?? 'Sin número'); ?></span></td>
                            <td>Este recibo tiene errores</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="report-button-container">
              <!--  <button id="generateReport" class="report-button">Generar Informe de Errores</button>        -
        <form method="post">
            <button type="submit" name="generateReport">Generar Informe de Errores</button>
        </form>-->
  
        <div class="contenido">
       
        <div class="container-fluid">
            <article>
                <button id="btn-abrir-popup" class="btn-abrir-popup">Generar Informe de Errores</button>
            </article>
        </div>
    </div>

    <div class="overlay" id="overlay">
        <div class="popup" id="popup">
            <h3>¿Cómo desea recibir el informe?</h3>
            <div class="contenedor-botones">
                <button id="btn-enviar-correo" class="btn-popup">Enviar por correo</button>
                <button id="btn-descargar-local" class="btn-popup2">Descargar en local</button>
                <button id="btn-cancelar" class="btn-popup">Cancelar</button>
            </div>
        </div>
    </div>


<?php endif; ?>

    </form>

   

    <script src="main.js"></script> 
    <script src="popup.js"></script>
</body>
</html>