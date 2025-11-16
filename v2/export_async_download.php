<?php
/**
 * FTFS Dashboard v2.0 - Async Export Download
 *
 * Descrizione:
 * Scarica il file CSV completato dall'export asincrono.
 *
 * Endpoint: GET /export_async_download.php?job_id={job_id}
 *
 * Risposta: File CSV
 *
 * @author Claude Code
 * @version 2.0
 */

session_start();
if (!isset($_SESSION['username'])) {
    die(json_encode(['error' => 'Non autenticato']));
}

$job_id = $_GET['job_id'] ?? null;
if (!$job_id) {
    die(json_encode(['error' => 'job_id richiesto']));
}

// Load progress file
$progressFile = sys_get_temp_dir() . '/export_' . $job_id . '_progress.json';

if (!file_exists($progressFile)) {
    die(json_encode(['error' => 'Job non trovato']));
}

$jobInfo = json_decode(file_get_contents($progressFile), true);

// Check if completed
if ($jobInfo['status'] !== 'completed') {
    die(json_encode(['error' => 'Export non ancora completato']));
}

// Get CSV file path
$csvFile = $jobInfo['csv_file'] ?? (sys_get_temp_dir() . '/export_' . $job_id . '.csv');

if (!file_exists($csvFile)) {
    die(json_encode(['error' => 'File CSV non trovato']));
}

// Send file
$filename = 'transazioni_ftfs_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($csvFile));
header('Pragma: no-cache');
header('Expires: 0');

// Stream file
readfile($csvFile);

// Optional: Delete temp files after download
// unlink($csvFile);
// unlink($progressFile);
?>
