<?php
include 'config.php'; // Assicurati che questo file esista e sia corretto
session_start();
$bu = htmlspecialchars($_SESSION['bu']);
// Ricevi i dati dal frontend
$data = json_decode(file_get_contents('php://input'), true);

// Estrai la domanda dell'utente
$userQuery = $data['query'] ?? null;

// Inizializza le variabili per la query
$response = [];

if ($userQuery) {
    // Qui dovresti integrare il motore di NLU (Dialogflow, ecc.)
    // Per ora, facciamo un esempio con una logica semplice
    if (strpos(strtolower($userQuery), 'transazioni') !== false) {
        // Esempio: se la domanda contiene "transazioni", mostra le transazioni
        $intent = "visualizza_transazioni";
        $parameters = [];
        // Puoi aggiungere qui la logica per estrarre le entità dalla domanda
        // Ad esempio, se l'utente chiede "transazioni di ieri", puoi impostare $parameters['date']
        if (strpos(strtolower($userQuery), 'ieri') !== false) {
            $parameters['date']['startDate'] = date("Y-m-d 00:00:00", strtotime("-1 day"));
            $parameters['date']['endDate'] = date("Y-m-d 23:59:59", strtotime("-1 day"));
        }
        if (strpos(strtolower($userQuery), 'oggi') !== false) {
            $parameters['date']['startDate'] = date("Y-m-d 00:00:00");
            $parameters['date']['endDate'] = date("Y-m-d 23:59:59");
        }
        if (preg_match('/superiore a (\d+)/', strtolower($userQuery), $matches)) {
            $parameters['minAmount'] = $matches[1];
        }
        if (preg_match('/inferiore a (\d+)/', strtolower($userQuery), $matches)) {
            $parameters['maxAmount'] = $matches[1];
        }
        if (preg_match('/terminale (\w+)/', strtolower($userQuery), $matches)) {
            $parameters['terminalID'] = $matches[1];
        }
        if (preg_match('/sia code (\w+)/', strtolower($userQuery), $matches)) {
            $parameters['siaCode'] = $matches[1];
        }
    } else if (strpos(strtolower($userQuery), 'conta') !== false) {
        $intent = "conta_transazioni";
        $parameters = [];
        if (strpos(strtolower($userQuery), 'ieri') !== false) {
            $parameters['date']['startDate'] = date("Y-m-d 00:00:00", strtotime("-1 day"));
            $parameters['date']['endDate'] = date("Y-m-d 23:59:59", strtotime("-1 day"));
        }
        if (strpos(strtolower($userQuery), 'oggi') !== false) {
            $parameters['date']['startDate'] = date("Y-m-d 00:00:00");
            $parameters['date']['endDate'] = date("Y-m-d 23:59:59");
        }
        if (preg_match('/superiore a (\d+)/', strtolower($userQuery), $matches)) {
            $parameters['minAmount'] = $matches[1];
        }
        if (preg_match('/inferiore a (\d+)/', strtolower($userQuery), $matches)) {
            $parameters['maxAmount'] = $matches[1];
        }
        if (preg_match('/terminale (\w+)/', strtolower($userQuery), $matches)) {
            $parameters['terminalID'] = $matches[1];
        }
        if (preg_match('/sia code (\w+)/', strtolower($userQuery), $matches)) {
            $parameters['siaCode'] = $matches[1];
        }
    } else {
        $intent = null;
    }

    // Inizializza le variabili per la query
    $startDate = isset($parameters['date']['startDate']) ? date('Y-m-d H:i:s', strtotime($parameters['date']['startDate'])) : null;
    $endDate = isset($parameters['date']['endDate']) ? date('Y-m-d H:i:s', strtotime($parameters['date']['endDate'])) : null;
    
    $minAmount = $parameters['minAmount'] ?? null;
    $maxAmount = $parameters['maxAmount'] ?? null;
    $terminalID = $parameters['terminalID'] ?? null;
    $siaCode = $parameters['siaCode'] ?? null;

    // Costruisci la query SQL in base all'intenzione e alle entità
    $sql = "";

    if ($intent == "visualizza_transazioni") {
        // Costruisci la query SQL
        $whereClause = " WHERE st.bu = ? or st.bu1 = ? or st.bu2 = ? "; // Use a placeholder
        $params = [$bu,$bu,$bu]; // Add $bu to the parameters array
        $types = "sss"; // Add the type for $bu (string)

        if ($startDate && $endDate) {
            $whereClause .= " AND ft.DtPos BETWEEN ? AND ? ";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }
        

        if ($minAmount !== null) {
            $whereClause .= " AND ft.Amount >= ? ";
            $params[] = $minAmount;
            $types .= "d";
        }

        if ($maxAmount !== null) {
            $whereClause .= " AND ft.Amount <= ? ";
            $params[] = $maxAmount;
            $types .= "d";
        }

        if ($terminalID !== null && $terminalID !== "") {
            $whereClause .= " AND ft.TermId = ? ";
            $params[] = $terminalID;
            $types .= "s";
        }
        if ($siaCode !== null && $siaCode !== "") {
            $whereClause .= " AND ft.SiaCode = ? ";
            $params[] = $siaCode;
            $types .= "s";
        }
        // Add ORDER BY clause here
        $sql = "SELECT ft.MeId AS codificaStab, ft.TermId AS terminalID, st.Modello_pos AS Modello_pos, ft.Pan AS pan, ft.DtPos AS dataOperazione, ft.Amount AS importo, ft.ApprNum AS codiceAutorizzativo, ft.Acquirer AS acquirer, ft.PosAcq AS PosAcq, ft.AId AS AId, ft.PosStan AS PosStan, ft.Conf AS Conf, ft.NumOper AS NumOper, ft.TP AS TP, ft.TPC AS TPC, st.Insegna AS insegna, st.Ragione_Sociale AS Ragione_Sociale, st.indirizzo AS indirizzo, st.citta AS localita, st.prov AS prov, st.cap AS cap, ft.Trid FROM ftfs_transactions ft JOIN stores st ON st.TerminalID = ft.TermId " . $whereClause . " ORDER BY ft.DtPos DESC";
        $stmt = $conn->prepare($sql);
        if (strlen($types) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        $response['fulfillmentText'] = "Ecco le transazioni trovate:";
        $response['data'] = $data;
    } else if ($intent == "conta_transazioni") {
        // Costruisci la query SQL per contare le transazioni
        $whereClause = " WHERE st.bu = ? or st.bu1 = ? or st.bu2 = ? "; // Use a placeholder
        $params = [$bu,$bu,$bu]; // Add $bu to the parameters array
        $types = "sss"; // Add the type for $bu (string)

        if ($startDate && $endDate) {
            $whereClause .= " AND ft.DtPos BETWEEN ? AND ? ";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }

        if ($minAmount !== null) {
            $whereClause .= " AND ft.Amount >= ? ";
            $params[] = $minAmount;
            $types .= "d";
        }

        if ($maxAmount !== null) {
            $whereClause .= " AND ft.Amount <= ? ";
            $params[] = $maxAmount;
            $types .= "d";
        }

        if ($terminalID !== null && $terminalID !== "") {
            $whereClause .= " AND ft.TermId = ? ";
            $params[] = $terminalID;
            $types .= "s";
        }
        if ($siaCode !== null && $siaCode !== "") {
            $whereClause .= " AND ft.SiaCode = ? ";
            $params[] = $siaCode;
            $types .= "s";
        }

        $sql = "SELECT COUNT(*) AS total FROM ftfs_transactions ft JOIN stores st ON st.TerminalID = ft.TermId " . $whereClause;
        $stmt = $conn->prepare($sql);
        if (strlen($types) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total = $row['total'];
        $stmt->close();

        $response['fulfillmentText'] = "Ci sono " . $total . " transazioni.";
    } else {
        $response['fulfillmentText'] = "Non ho capito la tua domanda.";
    }
    $response['startDate'] = $startDate;
    $response['endDate'] = $endDate;
    $response['minAmount'] = $minAmount;
    $response['maxAmount'] = $maxAmount;
    $response['terminalID'] = $terminalID;
    $response['siaCode'] = $siaCode;
} else {
    $response['fulfillmentText'] = "Nessuna domanda ricevuta.";
}

// Restituisci la risposta in formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
