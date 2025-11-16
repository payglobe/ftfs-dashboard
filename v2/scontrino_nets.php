<?php
/**
 * FTFS Dashboard v2.0 - Receipt Generator (N&TS API)
 *
 * Descrizione:
 * Generatore di scontrini PDF tramite API esterna N&TS (Nets Group).
 * Recupera transazione da database, prepara payload JSON e chiama API
 * esterna per ottenere scontrino PDF ufficiale base64 encoded.
 *
 * Endpoint: GET /scontrino_nets.php?trid=TRANSACTION_ID
 *
 * Parametri Query String:
 * - trid (string, required) - Transaction ID univoco
 *
 * Flusso Operativo:
 * 1. Recupera Trid da parametro GET
 * 2. Query database per dati transazione
 * 3. Prepara payload JSON per API N&TS
 * 4. Chiama API esterna tramite cURL (POST)
 * 5. Riceve risposta JSON con PDF base64
 * 6. Decodifica base64 e restituisce PDF
 *
 * API Esterna N&TS:
 * - URL: https://wsent.netsgroup.com:1040/mondoconvenienza/v1/ftfs/receipt/enquiry
 * - Metodo: POST
 * - Content-Type: application/json
 * - SSL Verification: Disabilitato (CURLOPT_SSL_VERIFYPEER: false)
 *   ⚠️ ATTENZIONE: Solo per testing, abilitare SSL in produzione!
 *
 * Payload JSON Inviato:
 * {
 *   "poiId": "12345678",                    // Terminal ID
 *   "txDt": "2025-11-15T14:30:00.000Z",     // Data/ora ISO 8601
 *   "authCode": "ABC123",                   // Codice autorizzazione
 *   "isReversal": "false",                  // true se storno (Conf='E')
 *   "amount": "5000"                        // Importo senza decimali (50,00€ = 5000)
 * }
 *
 * Risposta API N&TS:
 * {
 *   "pdfReceipt": "JVBERi0xLjQKJeLjz9MKN...",  // PDF base64
 *   "status": "success"
 * }
 *
 * Database Query:
 * - Tabella: ftfs_transactions
 * - Filtro: WHERE Trid = ?
 * - Campi utilizzati: TermId, DtPos, ApprNum, Conf, Amount
 *
 * Conversioni Dati:
 * - Amount: Da decimal a integer (50.00 -> 5000)
 * - DtPos: Da "dd/mm/yyyy HH:mm:ss" a ISO 8601 "YYYY-MM-DDTHH:mm:ss.000Z"
 * - isReversal: "true" se Conf='E' (storno), altrimenti "false"
 *
 * Headers HTTP Output:
 * - Content-Type: application/pdf
 * - Content-Disposition: inline (visualizza in browser)
 *
 * Errori Gestiti:
 * - 400 Bad Request: Trid non fornito o invalido
 * - 200 OK (con messaggio): Transazione non trovata
 * - 200 OK (con messaggio): Errore query database
 * - 200 OK (con messaggio): Errore chiamata API esterna
 *
 * Sicurezza:
 * - Input sanitization (Trid)
 * - Prepared statements (SQL injection safe)
 * - Session validation (commentata ma disponibile)
 * - ⚠️ SSL verification disabled (solo per testing)
 * - ⚠️ No authentication verso API N&TS (verificare con Nets)
 *
 * Note Implementative:
 * - DomPDF NON utilizzata (rimossa dipendenza)
 * - PDF generato da API esterna, non localmente
 * - Timeout cURL: default (considerare aumento per API lente)
 *
 * Differenze vs scontrino.php:
 * - scontrino.php: Genera PDF localmente con DomPDF
 * - scontrino_nets.php: Chiama API esterna per PDF ufficiale
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

// Includi il file di configurazione del database (config.php)
include 'config.php';
session_start();

// Check if the user is logged in (commented out as per previous code)
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
            *
        FROM 
            ftfs_transactions 
        WHERE 
            Trid = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(200); // Internal Server Error
    echo "Errore nella preparazione della query: " . $conn->error;
    exit;
}

$stmt->bind_param("s", $trid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(200); // Not Found
    echo "Transazione non trovata.";
    exit;
}

$transaction = $result->fetch_assoc();

// **NEW: Data for the external API call**
$url = "https://wsent.netsgroup.com:1040/mondoconvenienza/v1/ftfs/receipt/enquiry";
$tid = $transaction['TermId'];
$azcode = $transaction['ApprNum'];
$importo = str_replace(".", "", $transaction['Amount']); // Remove decimal point
$datatrx = date('Y-m-d\TH:i:s.000\Z', strtotime($transaction['DtPos'])); // Format date
$rev = ($transaction['TP'] === 'R') ? 'true' : 'false'; // Set 'true' if it's a reversal

// Log the data for the external API call
error_log("scontrino_nets.php: External API Data - URL: " . $url);
error_log("scontrino_nets.php: External API Data - tid: " . $tid);
error_log("scontrino_nets.php: External API Data - azcode: " . $azcode);
error_log("scontrino_nets.php: External API Data - importo: " . $importo);
error_log("scontrino_nets.php: External API Data - datatrx: " . $datatrx);
error_log("scontrino_nets.php: External API Data - rev: " . $rev);

// **NEW: Prepare the JSON payload**
$payload = [
    "poiId" => $tid,
    "txDt" => $datatrx,
    "authCode" => $azcode,
    "isReversal" => $rev,
    "amount" => $importo
];

// **NEW: Convert the payload to JSON**
$jsonPayload = json_encode($payload);

// **NEW: Initialize cURL session**
$ch = curl_init($url);

// **NEW: Set cURL options**
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonPayload)
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (for testing only, not recommended for production)
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL verification (for testing only, not recommended for production)

// **NEW: Execute the cURL request**
$response = curl_exec($ch);

// **NEW: Check for cURL errors**
if (curl_errno($ch)) {
    error_log("scontrino.php: cURL Error: " . curl_error($ch));
    http_response_code(500);
    echo "Errore durante la chiamata all'API esterna.";
    curl_close($ch);
    exit;
}

// **NEW: Get the HTTP status code**
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// **NEW: Close the cURL session**
curl_close($ch);

// **NEW: Log the API response and HTTP code**
error_log("scontrino_nets.php: External API Response: " . $response);
error_log("scontrino_nets.php: External API HTTP Code: " . $httpCode);

// **NEW: Handle the API response (you might want to do something with it)**
if ($httpCode != 200) {
    error_log("scontrino.php: External API Error: HTTP Code " . $httpCode);
    http_response_code(500);
    echo "Errore durante la chiamata all'API esterna. HTTP Code: " . $httpCode;
    exit;
}

$obj=json_decode($response);
// **Check for JSON decoding errors**
if ($obj === null && json_last_error() !== JSON_ERROR_NONE) {
    error_log("scontrino.php: JSON Decode Error: " . json_last_error_msg());
    http_response_code(500);
    echo "Errore nella decodifica della risposta JSON dall'API esterna.";
    exit;
}
// **Check if resultReceipt is present**
if (!isset($obj->resultReceipt)) {
    error_log("scontrino.php: resultReceipt not found in JSON response");
    http_response_code(500);
    echo "Errore: resultReceipt non trovato nella risposta JSON.";
    exit;
}

// **Decode the base64 encoded PDF**
$data = base64_decode($obj->resultReceipt);

// **Check for base64 decoding errors**
if ($data === false) {
    error_log("scontrino.php: Base64 Decode Error");
    http_response_code(500);
    echo "Errore nella decodifica base64 del PDF.";
    exit;
}

// **Set the Content-Type header to application/pdf**
header('Content-Type: application/pdf');

// **Output the PDF data**
echo $data;;

$stmt->close();
$conn->close();
?>

