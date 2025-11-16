<?php
/*

use payglobe;
-- Per la JOIN
CREATE INDEX idx_stores_TerminalID ON stores(TerminalID);
CREATE INDEX idx_ftfs_transactions_TermId ON ftfs_transactions(TermId);

-- Per il filtro WHERE
CREATE INDEX idx_stores_bu ON stores(bu);
CREATE INDEX idx_stores_bu1 ON stores(bu1);
CREATE INDEX idx_stores_TerminalID_prefix ON stores(TerminalID); -- anche se il LIKE può non usare bene l'indice

-- Per ORDER BY
CREATE INDEX idx_ftfs_transactions_DtPos ON ftfs_transactions(DtPos DESC);
*/
include 'config.php'; // Assicurati che questo file esista e sia corretto
include 'bu_config.php'; // Include BU configuration
session_start();
$bu = trim($_SESSION['bu'], "'");
// Funzione per ottenere i dati per il diagramma a torta e il riepilogo
function getAcquirerData($conn, $bu, $startDate = null, $endDate = null, $minAmount = null, $maxAmount = null, $terminalID = null, $puntoVendita = null, $searchValue = null, $conf = null) {
    //error_log("getAcquirerData called with: bu=$bu, startDate=$startDate, endDate=$endDate, minAmount=$minAmount, maxAmount=$maxAmount, terminalID=$terminalID, puntoVendita=$puntoVendita, searchValue=$searchValue");
    $whereClause = " WHERE  ft.TermId REGEXP ? "; // Added st.bu = '12'
     //error_log("BU: " . $bu);
    $params[] = $bu;
    $types = "s";
    $whereConditions = [];

    // Build the query description
    $queryDescription = "Visualizzazione delle transazioni FTFS";
    if ($startDate && $endDate) {
        $queryDescription .= " dal " . date("d/m/Y H:i:s", strtotime($startDate)) . " al " . date("d/m/Y H:i:s", strtotime($endDate));
    }
    if ($minAmount !== null) {
        $queryDescription .= " con importo minimo di " . number_format($minAmount, 2, ',', '.') . " €";
    }
    if ($maxAmount !== null) {
        $queryDescription .= " e importo massimo di " . number_format($maxAmount, 2, ',', '.') . " €";
    }
    if ($terminalID !== null && $terminalID !== "") {
        $queryDescription .= " per il terminale con ID " . $terminalID;
    }
    if ($puntoVendita !== null && $puntoVendita !== "") {
        $queryDescription .= " per il punto vendita " . $puntoVendita;
    }

    if ($conf !== null && $conf !== "") {
        $confValues = explode(",", $conf); // Split the string into an array
        $placeholders = implode(",", array_fill(0, count($confValues), "?")); // Create placeholders (?,?,?)
        $whereConditions[] = "ft.Conf IN ($placeholders)"; // Use IN clause with multiple placeholders
        $params = array_merge($params, $confValues); // Add each value to the parameters array
        $types .= str_repeat("s", count($confValues)); // Add the correct number of 's' types
    }

    /*if ($conf !== null && $conf !== "") {
        //$whereConditions[] = "ft.Conf = ?";
        $whereConditions[] = "ft.Conf IN ('E','C')"; // Use IN clause
       //  $params[] = $conf;
       // $types .= "s";
    }*/
       // ft.GtResp = '000' ok 116 = fondi insuff 

        // diverso da Notific
        $whereConditions[] = "ft.TP <> ?"; // Changed to ft.DtPos
        $params[] = "N";
        $types .= "s";

         $whereConditions[] = "ft.GtResp = ?"; // Changed to ft.DtPos
        $params[] = "000";
        $types .= "s";

    if ($startDate !== null && $endDate !== null) {
        $whereConditions[] = "ft.DtPos BETWEEN ? AND ?"; // Changed to ft.DtPos
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= "ss";
    }

    if ($minAmount !== null && $minAmount !== "") {
        $whereConditions[] = "ft.Amount >= ?"; // Changed to ft.Amount
        $params[] = $minAmount;
        $types .= "d";
    }

    if ($maxAmount !== null && $maxAmount !== "") {
        $whereConditions[] = "ft.Amount <= ?"; // Changed to ft.Amount
        $params[] = $maxAmount;
        $types .= "d";
    }

    if ($terminalID !== null && $terminalID !== "") {
        $whereConditions[] = "ft.TermId = ?"; // Changed to ft.TermId
        $params[] = $terminalID;
        $types .= "s";
    }
    if ($puntoVendita !== null && $puntoVendita !== "") {
        // Filtra per TerminalID usando una subquery - molto più veloce!
        $storesTableForSubquery = getStoresTable($bu);
        $whereConditions[] = "ft.TermId IN (SELECT TerminalID FROM " . $storesTableForSubquery . " WHERE Insegna = ?)";
        $params[] = $puntoVendita;
        $types .= "s";
    }
    // Add search condition
    if ($searchValue !== null && $searchValue !== "") {
        $searchCondition = "(ft.MeId LIKE ? OR ft.TermId LIKE ? OR st.Modello_pos LIKE ? OR ft.Pan LIKE ? OR ft.ApprNum LIKE ? OR ft.Acquirer LIKE ? OR st.Insegna LIKE ? OR st.Ragione_Sociale LIKE ? OR st.indirizzo LIKE ? OR ft.SiaCode LIKE ?)";
        $whereConditions[] = $searchCondition;
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $params[] = "%" . $searchValue . "%";
        $types .= "ssssssssss";
    }
    if (!empty($whereConditions)) {
        $whereClause .= " AND " . implode(" AND ", $whereConditions);
    }

    // Get the correct stores table based on BU
    $storesTable = getStoresTable($bu);

    // Modified SQL to include JOIN with stores and use aliases
    $sql = "SELECT ft.Acquirer, COUNT(*) AS count, SUM(ft.Amount) AS total_amount FROM ftfs_transactions ft LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " . $whereClause . " GROUP BY ft.Acquirer";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
       // error_log("Error preparing main statement: " . $conn->error);
        return ['acquirerData' => [], 'totalTransactions' => 0, 'totalAmount' => 0, 'queryDescription' => ""];
    }
    // Log the final query and parameters
    //error_log("Final SQL Query getAcquirerData: " . $sql);
    //error_log("Final Parameters: " . print_r($params, true));
    //error_log("Final Types: " . $types);
    if (strlen($types) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false) {
      //  error_log("Error getting result: " . $stmt->error);
        return ['acquirerData' => [], 'totalTransactions' => 0, 'totalAmount' => 0, 'queryDescription' => ""];
    }
    $data = [];
    $totalTransactions = 0;
    $totalAmount = 0;
    while ($row = $result->fetch_assoc()) {
        $data[$row['Acquirer']] = ['count' => $row['count'], 'total_amount' => $row['total_amount']];
        $totalTransactions += $row['count'];
        $totalAmount += $row['total_amount'];
    }
    $stmt->close();
   // error_log("getAcquirerData returning: " . json_encode(['acquirerData' => $data, 'totalTransactions' => $totalTransactions, 'totalAmount' => $totalAmount]));
    return ['acquirerData' => $data, 'totalTransactions' => $totalTransactions, 'totalAmount' => $totalAmount, 'queryDescription' => $queryDescription];
}

