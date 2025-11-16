<?php
include 'config.php';
session_start();
$message = "";
$showForm = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM users WHERE password_reset_token = ? AND password_reset_token_expiry > NOW()");
    if ($stmt === false) {
        die("Errore nella preparazione della query: " . $conn->error);
    }
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $showForm = true;
        $row = $result->fetch_assoc();
        $email = $row['email'];
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($newPassword === $confirmPassword) {
                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update the password and clear the token
                $stmt = $conn->prepare("UPDATE users SET password = ?, password_reset_token = NULL, password_reset_token_expiry = NULL, password_last_changed = NOW() WHERE email = ?");
                if ($stmt === false) {
                    die("Errore nella preparazione della query: " . $conn->error);
                }
                $stmt->bind_param("ss", $hashedPassword, $email);
                $stmt->execute();

                $message = "La password è stata aggiornata con successo.";
                $showForm = false;
            } else {
                $message = "Le password non corrispondono.";
            }
        }
    } else {
        $message = "Il link per il reset della password non è valido o è scaduto.";
    }
} elseif (isset($_SESSION['reset_password_user'])) {
    $showForm = true;
    $email = $_SESSION['reset_password_user'];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword === $confirmPassword) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            // Update the password and clear the token
            $stmt = $conn->prepare("UPDATE users SET password = ?, password_last_changed = NOW() WHERE email = ?");
            if ($stmt === false) {
                die("Errore nella preparazione della query: " . $conn->error);
            }
            $stmt->bind_param("ss", $hashedPassword, $email);
            $stmt->execute();

            $message = "La password è stata aggiornata con successo.";
            $showForm = false;
            unset($_SESSION['reset_password_user']);
        } else {
            $message = "Le password non corrispondono.";
        }
    }
} else {
    $message = "Nessuna richiesta di reset della password trovata.";
}
?>
<?php include 'header.php'; ?>
    <title>Reset Password</title>
    <div class="container mt-5">
        <h2>Reset Password</h2>
        <?php if (!empty($message)) { ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php } ?>

        <?php if ($showForm) { ?>
            <form method="post">
                <div class="form-group">
                    <label for="new_password">Nuova Password:</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Conferma Nuova Password:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Salva</button>
            </form>
        <?php } ?>
        <a href="login.php" class="btn btn-link">Torna al login</a>
    </div>
<?php include 'footer.php'; ?>

