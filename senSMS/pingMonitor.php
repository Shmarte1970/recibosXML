<?php
// PingMonitor.php

class PingMonitor {
    private $targetIP;
    private $timeout;

    public function __construct($targetIP, $timeout = 1) {
        $this->targetIP = $targetIP;
        $this->timeout = $timeout;
    }

    public function ping() {
        $os = PHP_OS_FAMILY;
        $command = '';

        if ($os === 'Windows') {
            $command = "ping -n 1 -w " . ($this->timeout * 1000) . " " . escapeshellarg($this->targetIP);
        } else {
            $command = "ping -c 1 -W " . $this->timeout . " " . escapeshellarg($this->targetIP);
        }

        $output = shell_exec($command);
        echo $output; // Para depurar la salida del comando

        // Verificar la salida del comando
        if (strpos($output, 'Reply from') !== false || strpos($output, 'TTL=') !== false) {
            return true; // Ping exitoso
        } else {
            return false; // Ping fallido
        }
    }

    public function monitor() {
        if (!$this->ping()) {
            $this->sendSMS();
        }
    }

    private function sendSMS() {
        // Aquí debes implementar tu lógica para enviar un SMS
        echo "¡El ping ha fallado! Enviando SMS...\n";
        
        // Ejemplo de lógica para enviar un SMS usando un servicio externo
        // Este es solo un ejemplo, necesitarás configurar tu servicio de SMS.
        // $twilioClient = new Twilio\Rest\Client($sid, $token);
        // $twilioClient->messages->create($to, [
        //     'from' => $from,
        //     'body' => 'El ping a ' . $this->targetIP . ' ha fallado.'
        // ]);
    }
}
