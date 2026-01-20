<?php
/**
 * FTFS Dashboard v2.0 - Export Storni CSV
 * Export delle transazioni autorizzate ma non confermate
 */

session_start();
if (!isset($_SESSION['username'])) {
    die('Non autenticato');
}

include 'config.php';
include 'bu_config.php';

$bu = trim($_SESSION['bu'], "'");

// Parametri
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-7 days')) . ' 00:00:00';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d H:i:s');
$onlyCritical = isset($_GET['onlyCritical']) ? $_GET['onlyCritical'] === 'true' : false;

// Headers per download CSV
$filename = 'storni_ftfs_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache');

// Output
$output = fopen('php://output', 'w');

// BOM UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header CSV
fputcsv($output, [
    'STATO',
    'TERMINAL_ID',
    'NUM_OPER',
    'DATA_ORA',
    'IMPORTO',
    'COD_AUTORIZZAZIONE',
    'NEXT_NUM_OPER',
    'NEXT_DATA_ORA',
    'MINUTI_DOPO',
    'PUNTO_VENDITA',
    'CITTA'
], ';');

// Query
$query = "
SELECT
    t1.TermId,
    t1.NumOper,
    t1.DtPos as DataNonConfermata,
    t1.Amount,
    t1.ApprNum,
    t1.GtResp,
    t2.NumOper as NextNumOper,
    t2.DtPos as NextDtPos,
    TIMESTAMPDIFF(MINUTE, t1.DtPos, t2.DtPos) as MinutiDopo,
    TIMESTAMPDIFF(MINUTE, t1.DtPos, NOW()) as MinutiDaOra,
    st.Insegna,
    st.citta,
    CASE
        WHEN t2.NumOper IS NULL AND TIMESTAMPDIFF(MINUTE, t1.DtPos, NOW()) > 5 THEN 'A RISCHIO'
        WHEN t2.NumOper IS NULL AND TIMESTAMPDIFF(MINUTE, t1.DtPos, NOW()) <= 5 THEN 'IN ATTESA'
        WHEN t2.NumOper = t1.NumOper THEN 'GIA STORNATA'
        WHEN t2.NumOper < t1.NumOper THEN 'RESETTATO'
        ELSE 'ALTRO'
    END as Stato
FROM ftfs_transactions t1
LEFT JOIN ftfs_transactions t2 ON t2.TermId = t1.TermId
    AND t2.DtPos > t1.DtPos
    AND t2.DtPos = (
        SELECT MIN(DtPos) FROM ftfs_transactions
        WHERE TermId = t1.TermId AND DtPos > t1.DtPos
    )
LEFT JOIN " . getStoresTable($bu) . " st ON t1.TermId = st.TerminalID
WHERE t1.Conf = ' '
AND t1.TP <> 'N'
AND t1.GtResp = '000'
AND t1.ApprNum IS NOT NULL
AND t1.ApprNum <> ''
AND t1.DtPos BETWEEN ? AND ?
AND t1.TermId REGEXP ?
ORDER BY t1.DtPos DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $startDate, $endDate, $bu);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $stato = $row['Stato'];

    // Filtra solo critici se richiesto
    if ($onlyCritical && !in_array($stato, ['GIA STORNATA', 'A RISCHIO'])) {
        continue;
    }

    // Formatta data
    $dataOra = date('d/m/Y H:i:s', strtotime($row['DataNonConfermata']));
    $nextDataOra = $row['NextDtPos'] ? date('d/m/Y H:i:s', strtotime($row['NextDtPos'])) : '';

    // Formatta importo
    $importo = number_format($row['Amount'], 2, ',', '.');

    fputcsv($output, [
        $stato,
        $row['TermId'],
        $row['NumOper'],
        $dataOra,
        $importo,
        $row['ApprNum'],
        $row['NextNumOper'] ?? '',
        $nextDataOra,
        $row['MinutiDopo'] ?? $row['MinutiDaOra'],
        $row['Insegna'] ?? '',
        $row['citta'] ?? ''
    ], ';');
}

$stmt->close();
fclose($output);
?>
