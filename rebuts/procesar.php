<?php

function pausa(){
    echo 'Registro encontrado y comparado Pulsa una tecla para seguir...';
    fgets(STDIN);
}

// Eliminar el fichero XML
$filename = "rebuts.xml";

if (file_exists($filename)){
    if(unlink($filename)){
        echo "El fichero $filename ha sido borrado."."\n";        
    }else{
        echo "No se ha podido eliminar el fichero $filename"."\n";        
    }
}else {
    echo "El fichero $filename no existe."."\n";    
}

// Introduccion de la Fecha 
$fechaRebuts = readline('Ingrese la fecha en este formato YYYY-MM-DD: ');

$fechaIntroducida = DateTime::createFromFormat('Y-m-d', $fechaRebuts);

if (!$fechaIntroducida || $fechaIntroducida->format('Y-m-d') !== $fechaRebuts) {
    die('Formato de fecha inválido. Asegúrate de usar el formato YYYY-MM-DD');
}


// Conexion con la base de datos covellog
$servername = 'localhost';
$username = 'admin';
$password = 'admin2023';
$dbcovellog = 'covellog';

// Creacion de conexion
$covellog = new mysqli($servername, $username, $password, $dbcovellog);

if ($covellog->connect_error) {
    die('Error connecting: ' . $covellog->connect_error);
} else {
    echo 'Conexion establecida con BBDD Covellog'."\n";
}

// Conexion con la base de datos Zarca
$dbzarca = 'zarca';
$zarca = new mysqli($servername, $username, $password, $dbzarca);

if ($zarca->connect_error){
    die('Error connecting: '. $zarca->connect_error);    
} else {
    echo 'Conexion establecida con BBDD Zarca '."\n";
    
}

// Vaciar tabla temporary
$truncateSql = "TRUNCATE TABLE temporary";
if (!$covellog->query($truncateSql)) {
    die('Error al vaciar la tabla temporal: ' . $covellog->error);
}

// Consulta a la base de datos
$sql = "SELECT * FROM rebut where DATA >= ? ";
// $result = $covellog->query($sql);
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


    echo "\n Datos insertados correctamente en la tabla Tamporary." . PHP_EOL;
}

// Consulta a la tabla Bancos de Zarca
$sql = "SELECT * FROM zcctabancariaszarca ";
// $result = $zarca->query($sql);
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
        //
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
    echo "\n No se encontraron Bancos." . PHP_EOL;
} else {
    $count = 1;
    foreach ($dataBco as $row) {
        echo "cta:" . $row['concatenado'] . "\n";
        echo "nomBco " . $row['nomBco'] . "\n";
        echo "Identificador SEPA: " . $row['idSepa'] . "\n";        
        echo "----------------------\n";
        $count++;
    }    
    echo "total datos insertados en dataBco..:".$count."\n";
    pausa();
}

if (empty($dataBco)) {
    echo "\nNo se encontraron resultados." . PHP_EOL;
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
echo "Valores únicos del campo COMPTE:\n";
print_r($uniqueCompteArray);

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
            //sleep(5);
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
    $stmtUpdate = $covellog->prepare($updateSql);
    if ($stmtUpdate === false) {
        die('Error en la preparación de la consulta de actualización: ' . $covellog->error);
    }
    
    $stmtUpdate->bind_param('sss', $nom, $cif, $compte);
    $stmtUpdate->execute();
    echo "Actualización realizada para COMPTE: $compte con NOM: $nom y CIF: $cif\n";
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
    echo "0 resultados";
}

$stmt->close();
$covellog->close();
$zarca->close();

// Creacion del fichero XML
$xml = new SimpleXMLElement('<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02"/>');


// Añadir el número total de registros
$totalRecords = count($data);
$xml->addChild('total_registros', $totalRecords);
$cstmrDrctDbtInitn = $xml->addChild('CstmrDrctDbtInitn');


$contador = 1;

