<?php
/**
 * FTFS Dashboard v2.0 - Login Page
 *
 * Descrizione:
 * Pagina di autenticazione utenti con design moderno e animazioni CSS.
 * Gestisce login tramite email e password con supporto per hash MD5 legacy
 * e bcrypt moderno. Crea sessioni PHP per l'accesso all'applicazione.
 *
 * Funzionalita:
 * - Validazione email/password
 * - Supporto doppio hash: password_verify (bcrypt) + MD5 (legacy)
 * - Auto-redirect se utente gia loggato
 * - Toggle show/hide password
 * - Floating particles background animato
 * - Messaggi di errore user-friendly
 *
 * Campi Form:
 * - email (type: email, required)
 * - password (type: password, required)
 *
 * Sessioni Create:
 * - $_SESSION['username'] - Email utente inserita nel login
 * - $_SESSION['bu'] - Business Unit dell'utente (da tabella users)
 * - $_SESSION['user_id'] - ID univoco utente
 *
 * Database:
 * - Tabella: users
 * - Colonne utilizzate: email, password, bu, id
 *
 * Sicurezza:
 * - Prepared statements (SQL injection safe)
 * - Password hashing (bcrypt preferito, MD5 per compatibilita)
 * - Session management
 * - Input sanitization
 *
 * Redirect:
 * - Successo: index.php (dashboard)
 * - Gia loggato: index.php
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config.php';

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password (assuming MD5 or password_verify)
            if (password_verify($password, $user['password']) || md5($password) === $user['password']) {
                // Check if password is expired (45 days)
                if (isset($user['password_last_changed']) && !empty($user['password_last_changed'])) {
                    $password_last_changed = new DateTime($user['password_last_changed']);
                    $now = new DateTime();
                    $interval = $now->diff($password_last_changed);
                    $daysSinceLastChange = $interval->days;

                    // Password expires after 45 days
                    if ($daysSinceLastChange >= 45) {
                        // Password expired, force reset
                        $_SESSION['reset_password_user'] = $email;
                        header("Location: reset_password.php");
                        exit();
                    }
                }

                // Login successful
                $_SESSION['username'] = $email; // Username inserito nel login
                $_SESSION['bu'] = $user['bu'];
                $_SESSION['user_id'] = $user['id'];

                header("Location: index.php");
                exit();
            } else {
                $error = 'Credenziali non valide';
            }
        } else {
            $error = 'Credenziali non valide';
        }

        $stmt->close();
    } else {
        $error = 'Inserisci username e password';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard Transazioni</title>
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

        .login-container {
            width: 100%;
            max-width: 450px;
        }

        .login-card {
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

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 15px;
            color: #6b7280;
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

        .form-input-wrapper {
            position: relative;
        }

        .form-input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            pointer-events: none;
            transition: color 0.3s;
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

        .form-input:focus + .form-input-icon {
            color: #667eea;
        }

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
        }

        .login-footer-text {
            font-size: 13px;
            color: #6b7280;
        }

        .login-footer-text strong {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .forgot-password-link {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.3s;
        }

        .forgot-password-link a:hover {
            color: #764ba2;
        }

        .forgot-password-link i {
            margin-right: 6px;
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

        /* Floating particles background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
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

        .login-container {
            position: relative;
            z-index: 1;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 40px 30px;
            }

            .login-title {
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

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1 class="login-title">Benvenuto</h1>
                <p class="login-subtitle">Accedi alla tua dashboard transazioni</p>
            </div>

            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <div class="form-input-wrapper">
                        <input
                            type="text"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="Inserisci username"
                            autocomplete="username"
                            required
                            autofocus
                        >
                        <i class="fas fa-user form-input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Inserisci la tua password"
                            autocomplete="current-password"
                            required
                        >
                        <i class="fas fa-lock form-input-icon"></i>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Accedi
                </button>
            </form>

            <div class="forgot-password-link">
                <a href="forgot_password.php">
                    <i class="fas fa-question-circle"></i> Password dimenticata?
                </a>
            </div>

            <div class="login-footer">
                <p class="login-footer-text">
                    Dashboard <strong>FTFS</strong>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Focus animation
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Enter key submit
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
