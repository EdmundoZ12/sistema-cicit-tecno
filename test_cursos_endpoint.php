<?php

// Script de prueba para verificar el endpoint de cursos
use GuzzleHttp\Client;

require_once 'vendor/autoload.php';

$client = new Client();

try {
    echo "Probando endpoint /responsable/cursos...\n";

    $response = $client->get('http://localhost:8000/responsable/cursos', [
        'headers' => [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ],
        'allow_redirects' => false
    ]);

    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Headers: " . json_encode($response->getHeaders(), JSON_PRETTY_PRINT) . "\n";
    echo "Body: " . $response->getBody() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getResponse') && $e->getResponse()) {
        echo "Response Status: " . $e->getResponse()->getStatusCode() . "\n";
        echo "Response Body: " . $e->getResponse()->getBody() . "\n";
    }
}
