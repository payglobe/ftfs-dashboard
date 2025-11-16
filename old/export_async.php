<?php
session_start();

error_log("=== START async export ===");
error_log("GET: " . print_r($_GET, true));

$bu            = $_GET['bu'] ?? '';
$startDate     = $_GET['startDate'] ?? '';
$endDate       = $_GET['endDate'] ?? '';
$minAmount     = $_GET['minAmount'] ?? '';
$maxAmount     = $_GET['maxAmount'] ?? '';
$terminalID    = $_GET['terminalID'] ?? '';
$puntoVendita  = $_GET['puntoVendita'] ?? '';
$token         = uniqid("export_", true);

$filePath = __DIR__ . "/exports/$token.xls";

// ✅ Costruisci il comando correttamente, con output rediretto
$phpBin = '/usr/bin/php'; // usa path completo per sicurezza
$cmd = sprintf(
    '%s %s %s %s %s %s %s %s %s %s > /dev/null 2>&1 &',
    escapeshellcmd($phpBin),
    escapeshellarg(__DIR__ . '/export_worker.php'),
    escapeshellarg($token),
    escapeshellarg($startDate),
    escapeshellarg($endDate),
    escapeshellarg($minAmount),
    escapeshellarg($maxAmount),
    escapeshellarg($terminalID),
    escapeshellarg($puntoVendita),
    escapeshellarg($bu)
);

error_log("CMD: $cmd");

// ✅ Avvia in *vero* background, senza attendere esito
pclose(popen($cmd, 'r'));

echo json_encode(["token" => $token]);