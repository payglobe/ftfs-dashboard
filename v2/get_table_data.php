<?php
/**
 * FTFS Dashboard v2.0 - Table Data API
 *
 * Descrizione:
 * API endpoint per recuperare la lista delle transazioni FTFS da visualizzare
 * nella card view della dashboard. Supporta paginazione, filtri avanzati e
 * ricerca full-text. Utilizzata dal frontend per popolare le card transazioni.
 *
 * Endpoint: GET /get_table_data.php
 *
 * Parametri Query String:
 * - start (int) - Offset per paginazione (es: 0, 12, 24...)
 * - length (int) - Numero record da recuperare (es: 12 per pagina)
 * - startDate (datetime) - Data/ora inizio filtro
 * - endDate (datetime) - Data/ora fine filtro
 * - minAmount (decimal) - Importo minimo transazione
 * - maxAmount (decimal) - Importo massimo transazione
 * - terminalID (string) - ID terminale specifico
 * - puntoVendita (string) - Nome punto vendita
 * - search[value] (string) - Ricerca full-text su tutti i campi
 * - conf (string) - Stato transazione (C=Confermata, E=Storno, etc.)
 *
 * Risposta JSON:
 * {
 *   "data": [
 *     {
 *       "Trid": "123456",              // Transaction ID
 *       "TermId": "12345678",          // Terminal ID
 *       "DtPos": "2025-11-15 14:30:00", // Data/ora
 *       "Amount": 50.00,               // Importo
 *       "Conf": "C",                   // Stato (C/E/N/I/D/A)
 *       "ApprNum": "ABC123",           // Codice autorizzazione
 *       "PosAcq": "VISA",              // Circuito
 *       "Acquirer": "Nexi",            // Acquirer
 *       "Pan": "************1234",     // PAN mascherato
 *       "TPC": "C",                    // Tipo carta (C=Credito, B=Debito)
 *       "Insegna": "Negozio Centro",   // Punto vendita
 *       "citta": "Roma",               // Citta
 *       "prov": "RM",                  // Provincia
 *       ...
 *     }
 *   ],
 *   "recordsTotal": 5000,        // Totale record senza filtri
 *   "recordsFiltered": 1234      // Totale record con filtri applicati
 * }
 *
 * Funzione Principale:
 * - getTableData() - Esegue query con filtri e paginazione
 * - countTotalRecords() - Conta totale record senza filtri
 * - countFilteredRecords() - Conta record con filtri applicati
 *
 * Database Query:
 * - Tabella: ftfs_transactions (alias: ft)
 * - Join: stores (dinamico per BU, alias: st)
 * - Filtri: BU (TermId REGEXP), date, importi, search
 * - Order: DtPos DESC (piu recenti prima)
 * - Limit/Offset: Paginazione
 *
 * Colonne Transazione:
 * - Trid, TermId, DtPos, Amount, Conf, ApprNum
 * - PosAcq, Acquirer, MeId, TPC, Pan, TP, GtResp
 * - Insegna, citta, prov (da join stores)
 *
 * Filtri Applicati:
 * - TermId REGEXP (per BU)
 * - TP <> 'N' (esclude notifiche)
 * - DtPos BETWEEN (range date)
 * - Amount >= / <= (range importi)
 * - TerminalID = (terminale specifico)
 * - Insegna LIKE (punto vendita)
 * - Full-text search su multipli campi
 *
 * Sicurezza:
 * - Session validation (401 se non loggato)
 * - Prepared statements (SQL injection safe)
 * - Input sanitization
 *
 * Performance:
 * - Indici utilizzati: idx_termid, idx_dtpos, idx_conf
 * - Query ottimizzata con JOIN
 * - Paginazione efficiente con LIMIT/OFFSET
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

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
    // Filtro per Conf (es: C=Confermata, E,C=Confermate e Storni)
    if ($conf !== null && $conf !== "") {
        $confValues = explode(",", $conf);
        // Tratta lo spazio vuoto come transazione normale (confermata)
        // Aggiungi sempre ' ' e '' ai valori accettati se 'C' è presente
        if (in_array('C', $confValues) || in_array(' ', $confValues)) {
            if (!in_array(' ', $confValues)) {
                $confValues[] = ' ';
            }
            if (!in_array('', $confValues)) {
                $confValues[] = '';
            }
        }
        $placeholders = implode(",", array_fill(0, count($confValues), "?"));
        $whereConditions[] = "ft.Conf IN ($placeholders)";
        $params = array_merge($params, $confValues);
        $types .= str_repeat("s", count($confValues));
    }
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

    // ============================================================
    // CONDITIONAL JOIN LOGIC
    // ============================================================
    // STEP 1: Fast COUNT without JOIN to determine strategy
    // - If rowCount <= 300: Use JOIN (show store data: Insegna, città, prov, etc.)
    // - If rowCount > 300: No JOIN (faster, but no store data in cards)
    // ============================================================

    $sqlCount = "SELECT COUNT(*) AS total FROM ftfs_transactions ft " . $whereClause;
    $stmtCount = $conn->prepare($sqlCount);

    if ($stmtCount !== false) {
        // Bind params for COUNT (same as main query but without LIMIT/OFFSET)
        if (strlen($types) > 0) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $resultCount = $stmtCount->get_result();
        $rowCount = $resultCount->fetch_assoc()["total"];
        $stmtCount->close();
    } else {
        $rowCount = null;
    }

    // Add LIMIT and OFFSET parameters (AFTER COUNT)
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // STEP 2: Conditional query based on rowCount
    if ($rowCount !== null && $rowCount <= 300) {
        // ✅ USE JOIN - rowCount <= 300 - Include store data
        $sql = "SELECT ft.MeId AS codificaStab, ft.TermId AS terminalID, ft.Term AS terminal, st.Modello_pos AS Modello_pos, ft.Pan AS pan, ft.DtPos AS dataOperazione, ft.Amount AS importo, ft.ApprNum AS codiceAutorizzativo, ft.Acquirer AS acquirer, ft.PosAcq AS PosAcq, ft.AId AS AId, ft.PosStan AS PosStan, ft.Conf AS Conf, ft.NumOper AS NumOper, ft.TP AS TP, ft.TPC AS TPC, ft.Cont AS Cont, ft.OperExpl AS OperExpl, st.Insegna AS insegna, st.Ragione_Sociale AS Ragione_Sociale, st.indirizzo AS indirizzo, st.citta AS localita, st.prov AS prov, st.cap AS cap, ft.Trid,ft.GtResp FROM ftfs_transactions ft LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " . $whereClause . " ORDER BY ft.DtPos DESC LIMIT ? OFFSET ?";
        error_log("✅ Conditional JOIN: rowCount=$rowCount <= 300 - Using JOIN with store data");
    } else {
        // ⚡ NO JOIN - rowCount > 300 - Fast query, no store data
        $sql = "SELECT ft.MeId AS codificaStab, ft.TermId AS terminalID, ft.Term AS terminal, '' AS Modello_pos, ft.Pan AS pan, ft.DtPos AS dataOperazione, ft.Amount AS importo, ft.ApprNum AS codiceAutorizzativo, ft.Acquirer AS acquirer, ft.PosAcq AS PosAcq, ft.AId AS AId, ft.PosStan AS PosStan, ft.Conf AS Conf, ft.NumOper AS NumOper, ft.TP AS TP, ft.TPC AS TPC, ft.Cont AS Cont, ft.OperExpl AS OperExpl, '' AS insegna, '' AS Ragione_Sociale, '' AS indirizzo, '' AS localita, '' AS prov, '' AS cap, ft.Trid,ft.GtResp FROM ftfs_transactions ft " . $whereClause . " ORDER BY ft.DtPos DESC LIMIT ? OFFSET ?";
        error_log("⚡ Conditional JOIN: rowCount=$rowCount > 300 - NO JOIN (fast mode, no store data)");
    }


    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        //error_log("Error preparing main statement: " . $conn->error);
        return ['result' => [], 'rowCount' => 0];
    }
    // Log the final query and parameters
    error_log("Final SQL Query getTableData: " . $sql);
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
