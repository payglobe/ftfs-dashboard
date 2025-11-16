<?php
/**
 * FTFS Dashboard v2.0 - Async Export Worker
 *
 * Descrizione:
 * Background worker che processa export CSV progressivamente.
 * Elabora 500 record alla volta con JOIN per dati store completi.
 *
 * Esecuzione: php export_async_worker.php {job_id}
 *
 * @author Claude Code
 * @version 2.0
 */

// Get job_id from command line
$job_id = $argv[1] ?? null;
if (!$job_id) {
    die("ERROR: job_id not provided\n");
}

// Increase limits
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '0'); // No limit for background process
set_time_limit(0);

// Load config
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/bu_config.php';

// Load job info
$progressFile = sys_get_temp_dir() . '/export_' . $job_id . '_progress.json';
if (!file_exists($progressFile)) {
    die("ERROR: Progress file not found\n");
}

$jobInfo = json_decode(file_get_contents($progressFile), true);
$totalRows = $jobInfo['total_rows'];
$bu = $jobInfo['bu'];
$whereClause = $jobInfo['whereClause'];
$params = $jobInfo['params'];
$types = $jobInfo['types'];

// Open CSV file
$csvFile = sys_get_temp_dir() . '/export_' . $job_id . '.csv';
$output = fopen($csvFile, 'w');

// Add BOM for UTF-8 Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// CSV Headers
$headers = [
    'DATA_ORA', 'TML_PAYGLOBE', 'TML_ACQUIRER', 'MERCHANT_ID',
    'TIPO_CARTA', 'CIRCUITO', 'STATO', 'MODELLO_POS', 'PAN',
    'IMPORTO', 'CODICE_CONFERMA_ACQ', 'NOME_ACQUIRER',
    'PUNTO_VENDITA', 'CITTA', 'PROVINCIA'
];
fputcsv($output, $headers, ';');

// Get stores table
$storesTable = getStoresTable($bu);

// Process in batches of 500 rows
$batchSize = 500;
$offset = 0;
$processedRows = 0;

while ($offset < $totalRows) {
    // Query with JOIN for complete store data
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
    $batchParams = $params;
    $batchParams[] = $batchSize;
    $batchParams[] = $offset;
    $batchTypes = $types . "ii";

    $stmt->bind_param($batchTypes, ...$batchParams);
    $stmt->execute();
    $result = $stmt->get_result();

    // Process each row
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

        // Use codiceesercente for SETEFI
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
            case "I": $stato = "STORNO IMPLICITO"; break;
            case "C": $stato = "CONFERMATA"; break;
            case "D": $stato = "STORNO STESSO OP"; break;
            case "A": $stato = "STORNO IMPLICITO"; break;
            case "E": $stato = "STORNO ESPLICITO"; break;
            case "N": $stato = "PREAUTH CONFERMATA"; break;
            default: $stato = "---";
        }
        $csvRow[] = $stato;

        // MODELLO_POS
        $csvRow[] = $row["Modello_pos"];

        // PAN
        $csvRow[] = $row["pan"];

        // IMPORTO
        $csvRow[] = number_format($row["importo"], 2, ',', '.');

        // CODICE_CONFERMA_ACQ
        $csvRow[] = $row["codiceAutorizzativo"];

        // NOME_ACQUIRER - Clean acquirer code
        $acquirer = $row["acquirer"];
        if (strpos($acquirer, '1008') === 0) {
            $acquirer = substr($acquirer, 4);
        }
        $csvRow[] = $acquirer;

        // PUNTO_VENDITA
        $csvRow[] = $row["insegna"];

        // CITTA
        $csvRow[] = $row["citta"];

        // PROVINCIA
        $csvRow[] = $row["prov"];

        // Write CSV row
        fputcsv($output, $csvRow, ';');
        $processedRows++;
    }

    $stmt->close();
    $offset += $batchSize;

    // Update progress
    $jobInfo['processed_rows'] = $processedRows;
    $jobInfo['percent'] = round(($processedRows / $totalRows) * 100, 2);
    $jobInfo['last_update'] = time();
    file_put_contents($progressFile, json_encode($jobInfo));

    // Sleep briefly to prevent CPU overload
    usleep(10000); // 10ms
}

fclose($output);
$conn->close();

// Mark as completed
$jobInfo['status'] = 'completed';
$jobInfo['processed_rows'] = $totalRows;
$jobInfo['percent'] = 100;
$jobInfo['completion_time'] = time();
$jobInfo['csv_file'] = $csvFile;
file_put_contents($progressFile, json_encode($jobInfo));

echo "Export completed: $processedRows rows\n";
?>
