<?php

function generarInformeRecibosDefectuosos($datosDefectuosos) {
    $fecha_actual = date("Y-m-d"); // Formato: YYYY-MM-DD
    $archivo_txt = "RecibosErroneos_" . $fecha_actual . ".txt";

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

    return $archivo_txt;
}

?>