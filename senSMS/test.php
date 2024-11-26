<?php
// Archivo de prueba para verificar cURL y SSL
$url = "https://api.twilio.com/2010-04-01/Accounts.json";
$certPath = 'C:\wamp64\cacert.pem';

// Inicializa cURL
$ch = curl_init($url);

// Configura cURL para usar el certificado
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_CAINFO, $certPath);

// Ejecuta cURL
$response = curl_exec($ch);

// Maneja errores
if (curl_errno($ch)) {
    echo 'Error de cURL: ' . curl_error($ch);
} else {
    echo 'Respuesta de cURL: ' . $response;
}

// Cierra la sesión de cURL
curl_close($ch);
?>
