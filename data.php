<?php
// data.php
$token = getenv('token');
$empresa = getenv('empresa');
$sucursal = getenv('sucursal');

// Validación: Si alguna está vacía, devolvemos error.
if (!$token || !$empresa || !$sucursal) {
    header('Content-Type: application/json');
    echo json_encode([
        "error" => true,
        "message" => "Faltan variables de entorno. Configúralas en CapRover (App Configs)."
    ]);
    exit;
}

// Construimos la URL
$url = "https://api.icarosoft.com/helpdesk-status/?token={$token}&empresa={$empresa}&sucursal={$sucursal}";

// 2. INICIALIZAR CURL
$ch = curl_init();

// Opciones de configuración
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

// Ejecutar la petición
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Cerrar conexión
curl_close($ch);

// 3. DEVOLVER LA RESPUESTA
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