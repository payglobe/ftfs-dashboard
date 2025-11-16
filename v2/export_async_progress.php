<?php
/**
 * FTFS Dashboard v2.0 - Async Export Progress API
 *
 * Descrizione:
 * Restituisce lo stato di avanzamento dell'export asincrono.
 *
 * Endpoint: GET /export_async_progress.php?job_id={job_id}
 *
 * Risposta JSON:
 * {
 *   "status": "processing",
 *   "percent": 45.67,
 *   "processed_rows": 5432,
 *   "total_rows": 11890,
 *   "elapsed_time": 12
 * }
 *
 * Stati possibili:
 * - processing: In elaborazione
 * - completed: Completato
 * - error: Errore
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

// Calculate elapsed time
$elapsedTime = time() - $jobInfo['start_time'];

// Prepare response
$response = [
    'status' => $jobInfo['status'],
    'percent' => $jobInfo['percent'] ?? 0,
    'processed_rows' => $jobInfo['processed_rows'],
    'total_rows' => $jobInfo['total_rows'],
    'elapsed_time' => $elapsedTime
];

// Add completion info if completed
if ($jobInfo['status'] === 'completed') {
    $response['completion_time'] = $jobInfo['completion_time'];
    $response['total_duration'] = $jobInfo['completion_time'] - $jobInfo['start_time'];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
