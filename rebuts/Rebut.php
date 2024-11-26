<?php

function pausa(){
    echo 'Registro encontrado y comparado Pulsa una tecla para seguir...';
    fgets(STDIN);
}

$fechaRebuts = readline('Ingrese la fecha en este formato YYYY-MM-DD: ');

$fechaIntroducida = DateTime::createFromFormat('Y-m-d', $fechaRebuts);

if (!$fechaIntroducida || $fechaIntroducida->format('Y-m-d') !== $fechaRebuts) {
    die('Formato de fecha inválido. Asegúrate de usar el formato YYYY-MM-DD');
}


// Conexion con la base de datos zarca
$servername = 'localhost';
$username = 'admin';
$password = 'admin2023';
$dbname = 'covellog';

// Creacion de conexion
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Error connecting: ' . $conn->connect_error);
} else {
    echo 'Conexion exitosa';
}

$truncateSql = "TRUNCATE TABLE temporary";
if (!$conn->query($truncateSql)) {
    die('Error al vaciar la tabla temporal: ' . $conn->error);
}

// Consulta a la base de datos
$sql = "SELECT * FROM rebut where DATA >= ? ";
// $result = $conn->query($sql);
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conn->error);
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
    echo "0 resultados";
}


if (empty($data)) {
    echo "\n No se encontraron resultados." . PHP_EOL;
} else {
    $result = 1;
    foreach ($data as $row) {
        echo "Fecha: " . $row['DATA'] . "\n";
        echo "Importe: " . $row['IMPORT'] . "\n";
        echo "Factura: " . $row['NUMFACTURA'] . "\n";
        echo "Rebut: " . $row['NUMREBUT'] . "\n";
        echo "Fecha Vencimiento: " . $row['DATAVENCIMENT'] . "\n";
        echo "Cuenta: " . $row['COMPTE'] . "\n";
        echo "Dirección: " . $row['ADRECA'] . "\n";
        echo "Estado: " . $row['ESTAT'] . "\n";
        echo "Observaciones: " . $row['OBSERVACIONS'] . "\n";
        echo "----------------------\n";
        $result++;
    }    
    echo "total datos insertados en Temporal..:".$result;
}

if (empty($data)) {
    echo "\nNo se encontraron resultados." . PHP_EOL;
} else {
    // Preparar la consulta de inserción
    $insertSql = "INSERT INTO temporary (NUMFACTURA, NUMREBUT, IMPORT, DATE, DATEVENCIMENT, COMPTE) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($insertSql);
    if ($stmtInsert === false) {
        die('Error en la preparación de la consulta de inserción: ' . $conn->error);
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


    echo "\n Datos insertados correctamente en la tabla Tamporary." . PHP_EOL;
}


// Obtener valores únicos del campo COMPTE
$uniqueCompteSql = "SELECT DISTINCT COMPTE FROM temporary";
$resultUniqueCompte = $conn->query($uniqueCompteSql);

$uniqueCompteArray = array();

if ($resultUniqueCompte->num_rows > 0) {
    while ($row = $resultUniqueCompte->fetch_assoc()) {
        $uniqueCompteArray[] = $row['COMPTE'];
    }
}

// Mostrar el array de valores únicos
echo "Valores únicos del campo COMPTE:\n";
print_r($uniqueCompteArray);

// Array para almacenar NOM y COMPTE
$nomCompteArray = array();

// Obtener todos los registros de la tabla empresa
$empresaQuery = "SELECT NOM, CCORRENT, CIF FROM empresa";
$empresaResult = $conn->query($empresaQuery);
if ($empresaResult === false) {
    die('Error en la consulta de empresa: ' . $conn->error);
}

$empresaData = array();
while ($empresaRow = $empresaResult->fetch_assoc()) {
    $empresaData[] = $empresaRow;
}

$numeroComprado = 1;

// Imprimir cada registro almacenado en $empresaData
echo "Registros de la tabla empresa:\n";
for ($i = 0; $i < count($empresaData); $i++) {
    echo "NOM: " . $empresaData[$i]['NOM'] . ", CCORRENT: " . $empresaData[$i]['CCORRENT'] .  $empresaData[$i]['CIF']. ", CIF:"."\n";
}

// Imprimir el cómputo total de registros almacenados en $empresaData
echo "Total de registros en la tabla empresa: " . count($empresaData) . "\n";

// Comparar los valores de COMPTE en $data con CCORRENT en empresa y almacenar NOM y COMPTE en el nuevo array
for ($i = 0; $i < count($data); $i++) {
        print_r($data[$i]['COMPTE']);
        echo ' Valor del data:'."\n";        
    for ($j = 0; $j < count($empresaData); $j++) {   
        echo "Entro en el segundo for"."\n";
        print_r('Valor de J '.$j."\n");
     

        $compteData = trim ($data[$i]['COMPTE']);
        $compteEmpresa = trim ($empresaData[$j]['CCORRENT']);
        $nombreEmpresa = trim ($empresaData[$j]['NOM']);
        $cifEmpresa = trim ($empresaData[$j]['CIF']);

        echo 'Que esta comparando ' . "\n";
        print_r('Valor de COMPTE $data '.$compteData."\n");
        print_r('Valor de CCORRENT $empresaData '.$compteEmpresa."\n");
        print_r('Valor de NOM $empresaData '.$nombreEmpresa."\n");
        print_r('Cif empresa no esta en Rebuts y hay que añadirlo de empresa $empresaData '.$cifEmpresa."\n");


        if ($compteData == $compteEmpresa) {
            echo 'Entro en el if '."\n";          
            print_r($compteData == $compteEmpresa."\n");
            print_r('Registros encontrados '.$numeroComprado."\n");
            //pausa();
            sleep(5);
            $nomCompteArray[] = array(
                'NOM' => $empresaData[$j]['NOM'],
                'COMPTE' => $empresaData[$j]['CCORRENT'],
                'CIF' => $empresaData[$j]['CIF']
            );
            $numeroComprado++;

        }
        echo 'No he encontrado coincidencias en el if'."\n";
    }
}


// Mostrar el array de valores NOM y COMPTE
echo "Valores de NOM y COMPTE correspondientes:\n";
print_r($nomCompteArray);

// Vaciar el array después de la inserción
$data = array();

foreach ($nomCompteArray as $entry) {
    $nom = $entry['NOM'];
    $compte = $entry['COMPTE'];
    $cif = $entry['CIF'];
    
    $updateSql = "UPDATE temporary SET NOM = ?, CIF = ? WHERE COMPTE = ?";
    $stmtUpdate = $conn->prepare($updateSql);
    if ($stmtUpdate === false) {
        die('Error en la preparación de la consulta de actualización: ' . $conn->error);
    }
    
    $stmtUpdate->bind_param('sss', $nom, $cif, $compte);
    $stmtUpdate->execute();
    echo "Actualización realizada para COMPTE: $compte con NOM: $nom y CIF: $cif\n";
}



$stmt->close();
$conn->close();
?>