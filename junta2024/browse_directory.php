<?php
header('Content-Type: application/json');

function scanDirectory($path) {
    $directories = [];
    
    try {
        if (is_dir($path)) {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $item) {
                if (!$item->isDot() && $item->isDir()) {
                    $directories[] = $item->getPathname();
                }
            }
        }
        
        // Obtener el directorio padre si no es la raíz
        $parentDir = dirname($path);
        if ($parentDir !== $path) {
            array_unshift($directories, $parentDir);
        }
        
        return [
            'success' => true,
            'current' => $path,
            'directories' => $directories
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

$path = isset($_GET['path']) ? $_GET['path'] : 'C:\\';
echo json_encode(scanDirectory($path));