<?php
/**
 * FTFS Dashboard v2.0 - Reset Password Page
 *
 * Descrizione:
 * Pagina per il reset della password quando scaduta (>45 giorni).
 * L'utente viene reindirizzato automaticamente qui dal login se la password
 * è scaduta. Non invia email, funziona solo con sessione PHP.
 *
 * Funzionalita:
 * - Reset password forzato da scadenza (45 giorni)
 * - Validazione nuova password (lunghezza minima, corrispondenza)
 * - Aggiornamento password_last_changed a NOW()
 * - Design moderno con animazioni CSS
 *
 * Flusso:
 * 1. Login rileva password scaduta
 * 2. Setta $_SESSION['reset_password_user']
 * 3. Redirect a questa pagina
 * 4. Utente inserisce nuova password
 * 5. Update database e redirect a login
 *
 * Sessioni Richieste:
 * - $_SESSION['reset_password_user'] - Username per reset
 *
 * Database Update:
 * - UPDATE users SET password = ?, password_last_changed = NOW()
 *
 * Validazioni:
 * - Nuova password e conferma devono corrispondere
 * - Password minimo 6 caratteri (modificabile)
 *
 * Sicurezza:
 * - Password hashata con password_hash() bcrypt
 * - Prepared statements
 * - Session validation
 * - Clear session dopo reset
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

include 'config.php';
session_start();

$message = "";
$messageType = ""; // success or error
$showForm = false;
$email = "";

// Check if user is coming from email link (forgot password)
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM users WHERE password_reset_token = ? AND password_reset_token_expiry > NOW()");
    if ($stmt === false) {
        $message = "Errore database: " . $conn->error;
        $messageType = "error";
    } else {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $showForm = true;
            $row = $result->fetch_assoc();
            $email = $row['email'];

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if ($newPassword !== $confirmPassword) {
                    $message = "Le password non corrispondono";
                    $messageType = "error";
                } elseif (strlen($newPassword) < 6) {
                    $message = "La password deve essere almeno 6 caratteri";
                    $messageType = "error";
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    // Update password and clear token
                    $updateStmt = $conn->prepare("UPDATE users SET password = ?, password_reset_token = NULL, password_reset_token_expiry = NULL, password_last_changed = NOW() WHERE email = ?");
                    if ($updateStmt === false) {
                        $message = "Errore database: " . $conn->error;
                        $messageType = "error";
                    } else {
                        $updateStmt->bind_param("ss", $hashedPassword, $email);
                        if ($updateStmt->execute()) {
                            $message = "Password aggiornata con successo! Reindirizzamento al login...";
                            $messageType = "success";
                            $showForm = false;
                            header("refresh:2;url=login.php");
                        } else {
                            $message = "Errore durante l'aggiornamento: " . $updateStmt->error;
                            $messageType = "error";
                        }
                        $updateStmt->close();
                    }
                }
            }
        } else {
            $message = "Il link per il reset della password non è valido o è scaduto.";
            $messageType = "error";
        }
        $stmt->close();
    }
}
// Check if user is coming from expired password flow
elseif (isset($_SESSION['reset_password_user'])) {
    $showForm = true;
    $email = $_SESSION['reset_password_user'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate passwords match
        if ($newPassword !== $confirmPassword) {
            $message = "Le password non corrispondono";
            $messageType = "error";
        }
        // Validate password length (minimum 6 characters)
        elseif (strlen($newPassword) < 6) {
            $message = "La password deve essere almeno 6 caratteri";
            $messageType = "error";
        }
        else {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password and password_last_changed
            $stmt = $conn->prepare("UPDATE users SET password = ?, password_last_changed = NOW() WHERE email = ?");
            if ($stmt === false) {
                $message = "Errore database: " . $conn->error;
                $messageType = "error";
            } else {
                $stmt->bind_param("ss", $hashedPassword, $email);
                if ($stmt->execute()) {
                    $message = "Password aggiornata con successo! Reindirizzamento al login...";
                    $messageType = "success";
                    $showForm = false;
                    unset($_SESSION['reset_password_user']);

                    // Auto-redirect after 2 seconds
                    header("refresh:2;url=login.php");
                } else {
                    $message = "Errore durante l'aggiornamento: " . $stmt->error;
                    $messageType = "error";
                }
                $stmt->close();
            }
        }
    }
}
// No valid reset method found
else {
    $message = "Accesso non autorizzato. Effettua il login.";
    $messageType = "error";
    header("refresh:2;url=login.php");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Dashboard Transazioni</title>
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

        .reset-container {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
        }

        .reset-card {
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

        .reset-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .reset-icon {
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

        .reset-icon i {
            color: white;
            font-size: 32px;
        }

        .reset-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .reset-subtitle {
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

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .password-requirements {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
            padding-left: 4px;
        }

        .password-requirements i {
            font-size: 10px;
            margin-right: 6px;
        }

        .btn-reset {
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

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .btn-reset:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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
            .reset-card {
                padding: 40px 30px;
            }

            .reset-title {
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

    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <div class="reset-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="reset-title">Reset Password</h1>
                <p class="reset-subtitle">
                    <?php if ($showForm): ?>
                    La tua password è scaduta (>45 giorni). Per sicurezza, inserisci una nuova password.
                    <?php else: ?>
                    Gestione reset password
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($showForm): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="new_password">
                        <i class="fas fa-lock"></i> Nuova Password
                    </label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            class="form-input"
                            placeholder="Inserisci nuova password"
                            autocomplete="new-password"
                            required
                            autofocus
                        >
                        <i class="fas fa-lock form-input-icon"></i>
                        <i class="fas fa-eye password-toggle" id="toggleNew"></i>
                    </div>
                    <div class="password-requirements">
                        <i class="fas fa-info-circle"></i>Minimo 6 caratteri
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class="fas fa-lock"></i> Conferma Password
                    </label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-input"
                            placeholder="Conferma nuova password"
                            autocomplete="new-password"
                            required
                        >
                        <i class="fas fa-lock form-input-icon"></i>
                        <i class="fas fa-eye password-toggle" id="toggleConfirm"></i>
                    </div>
                </div>

                <button type="submit" class="btn-reset">
                    <i class="fas fa-save"></i> Salva Nuova Password
                </button>
            </form>
            <?php endif; ?>

            <div class="back-to-login">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Torna al Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const toggleNew = document.getElementById('toggleNew');
        const toggleConfirm = document.getElementById('toggleConfirm');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        if (toggleNew) {
            toggleNew.addEventListener('click', function() {
                const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                newPasswordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

        if (toggleConfirm) {
            toggleConfirm.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

        // Real-time password match validation
        if (confirmPasswordInput && newPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== newPasswordInput.value && this.value.length > 0) {
                    this.style.borderColor = '#ef4444';
                } else {
                    this.style.borderColor = '#e5e7eb';
                }
            });
        }
    </script>
</body>
</html>
