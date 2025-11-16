<?php
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    http_response_code(400);
    echo json_encode(['error' => 'Token mancante']);
    exit;
}

$filePath = __DIR__ . "/exports/$token.xls";
$fileUrl = "exports/$token.xls";

if (file_exists($filePath)) {
    clearstatcache(true, $filePath); // 🔄 forza aggiornamento dati file

    $fileSize = filesize($filePath);
    $lastModified = filemtime($filePath);
    $now = time();

    // 🔒 Sicurezza: il file è considerato pronto se è >100KB e stabile da almeno 2 secondi
    if ($fileSize > 100 * 1024 && ($now - $lastModified) > 2) {
        echo json_encode([
            'ready' => true,
            'url' => $fileUrl
        ]);
        exit;
    }
}

// Altrimenti non ancora pronto
echo json_encode(['ready' => false]);
