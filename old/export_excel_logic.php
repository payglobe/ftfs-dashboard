<?php
// Assicurati che queste variabili siano già definite nel file che include:
// $conn, $bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $siaCode

function getTableDataForExport($conn, $bu, $startDate = null, $endDate = null, $minAmount = null, $maxAmount = null, $terminalID = null, $siaCode = null) {
    $whereClause = " WHERE ft.TermId REGEXP ? ";
    $params = [$bu];
    $types = "s";

    $whereConditions = [];
    $whereConditions[] = "ft.GtResp = ?";
    $params[] = "000";
    $types .= "s";

    $whereConditions[] = "ft.TP <> ?";
    $params[] = "N";
    $types .= "s";

    if ($startDate && $endDate) {
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

    if (!empty($whereConditions)) {
        $whereClause .= " AND " . implode(" AND ", $whereConditions);
    }

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
            LEFT JOIN stores st ON st.TerminalID = ft.TermId
            $whereClause";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Errore prepare: " . $conn->error);
        return false;
    }

    if (strlen($types) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

// ==============================
// OUTPUT EXCEL (come testo tabellare)
// ==============================

echo "DATA_ORA\tTML_PAYGLOBE\tTML_ACQUIRER\tMERCHANT_ID\tTIPO_CARTA\tCIRCUITO\tSTATO\tMODELLO_POS\tPAN\tIMPORTO\tCODICE_CONFERMA_ACQ\tNOME_ACQUIRER\tPUNTO_VENDITA\tCITTA\tSORGENTE\n";

$result = getTableDataForExport($conn, $bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $siaCode);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        if (strpos($row["acquirer"], '1008') === 0) {
            $row["acquirer"] = substr($row["acquirer"], 4);
        }

        $formattedDate = $row["dataOperazione"] && strtotime($row["dataOperazione"]) !== false
            ? date('d/m/Y H:i:s', strtotime($row["dataOperazione"]))
            : "";

        echo $formattedDate . "\t";
        echo $row["terminalID"] . "\t";
        echo "'" . $row["terminal"] . "\t";

        if (strpos($row["codificaStab"], '0001024') === 0) {
            $row["codificaStab"] = substr($row["codificaStab"], 7);
        }

        if (strpos($row["acquirer"], 'SETEFI') === 0) {
            echo "'" . ($row["codiceesercente"] ?: $row["codificaStab"]) . "\t";
        } else {
            echo "'" . $row["codificaStab"] . "\t";
        }

        echo ($row["tipocarta"] === "C" ? "CREDITO" : ($row["tipocarta"] === "B" ? "DEBITO" : "")) . "\t";
        echo $row["circuito"] . "\t";

        switch ($row["stato"]) {
            case "I": echo "STORNO IMPLICITO\t"; break;
            case "C": echo "CONFERMATA\t"; break;
            case "D": echo "STORNO STESSO OP\t"; break;
            case "A": echo "STORNO IMPLICITO\t"; break;
            case "E": echo "STORNO ESPLICITO\t"; break;
            case "N": echo "PREAUTH CONFERMATA\t"; break;
            default: echo "---\t"; break;
        }

        echo $row["Modello_pos"] . "\t";
        echo $row["pan"] . "\t";
        echo number_format($row["importo"], 2, ',', '.') . "\t";
        echo "'" . $row["codiceAutorizzativo"] . "\t";
        echo $row["acquirer"] . "\t";
        echo $row["insegna"] . "\t";
        echo $row["citta"] . "\t";
        echo $row["prov"] . "\n";
    }
} else {
    echo "Nessun dato trovato\n";
}
?>
