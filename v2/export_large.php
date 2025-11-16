<?php
/**
 * FTFS Dashboard v2.0 - Large CSV Export (Streaming)
 *
 * Descrizione:
 * Export CSV ottimizzato per grandi dataset fino a 300.000+ righe.
 * Utilizza streaming output e chunked processing per evitare memory limit.
 * Non carica tutti i dati in memoria ma elabora e invia a blocchi.
 *
 * Endpoint: GET /export_large.php
 *
 * Caratteristiche Tecniche:
 * - Streaming output: fopen('php://output', 'w')
 * - Chunked processing: 5.000 righe per batch
 * - Memory limit: 512MB
 * - Execution time: 600s (10 minuti)
 * - Output buffering: Disabilitato per streaming
 * - Flush periodico: Ogni 100 righe
 *
 * Parametri Query String:
 * - startDate (datetime) - Data/ora inizio filtro
 * - endDate (datetime) - Data/ora fine filtro
 * - minAmount (decimal) - Importo minimo
 * - maxAmount (decimal) - Importo massimo
 * - terminalID (string) - ID terminale
 * - puntoVendita (string) - Nome punto vendita
 * - bu (string) - Business Unit
 *
 * Formato CSV:
 * - Separatore: ; (punto e virgola)
 * - Encoding: UTF-8 con BOM
 * - Nome file: transazioni_ftfs_YYYY-MM-DD_HHmmss.csv
 *
 * Colonne CSV (15 totali):
 * 1. DATA_ORA (formato: dd/mm/yyyy HH:mm:ss)
 * 2. TML_PAYGLOBE
 * 3. TML_ACQUIRER
 * 4. MERCHANT_ID (pulito da prefisso 0001024)
 * 5. TIPO_CARTA (CREDITO/DEBITO)
 * 6. CIRCUITO (VISA, MasterCard, etc.)
 * 7. STATO (CONFERMATA, STORNO, RIFIUTATA, etc.)
 * 8. MODELLO_POS
 * 9. PAN (mascherato)
 * 10. IMPORTO (formato italiano: 1.234,56)
 * 11. CODICE_CONFERMA_ACQ
 * 12. NOME_ACQUIRER (pulito da prefisso 1008)
 * 13. PUNTO_VENDITA
 * 14. CITTA
 * 15. PROVINCIA
 *
 * Headers HTTP:
 * - Content-Type: text/csv; charset=UTF-8
 * - Content-Disposition: attachment; filename="..."
 * - Cache-Control: no-cache
 *
 * Performance Stimata:
 * - 1.000 righe: ~1 secondo
 * - 10.000 righe: ~10 secondi
 * - 100.000 righe: ~100 secondi
 * - 300.000 righe: ~300 secondi (5 minuti)
 *
 * Database:
 * - Tabella: ftfs_transactions (join con stores_*)
 * - LIMIT/OFFSET per chunked processing
 *
 * Sicurezza:
 * - Session validation
 * - Prepared statements
 * - Input sanitization
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

session_start();
if (!isset($_SESSION['username'])) {
    die(json_encode(['error' => 'Non autenticato']));
}

include 'config.php';
include 'bu_config.php';
$bu = htmlspecialchars($_SESSION['bu']);

// Increase limits for large exports
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '600'); // 10 minutes
set_time_limit(600);

// Get filters
$startDate = isset($_GET['startDate']) && $_GET['startDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['startDate'])) : null;
$endDate = isset($_GET['endDate']) && $_GET['endDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['endDate'])) : null;
$minAmount = (isset($_GET['minAmount']) && $_GET['minAmount'] !== '') ? $_GET['minAmount'] : null;
$maxAmount = (isset($_GET['maxAmount']) && $_GET['maxAmount'] !== '') ? $_GET['maxAmount'] : null;
$terminalID = isset($_GET['terminalID']) && $_GET['terminalID'] !== '' ? $_GET['terminalID'] : null;
$puntoVendita = isset($_GET['puntoVendita']) && $_GET['puntoVendita'] !== '' ? $_GET['puntoVendita'] : null;

// Build WHERE clause
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

// Punto vendita
if ($puntoVendita !== null && $puntoVendita !== "") {
    // USA SUBQUERY invece di JOIN per performance
    $storesTableForSubquery = getStoresTable($bu);
    $whereConditions[] = "ft.TermId IN (SELECT TerminalID FROM " . $storesTableForSubquery . " WHERE Insegna = ?)";
    $params[] = $puntoVendita;  // Exact match, non LIKE
    $types .= "s";
}

// Combine WHERE conditions
if (!empty($whereConditions)) {
    $whereClause .= " AND " . implode(" AND ", $whereConditions);
}

$storesTable = getStoresTable($bu);

// First, count total rows for progress tracking
// Fast COUNT without JOIN - molto piu veloce!
$countSql = "SELECT COUNT(*) as total FROM ftfs_transactions ft " . $whereClause;
$stmt = $conn->prepare($countSql);
if (strlen($types) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalRows = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Set headers for CSV download
$filename = 'transazioni_ftfs_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Accel-Buffering: no'); // Disable Nginx buffering

// Add BOM for UTF-8 Excel compatibility
echo "\xEF\xBB\xBF";

// Disable output buffering for streaming
ob_implicit_flush(1); // Enable implicit flush for streaming
if (ob_get_level()) {
    ob_end_clean();
}

// Output CSV header
$headers = [
    'DATA_ORA',
    'TML_PAYGLOBE',
    'TML_ACQUIRER',
    'MERCHANT_ID',
    'TIPO_CARTA',
    'CIRCUITO',
    'STATO',
    'MODELLO_POS',
    'PAN',
    'IMPORTO',
    'CODICE_CONFERMA_ACQ',
    'NOME_ACQUIRER',
    'PUNTO_VENDITA',
    'CITTA',
    'PROVINCIA'
];

// Open output stream
$output = fopen('php://output', 'w');

// Output header row
fputcsv($output, $headers, ';');
flush();
if (ob_get_level()) ob_flush();

// Process data in chunks to avoid memory issues
$chunkSize = 5000; // Process 5000 rows at a time
$offset = 0;
$processedRows = 0;

while ($offset < $totalRows) {
    // Query with LIMIT and OFFSET for chunked processing
    $sql = "SELECT
                ft.TermId AS terminalID,
                ft.Term AS terminal,
                ft.MeId AS codificaStab,
                ft.TPC AS tipocarta,
                st.Modello_pos AS Modello_pos,
                ft.Pan AS pan,
                ft.PosAcq as circuito,
                ft.Conf as stato,
                ft.DtPos AS dataOperazione,
                ft.Amount AS importo,
                ft.ApprNum AS codiceAutorizzativo,
                ft.Acquirer AS acquirer,
                st.Insegna AS insegna,
                st.citta AS citta,
                st.prov AS prov,
                st.sia_pagobancomat as codiceesercente
            FROM ftfs_transactions ft
            LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " .
            $whereClause . "
            ORDER BY ft.DtPos DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    // Add LIMIT and OFFSET to params
    $chunkParams = $params;
    $chunkParams[] = $chunkSize;
    $chunkParams[] = $offset;
    $chunkTypes = $types . "ii";

    $stmt->bind_param($chunkTypes, ...$chunkParams);
    $stmt->execute();
    $result = $stmt->get_result();

    // Process and output each row
    while ($row = $result->fetch_assoc()) {
        $csvRow = [];

        // DATA_ORA
        if ($row["dataOperazione"] && strtotime($row["dataOperazione"]) !== false) {
            $csvRow[] = date('d/m/Y H:i:s', strtotime($row["dataOperazione"]));
        } else {
            $csvRow[] = "";
        }

        // TML_PAYGLOBE
        $csvRow[] = $row["terminalID"];

        // TML_ACQUIRER
        $csvRow[] = $row["terminal"];

        // MERCHANT_ID - Clean codificaStab
        $codificaStab = $row["codificaStab"];
        if (strpos($codificaStab, '0001024') === 0) {
            $codificaStab = substr($codificaStab, 7);
        }

        // Use codiceesercente for SETEFI, otherwise use codificaStab
        if (strpos($row["acquirer"], 'SETEFI') === 0) {
            if (strlen($row["codiceesercente"]) > 0) {
                $csvRow[] = $row["codiceesercente"];
            } else {
                $csvRow[] = $codificaStab;
            }
        } else {
            $csvRow[] = $codificaStab;
        }

        // TIPO_CARTA
        if ($row["tipocarta"] == "C") {
            $csvRow[] = "CREDITO";
        } else if ($row["tipocarta"] == "B") {
            $csvRow[] = "DEBITO";
        } else {
            $csvRow[] = "";
        }

        // CIRCUITO
        $csvRow[] = $row["circuito"];

        // STATO
        $stato = "";
        switch ($row["stato"]) {
            case "I":
                $stato = "STORNO IMPLICITO";
                break;
            case "C":
                $stato = "CONFERMATA";
                break;
            case "D":
                $stato = "STORNO STESSO OP";
                break;
            case "A":
                $stato = "STORNO IMPLICITO";
                break;
            case "E":
                $stato = "STORNO ESPLICITO";
                break;
            case "N":
                $stato = "PREAUTH CONFERMATA";
                break;
            default:
                $stato = "---";
        }
        $csvRow[] = $stato;

        // MODELLO_POS
        $csvRow[] = $row["Modello_pos"];

        // PAN
        $csvRow[] = $row["pan"];

        // IMPORTO
        $csvRow[] = number_format($row["importo"], 2, ',', '.');

        // CODICE_CONFERMA_ACQ - Clean acquirer code
        $acquirer = $row["acquirer"];
        if (strpos($acquirer, '1008') === 0) {
            $acquirer = substr($acquirer, 4);
        }
        $csvRow[] = $row["codiceAutorizzativo"];

        // NOME_ACQUIRER
        $csvRow[] = $acquirer;

        // PUNTO_VENDITA
        $csvRow[] = $row["insegna"];

        // CITTA
        $csvRow[] = $row["citta"];

        // PROVINCIA
        $csvRow[] = $row["prov"];

        // Output CSV row
        fputcsv($output, $csvRow, ';');

        $processedRows++;

        // Flush output buffer every 100 rows to stream data
        if ($processedRows % 100 == 0) {
            flush();
            if (ob_get_level()) ob_flush();
        }
    }

    $stmt->close();
    $offset += $chunkSize;
}

fclose($output);
$conn->close();
?>
