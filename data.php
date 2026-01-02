<?php
// ...existing code...
$envPath = __DIR__ . '/.env';
$token = $empresa = $sucursal = null;

if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    // Eliminar comentarios con // y líneas vacías
    $env = preg_replace('/^\s*\/\/.*$/m', '', $env);
    // Buscar patrones key = "value" (con o sin comillas y con o sin ;)
    if (preg_match('/token\s*=\s*(["\']?)(.*?)\1\s*;?/i', $env, $m)) $token = trim($m[2]);
    if (preg_match('/empresa\s*=\s*(["\']?)(.*?)\1\s*;?/i', $env, $m)) $empresa = trim($m[2]);
    if (preg_match('/sucursal\s*=\s*(["\']?)(.*?)\1\s*;?/i', $env, $m)) $sucursal = trim($m[2]);
}

if (!$token || !$empresa || !$sucursal) {
    header('Content-Type: application/json');
    echo json_encode([
        "error" => true,
        "message" => "Faltan variables de configuración en .env"
    ]);
    exit;
}

// Construimos la URL
$url = "https://api.icarosoft.com/helpdesk-status/?token={$token}&empresa={$empresa}&sucursal={$sucursal}";

// 2. INICIALIZAR CURL (Para hacer la petición como si fuera un navegador)
$ch = curl_init();

// Opciones de configuración
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Esperar máximo 15 segundos
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evitar problemas de SSL en servidores locales

// Ejecutar la petición
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Cerrar conexión
curl_close($ch);

// 3. DEVOLVER LA RESPUESTA AL DASHBOARD
// Le decimos al navegador que esto es JSON
header('Content-Type: application/json');

// Si Icarosoft respondió bien (código 200), pasamos los datos
if ($httpCode === 200 && $response) {
    echo $response;
} else {
    // Si falló, devolvemos un error controlado
    echo json_encode([
        "error" => true,
        "message" => "Error al conectar con API externa",
        "debug_code" => $httpCode
    ]);
}
?>