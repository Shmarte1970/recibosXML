// Send an SMS using Twilio's REST API and PHP
<?php
// Required if your environment does not handle autoloading
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;

// Your Account SID and Auth Token from console.twilio.com
$sid    = "AC343bf6346f33bb6ab5279047ad7e412d";
$token  = "b271dd10511f02b942dc9c5e4bce1198";


// Crear una instancia de CurlClient con la opción de SSL configurada
$httpClient = new CurlClient([
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_CAINFO => 'C:\wamp64\cacert.pem'
]);

// Crear una instancia del cliente de Twilio con el cliente HTTP personalizado
$twilio = new Client($sid, $token);
$twilio->setHttpClient($httpClient);

// Use the Client to make requests to the Twilio REST API
$twilio->messages->create(
    // The number you'd like to send the message to
    '+34678995325',
    [
        // A Twilio phone number you purchased at https://console.twilio.com
        'from' => '+13193135438',
        // The body of the text message you'd like to send
        'body' => "newTest desde PHP"
    ]
);