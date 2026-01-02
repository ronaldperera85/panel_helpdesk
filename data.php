<?php
// data.php

// ==========================================
// 1. LÓGICA HÍBRIDA (LOCAL vs PRODUCCIÓN)
// ==========================================

$localEnv = [];
$envPath = __DIR__ . '/.env';

// Si existe el archivo .env (Entorno Local), lo leemos manualmente
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios que empiezan con #
        if (strpos(trim($line), '#') === 0) continue;

        // Separar Clave=Valor
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Quitar comillas si las hay
            $value = trim($value, "\"'");
            $localEnv[$key] = $value;
        }
    }
}

// Función auxiliar: Busca en el .env local, si no está, busca en el servidor (CapRover)
function get_config($key, $localData) {
    // 1. Prioridad: Archivo .env local
    if (isset($localData[$key])) {
        return $localData[$key];
    }
    
    // 2. Respaldo: Variables de entorno del sistema (CapRover/Docker)
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    
    // 3. Intento extra: $_ENV o $_SERVER (por si acaso)
    if (isset($_ENV[$key])) return $_ENV[$key];
    
    return null;
}

// ==========================================
// 2. OBTENER VARIABLES
// ==========================================

$token = get_config('token', $localEnv);
$empresa = get_config('empresa', $localEnv);
$sucursal = get_config('sucursal', $localEnv);

// Validación
if (!$token || !$empresa || !$sucursal) {
    header('Content-Type: application/json');
    echo json_encode([
        "error" => true,
        "message" => "Faltan variables de configuración (ni en .env ni en CapRover)"
    ]);
    exit;
}

// ==========================================
// 3. PETICIÓN A LA API
// ==========================================

// Construimos la URL
$url = "https://api.icarosoft.com/helpdesk-status/?token={$token}&empresa={$empresa}&sucursal={$sucursal}";

$ch = curl_init();

// Opciones de configuración
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

// Ejecutar la petición
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// ==========================================
// 4. RESPUESTA
// ==========================================

header('Content-Type: application/json');

if ($httpCode === 200 && $response) {
    echo $response;
} else {
    echo json_encode([
        "error" => true,
        "message" => "Error al conectar con API externa",
        "debug_code" => $httpCode
    ]);
}
?>