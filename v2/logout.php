<?php
/**
 * FTFS Dashboard v2.0 - Logout Page
 *
 * Descrizione:
 * Pagina di logout con conferma visiva animata e countdown automatico.
 * Distrugge tutte le sessioni PHP e reindirizza al login dopo 5 secondi.
 *
 * Funzionalita:
 * - Distruzione completa sessioni PHP
 * - Animated success checkmark (SVG)
 * - Countdown 5 secondi con auto-redirect
 * - Click anywhere per saltare countdown
 * - Floating particles background
 * - Messaggio di conferma logout
 *
 * Azioni Eseguite:
 * - session_unset() - Rimuove tutte le variabili di sessione
 * - session_destroy() - Distrugge completamente la sessione
 *
 * Redirect Automatico:
 * - Destinazione: login.php
 * - Delay: 5 secondi (skippabile con click)
 * - Metodo: window.location.href via JavaScript
 *
 * Design:
 * - Gradient background (purple-blue)
 * - Card centrale con animazioni
 * - Checkmark SVG animato
 * - Countdown circolare
 * - Responsive design
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Dashboard Transazioni</title>
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
            overflow: hidden;
        }

        .logout-container {
            text-align: center;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .logout-card {
            background: white;
            border-radius: 24px;
            padding: 60px 50px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .logout-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradientMove 3s linear infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        .logout-icon-wrapper {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 50px rgba(102, 126, 234, 0.6);
            }
        }

        .logout-icon {
            font-size: 56px;
            color: white;
            animation: wave 2s ease-in-out infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        .logout-title {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }

        .logout-message {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .logout-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .countdown {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #9ca3af;
        }

        .countdown strong {
            color: #667eea;
            font-weight: 700;
        }

        /* Success checkmark animation */
        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            position: relative;
        }

        .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid #10b981;
            animation: scaleUp 0.5s ease;
        }

        @keyframes scaleUp {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .check-icon::before {
            top: 3px;
            left: -2px;
            width: 30px;
            transform-origin: 100% 50%;
            border-radius: 100px 0 0 100px;
        }

        .check-icon::after {
            top: 0;
            left: 30px;
            width: 60px;
            transform-origin: 0 50%;
            border-radius: 0 100px 100px 0;
            animation: rotateCircle 0.8s ease-in;
        }

        .check-icon::before,
        .check-icon::after {
            content: '';
            height: 100px;
            position: absolute;
            background: white;
            transform: rotate(-45deg);
        }

        .check-icon .icon-line {
            height: 5px;
            background-color: #10b981;
            display: block;
            border-radius: 2px;
            position: absolute;
            z-index: 10;
        }

        .check-icon .icon-line.line-tip {
            top: 46px;
            left: 14px;
            width: 25px;
            transform: rotate(45deg);
            animation: iconLineTip 0.75s;
        }

        .check-icon .icon-line.line-long {
            top: 38px;
            right: 8px;
            width: 47px;
            transform: rotate(-45deg);
            animation: iconLineLong 0.75s;
        }

        @keyframes rotateCircle {
            0% {
                transform: rotate(-45deg);
            }
            5% {
                transform: rotate(-45deg);
            }
            12% {
                transform: rotate(-405deg);
            }
            100% {
                transform: rotate(-405deg);
            }
        }

        @keyframes iconLineTip {
            0% {
                width: 0;
                left: 1px;
                top: 19px;
            }
            54% {
                width: 0;
                left: 1px;
                top: 19px;
            }
            70% {
                width: 50px;
                left: -8px;
                top: 37px;
            }
            84% {
                width: 17px;
                left: 21px;
                top: 48px;
            }
            100% {
                width: 25px;
                left: 14px;
                top: 46px;
            }
        }

        @keyframes iconLineLong {
            0% {
                width: 0;
                right: 46px;
                top: 54px;
            }
            65% {
                width: 0;
                right: 46px;
                top: 54px;
            }
            84% {
                width: 55px;
                right: 0;
                top: 35px;
            }
            100% {
                width: 47px;
                right: 8px;
                top: 38px;
            }
        }

        /* Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255,255,255,0.6);
            border-radius: 50%;
            animation: float 12s infinite;
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
                transform: translateY(-100vh) translateX(50px);
                opacity: 0;
            }
        }

        @media (max-width: 480px) {
            .logout-card {
                padding: 40px 30px;
            }

            .logout-title {
                font-size: 26px;
            }

            .logout-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Particles -->
    <div class="particles">
        <?php for($i = 0; $i < 40; $i++): ?>
        <div class="particle" style="left: <?php echo rand(0, 100); ?>%; animation-delay: <?php echo rand(0, 12); ?>s; animation-duration: <?php echo rand(8, 16); ?>s;"></div>
        <?php endfor; ?>
    </div>

    <div class="logout-container">
        <div class="logout-card">
            <!-- Animated Success Checkmark -->
            <div class="success-checkmark">
                <div class="check-icon">
                    <span class="icon-line line-tip"></span>
                    <span class="icon-line line-long"></span>
                </div>
            </div>

            <h1 class="logout-title">Logout Effettuato</h1>
            <p class="logout-message">
                Sei stato disconnesso con successo dalla dashboard.<br>
                A presto! 👋
            </p>

            <div class="logout-actions">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Accedi di Nuovo
                </a>
                <a href="/" class="btn btn-secondary">
                    <i class="fas fa-home"></i>
                    Home
                </a>
            </div>

            <div class="countdown">
                Reindirizzamento al login tra <strong id="countdown">5</strong> secondi...
            </div>
        </div>
    </div>

    <script>
        // Countdown redirect
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');

        const interval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = 'login.php';
            }
        }, 1000);

        // Allow clicking anywhere to skip countdown
        document.body.addEventListener('click', function(e) {
            if (!e.target.closest('.btn')) {
                clearInterval(interval);
                window.location.href = 'login.php';
            }
        });
    </script>
</body>
</html>
