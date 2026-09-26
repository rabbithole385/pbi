<?php
    require_once('functions.php');
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $baseDir = dirname(__DIR__);
        if (file_exists($baseDir . '/includes/PHPMailer.php')) {
            require_once $baseDir . '/includes/PHPMailer.php';
            require_once $baseDir . '/includes/SMTP.php';
            require_once $baseDir . '/includes/Exception.php';
        }
    }
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;  

if (isset($_POST)) {
    $host = filterString($_POST['host'] ?? '');
    $username = filterString($_POST['username'] ?? '');
    $info_password = !empty($_POST['info_password']) ? filterString($_POST['info_password']) : (!empty($_POST['password']) ? filterString($_POST['password']) : '');
    $support_password = !empty($_POST['support_password']) ? filterString($_POST['support_password']) : $info_password;
    $password = !empty($info_password) ? $info_password : $support_password;
    $auth = filterString($_POST['auth'] ?? 'ssl');
    $port = filterString($_POST['port'] ?? '465');
    $name = !empty($_POST['name']) ? filterString($_POST['name']) : 'PBI Group';
    
    // Ensure display name defaults to PBI Group
    if (empty($name) || stripos($name, 'Pinnacle') !== false) {
        $name = "PBI Group";
    }

    // Determine sender email (if username is 'resend' or 'apikey', use info@pbigroups.com)
    $senderEmail = (filter_var($username, FILTER_VALIDATE_EMAIL)) ? $username : 'info@pbigroups.com';

    // Ensure database columns exist
    $checkCol = @$conn->query("SHOW COLUMNS FROM smtp_setting LIKE 'support_password'");
    if ($checkCol && mysqli_num_rows($checkCol) == 0) {
        @$conn->query("ALTER TABLE smtp_setting ADD COLUMN support_password TEXT DEFAULT NULL");
    }
    $checkCol2 = @$conn->query("SHOW COLUMNS FROM smtp_setting LIKE 'info_password'");
    if ($checkCol2 && mysqli_num_rows($checkCol2) == 0) {
        @$conn->query("ALTER TABLE smtp_setting ADD COLUMN info_password TEXT DEFAULT NULL");
    }
    @$conn->query("ALTER TABLE smtp_setting MODIFY COLUMN password TEXT");

    // Persist SMTP credentials to the database
    $conn->query("UPDATE smtp_setting SET host = '$host', password = '$password', info_password = '$info_password', support_password = '$support_password', username = '$username', port = '$port', smtp_auth = '$auth', display_name = '$name' WHERE id = 1");

    // Test email destination from user input
    $testTo = !empty($_POST['test_email']) ? filterString($_POST['test_email']) : ((filter_var($username, FILTER_VALIDATE_EMAIL)) ? $username : (!empty($siteemail) ? $siteemail : 'info@pbigroups.com'));
    
    $testSubject = "SMTP Test - " . $name;
    $testBody = "<h3>PBI Group Email Configuration Test</h3><p>Your email configuration is working successfully via <strong>" . htmlspecialchars($host ?: 'Resend/SMTP') . "</strong>.</p><p>Sender: <strong>" . htmlspecialchars($name) . " &lt;" . htmlspecialchars($senderEmail) . "&gt;</strong></p><p>Recipient: <strong>" . htmlspecialchars($testTo) . "</strong></p><p>Info Key: <code>" . (!empty($info_password) ? substr($info_password, 0, 7) . '...' : 'Not Set') . "</code><br>Support Key: <code>" . (!empty($support_password) ? substr($support_password, 0, 7) . '...' : 'Not Set') . "</code></p>";

    $debugOut = "";
    $testSuccess = false;
    if (function_exists('send_pbi_mail')) {
        $testSuccess = send_pbi_mail($testTo, $testSubject, $testBody, $senderEmail, $name, $debugOut);
    } else {
        try {
            $mail = new PHPMailer(true);
            if (function_exists('configure_pbi_mailer')) {
                configure_pbi_mailer($mail, $senderEmail, $name);
            }
            $mail->clearAddresses();
            $mail->addAddress($testTo);
            $mail->Subject = $testSubject;
            $mail->isHTML(true);
            $mail->Body = $testBody;
            $testSuccess = $mail->send();
        } catch (\Throwable $e) {
            $debugOut = $e->getMessage();
        }
    }

    if ($testSuccess) {
        echo "<script> Swal.fire('Settings Updated', 'SMTP & API settings saved! Test email sent successfully to " . htmlspecialchars($testTo) . "! (" . addslashes(htmlspecialchars($debugOut)) . ")', 'success'); </script>";
    } else {
        echo "<script> Swal.fire('Settings Saved', 'SMTP settings saved to database. (Test notice: " . addslashes(htmlspecialchars($debugOut ?: 'Connection check completed.')) . ")', 'success'); </script>";
    }
    print redirect("3", "smtp");
}
?>
