<?php
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    http_response_code(400);
    echo json_encode(['error' => 'Token mancante']);
    exit;
}

$exportsDir = __DIR__ . "/exports/";
$filePath = $exportsDir . "$token.xls";
$progressFile = $exportsDir . "$token.progress";
$fileUrl = "exports/$token.xls";

$response = [
    'ready' => false,
    'progress' => 10 // Parti da 10%
];

// 🔁 1. Se esiste il file .progress, leggilo
if (file_exists($progressFile)) {
    $progress = intval(file_get_contents($progressFile));
    $response['progress'] = min(max($progress, 10), 100); // Range 10–100
}

// ✅ 2. Se il file Excel è completo e stabile, segnalo completamento
if (file_exists($filePath)) {
    clearstatcache(true, $filePath);
    $fileSize = filesize($filePath);
    $lastModified = filemtime($filePath);
    $now = time();

    // Se progress è 100% e il file esiste con dimensione > 0, è pronto
    if ($response['progress'] >= 100 && $fileSize > 0) {
        $response['ready'] = true;
        $response['url'] = $fileUrl;
        $response['progress'] = 100;
    }
    // Oppure se il file è stabile (non modificato da almeno 2 secondi) e ha dimensione ragionevole
    else if ($fileSize > 100 * 1024 && ($now - $lastModified) > 2) {
        $response['ready'] = true;
        $response['url'] = $fileUrl;
        $response['progress'] = 100;
    }
}

echo json_encode($response);