<?php

// conexion.php

// Variables de conexión a la base de datos
$servername = 'localhost';
$username = 'admin';
$password = 'admin2023';
$dbcovellog = 'covellog';
$dbzarca = 'zarca';

// Función para conectar a Covellog
function connectCovellog() {
    global $servername, $username, $password, $dbcovellog;
    $conn = new mysqli($servername, $username, $password, $dbcovellog);
    if ($conn->connect_error) {
        die('Error connecting to Covellog: ' . $conn->connect_error);
    }
    echo "<script>console.log('Conexión establecida con BBDD Covellog');</script>";
    return $conn;
}

// Función para conectar a Zarca
function connectZarca() {
    global $servername, $username, $password, $dbzarca;
    $conn = new mysqli($servername, $username, $password, $dbzarca);
    if ($conn->connect_error) {
        die('Error connecting to Zarca: ' . $conn->connect_error);
    }
    echo "<script>console.log('Conexión establecida con BBDD Zarca');</script>";
    return $conn;
}

?>
