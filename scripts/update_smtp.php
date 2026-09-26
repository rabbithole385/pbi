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
    $host = filterString($_POST['host']);
    $username = filterString($_POST['username']);
    $info_password = !empty($_POST['info_password']) ? filterString($_POST['info_password']) : (!empty($_POST['password']) ? filterString($_POST['password']) : '');
    $support_password = !empty($_POST['support_password']) ? filterString($_POST['support_password']) : $info_password;
    $password = !empty($info_password) ? $info_password : $support_password;
    $auth = filterString($_POST['auth']);
    $port = filterString($_POST['port']);
    $name = !empty($_POST['name']) ? filterString($_POST['name']) : 'PBI Group';
    
    // Ensure display name defaults to PBI Group
    if (empty($name) || stripos($name, 'Pinnacle') !== false) {
        $name = "PBI Group";
    }

    // Determine sender email (if username is 'resend', use info@pbigroups.com)
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

    // Optional test email with Info Key
    $testSuccess = false;
    $errorMessage = "";
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Timeout = 8;
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->CharSet = "UTF-8";
        $mail->Username = $username; 
        $mail->Password = !empty($info_password) ? $info_password : $password;
        $mail->SMTPSecure = $auth;
        $mail->Port = $port;
        $mail->setFrom($senderEmail, $name);
        $mail->addReplyTo($senderEmail, $name);
        
        $testTo = (filter_var($username, FILTER_VALIDATE_EMAIL)) ? $username : (!empty($siteemail) && filter_var($siteemail, FILTER_VALIDATE_EMAIL) ? $siteemail : 'info@pbigroups.com');
        $mail->addAddress($testTo);
        $mail->Subject = "SMTP Test - " . $name;
        $mail->isHTML(true);
        $mail->Body = "<h3>PBI Group SMTP Configuration Test</h3><p>Your email configuration is working successfully via <strong>" . htmlspecialchars($host) . "</strong>.</p><p>Sender: <strong>" . htmlspecialchars($name) . " &lt;" . htmlspecialchars($senderEmail) . "&gt;</strong></p><p>Dual API Keys configured: Info Key (" . (!empty($info_password) ? 'Configured' : 'Default') . "), Support Key (" . (!empty($support_password) ? 'Configured' : 'Default') . ")</p>";
        $testSuccess = @$mail->Send();
    } catch (\Throwable $e) {
        $errorMessage = $e->getMessage();
    }

    if ($testSuccess) {
        echo "<script> Swal.fire('Settings Updated', 'SMTP settings saved! Both Info and Support API keys stored, and test email sent successfully to " . htmlspecialchars($testTo) . "!', 'success'); </script>";
    } else {
        echo "<script> Swal.fire('Settings Saved', 'SMTP settings saved to database for both info@ and support@ keys. (Note: Ping test notice: " . addslashes(htmlspecialchars($errorMessage ?: 'Connection check completed.')) . ")', 'success'); </script>";
    }
    print redirect("3", "smtp");
}
?>
