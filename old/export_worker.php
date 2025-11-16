<?php
// export_worker.php — Avviato in background da export_async.php

// Questi messaggi finiranno nel log di Apache (error_log default)
error_log("== INIZIO SCRIPT ==");
error_log("Token: " . $argv[1]);
error_log("StartDate: " . $argv[2]);
error_log("EndDate: " . $argv[3]);
error_log("MinAmount: " . $argv[4]);
error_log("MaxAmount: " . $argv[5]);
error_log("TerminalID: " . $argv[6]);
error_log("PuntoVendita: " . $argv[7]);
error_log("BU: " . $argv[8]);

$token        = $argv[1];
$startDate    = $argv[2];
$endDate      = $argv[3];
$minAmount    = $argv[4];
$maxAmount    = $argv[5];
$terminalID   = $argv[6];
$puntoVendita = $argv[7];
$bu           = $argv[8];

$scriptPath = '/var/www/html/ftfs/export_excel_logic_progress.php';
$phpBin = '/usr/bin/php';

$cmd = "$phpBin \"$scriptPath\" \"$token\" \"$startDate\" \"$endDate\" \"$minAmount\" \"$maxAmount\" \"$terminalID\" \"$puntoVendita\" \"$bu\"  &";
error_log("Eseguo comando: $cmd");

exec($cmd);