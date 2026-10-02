<?php
/**
 * Log out of Webmail session
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['webmail_logged']);
unset($_SESSION['webmail_account_id']);
unset($_SESSION['webmail_email']);
unset($_SESSION['webmail_name']);
unset($_SESSION['webmail_role']);

header("Location: login.php");
exit;
