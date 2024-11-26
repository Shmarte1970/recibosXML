<?php

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

// Consulta a la base de datos
$sql = "SELECT * FROM temporary where DATE >= ? ";
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

// Cierre de la conexion
$stmt->close();
$conn->close();


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
    $othr->addChild('Id', 'ES13000B65767030'); // (PRM identificador Facilitado por la entidad )
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


echo 'Fichero XML creado exitosamente';



?>
