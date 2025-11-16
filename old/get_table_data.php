<?php
include 'config.php';
include 'bu_config.php'; // Include BU configuration
session_start();
$bu = trim($_SESSION['bu'], "'");
// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized';
    exit;
}

// Funzione per eseguire la query e restituire i risultati
function getTableData($conn, $bu, $limit, $offset, $startDate = null, $endDate = null, $minAmount = null, $maxAmount = null, $terminalID = null, $puntoVendita = null, $searchValue = null, $conf = null) {
//error_log("getTableData called with: bu=$bu, limit=$limit, offset=$offset, startDate=$startDate, endDate=$endDate, minAmount=$minAmount, maxAmount=$maxAmount, terminalID=$terminalID, puntoVendita=$puntoVendita, searchValue=$searchValue");

   // $whereClause = " WHERE (st.bu = ? OR st.bu1 = ? OR st.TerminalID LIKE ?) "; // Base WHERE clause
   // $params = [$bu, $bu, $bu.'%']; // Parameters for the base WHERE clause
   // $types = "sss"; // Types for the base WHERE clause

    $whereClause = " WHERE  ft.TermId REGEXP ? "; 
    $params[] = $bu; // Parameters for the base WHERE clause
    $types = "s"; // Types for the base WHERE clause

    $whereConditions = []; // Array to store additional WHERE conditions
    
    // se la inserisco ometto quelle rifiutate
   /* if ($conf !== null && $conf !== "") {
        $whereConditions[] = "ft.Conf = ?";
        $params[] = $conf;
        $types .= "s";
    }
*/
    // Add filter conditions
    if ($startDate !== null && $endDate !== null) {
        $whereConditions[] = "ft.DtPos BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= "ss";
    }
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
    if ($terminalID !== null && $terminalID !== "") {
        $whereConditions[] = "ft.TermId = ?";
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

    // Combine additional WHERE conditions with AND
    if (!empty($whereConditions)) {
        $whereClause .= " AND " . implode(" AND ", $whereConditions);
    }

    // Get the correct stores table based on BU
    $storesTable = getStoresTable($bu);

    // Count query
    $sqlCount = "SELECT COUNT(*) AS total FROM ftfs_transactions ft LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " . $whereClause;
    $stmtCount = $conn->prepare($sqlCount);
    if ($stmtCount === false) {
       // error_log("Error preparing count statement: " . $conn->error);
        return ['result' => [], 'rowCount' => 0];
    }
    // Log the final query and parameters
   // error_log("Final SQL Count Query: " . $sqlCount);
   // error_log("Final Count Parameters: " . print_r($params, true));
    //error_log("Final Count Types: " . $types);
    if (strlen($types) > 0) {
        $stmtCount->bind_param($types, ...$params);
    }
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    if ($resultCount === false) {
       // error_log("Error getting count result: " . $stmtCount->error);
        return ['result' => [], 'rowCount' => 0];
    }
    $rowCount = $resultCount->fetch_assoc()['total'];
    $stmtCount->close();

    // Add LIMIT and OFFSET parameters
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // Main query
    $sql = "SELECT ft.MeId AS codificaStab, ft.TermId AS terminalID, ft.Term AS terminal, st.Modello_pos AS Modello_pos, ft.Pan AS pan, ft.DtPos AS dataOperazione, ft.Amount AS importo, ft.ApprNum AS codiceAutorizzativo, ft.Acquirer AS acquirer, ft.PosAcq AS PosAcq, ft.AId AS AId, ft.PosStan AS PosStan, ft.Conf AS Conf, ft.NumOper AS NumOper, ft.TP AS TP, ft.TPC AS TPC, st.Insegna AS insegna, st.Ragione_Sociale AS Ragione_Sociale, st.indirizzo AS indirizzo, st.citta AS localita, st.prov AS prov, st.cap AS cap, ft.Trid,ft.GtResp FROM ftfs_transactions ft LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " . $whereClause . " ORDER BY ft.DtPos DESC LIMIT ? OFFSET ?";


    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        //error_log("Error preparing main statement: " . $conn->error);
        return ['result' => [], 'rowCount' => 0];
    }
    // Log the final query and parameters
    //error_log("Final SQL Query getTableData: " . $sql);
    //error_log("Final Parameters: " . print_r($params, true));
    //error_log("Final Types: " . $types);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Convert Trid to string here
        $row['Trid'] = strval($row['Trid']);
        $data[] = $row;
    }
    $stmt->close();

    //error_log("getTableData returning: " . json_encode(['result' => $data, 'rowCount' => $rowCount]));
    return ['result' => $data, 'rowCount' => $rowCount];
}

// Get parameters from GET request
//$limit = 10;
$limit = isset($_GET['length']) ? intval($_GET['length']) : 10; // Get length, default to 10
$page = isset($_GET['start']) ? $_GET['start'] : 0;
$offset = $page;
$startDate = isset($_GET['startDate']) && $_GET['startDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['startDate'])) : null;
$endDate = isset($_GET['endDate']) && $_GET['endDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['endDate'])) : null;
$minAmount = (isset($_GET['minAmount']) && $_GET['minAmount'] !== '') ? $_GET['minAmount'] : null;
$maxAmount = (isset($_GET['maxAmount']) && $_GET['maxAmount'] !== '') ? $_GET['maxAmount'] : null;
$terminalID = isset($_GET['terminalID']) ? $_GET['terminalID'] : null;
$puntoVendita = isset($_GET['puntoVendita']) ? $_GET['puntoVendita'] : null;
$searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : null; // Get the search term
//error_log("get_table_data.php received: startDate=$startDate, endDate=$endDate, minAmount=$minAmount, maxAmount=$maxAmount, terminalID=$terminalID, puntoVendita=$puntoVendita, page=$page, searchValue=$searchValue");
$conf = isset($_GET['conf']) ? $_GET['conf'] : null;
// Ottieni i dati della tabella
$tableData = getTableData($conn, $bu, $limit, $offset, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $puntoVendita, $searchValue, $conf);

// Restituisci i dati in formato JSON
header('Content-Type: application/json');
echo json_encode($tableData);
?>
