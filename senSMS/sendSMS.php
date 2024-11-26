
<?php
// Actualiza la ruta a tu archivo autoload.php
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;

// Configura tus credenciales de Twilio
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

try {
    $message = $twilio->messages
      ->create(
        "+34678995325", // to
        array(
          "from" => "+13193135438",
          "body" => "El servidor del Erp360 esta Caido"
        )
      );

    if ($message) {
        print($message->sid);
    } else {
        echo "El mensaje no fue creado correctamente.";
    }
} catch (\Twilio\Exceptions\TwilioException $e) {
    echo 'Twilio Exception capturada: ', $e->getMessage(), "\n";
} catch (Exception $e) {
    echo 'General Exception capturada: ', $e->getMessage(), "\n";
}
?>
