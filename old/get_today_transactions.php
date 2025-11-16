<?php
// File: api/get_today_transactions.php

header('Content-Type: application/json');
require_once 'config.php'; // include file with DB credentials


if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connessione al database fallita."]);
    exit;
}

// Verifica se TermId è stato passato via GET
if (!isset($_GET['termId'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parametro 'termId' mancante."]);
    exit;
}

$termId = $conn->real_escape_string($_GET['termId']);

// Ottenere solo le transazioni del giorno corrente per uno specifico TermId
$sql = "SELECT id, Trid, TermId, DtTrans, Amount, Pan, Tpc,PosAcq,TP,GtResp
        FROM ftfs_transactions
        WHERE DATE(DtTrans) = CURDATE() AND TermId = '$termId'
        ORDER BY DtTrans DESC";

$result = $conn->query($sql);

$transactions = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Mascherare il PAN (ultime 4 cifre)
        $pan = $row['Pan'];
        $masked_pan = str_repeat("*", max(strlen($pan) - 4, 0)) . substr($pan, -4);
        $tp_description = '';
        switch ($row['TP']) {
            case 'A':
                $tp_description = 'Acquisto';
                break;
            case 'R':
                $tp_description = 'Storno';
                break;
            case 'N':
                $tp_description = 'Notifica';
                break;
            case 'X':
                $tp_description = 'Manuale';
                break;
            default:
                $tp_description = 'Sconosciuto';
                break;
        }

         // Create a DateTime object from the DtTrans string
        $dateTimeObj = new DateTime($row['DtTrans']);
        // Format to get only the time (HH:MM:SS)
        $timeOnly = $dateTimeObj->format('H:i:s');
        // Or for HH:MM format:
        // $timeOnly = $dateTimeObj->format('H:i');

        $amount_with_tp =   $row['Amount'] ;
        $transactions[] = [
            'id' => $row['id'],
            'Trid' => $row['Trid'],
            'TermId' => $row['TermId'],
            'DtTrans' => $timeOnly,
            'Amount' => $amount_with_tp,
            'CardType' => ($row['Tpc'] === 'C') ? 'Credito ' : 'Debito ',
            'PanMasked' => $masked_pan,
            'Brand' => $row['PosAcq'],
            'TP' => $tp_description,
            'GtResp' => $row['GtResp']
        ];
    }
}

echo json_encode($transactions);
$conn->close();
?>

