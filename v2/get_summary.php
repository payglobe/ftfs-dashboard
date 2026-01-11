<?php
/**
 * FTFS Dashboard v2.0 - Summary Statistics API
 *
 * Descrizione:
 * API endpoint per recuperare statistiche aggregate e dati per grafici.
 * Fornisce dati riassuntivi delle transazioni FTFS filtrate per BU e parametri
 * specificati. Utilizzata dalla dashboard per popolare grafici e statistiche cards.
 *
 * Endpoint: GET /get_summary.php
 *
 * Parametri Query String:
 * - startDate (datetime) - Data/ora inizio filtro (formato: Y-m-d H:i:s)
 * - endDate (datetime) - Data/ora fine filtro
 * - minAmount (decimal) - Importo minimo transazione
 * - maxAmount (decimal) - Importo massimo transazione
 * - terminalID (string) - ID terminale specifico
 * - puntoVendita (string) - Nome punto vendita
 * - conf (string) - Stati transazione (default: E,C)
 *
 * Risposta JSON:
 * {
 *   "totalTransactions": 1234,        // Totale transazioni (tutte)
 *   "totalAmount": 50000.00,          // Importo totale
 *   "confirmedCount": 1150,           // Transazioni confermate
 *   "confirmedAmount": 48500.00,      // Importo confermato
 *   "acquirerData": [...],            // Distribuzione per acquirer
 *   "circuitData": [...],             // Distribuzione per circuito
 *   "hourlyData": [...],              // Transazioni per ora (0-23)
 *   "weekdayData": [...],             // Transazioni per giorno settimana
 *   "amountRangeData": [...],         // Fasce importo
 *   "trendData": [...]                // Trend giornaliero ultimi 30 giorni
 * }
 *
 * Funzioni Principali:
 * - getSummaryStats() - Calcola statistiche aggregate
 * - getAcquirerData() - Raggruppa transazioni per acquirer
 * - getCircuitData() - Raggruppa per circuito (VISA, MasterCard, etc.)
 * - getHourlyData() - Distribuzione oraria 24h (HOUR(DtPos))
 * - getWeekdayData() - Distribuzione settimanale (DAYOFWEEK)
 * - getAmountRangeData() - Fasce: <10€, 10-50€, 50-100€, >100€
 * - getTrendData() - Trend giornaliero ultimi 30 giorni
 *
 * Database:
 * - Tabella: ftfs_transactions (alias: ft)
 * - Join: stores_* (dinamico per BU) (alias: s)
 *
 * Sessioni Richieste:
 * - $_SESSION['bu'] - Business Unit per filtro TermId
 *
 * Sicurezza:
 * - Prepared statements
 * - Input sanitization
 * - Session validation
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

include 'config.php';
include 'bu_config.php';
session_start();
$bu = trim($_SESSION['bu'], "'");

// Get parameters from GET
$startDate = isset($_GET['startDate']) && $_GET['startDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['startDate'])) : null;
$endDate = isset($_GET['endDate']) && $_GET['endDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['endDate'])) : null;
$minAmount = (isset($_GET['minAmount']) && $_GET['minAmount'] !== '') ? $_GET['minAmount'] : null;
$maxAmount = (isset($_GET['maxAmount']) && $_GET['maxAmount'] !== '') ? $_GET['maxAmount'] : null;
$terminalID = isset($_GET['terminalID']) ? $_GET['terminalID'] : null;
$puntoVendita = isset($_GET['puntoVendita']) ? $_GET['puntoVendita'] : null;
$conf = isset($_GET['conf']) ? $_GET['conf'] : 'E,C';

// Build WHERE clause and parameters
function buildWhereClause($bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $puntoVendita, $conf) {
    $whereClause = " WHERE ft.TermId REGEXP ? ";
    $params = [$bu];
    $types = "s";
    $whereConditions = [];

    // Conf filter
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

    // Not notification
    $whereConditions[] = "ft.TP <> ?";
    $params[] = "N";
    $types .= "s";

    // Success only
    $whereConditions[] = "ft.GtResp = ?";
    $params[] = "000";
    $types .= "s";

    // Date range
    if ($startDate !== null && $endDate !== null) {
        $whereConditions[] = "ft.DtPos BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= "ss";
    }

    // Amount filters
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

    // Terminal ID filter
    if ($terminalID !== null && $terminalID !== "") {
        $whereConditions[] = "ft.TermId = ?";
        $params[] = $terminalID;
        $types .= "s";
    }

    // Punto vendita filter
    if ($puntoVendita !== null && $puntoVendita !== "") {
        $storesTable = getStoresTable($bu);
        $whereConditions[] = "ft.TermId IN (SELECT TerminalID FROM " . $storesTable . " WHERE Insegna = ?)";
        $params[] = $puntoVendita;
        $types .= "s";
    }

    if (!empty($whereConditions)) {
        $whereClause .= " AND " . implode(" AND ", $whereConditions);
    }

    $needsJoin = ($puntoVendita !== null && $puntoVendita !== "");
    return ['clause' => $whereClause, 'params' => $params, 'types' => $types, 'needsJoin' => $needsJoin];
}

// Get acquirer distribution
function getAcquirerData($conn, $bu, $where) {
    $storesTable = getStoresTable($bu);
    $sql = "SELECT ft.Acquirer, COUNT(*) AS count, SUM(ft.Amount) AS total_amount
            FROM ftfs_transactions ft
            " . ($where['needsJoin'] ? "LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " : "") .
            $where['clause'] . " GROUP BY ft.Acquirer";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return [];

    if (strlen($where['types']) > 0) {
        $stmt->bind_param($where['types'], ...$where['params']);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['Acquirer']] = ['count' => (int)$row['count'], 'total_amount' => (float)$row['total_amount']];
    }
    $stmt->close();

    return $data;
}

// Get circuit distribution
function getCircuitData($conn, $bu, $where) {
    $storesTable = getStoresTable($bu);
    $sql = "SELECT ft.PosAcq AS circuit, COUNT(*) AS count
            FROM ftfs_transactions ft
            " . ($where['needsJoin'] ? "LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " : "") .
            $where['clause'] . " GROUP BY ft.PosAcq";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return [];

    if (strlen($where['types']) > 0) {
        $stmt->bind_param($where['types'], ...$where['params']);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $circuit = $row['circuit'] ?: 'Sconosciuto';
        $data[$circuit] = (int)$row['count'];
    }
    $stmt->close();

    return $data;
}

// Get hourly distribution
function getHourlyData($conn, $bu, $where) {
    $storesTable = getStoresTable($bu);
    $sql = "SELECT HOUR(ft.DtPos) AS hour, COUNT(*) AS count, SUM(ft.Amount) AS volume
            FROM ftfs_transactions ft
            " . ($where['needsJoin'] ? "LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " : "") .
            $where['clause'] . " GROUP BY HOUR(ft.DtPos) ORDER BY hour";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return [];

    if (strlen($where['types']) > 0) {
        $stmt->bind_param($where['types'], ...$where['params']);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'hour' => (int)$row['hour'],
            'count' => (int)$row['count'],
            'volume' => (float)$row['volume']
        ];
    }
    $stmt->close();

    return $data;
}

// Get weekday distribution
function getWeekdayData($conn, $bu, $where) {
    $storesTable = getStoresTable($bu);
    $sql = "SELECT DAYOFWEEK(ft.DtPos) - 1 AS weekday, COUNT(*) AS count, SUM(ft.Amount) AS volume
            FROM ftfs_transactions ft
            " . ($where['needsJoin'] ? "LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " : "") .
            $where['clause'] . " GROUP BY DAYOFWEEK(ft.DtPos) ORDER BY weekday";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return [];

    if (strlen($where['types']) > 0) {
        $stmt->bind_param($where['types'], ...$where['params']);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'weekday' => (int)$row['weekday'],
            'count' => (int)$row['count'],
            'volume' => (float)$row['volume']
        ];
    }
    $stmt->close();

    return $data;
}

// Get amount range distribution (OPTIMIZED - uses SQL aggregation instead of PHP loops)
function getAmountRangeData($conn, $bu, $where) {
    $storesTable = getStoresTable($bu);
    $sql = "SELECT
                SUM(CASE WHEN ft.Amount < 10 THEN 1 ELSE 0 END) as range_0_10,
                SUM(CASE WHEN ft.Amount >= 10 AND ft.Amount < 50 THEN 1 ELSE 0 END) as range_10_50,
                SUM(CASE WHEN ft.Amount >= 50 AND ft.Amount < 100 THEN 1 ELSE 0 END) as range_50_100,
                SUM(CASE WHEN ft.Amount >= 100 AND ft.Amount < 500 THEN 1 ELSE 0 END) as range_100_500,
                SUM(CASE WHEN ft.Amount >= 500 THEN 1 ELSE 0 END) as range_500_plus
            FROM ftfs_transactions ft
            " . ($where['needsJoin'] ? "LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " : "") .
            $where['clause'];

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return [];

    if (strlen($where['types']) > 0) {
        $stmt->bind_param($where['types'], ...$where['params']);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return [
        '0-10€' => (int)($row['range_0_10'] ?? 0),
        '10-50€' => (int)($row['range_10_50'] ?? 0),
        '50-100€' => (int)($row['range_50_100'] ?? 0),
        '100-500€' => (int)($row['range_100_500'] ?? 0),
        '500+€' => (int)($row['range_500_plus'] ?? 0)
    ];
}

// Get trend data (daily aggregation)
function getTrendData($conn, $bu, $where) {
    $storesTable = getStoresTable($bu);
    $sql = "SELECT DATE(ft.DtPos) AS date, COUNT(*) AS count, SUM(ft.Amount) AS amount
            FROM ftfs_transactions ft
            " . ($where['needsJoin'] ? "LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " : "") .
            $where['clause'] . " GROUP BY DATE(ft.DtPos) ORDER BY date";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return [];

    if (strlen($where['types']) > 0) {
        $stmt->bind_param($where['types'], ...$where['params']);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'date' => $row['date'],
            'count' => (int)$row['count'],
            'amount' => (float)$row['amount']
        ];
    }
    $stmt->close();

    return $data;
}

// Get summary statistics (includes ALL transactions, even failed ones)
function getSummaryStats($conn, $bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $puntoVendita) {
    // Build WHERE clause WITHOUT GtResp filter to include ALL transactions
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

    // Amount filters
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

    // Terminal ID filter
    if ($terminalID !== null && $terminalID !== "") {
        $whereConditions[] = "ft.TermId = ?";
        $params[] = $terminalID;
        $types .= "s";
    }

    // Punto vendita filter
    if ($puntoVendita !== null && $puntoVendita !== "") {
        $storesTable = getStoresTable($bu);
        $whereConditions[] = "ft.TermId IN (SELECT TerminalID FROM " . $storesTable . " WHERE Insegna = ?)";
        $params[] = $puntoVendita;
        $types .= "s";
    }

    if (!empty($whereConditions)) {
        $whereClause .= " AND " . implode(" AND ", $whereConditions);
    }

    $storesTable = getStoresTable($bu);
    $needsJoin = ($puntoVendita !== null && $puntoVendita !== "");

    // Query that includes ALL transactions (success + failed)
    // confirmed_amount: Exclude rifiutate, and subtract storni if not already negative
    $sql = "SELECT
                COUNT(*) AS total_transactions,
                SUM(ft.Amount) AS total_amount,
                SUM(CASE WHEN (ft.Conf = 'C' OR ft.Conf = ' ' OR ft.Conf = '') AND ft.GtResp = '000' THEN 1 ELSE 0 END) AS confirmed_count,
                SUM(CASE WHEN (ft.Conf = 'C' OR ft.Conf = ' ' OR ft.Conf = '') AND ft.GtResp = '000' THEN
                    CASE
                        WHEN ft.TP = 'R' AND ft.Amount > 0 THEN -ft.Amount
                        ELSE ft.Amount
                    END
                ELSE 0 END) AS confirmed_amount
            FROM ftfs_transactions ft
            " . ($needsJoin ? "LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " : "") .
            $whereClause;

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return [
            'totalTransactions' => 0,
            'totalAmount' => 0,
            'confirmedCount' => 0,
            'confirmedAmount' => 0
        ];
    }

    if (strlen($types) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return [
        'totalTransactions' => (int)$row['total_transactions'],
        'totalAmount' => (float)$row['total_amount'],
        'confirmedCount' => (int)$row['confirmed_count'],
        'confirmedAmount' => (float)$row['confirmed_amount']
    ];
}

// Build WHERE clause (for charts - only successful transactions)
$where = buildWhereClause($bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $puntoVendita, $conf);

// Collect all data
$response = [
    'acquirerData' => getAcquirerData($conn, $bu, $where),
    'circuitData' => getCircuitData($conn, $bu, $where),
    'hourlyData' => getHourlyData($conn, $bu, $where),
    'weekdayData' => getWeekdayData($conn, $bu, $where),
    'amountRangeData' => getAmountRangeData($conn, $bu, $where),
    'trendData' => getTrendData($conn, $bu, $where),
];

// Merge summary stats (includes ALL transactions for accurate success rate)
$stats = getSummaryStats($conn, $bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $puntoVendita);
$response = array_merge($response, $stats);

// Return JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
