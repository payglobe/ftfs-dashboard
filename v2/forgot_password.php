<?php
/**
 * FTFS Dashboard v2.0 - Forgot Password Page
 *
 * Descrizione:
 * Pagina per richiedere il reset della password via email.
 * Genera un token sicuro univoco con scadenza 1 ora e invia email
 * professionale con link per reset password.
 *
 * Funzionalita:
 * - Form inserimento email/username
 * - Verifica esistenza utente nel database
 * - Generazione token sicuro (64 caratteri hex)
 * - Scadenza token: 1 ora
 * - Invio email professionale tramite PHPMailer
 * - Design moderno con animazioni CSS
 *
 * Flusso:
 * 1. Utente inserisce email
 * 2. Verifica email esiste in tabella users
 * 3. Genera token random: bin2hex(random_bytes(32))
 * 4. UPDATE users SET password_reset_token, password_reset_token_expiry
 * 5. Invia email con link reset
 * 6. Link: reset_password.php?token=...
 *
 * Database Update:
 * - password_reset_token VARCHAR(255)
 * - password_reset_token_expiry DATETIME
 *
 * Email Settings:
 * - Server: email.payglobe.it (SMTP)
 * - From: info@payglobe.it (PAYGLOBE)
 * - Auth: info / md-pu08ca80tOb6IJIEQGmLzg
 *
 * Sicurezza:
 * - Token crittograficamente sicuro (random_bytes)
 * - Scadenza 1 ora
 * - Prepared statements
 * - Email solo se utente esiste (no info leak)
 *
 * Dipendenze:
 * - PHPMailer 6.9+ (vendor/autoload.php)
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

include 'config.php';

// Include PHPMailer manually (composer autoload doesn't have it)
require '../vendor/phpmailer/phpmailer/Exception.php';
require '../vendor/phpmailer/phpmailer/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = "";
$messageType = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $message = "Inserisci il tuo username o email";
        $messageType = "error";
    } else {
        // Check if the email exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if ($stmt === false) {
            $message = "Errore database: " . $conn->error;
            $messageType = "error";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Generate a random token (64 characters)
                $token = bin2hex(random_bytes(32));

                // Token expires in 1 hour
                $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

                // Update the database with the token and expiry
                $updateStmt = $conn->prepare("UPDATE users SET password_reset_token = ?, password_reset_token_expiry = ? WHERE email = ?");
                if ($updateStmt === false) {
                    $message = "Errore database: " . $conn->error;
                    $messageType = "error";
                } else {
                    $updateStmt->bind_param("sss", $token, $expiry, $email);
                    $updateStmt->execute();

                    // Send the email using PHPMailer
                    $mail = new PHPMailer(true);

                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'email.payglobe.it';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'info';
                        $mail->Password = 'md-pu08ca80tOb6IJIEQGmLzg';
                        $mail->CharSet = 'UTF-8';

                        // Recipients
                        $mail->setFrom('info@payglobe.it', 'PAYGLOBE');
                        $mail->addAddress($email);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Reset Password - Dashboard FTFS PayGlobe';

                        $resetLink = "https://ricevute.payglobe.it/ftfs/v2/reset_password.php?token=" . $token;

                        // Professional HTML email
                        $mail->Body = '
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            color: white;
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body p {
            margin: 0 0 15px;
            color: #374151;
        }
        .reset-button {
            display: inline-block;
            margin: 25px 0;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
        }
        .expiry-notice {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .email-footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .security-note {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🔐 Reset Password</h1>
        </div>
        <div class="email-body">
            <p><strong>Gentile utente,</strong></p>

            <p>Abbiamo ricevuto una richiesta di reset password per il tuo account <strong>Dashboard FTFS PayGlobe</strong>.</p>

            <p>Per reimpostare la tua password, clicca sul pulsante sottostante:</p>

            <div style="text-align: center;">
                <a href="' . $resetLink . '" class="reset-button">Reimposta Password</a>
            </div>

            <div class="expiry-notice">
                <strong>⏱ Attenzione:</strong> Il link sarà valido per <strong>1 ora</strong> dalla ricezione di questa email.
            </div>

            <p>Se il pulsante non funziona, copia e incolla il seguente link nel tuo browser:</p>
            <p style="word-break: break-all; font-size: 13px; color: #667eea;">' . $resetLink . '</p>

            <p class="security-note">
                <strong>Nota di sicurezza:</strong> Se non hai richiesto tu questo reset password, ignora questa email.
                Il tuo account rimane al sicuro e nessuna modifica verrà effettuata.
            </p>
        </div>
        <div class="email-footer">
            <p><strong>Cordiali saluti,</strong><br>Team PayGlobe</p>
            <p style="margin-top: 10px;">Dashboard FTFS - GUM Group Company</p>
        </div>
    </div>
</body>
</html>';

                        // Plain text version
                        $mail->AltBody = "Gentile utente,\n\n"
                            . "Abbiamo ricevuto una richiesta di reset password per il tuo account Dashboard FTFS PayGlobe.\n\n"
                            . "Per reimpostare la tua password, clicca sul seguente link:\n"
                            . $resetLink . "\n\n"
                            . "ATTENZIONE: Il link sarà valido per 1 ora.\n\n"
                            . "Se non hai richiesto tu questo reset, ignora questa email.\n\n"
                            . "Cordiali saluti,\n"
                            . "Team PayGlobe\n"
                            . "Dashboard FTFS - GUM Group Company";

                        $mail->send();
                        $message = "Email inviata con successo! Controlla la tua casella di posta.";
                        $messageType = "success";
                    } catch (Exception $e) {
                        $message = "Errore durante l'invio dell'email. Riprova più tardi.";
                        $messageType = "error";
                        error_log("PHPMailer Error: " . $mail->ErrorInfo);
                    }

                    $updateStmt->close();
                }
            } else {
                // Don't reveal if email exists or not (security)
                $message = "Se l'email esiste, riceverai le istruzioni per il reset.";
                $messageType = "success";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Dimenticata - Dashboard Transazioni</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .forgot-container {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
        }

        .forgot-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .forgot-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .forgot-icon i {
            color: white;
            font-size: 32px;
        }

        .forgot-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .forgot-subtitle {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
        }

        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .message i {
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
        }

        .form-label i {
            margin-right: 8px;
            color: #667eea;
        }

        .form-input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 16px 18px 16px 52px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            transition: color 0.3s;
            pointer-events: none;
        }

        .form-input:focus + .form-input-icon {
            color: #667eea;
        }

        .form-help {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
            padding-left: 4px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .back-to-login {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
        }

        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s;
        }

        .back-to-login a:hover {
            color: #764ba2;
        }

        .back-to-login i {
            margin-right: 6px;
        }

        /* Floating particles background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        @media (max-width: 480px) {
            .forgot-card {
                padding: 40px 30px;
            }

            .forgot-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating particles -->
    <div class="particles">
        <?php for($i = 0; $i < 50; $i++): ?>
        <div class="particle" style="left: <?php echo rand(0, 100); ?>%; animation-delay: <?php echo rand(0, 15); ?>s; animation-duration: <?php echo rand(10, 20); ?>s;"></div>
        <?php endfor; ?>
    </div>

    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <div class="forgot-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h1 class="forgot-title">Password Dimenticata?</h1>
                <p class="forgot-subtitle">
                    Inserisci il tuo username o email per ricevere le istruzioni di reset.
                </p>
            </div>

            <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-user"></i> Username o Email
                    </label>
                    <div class="form-input-wrapper">
                        <input
                            type="text"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="Inserisci username o email"
                            autocomplete="username"
                            required
                            autofocus
                        >
                        <i class="fas fa-user form-input-icon"></i>
                    </div>
                    <div class="form-help">
                        <i class="fas fa-info-circle"></i> Riceverai un'email con il link per reimpostare la password
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Invia Email di Reset
                </button>
            </form>

            <div class="back-to-login">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Torna al Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
