<?php
// send_receipt_email.php
session_start(); // Start the session

include 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trid = $_POST['trid'];
    $email = $_POST['email'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Indirizzo email non valido.";
        exit;
    }

   
    $baseUrl ="https://ricevute.payglobe.it";
    // Construct the full URL for scontrino.php
    $scontrinoUrl = $baseUrl . '/ftfs/scontrino.php?trid=' . urlencode($trid);

    error_log("send_receipt_email.php: Attempting to fetch PDF from: " . $scontrinoUrl); // Log the URL

    // Generate the receipt PDF
    $pdfContent = @file_get_contents($scontrinoUrl); // Use @ to suppress warnings

    if ($pdfContent === false) {
        $error = error_get_last();
        error_log("send_receipt_email.php: Error fetching PDF: " . print_r($error, true));
        echo "Errore nel recupero del PDF.";
        exit;
    }
    if (strlen($pdfContent) == 0) {
        error_log("send_receipt_email.php: PDF content is empty.");
        echo "Errore PDF vuoto.";
        exit;
    }
    error_log("send_receipt_email.php: PDF content length: " . strlen($pdfContent)); // Log the PDF content length

    // Send the email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'email.payglobe.it';
        $mail->SMTPAuth = true;
        $mail->Username = 'info';
        $mail->Password = 'md-pu08ca80tOb6IJIEQGmLzg';

        // Recipients
        $mail->setFrom('info@payglobe.it', 'PAYGLOBE');
        $mail->addAddress($email);

        // Attach the PDF
        $mail->addStringAttachment($pdfContent, 'scontrino.pdf', 'base64', 'application/pdf');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Scontrino Payglobe';
        $mail->Body = mb_convert_encoding('Il suo scontrino della transazione è pronto.', 'UTF-8', 'UTF-8');


        $mail->send();
        echo "Email inviata con successo a " . $email;
    } catch (Exception $e) {
        echo "Errore durante l'invio dell'email: {$mail->ErrorInfo} ";
        error_log("PHPMailer Error: " . $mail->ErrorInfo); // Log the specific PHPMailer error
        error_log("Exception Error: " . $e->getMessage()); // Log the exception message
    }
}
?>
