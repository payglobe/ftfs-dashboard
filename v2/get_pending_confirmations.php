<?php
/**
 * FTFS Dashboard v2.0 - Pending Confirmations API
 *
 * Descrizione:
 * API endpoint per identificare transazioni con Conf=' ' (spazio) che potrebbero
 * diventare storni impliciti. Analizza la sequenza di NumOper per ogni terminale.
 *
 * Logica di Identificazione:
 * - STORNO IMPLICITO CERTO: La prossima TX ha lo stesso NumOper
 * - POSSIBILE STORNO IMPLICITO: Nessuna TX successiva entro 5 minuti (per TX recenti)
 * - CONTATORE RESETTATO: Il NumOper successivo e' inferiore (terminale riavviato)
 * - CONFERMATA (DDL): Il NumOper successivo e' +1 (DDL o conferma normale)
 *
 * Endpoint: GET /get_pending_confirmations.php
 *
 * @author Claude Code
 * @version 2.0
 * @date Gennaio 2026
 */

include 'config.php';
include 'bu_config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$bu = trim($_SESSION['bu'], "'");
header('Content-Type: application/json');

// Parametri
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-7 days')) . ' 00:00:00';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d H:i:s');
$onlyCritical = isset($_GET['onlyCritical']) ? $_GET['onlyCritical'] === 'true' : false;

// Query per trovare transazioni con Conf=' ' e analizzare la sequenza
$query = "
SELECT
    t1.TermId,
    t1.NumOper,
    t1.DtPos as DataNonConfermata,
    t1.Amount,
    t1.ApprNum,
    t1.GtResp,
    t1.Pan,
    t2.NumOper as NextNumOper,
    t2.DtPos as NextDtPos,
    t2.Conf as NextConf,
    TIMESTAMPDIFF(MINUTE, t1.DtPos, t2.DtPos) as MinutiDopo,
    TIMESTAMPDIFF(MINUTE, t1.DtPos, NOW()) as MinutiDaOra,
    st.Insegna,
    st.citta,
    CASE
        WHEN t2.NumOper IS NULL AND TIMESTAMPDIFF(MINUTE, t1.DtPos, NOW()) > 5 THEN 'POSSIBILE_STORNO_IMPLICITO'
        WHEN t2.NumOper IS NULL AND TIMESTAMPDIFF(MINUTE, t1.DtPos, NOW()) <= 5 THEN 'IN_ATTESA'
        WHEN t2.NumOper = t1.NumOper THEN 'STORNO_IMPLICITO_CERTO'
        WHEN t2.NumOper < t1.NumOper THEN 'CONTATORE_RESETTATO'
        WHEN t2.NumOper = t1.NumOper + 1 THEN 'CONFERMATA_DDL'
        ELSE 'CONTATORE_SALTATO'
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

$transactions = [];
$stats = [
    'totale' => 0,
    'storno_implicito_certo' => 0,
    'possibile_storno_implicito' => 0,
    'in_attesa' => 0,
    'contatore_resettato' => 0,
    'confermata_ddl' => 0,
    'contatore_saltato' => 0,
    'importo_a_rischio' => 0
];

while ($row = $result->fetch_assoc()) {
    // Filtra solo casi critici se richiesto
    if ($onlyCritical && !in_array($row['Stato'], ['STORNO_IMPLICITO_CERTO', 'POSSIBILE_STORNO_IMPLICITO'])) {
        continue;
    }

    $transactions[] = [
        'termId' => $row['TermId'],
        'numOper' => $row['NumOper'],
        'dataNonConfermata' => $row['DataNonConfermata'],
        'amount' => floatval($row['Amount']),
        'apprNum' => $row['ApprNum'],
        'gtResp' => $row['GtResp'],
        'pan' => $row['Pan'],
        'nextNumOper' => $row['NextNumOper'],
        'nextDtPos' => $row['NextDtPos'],
        'nextConf' => $row['NextConf'],
        'minutiDopo' => $row['MinutiDopo'],
        'minutiDaOra' => $row['MinutiDaOra'],
        'insegna' => $row['Insegna'],
        'citta' => $row['citta'],
        'stato' => $row['Stato'],
        'autorizzata' => !empty($row['ApprNum']) && $row['GtResp'] === '000'
    ];

    $stats['totale']++;

    // Conta per stato
    switch ($row['Stato']) {
        case 'STORNO_IMPLICITO_CERTO':
            $stats['storno_implicito_certo']++;
            break;
        case 'POSSIBILE_STORNO_IMPLICITO':
            $stats['possibile_storno_implicito']++;
            // Se autorizzata, somma all'importo a rischio
            if (!empty($row['ApprNum']) && $row['GtResp'] === '000') {
                $stats['importo_a_rischio'] += floatval($row['Amount']);
            }
            break;
        case 'IN_ATTESA':
            $stats['in_attesa']++;
            break;
        case 'CONTATORE_RESETTATO':
            $stats['contatore_resettato']++;
            break;
        case 'CONFERMATA_DDL':
            $stats['confermata_ddl']++;
            break;
        case 'CONTATORE_SALTATO':
            $stats['contatore_saltato']++;
            break;
    }
}

$stmt->close();

echo json_encode([
    'success' => true,
    'data' => $transactions,
    'stats' => $stats,
    'filters' => [
        'startDate' => $startDate,
        'endDate' => $endDate,
        'onlyCritical' => $onlyCritical
    ]
], JSON_PRETTY_PRINT);
?>