foreach ($data as $indice) {
    $grpHdr = $cstmrDrctDbtInitn->addChild('GrpHdr');
    $grpHdr->addChild('MsgId', '1.1');    
    $dateTime = new DateTime($indice['date']);
    $formattedDate = $dateTime->format('Y-m-d\TH:i:s');
    $grpHdr->addChild('CreDtTM', htmlspecialchars($formattedDate));
    $grpHdr->addChild('NbOfTxs', htmlspecialchars($contador));
    $grpHdr->addChild('CtrlSum', htmlspecialchars($indice['import']));
    
    $initgPty = $grpHdr->addChild('InitgPty');
    $initgPty->addChild('Nm', htmlspecialchars($indice['nom']));

    $id = $initgPty->addChild('Id');
    $orgId = $id->addChild('OrgId');
    $othr = $orgId->addChild('Othr');
    $subFijo = 'ES';
    // $othr->addChild('Id', htmlspecialchars($indice['compte'])); 
    $othr->addChild('Id',$subFijo.$indice['cif'] );
    $pmtinf = $cstmrDrctDbtInitn->addChild('PmtInf');
    $pmtinf->addChild('PmtInfId', htmlspecialchars($indice['compte']));
    $pmtmtd = $pmtinf->addChild('PmtMtd','DD');
    $pmtinf->addChild('NbOfTxs', htmlspecialchars($contador));
    $pmtinf->addChild('CtrlSum', htmlspecialchars($indice['import']));
    $pmttpInf = $pmtinf->addChild('PmtTpInf');
    $svcLvl = $pmttpInf->addChild('SvcLvl');
    $cd = $svcLvl->addChild('Cd','SEPA');
    $lclInstrm = $pmttpInf->addChild('LclInstrm');
    $cd = $lclInstrm->addChild('Cd','CORE');
    $dateTime = new DateTime($indice['datevenciment']);
    $formattedDate = $dateTime->format('Y-m-d');
    $pmtinf->addChild('ReqColltnDt', htmlspecialchars($formattedDate));
    $cdtr = $pmtinf->addChild('Cdtr');
    $nM = $cdtr->addChild('Nm','Zarca, S.L.'); 
    $pstladr = $cdtr->addChild('PstlAdr');
    $ctry = $pstladr->addChild('Ctry','ES');
    $addLine = $pstladr->addChild('AdrLine','Carrer Electricitat, 2');
    $id = $cdtr->addChild('Id');
    $orgId = $id->addChild('OrgId');
    $othr = $orgId->addChild('Othr');
    $othr->addChild('Id', 'ESB65767030'); // (PRM identificador Facilitado por la entidad )
    $cdtrAcct = $pmtinf->addChild('CdtrAcct');
    $id = $cdtrAcct->addChild('Id');
    $iban = $id->addChild('IBAN','ES8201280532010500009727');
    $ccy = $cdtrAcct->addChild('Ccy','EU');
    $cdtrAgt = $pmtinf->addChild('CdtrAgt');
    $fininstnId = $cdtrAgt->addChild('FinInstnId');
    $bic = $fininstnId->addChild('BIC', 'BKBKESMMXXX');
    $chrbr = $pmtinf->addChild('ChrgBr','SLEV');
    $drctDbTxinf = $pmtinf->addChild('DrctDbtTxInf');
    $pmtId = $drctDbTxinf->addChild('PmtId');
    $instrId = $pmtId->addChild('ImstrId',htmlspecialchars($indice['numfactura']));
    $endToendId = $pmtId->addChild('EndToEndId', htmlspecialchars($indice['numfactura']));
    $pmttpInf = $drctDbTxinf->addChild('PmtTpinf');
    $seqTp = $pmttpInf->addChild('SeqTp','RCUR');
    $ctgyPurp = $pmttpInf->addChild('CtgyPurp');
    $cd = $ctgyPurp->addChild('Cd', '14');
    $leyenda = 'Ccy';
    $moneda = 'EUR';
    $instdAmt = $drctDbTxinf->addChild('InstdAmt', htmlspecialchars($indice['import']));
    $instdAmt->addAttribute($leyenda, $moneda); // Añade los atributos  Ccy y Eur a la etiqueta InstdAmt
    $drctdbtTx = $drctDbTxinf->addChild('DrctDbtTx');
    $mndtrltdInf = $drctdbtTx->addChild('MndtRltdInf');
    $mndtId = $mndtrltdInf->addChild('MndtId',$indice['numfactura']);
    $dateTime = new DateTime($indice['datevenciment']);
    $formattedDate = $dateTime->format('Y-m-d');
    $dtofsgntr = $mndtrltdInf->addChild('DtOfSgntr',$formattedDate);
    $state = 'false';
    $amdmntInd =  $mndtrltdInf->addChild('AmdmntInd',$state);
    $cdtrSchmeId = $drctdbtTx->addChild('CdtrSchmeId');
    $id = $cdtrSchmeId->addChild('Id');
    $orgId = $id->addChild('OrgId');
    $othr = $orgId->addChild('Othr');
    $othr->addChild('Id', htmlspecialchars($indice['compte'])); 
    $dbtragt = $drctDbTxinf->addChild('DbtrAgt');
    $fininstnId = $dbtragt->addChild('FinInstnId');
    $bic = $fininstnId->addChild('BIC','BBVAESMM');
    $dbtr = $drctDbTxinf->addChild('Dbtr');
    $nM = $dbtr->addChild('Nm',htmlspecialchars($indice['nom']));
    $pstladr = $dbtr->addChild('PstlAdr');
    $ctry = $pstladr->addChild('AdrLine','Meter aqui la direccion');
    $id = $dbtr->addChild('Id');
    $orgId = $id->addChild('OrgId');
    $othr = $orgId->addChild('Othr');
    $id = $othr->addChild('Id','ES13000B67614832'); /*<!--Identificacion del deudor (PRM ES Espanya, 13=iban, pongo 000 igual que el nuestro pero 182  es el cod BBVA dado por IA, BXXX cif deudor)-->*/
    $ctryofres = $dbtr->addChild('CtryOfRes','ES');
    $dbtracct = $drctDbTxinf->addChild('DbtrAcct');
    $id = $dbtracct->addChild('Id');
    $iban = $id->addChild('IBAN',$indice['compte']);
    $ccy = $dbtracct->addChild('Ccy', $moneda);
    $purp = $drctDbTxinf->addChild('Purp');
    $cd = $purp->addChild('Cd','14');
    $rmtInf = $drctDbTxinf->addChild('RmtInf');
    $ustrd = $rmtInf->addChild('Ustrd',htmlspecialchars($indice['numfactura']));





    





/*
    $grpHdr->addChild('NUMFACTURA', htmlspecialchars($indice['numfactura']));
    $grpHdr->addChild('NUMREBUT', htmlspecialchars($indice['numrebut']));
    $grpHdr->addChild('DATAVENCIMENT', htmlspecialchars($indice['datevenciment']));
    $grpHdr->addChild('COMPTE', htmlspecialchars($indice['compte']));    
    $grpHdr->addChild('otro_recibo');
  */  
    $contador++;
}



// Convertir SimpleXMLElement a DOMDocument para formatear la salida
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml->asXML());

$xmlString = $dom->saveXML();
$xmlString = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8"?>', $xmlString);

// Guardar el XML formateado en un fichero cambiando la linea con el encoding
file_put_contents('rebuts.xml', $xmlString);

print_r('total registros.:'.$totalRecords."\n");

echo 'Fichero XML creado exitosamente';


?>