<?php
session_start();
// ... (Your existing login.php code, including config.php and database connection)
include 'config.php';

// Your secret key from reCAPTCHA
$recaptchaSecretKey = '6LfgN_cqAAAAAMUhyVByEWjcv-hKzPDUf-CE1KZb'; // Replace with your actual secret key

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Verify reCAPTCHA Response
    if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptchaData = [
            'secret' => $recaptchaSecretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($recaptchaData)
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($recaptchaUrl, false, $context);
        $response = json_decode($result, true);
      
        if ($response['success']) {
            // 2. reCAPTCHA is valid, proceed with login
            $email = $_POST['email'];
            $username = $email;
            $password = $_POST['password'];
          
          
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $row = $result->fetch_assoc();
                 $password_last_changed = new DateTime($row['password_last_changed']);
                 $now = new DateTime();
                 $interval = $now->diff($password_last_changed);
                   $daysSinceLastChange = $interval->days;
                // 45 Giorni e scade la password
                    if ($daysSinceLastChange >= 45) {
                        $error = "La password è scaduta. Si prega di reimpostarla.";
                        // Redirect to password reset page or force password change
                 
                        $_SESSION['reset_password_user'] = $username;
                        header("Location: reset_password.php");
                        exit;
                    }

                 
                if (password_verify($password, $row['password'])) {
                    $_SESSION['username'] = $username;
                    $_SESSION['bu'] = $row['bu'];
                    header("Location: index.php");
                    exit;
                } else {
                    $message = "Incorrect password.";
                }
            } else {
                $message = "User not found.";
            }
        } else {
            // 3. reCAPTCHA failed
            $message = "Please verify that you are not a robot.";
        }
    } else {
        $message = "Please complete the reCAPTCHA.";
    }
}
?>
<?php
// Include the header AFTER the potential redirect
include 'header.php';
?>
<title>Login</title>
<div class="container mt-5">
    <h2>Login</h2>
    <?php if (!empty($message)) { ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php } ?>
    <form method="post">
        <div class="form-group">
            <label for="email">Username:</label>
            <input type="text" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <!-- reCAPTCHA v2 Checkbox -->
        <div class="g-recaptcha" data-sitekey="6LfgN_cqAAAAADShbUsjWMbainWNxaK2PXEQBZ25"></div>
        <br>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>

<!-- Include reCAPTCHA JavaScript -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php include 'footer.php'; ?>
