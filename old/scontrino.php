<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Includi il file di configurazione del database (config.php)
include 'config.php';
session_start();
// Check if the user is logged in
/*
if (!isset($_SESSION['username'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized';
    exit;
}
*/


// Verifica se è stato fornito un Trid
if (!isset($_GET['trid']) || empty($_GET['trid'])) {
    http_response_code(400); // Bad Request
    echo "Errore: Trid non fornito.";
    exit;
}

// Sanifica l'input Trid
$trid = $_GET['trid'];
error_log("scontrino.php: Trid ricevuto: " . $trid);
// Query al database per recuperare i dati della transazione con JOIN
$sql = "SELECT 
            ft.*, 
            st.Insegna,
            st.Ragione_Sociale,
            st.indirizzo,
            st.citta,
            st.prov,
            st.cap
        FROM 
            ftfs_transactions ft
        JOIN 
            stores st ON st.TerminalID = ft.TermId
        WHERE 
            ft.Trid = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500); // Internal Server Error
    echo "Errore nella preparazione della query: " . $conn->error;
    exit;
}

$stmt->bind_param("s", $trid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404); // Not Found
    echo "Transazione non trovata.";
    exit;
}

$transaction = $result->fetch_assoc();

// Determina il tipo di transazione in base al campo TP
switch ($transaction['TP']) {
    case 'A':
        $transactionType = "ACQUISTO";
        break;
    case 'R':
        $transactionType = "STORNO";
        break;
    case 'N':
        $transactionType = "NOTIFICA";
        break;
    case 'X':
        $transactionType = "MANUALE";
        break;
    default:
        $transactionType = "SCONOSCIUTO"; // Valore di default se TP non corrisponde a nessuno dei casi
        break;
}

// Determina il tipo di carta in base al campo Tpc
switch ($transaction['Tpc']) {
    case 'C':
        $cardType = "CARTA DI CREDITO";
        break;
    case 'B':
        $cardType = "CARTA DI DEBITO";
        break;
    default:
        $cardType = "CARTA"; // Valore di default se Tpc non corrisponde a nessuno dei casi
        break;
}

// Determina lo stato della transazione in base al campo Conf
switch ($transaction['Conf']) {
    case 'I':
        $transactionStatus = "TRANSAZIONE RIFIUTATA";
        break;
    case 'C':
        if ($transaction['GtResp'] ==='000')
            $transactionStatus = "TRANSAZIONE APPROVATA";
        else
             $transactionStatus = "TRANSAZIONE RIFIUTATA";
        break;
    case 'D':
        $transactionStatus = "TRANSAZIONE RIFIUTATA";
        break;
    case 'A':
        $transactionStatus = "TRANSAZIONE RIFIUTATA";
        break;
    case 'E':
        $transactionStatus = "TRANSAZIONE APPROVATA";
        $transactionType = "STORNO";
        break;
    case 'N':
        $transactionStatus = "TRANSAZIONE APPROVATA";
        break;
    default:
        $transactionStatus = "TRANSAZIONE RIFIUTATA"; // Valore di default se Conf non corrisponde a nessuno dei casi
        break;
}

// Estrai le ultime 11 cifre da TrKey
$aiic = substr($transaction['TrKey'], -11);

// Formatta PosStan a 6 cifre con zeri iniziali
$posStanFormatted = str_pad($transaction['PosStan'], 6, '0', STR_PAD_LEFT);
$posNumOperFormatted = str_pad($transaction['NumOper'], 6, '0', STR_PAD_LEFT);

// Format Amount with comma as decimal separator
$amountFormatted = number_format($transaction['Amount'], 2, ',', '.');

// Costruzione dello scontrino a partire dai dati del database

$receipt = <<<RECEIPT
<div style="width: 140.77pt; max-width: 120.77pt; height: auto; font-family: Courier; font-size: 10pt;">
<div style="text-align: center; line-height: 0.6; ">
<span style="font-weight: normal;">{$transaction['Ragione_Sociale']}</span><br>
<span style="font-weight: normal;">{$transaction['PosAcq']}</span><br>
<span style="font-weight: bold;">{$transactionType}</span><br>
<span style="font-weight: normal;">{$cardType}</span><br>
<span style="font-weight: small;">{$transaction['indirizzo']}</span><br>
<span style="font-weight: normal;">{$transaction['citta']} {$transaction['cap']} {$transaction['prov']}</span><br>
</div>

<div style="line-height: 0.6; margin: 0 10px;">
MERCH: {$transaction['MeId']}<br>
A.I.I.C.: {$aiic}<br>
DATA:{$transaction['DtPos']}<br>
TML:{$transaction['TermId']} STAN:{$posStanFormatted}<br>
AUT:{$transaction['ApprNum']} OPER:{$posNumOperFormatted}<br>
GT.RESP.CODE:{$transaction['GtResp']}<br>
PAN:{$transaction['Pan']}<br>
A.ID:{$transaction['AId']}<br>
APPL:{$transaction['PosAcq']}<br>
<span style="font-weight: bold;">IMPORTO &#8364; {$amountFormatted}</span><br>
</div>
<div style="text-align: center; line-height: 0.5; margin: 0 10px;">
    <span style="font-weight: bold;">{$transactionStatus}</span><br>
</div>
<div style="text-align: center; line-height: 0.5; margin: 0 0px;">
    <span>ARRIVEDERCI E GRAZIE</span><br>
<span style="font-weight: normal;">PAYGLOBE POS</span>
</div>
</div>
RECEIPT;
$html =  '<pre>' . $receipt . '</pre>';
//echo $html;
// Generazione PDF


$options = new Options();
$options->set('defaultFont', 'Courier');
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper(array(0, 0, 228.77, 441.89), 'portrait'); // 80mm = 226.77pt, A4 height = 441.89pt
$dompdf->render();
$dompdf->stream($transaction['TermId'].'.pdf', array("Attachment" => false));


$stmt->close();
$conn->close(); 
?>
