<?php
/**
 * FTFS Dashboard v2.0 - Async Export Start
 *
 * Descrizione:
 * Inizia export CSV in background e ritorna job_id per tracking.
 * Salva progress in file temporaneo e genera CSV progressivamente.
 *
 * Endpoint: POST /export_async_start.php
 *
 * Parametri POST:
 * - startDate, endDate, minAmount, maxAmount, terminalID, puntoVendita
 *
 * Risposta JSON:
 * {
 *   "status": "started",
 *   "job_id": "export_1234567890_abc123",
 *   "total_rows": 12345
 * }
 *
 * File Generati:
 * - /tmp/export_{job_id}_progress.json - Progress tracking
 * - /tmp/export_{job_id}.csv - CSV finale
 *
 * @author Claude Code
 * @version 2.0
 */

session_start();
if (!isset($_SESSION['username'])) {
    die(json_encode(['error' => 'Non autenticato']));
}

include 'config.php';
include 'bu_config.php';

$bu = trim($_SESSION['bu'], "'");

// Get filters
$startDate = isset($_POST['startDate']) && $_POST['startDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_POST['startDate'])) : null;
$endDate = isset($_POST['endDate']) && $_POST['endDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_POST['endDate'])) : null;
$minAmount = (isset($_POST['minAmount']) && $_POST['minAmount'] !== '') ? $_POST['minAmount'] : null;
$maxAmount = (isset($_POST['maxAmount']) && $_POST['maxAmount'] !== '') ? $_POST['maxAmount'] : null;
$terminalID = isset($_POST['terminalID']) && $_POST['terminalID'] !== '' ? $_POST['terminalID'] : null;
$puntoVendita = isset($_POST['puntoVendita']) && $_POST['puntoVendita'] !== '' ? $_POST['puntoVendita'] : null;

// Build WHERE clause (same as export_large.php)
$whereClause = " WHERE ft.TermId REGEXP ? ";
$params = [$bu];
$types = "s";
$whereConditions = [];

// Not notification
$whereConditions[] = "ft.TP <> ?";
$params[] = "N";
$types .= "s";

// Date range
if ($startDate !== null && $endDate !== null) {
    $whereConditions[] = "ft.DtPos BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

// Amount range
if ($minAmount !== null && $minAmount !== "") {
    $whereConditions[] = "ft.Amount >= ?";
    $params[] = $minAmount;
    $types .= "d";
}
if ($maxAmount !== null && $maxAmount !== "") {
    $whereConditions[] = "ft.Amount <= ?";
    $params[] = $maxAmount;
    $types .= "d";
}

// Terminal ID
if ($terminalID !== null && $terminalID !== "") {
    $whereConditions[] = "ft.TermId = ?";
    $params[] = $terminalID;
    $types .= "s";
}

// Punto vendita - SUBQUERY per performance
if ($puntoVendita !== null && $puntoVendita !== "") {
    $storesTableForSubquery = getStoresTable($bu);
    $whereConditions[] = "ft.TermId IN (SELECT TerminalID FROM " . $storesTableForSubquery . " WHERE Insegna = ?)";
    $params[] = $puntoVendita;
    $types .= "s";
}

// Combine WHERE conditions
if (!empty($whereConditions)) {
    $whereClause .= " AND " . implode(" AND ", $whereConditions);
}

// Fast COUNT without JOIN
$countSql = "SELECT COUNT(*) as total FROM ftfs_transactions ft " . $whereClause;
$stmt = $conn->prepare($countSql);
if (strlen($types) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalRows = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Generate unique job_id
$job_id = 'export_' . time() . '_' . bin2hex(random_bytes(8));

// Save job info to temp file
$jobInfo = [
    'job_id' => $job_id,
    'total_rows' => $totalRows,
    'processed_rows' => 0,
    'status' => 'processing',
    'start_time' => time(),
    'bu' => $bu,
    'whereClause' => $whereClause,
    'params' => $params,
    'types' => $types,
    'filters' => [
        'startDate' => $startDate,
        'endDate' => $endDate,
        'minAmount' => $minAmount,
        'maxAmount' => $maxAmount,
        'terminalID' => $terminalID,
        'puntoVendita' => $puntoVendita
    ]
];

$progressFile = sys_get_temp_dir() . '/export_' . $job_id . '_progress.json';
file_put_contents($progressFile, json_encode($jobInfo));

// Start background process
$phpBinary = PHP_BINARY;
$scriptPath = __DIR__ . '/export_async_worker.php';
$logFile = sys_get_temp_dir() . '/export_' . $job_id . '.log';

// Run in background (Windows and Linux compatible)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    pclose(popen("start /B \"\" \"$phpBinary\" \"$scriptPath\" \"$job_id\" > \"$logFile\" 2>&1", "r"));
} else {
    // Linux: use nohup for reliable background execution
    $cmd = sprintf(
        'nohup %s %s %s > %s 2>&1 & echo $!',
        escapeshellarg($phpBinary),
        escapeshellarg($scriptPath),
        escapeshellarg($job_id),
        escapeshellarg($logFile)
    );
    $pid = shell_exec($cmd);

    // Update job info with PID
    if ($pid) {
        $jobInfo['pid'] = trim($pid);
        file_put_contents($progressFile, json_encode($jobInfo));
    }
}

$conn->close();

// Return job info
header('Content-Type: application/json');
echo json_encode([
    'status' => 'started',
    'job_id' => $job_id,
    'total_rows' => $totalRows
]);
?>
