<?php
include 'config.php';
include 'bu_config.php'; // Include BU configuration
session_start();
$bu = htmlspecialchars($_SESSION['bu']);

// Set headers for Excel export
//header('Content-Type: application/vnd.ms-excel');
//header('Content-Disposition: attachment; filename=export_transazioni.xls');

// Function to fetch data with optional filters (similar to get_table_data.php)
function getTableDataForExport($conn, $bu, $startDate = null, $endDate = null, $minAmount = null, $maxAmount = null, $terminalID = null, $siaCode = null) {
   // $whereClause = " WHERE (st.bu = ? OR st.bu1 = ? OR st.TerminalID LIKE ?) "; // Base WHERE clause
   // $params = [$bu, $bu, $bu.'%']; // Parameters for the base WHERE clause
   // $types = "sss"; // Types for the base WHERE clause
    
    $whereClause = " WHERE  ft.TermId REGEXP ? "; // Added st.bu = '12'
    $params[] = $bu; // Parameters for the base WHERE clause
    $types = "s"; // Types for the base WHERE clause



    $whereConditions = []; // Array to store additional WHERE conditions


      $whereConditions[] = "ft.GtResp = ?"; // Changed to ft.DtPos
        $params[] = "000";
        $types .= "s";

          // diverso da Notifiche
        $whereConditions[] = "ft.TP <> ?"; // Changed to ft.DtPos
        $params[] = "N";
        $types .= "s";

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
    if ($siaCode !== null && $siaCode !== "") {
        $whereConditions[] = "ft.SiaCode = ?";
        $params[] = $siaCode;
        $types .= "s";
    }

    // Combine additional WHERE conditions with AND
    if (!empty($whereConditions)) {
        $whereClause .= " AND " . implode(" AND ", $whereConditions);
    }

    // Get the correct stores table based on BU
    $storesTable = getStoresTable($bu);

    $sql = "SELECT ft.TermId AS terminalID, ft.Term AS terminal, ft.MeId AS codificaStab, ft.TPC AS tipocarta, st.Modello_pos AS Modello_pos, ft.Pan AS pan, ft.PosAcq as circuito, ft.Conf as stato,ft.DtPos AS dataOperazione, ft.Amount AS importo, ft.ApprNum AS codiceAutorizzativo, ft.Acquirer AS acquirer, st.Insegna AS insegna, st.citta AS citta, st.prov AS prov, st.sia_pagobancomat as codiceesercente FROM ftfs_transactions ft LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId " . $whereClause;
    $stmt = $conn->prepare($sql);

    if (strlen($types) > 0) {
        $stmt->bind_param($types, ...$params);
    }
     error_log("Executing query: " . $sql . " with params: " . print_r($params, true) . " and types: " . $types);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

// Get filter parameters from GET request (if any)
$startDate = isset($_GET['startDate']) && $_GET['startDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['startDate'])) : null;
$endDate = isset($_GET['endDate']) && $_GET['endDate'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['endDate'])) : null;
$minAmount = (isset($_GET['minAmount']) && $_GET['minAmount'] !== '') ? $_GET['minAmount'] : null;
$maxAmount = (isset($_GET['maxAmount']) && $_GET['maxAmount'] !== '') ? $_GET['maxAmount'] : null;
$terminalID = isset($_GET['terminalID']) ? $_GET['terminalID'] : null;
$siaCode = isset($_GET['siaCode']) ? $_GET['siaCode'] : null;

// Fetch data using the function
$result = getTableDataForExport($conn, $bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $siaCode);

// Output headers for the Excel file
echo "DATA_ORA\tTML_PAYGLOBE\tTML_ACQUIRER\tMERCHANT_ID\tTIPO_CARTA\tCIRCUITO\tSTATO\tMODELLO_POS\tPAN\tIMPORTO\tCODICE_CONFERMA_ACQ\tNOME_ACQUIRER\tPUNTO_VENDITA\tCITTA\tSORGENTE\n";

// Output data rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // PULISCO IL CODICE ACQUIRER tolgo 1008 messo da N&TS

         if (strpos($row["acquirer"], '1008') === 0) {
            $row["acquirer"] = substr($row["acquirer"], 4);
        }

        if ($row["dataOperazione"] && strtotime($row["dataOperazione"]) !== false) {
            $formattedDate = date('d/m/Y H:i:s', strtotime($row["dataOperazione"]));
        } else {
            $formattedDate = ""; // Or "N/A" or any other placeholder
        }
        echo $formattedDate . "\t";
        echo $row["terminalID"] . "\t";
        echo  "'".$row["terminal"] . "\t";

        // Modify codificaStab
       
        if (strpos($row["codificaStab"], '0001024') === 0) {
                $row["codificaStab"] = substr($row["codificaStab"], 7);
            }


        // stampo il codice di trascodifica sia per i terminali sul circuito SETEFI
        // al posto di mettere il codice sia, mettere il codice esercente.    
        if (strpos($row["acquirer"], 'SETEFI') === 0)  {
            if(strlen($row["codiceesercente"])>0){
                // se presente lo metto 
                echo  "'". $row["codiceesercente"] . "\t";
            }
            else
            { // altrimenti metto quello di totes
                echo  "'". $row["codificaStab"] . "\t";
            }
           
        } else {
            // non é un SETEFI metto quello di totes
            echo  "'". $row["codificaStab"] . "\t";
        } 
        
           
         if ($row["tipocarta"] == "C") {
            echo "CREDITO". "\t";
        } else if ($row["tipocarta"] == "B") {
            echo "DEBITO". "\t";
        }
       
       
        echo $row["circuito"] . "\t";
    

         if ($row["stato"] == "I") {
            echo "STORNO IMPLICITO". "\t";
        } else if ($row["stato"] == "C") {
            echo "CONFERMATA". "\t";
        } else if ($row["stato"] == "D") {
            echo "STORNO STESSO OP". "\t";
        } else if ($row["stato"] == "A") {
            echo "STORNO IMPLICITO". "\t";
        } else if ($row["stato"] == "E") {
            echo "STORNO ESPLICITO". "\t";
        } else if ($row["stato"] == "N") {
            echo "PREAUTH CONFERMATA". "\t";
        } else echo "---". "\t";

        echo $row["Modello_pos"] . "\t";
       
        echo $row["pan"] . "\t";
       
        echo number_format($row["importo"], 2, ',', '.') . "\t"; // Format the amount
       
        echo  "'". $row["codiceAutorizzativo"] . "\t";
         
        echo $row["acquirer"] . "\t";
        echo $row["insegna"] . "\t";
        echo $row["citta"] . "\t";
        echo $row["prov"] . "\n";
    }
} else {
    echo "Nessun dato trovato\n";
}

$conn->close();
?>
