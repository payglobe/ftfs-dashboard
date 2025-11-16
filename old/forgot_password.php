<?php
include 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if ($stmt === false) {
        die("Errore nella preparazione della query: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32)); // Generate a random token
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Update the database with the token and expiry
        $stmt = $conn->prepare("UPDATE users SET password_reset_token = ?, password_reset_token_expiry = ? WHERE email = ?");
        if ($stmt === false) {
            die("Errore nella preparazione della query: " . $conn->error);
        }
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        // Send the email using PHPMailer
        $mail = new PHPMailer(true);


        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output (for testing)
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = 'email.payglobe.it'; // Set the SMTP server to send through (e.g., smtp.gmail.com)
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'info'; // SMTP username
            $mail->Password = 'md-pu08ca80tOb6IJIEQGmLzg'; // SMTP password
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
           // $mail->Port = 587; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('info@payglobe.it', 'PAYGLOBE'); // Your email and name
            $mail->addAddress($email); // Add a recipient

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Password Reset Request';
            $resetLink = "http://ricevute.payglobe.it/test-gemini/reset_password.php?token=" . $token;
            $mail->Body = "Click the following link to reset your password: <a href='" . $resetLink . "'>" . $resetLink . "</a>";
            $mail->AltBody = "Click the following link to reset your password: " . $resetLink; // Plain text version for non-HTML clients

            $mail->send();
            $message = "Un'email con le istruzioni per il reset della password è stata inviata al tuo indirizzo.";
        } catch (Exception $e) {
            $message = "Errore durante l'invio dell'email: {$mail->ErrorInfo}";
        }
    } else {
        $message = "L'indirizzo email non è stato trovato.";
    }
}
?>
<?php include 'header.php'; ?>
    <title>Password Dimenticata</title>
    <div class="container mt-5">
        <h2>Password Dimenticata</h2>
        <?php if (!empty($message)) { ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php } ?>
        <form method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Invia</button>
        </form>
    </div>
<?php include 'footer.php'; ?>