// Recupera i parametri dai parametri GET
$startDate = isset($_GET['startDate']) && $_GET['startDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['startDate'])) : null;
$endDate = isset($_GET['endDate']) && $_GET['endDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['endDate'])) : null;
$minAmount = (isset($_GET['minAmount']) && $_GET['minAmount'] !== '') ? $_GET['minAmount'] : null;
$maxAmount = (isset($_GET['maxAmount']) && $_GET['maxAmount'] !== '') ? $_GET['maxAmount'] : null;
$terminalID = isset($_GET['terminalID']) ? $_GET['terminalID'] : null;
$puntoVendita = isset($_GET['puntoVendita']) ? $_GET['puntoVendita'] : null;
$searchValue = isset($_GET['searchValue']) ? $_GET['searchValue'] : null; // Get the search term
//error_log("get_summary.php received: startDate=$startDate, endDate=$endDate, minAmount=$minAmount, maxAmount=$maxAmount, terminalID=$terminalID, puntoVendita=$puntoVendita, searchValue=$searchValue");
$conf = isset($_GET['conf']) ? $_GET['conf'] : null;

// Ottieni i dati per il diagramma a torta e il riepilogo
$acquirerDataResult = getAcquirerData($conn, $bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $puntoVendita, $searchValue, $conf);
$acquirerData = $acquirerDataResult['acquirerData'];
$totalTransactions = $acquirerDataResult['totalTransactions'];
$totalAmount = $acquirerDataResult['totalAmount'];
$queryDescription = $acquirerDataResult['queryDescription']; // Get the description

// Restituisci i dati in formato JSON
header('Content-Type: application/json');
echo json_encode(['acquirerData' => $acquirerData, 'totalTransactions' => $totalTransactions, 'totalAmount' => $totalAmount, 'queryDescription' => $queryDescription]);
?>
