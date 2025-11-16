<?php
require_once 'config.php';
require_once 'bu_config.php'; // Include BU configuration

// Log iniziale su Apache
error_log("== INIZIO SCRIPT ==");

// Prendi parametri da linea di comando
$token        = $argv[1] ?? '';
$startDate    = $argv[2] ?? null;
$endDate      = $argv[3] ?? null;
$minAmount    = $argv[4] ?? null;
$maxAmount    = $argv[5] ?? null;
$terminalID   = $argv[6] ?? null;
$puntoVendita = $argv[7] ?? null;
$bu           = $argv[8] ?? '';

error_log("Token: $token");
error_log("StartDate: $startDate | EndDate: $endDate | BU: $bu");

// Percorsi
$progressFile = __DIR__ . "/exports/{$token}.progress";
$outputFile   = __DIR__ . "/exports/{$token}.xls";

// Connessione al DB
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    error_log("Errore connessione DB: " . $conn->connect_error);
    file_put_contents($progressFile, 0);
    exit(1);
}
error_log("Connessione DB OK");

// Costruzione query
$whereClause = " WHERE ft.TermId REGEXP ? ";
$params = [$bu];
$types  = "s";

$whereConditions = [
    "ft.GtResp = ?" => "000",
    "ft.TP <> ?"    => "N"
];

foreach ($whereConditions as $condition => $val) {
    $whereClause .= " AND $condition";
    $params[] = $val;
    $types  .= "s";
}

if ($startDate && $endDate) {
    $whereClause .= " AND ft.DtPos BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types   .= "ss";
}
if ($minAmount !== null && $minAmount !== "") {
    $whereClause .= " AND ft.Amount >= ?";
    $params[] = $minAmount;
    $types   .= "d";
}
if ($maxAmount !== null && $maxAmount !== "") {
    $whereClause .= " AND ft.Amount <= ?";
    $params[] = $maxAmount;
    $types   .= "d";
}
if ($terminalID !== null && $terminalID !== "") {
    $whereClause .= " AND ft.TermId = ?";
    $params[] = $terminalID;
    $types   .= "s";
}
if ($puntoVendita !== null && $puntoVendita !== "") {
    // Filtra per TerminalID usando una subquery - molto più veloce!
    $storesTableForSubquery = getStoresTable($bu);
    $whereClause .= " AND ft.TermId IN (SELECT TerminalID FROM " . $storesTableForSubquery . " WHERE Insegna = ?)";
    $params[] = $puntoVendita;
    $types   .= "s";
}

// Get the correct stores table based on BU
$storesTable = getStoresTable($bu);
error_log("Using stores table: $storesTable");

// Conta righe
$countSQL = "SELECT COUNT(*) FROM ftfs_transactions ft
LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId $whereClause";
error_log("COUNT SQL: $countSQL");

$countStmt = $conn->prepare($countSQL);
if (!$countStmt) {
    error_log("Errore prepare COUNT: " . $conn->error);
    exit(1);
}
$countStmt->bind_param($types, ...$params);
$countStmt->execute();
$countStmt->bind_result($totalRows);
$countStmt->fetch();
$countStmt->close();

error_log("Totale righe da esportare: $totalRows");

$fp = fopen($outputFile, 'w');
if (!$fp) {
    error_log("Errore apertura file per scrittura: $outputFile");
    exit(1);
}
fwrite($fp, "DATA_ORA\tTML_PAYGLOBE\tTML_ACQUIRER\tMERCHANT_ID\tTIPO_CARTA\tCIRCUITO\tSTATO\tMODELLO_POS\tPAN\tIMPORTO\tCODICE_CONFERMA_ACQ\tNOME_ACQUIRER\tPUNTO_VENDITA\tCITTA\tSORGENTE\n");

// Query dati
$sql = "SELECT
    ft.TermId AS terminalID,
    ft.Term AS terminal,
    ft.MeId AS codificaStab,
    ft.TPC AS tipocarta,
    st.Modello_pos AS Modello_pos,
    ft.Pan AS pan,
    ft.PosAcq AS circuito,
    ft.Conf AS stato,
    ft.DtPos AS dataOperazione,
    ft.Amount AS importo,
    ft.ApprNum AS codiceAutorizzativo,
    ft.Acquirer AS acquirer,
    st.Insegna AS insegna,
    st.citta AS citta,
    st.prov AS prov,
    st.sia_pagobancomat AS codiceesercente
FROM ftfs_transactions ft
LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId $whereClause";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Errore prepare SELECT: " . $conn->error);
    exit(1);
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rowsWritten = 0;
error_log("Inizio scrittura righe su file...");

while ($row = $result->fetch_assoc()) {
    if (strpos($row["acquirer"], '1008') === 0) {
        $row["acquirer"] = substr($row["acquirer"], 4);
    }

    $formattedDate = $row["dataOperazione"] && strtotime($row["dataOperazione"]) !== false
        ? date('d/m/Y H:i:s', strtotime($row["dataOperazione"]))
        : "";

    $line = $formattedDate . "\t";
    $line .= $row["terminalID"] . "\t";
    $line .= "'" . $row["terminal"] . "\t";

    if (strpos($row["codificaStab"], '0001024') === 0) {
        $row["codificaStab"] = substr($row["codificaStab"], 7);
    }

    if (strpos($row["acquirer"], 'SETEFI') === 0) {
        $line .= "'" . ($row["codiceesercente"] ?: $row["codificaStab"]) . "\t";
    } else {
        $line .= "'" . $row["codificaStab"] . "\t";
    }

    $line .= ($row["tipocarta"] === "C" ? "CREDITO" : ($row["tipocarta"] === "B" ? "DEBITO" : "")) . "\t";
    $line .= $row["circuito"] . "\t";

    switch ($row["stato"]) {
        case "I": $line .= "STORNO IMPLICITO\t"; break;
        case "C": $line .= "CONFERMATA\t"; break;
        case "D": $line .= "STORNO STESSO OP\t"; break;
        case "A": $line .= "STORNO IMPLICITO\t"; break;
        case "E": $line .= "STORNO ESPLICITO\t"; break;
        case "N": $line .= "PREAUTH CONFERMATA\t"; break;
        default: $line .= "---\t"; break;
    }

    $line .= $row["Modello_pos"] . "\t";
    $line .= $row["pan"] . "\t";
    $line .= number_format($row["importo"], 2, ',', '.') . "\t";
    $line .= "'" . $row["codiceAutorizzativo"] . "\t";
    $line .= $row["acquirer"] . "\t";
    $line .= $row["insegna"] . "\t";
    $line .= $row["citta"] . "\t";
    $line .= $row["prov"] . "\n";

    fwrite($fp, $line);

    $rowsWritten++;
    if ($rowsWritten % 10 === 0 || $rowsWritten === $totalRows) {
        $progress = intval(($rowsWritten / $totalRows) * 100);
        file_put_contents($progressFile, $progress);
        error_log("Scritti $rowsWritten / $totalRows → $progress%");
    }
}

fclose($fp);
$conn->close();

error_log("File esportato: $outputFile");
error_log("== FINE SCRIPT ==");