<?php
/**
 * PBI / Banking Mailbox & Webmail Backend Core Engine
 * Manages Mailbox accounts, message storage, PHPMailer outgoing delivery,
 * IMAP/POP3 incoming sync (native + socket fallback), and session handling.
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/functions.php';

// Safe include of PHPMailer
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $phpMailerBase = dirname(__DIR__) . '/includes/';
    if (file_exists($phpMailerBase . 'PHPMailer.php')) {
        require_once $phpMailerBase . 'Exception.php';
        require_once $phpMailerBase . 'PHPMailer.php';
        require_once $phpMailerBase . 'SMTP.php';
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Initialize Mailbox Database Tables and Default Account
 */
function mb_init_database() {
    global $conn, $smtp_host, $smtp_username, $smtp_password, $smtp_port, $smtp_auth, $display_name, $siteemail, $sitename;

    if (!$conn || $conn->connect_error) {
        return false;
    }

    // 1. Table: mailbox_accounts
    $sqlAccounts = "CREATE TABLE IF NOT EXISTS `mailbox_accounts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(160) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `display_name` VARCHAR(120) NOT NULL,
        `signature` TEXT DEFAULT NULL,
        `role` VARCHAR(30) DEFAULT 'admin',
        `incoming_type` VARCHAR(20) DEFAULT 'imap',
        `incoming_host` VARCHAR(255) DEFAULT '',
        `incoming_port` INT DEFAULT 993,
        `incoming_secure` VARCHAR(10) DEFAULT 'ssl',
        `incoming_username` VARCHAR(255) DEFAULT '',
        `incoming_password` VARCHAR(255) DEFAULT '',
        `smtp_host` VARCHAR(255) DEFAULT '',
        `smtp_port` INT DEFAULT 587,
        `smtp_secure` VARCHAR(10) DEFAULT 'tls',
        `smtp_username` VARCHAR(255) DEFAULT '',
        `smtp_password` VARCHAR(255) DEFAULT '',
        `is_default` TINYINT(1) DEFAULT 1,
        `status` VARCHAR(20) DEFAULT 'active',
        `last_sync` DATETIME DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    @$conn->query($sqlAccounts);

    // 2. Table: mailbox_messages
    $sqlMessages = "CREATE TABLE IF NOT EXISTS `mailbox_messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `account_id` INT NOT NULL DEFAULT 1,
        `msg_uid` VARCHAR(150) DEFAULT NULL,
        `folder` VARCHAR(20) NOT NULL DEFAULT 'inbox',
        `sender_name` VARCHAR(150) DEFAULT '',
        `sender_email` VARCHAR(160) NOT NULL,
        `recipient_name` VARCHAR(150) DEFAULT '',
        `recipient_email` VARCHAR(160) NOT NULL,
        `cc` TEXT DEFAULT NULL,
        `bcc` TEXT DEFAULT NULL,
        `reply_to` VARCHAR(160) DEFAULT NULL,
        `subject` VARCHAR(255) NOT NULL DEFAULT '(No Subject)',
        `body_plain` LONGTEXT DEFAULT NULL,
        `body_html` LONGTEXT DEFAULT NULL,
        `has_attachment` TINYINT(1) DEFAULT 0,
        `attachments` LONGTEXT DEFAULT NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `is_starred` TINYINT(1) DEFAULT 0,
        `is_trash` TINYINT(1) DEFAULT 0,
        `date_received` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (`account_id`),
        INDEX (`folder`),
        INDEX (`is_read`),
        INDEX (`msg_uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    @$conn->query($sqlMessages);

    // Ensure uploads directory exists
    $uploadDir = dirname(__DIR__) . '/uploads/mail';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
        @file_put_contents($uploadDir . '/index.php', '<?php // Silence is golden');
    }

    // 3. Check if default account exists
    $chk = @$conn->query("SELECT id FROM `mailbox_accounts` LIMIT 1");
    if ($chk && $chk->num_rows === 0) {
        // Resolve best default email
        $defaultEmail = 'admin@pbigroups.com';
        if (!empty($smtp_username) && filter_var($smtp_username, FILTER_VALIDATE_EMAIL)) {
            $defaultEmail = $smtp_username;
        } elseif (!empty($siteemail) && filter_var($siteemail, FILTER_VALIDATE_EMAIL)) {
            $defaultEmail = $siteemail;
        } else {
            $uQ = @$conn->query("SELECT email, password FROM users WHERE id = 1 LIMIT 1");
            if ($uQ && $uR = $uQ->fetch_assoc()) {
                if (!empty($uR['email'])) $defaultEmail = $uR['email'];
            }
        }

        $defaultName = !empty($display_name) ? $display_name : ($sitename . ' Administration');
        $defaultPass = md5('Admin@2026'); // Standard default password
        $defaultHost = !empty($smtp_host) ? $smtp_host : 'mail.pbigroups.com';

        $inHost = $defaultHost;
        $inPort = 993;
        $inSecure = 'ssl';
        $inUser = $defaultEmail;
        $inPass = !empty($smtp_password) ? $smtp_password : '';

        $outHost = !empty($smtp_host) ? $smtp_host : $defaultHost;
        $outPort = !empty($smtp_port) ? (int)$smtp_port : 587;
        $outSecure = !empty($smtp_auth) ? $smtp_auth : 'tls';
        $outUser = !empty($smtp_username) ? $smtp_username : $defaultEmail;
        $outPass = !empty($smtp_password) ? $smtp_password : '';

        $stmt = $conn->prepare("INSERT INTO `mailbox_accounts` 
            (`name`, `email`, `password`, `display_name`, `role`, `incoming_type`, `incoming_host`, `incoming_port`, `incoming_secure`, `incoming_username`, `incoming_password`, `smtp_host`, `smtp_port`, `smtp_secure`, `smtp_username`, `smtp_password`, `is_default`, `status`) 
            VALUES (?, ?, ?, ?, 'admin', 'imap', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'active')");
        if ($stmt) {
            $stmt->bind_param('sssssisssissss', 
                $defaultName, $defaultEmail, $defaultPass, $defaultName, 
                $inHost, $inPort, $inSecure, $inUser, $inPass,
                $outHost, $outPort, $outSecure, $outUser, $outPass
            );
            $stmt->execute();
            $newAccountId = $conn->insert_id;
            $stmt->close();

            // Seed a welcome message
            mb_seed_welcome_messages($newAccountId, $defaultEmail, $defaultName);
        }
    }
    return true;
}

/**
 * Seed initial helpful messages so the mailbox is fully populated and alive
 */
function mb_seed_welcome_messages($accountId, $recipientEmail, $recipientName) {
    global $conn, $sitename;

    $now = date('Y-m-d H:i:s');
    $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));

    $welcomeSubject = "Welcome to your built-in " . $sitename . " Webmail Suite";
    $welcomeBodyHtml = '
    <div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; max-width: 650px; margin: 0 auto; color: #1e293b; line-height: 1.6;">
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 28px 24px; border-radius: 12px 12px 0 0; text-align: center;">
            <h2 style="margin: 0 0 8px; font-size: 24px; font-weight: 700;">Welcome to ' . htmlspecialchars($sitename) . ' Mail</h2>
            <p style="margin: 0; color: #94a3b8; font-size: 15px;">Your unified system for sending, receiving, and managing communications</p>
        </div>
        <div style="background: #ffffff; padding: 28px 24px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 12px 12px;">
            <p style="font-size: 16px;">Hello <strong>' . htmlspecialchars($recipientName) . '</strong>,</p>
            <p>Your built-in mailbox environment is ready. You now have access to:</p>
            <ul style="padding-left: 20px; color: #334155;">
                <li style="margin-bottom: 8px;"><strong>Full Sending & Receiving:</strong> Send emails via SMTP and sync incoming emails from IMAP/POP3 servers.</li>
                <li style="margin-bottom: 8px;"><strong>Separate Standalone Webmail Portal:</strong> A dedicated, branded login screen located at <code>/mail/</code> or <code>/webmail</code> that you can share with colleagues, support staff, or partners.</li>
                <li style="margin-bottom: 8px;"><strong>Built-in Admin Access:</strong> Direct 1-click access inside your banking Admin dashboard under <em>Mailbox</em>.</li>
                <li style="margin-bottom: 8px;"><strong>Attachments & Rich Text:</strong> Upload files, send HTML formatted messages, and organize emails into Inbox, Sent, Drafts, and Trash.</li>
            </ul>
            <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 6px; margin: 24px 0;">
                <h4 style="margin: 0 0 6px; color: #1e293b; font-size: 15px;">Quick Credentials for Standalone Portal:</h4>
                <p style="margin: 0; font-size: 14px; color: #475569;">
                    <strong>Login URL:</strong> <code>/mail/login</code><br>
                    <strong>Email:</strong> ' . htmlspecialchars($recipientEmail) . '<br>
                    <strong>Default Password:</strong> <code>Admin@2026</code> (change anytime in Settings)
                </p>
            </div>
            <p style="margin-top: 24px; font-size: 14px; color: #64748b;">
                Need to customize your incoming or outgoing servers? Go to <strong>Mailbox Settings</strong> to adjust your IMAP and SMTP configurations at any time.
            </p>
            <p style="margin-bottom: 0; color: #1e293b; font-weight: 600;">— ' . htmlspecialchars($sitename) . ' Systems</p>
        </div>
    </div>';
    $welcomeBodyPlain = "Welcome to " . $sitename . " Mailbox. Your webmail environment is ready for sending and receiving messages. Access your standalone webmail at /mail/login with email: " . $recipientEmail . " and password: Admin@2026";

    $stmt = $conn->prepare("INSERT INTO `mailbox_messages` 
        (`account_id`, `folder`, `sender_name`, `sender_email`, `recipient_name`, `recipient_email`, `subject`, `body_plain`, `body_html`, `is_read`, `is_starred`, `date_received`) 
        VALUES (?, 'inbox', 'System Support', 'system@pbi.local', ?, ?, ?, ?, ?, 0, 1, ?)");
    if ($stmt) {
        $stmt->bind_param('issssss', $accountId, $recipientName, $recipientEmail, $welcomeSubject, $welcomeBodyPlain, $welcomeBodyHtml, $now);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Retrieve a single mailbox account by ID or Email
 */
function mb_get_account($idOrEmail) {
    global $conn;
    mb_init_database();

    if (is_numeric($idOrEmail)) {
        $stmt = $conn->prepare("SELECT * FROM `mailbox_accounts` WHERE `id` = ? LIMIT 1");
        $id = (int)$idOrEmail;
        $stmt->bind_param('i', $id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM `mailbox_accounts` WHERE `email` = ? LIMIT 1");
        $stmt->bind_param('s', $idOrEmail);
    }

    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $account = $res->fetch_assoc();
        $stmt->close();
        return $account;
    }
    return null;
}

/**
 * Retrieve all mailbox accounts
 */
function mb_get_all_accounts() {
    global $conn;
    mb_init_database();
    $list = array();
    $q = @$conn->query("SELECT * FROM `mailbox_accounts` ORDER BY `is_default` DESC, `id` ASC");
    if ($q) {
        while ($r = $q->fetch_assoc()) {
            unset($r['password']); // Never expose raw hash in generic lists
            $list[] = $r;
        }
    }
    return $list;
}

/**
 * Get the current active webmail session or auto-authenticate logged-in admin
 */
function mb_get_current_session() {
    global $conn;
    mb_init_database();

    // 1. If explicit webmail session is active
    if (!empty($_SESSION['webmail_logged']) && !empty($_SESSION['webmail_account_id'])) {
        $acc = mb_get_account($_SESSION['webmail_account_id']);
        if ($acc && $acc['status'] === 'active') {
            return $acc;
        }
    }

    // 2. If Admin is logged in via main banking panel, auto-authenticate seamlessly!
    if (!empty($_SESSION['loggedAdmin']) || !empty($_SESSION['userAdmin'])) {
        $defaultAcc = mb_get_default_account();
        if ($defaultAcc) {
            $_SESSION['webmail_logged'] = 1;
            $_SESSION['webmail_account_id'] = $defaultAcc['id'];
            $_SESSION['webmail_email'] = $defaultAcc['email'];
            $_SESSION['webmail_name'] = $defaultAcc['display_name'];
            $_SESSION['webmail_role'] = 'admin';
            return $defaultAcc;
        }
    }

    return null;
}

/**
 * Get default account
 */
function mb_get_default_account() {
    global $conn;
    mb_init_database();
    $q = @$conn->query("SELECT * FROM `mailbox_accounts` WHERE `is_default` = 1 AND `status` = 'active' LIMIT 1");
    if ($q && $row = $q->fetch_assoc()) {
        return $row;
    }
    $q2 = @$conn->query("SELECT * FROM `mailbox_accounts` WHERE `status` = 'active' ORDER BY `id` ASC LIMIT 1");
    if ($q2 && $row2 = $q2->fetch_assoc()) {
        return $row2;
    }
    return null;
}

/**
 * Authenticate login for Webmail Portal
 */
function mb_authenticate_login($email, $password) {
    global $conn;
    mb_init_database();

    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
    $passHash = md5($password);

    // 1. Check mailbox_accounts
    $stmt = $conn->prepare("SELECT * FROM `mailbox_accounts` WHERE `email` = ? AND `status` = 'active' LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($account = $res->fetch_assoc()) {
            $stmt->close();
            // Verify password (matches md5 or bcrypt or plain)
            $matched = false;
            if ($account['password'] === $passHash || $account['password'] === $password) {
                $matched = true;
            } elseif (function_exists('password_verify') && @password_verify($password, $account['password'])) {
                $matched = true;
            }

            if ($matched) {
                $_SESSION['webmail_logged'] = 1;
                $_SESSION['webmail_account_id'] = $account['id'];
                $_SESSION['webmail_email'] = $account['email'];
                $_SESSION['webmail_name'] = $account['display_name'];
                $_SESSION['webmail_role'] = $account['role'];
                return array('success' => true, 'account' => $account);
            }
        } else {
            $stmt->close();
        }
    }

    // 2. Fallback: Check Super Admin in `users` table
    $stmt2 = $conn->prepare("SELECT * FROM `users` WHERE `email` = ? AND `password` = ? AND `id` = 1 LIMIT 1");
    if ($stmt2) {
        $stmt2->bind_param('ss', $email, $passHash);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($adminUser = $res2->fetch_assoc()) {
            $stmt2->close();
            // Ensure a mailbox account exists for this admin
            $acc = mb_get_account($adminUser['email']);
            if (!$acc) {
                $acc = mb_get_default_account();
            }
            if ($acc) {
                $_SESSION['webmail_logged'] = 1;
                $_SESSION['webmail_account_id'] = $acc['id'];
                $_SESSION['webmail_email'] = $acc['email'];
                $_SESSION['webmail_name'] = $acc['display_name'];
                $_SESSION['webmail_role'] = 'admin';
                return array('success' => true, 'account' => $acc);
            }
        } else {
            $stmt2->close();
        }
    }

    return array('success' => false, 'message' => 'Invalid email address or password.');
}

/**
 * Get folder counts (Inbox, Sent, Drafts, Trash, Starred, Unread)
 */
function mb_get_counts($accountId) {
    global $conn;
    mb_init_database();

    $counts = array(
        'inbox' => 0,
        'unread' => 0,
        'starred' => 0,
        'sent' => 0,
        'drafts' => 0,
        'trash' => 0
    );

    $accId = (int)$accountId;

    // Unread in inbox
    $qUnread = @$conn->query("SELECT COUNT(*) AS c FROM `mailbox_messages` WHERE `account_id` = $accId AND `folder` = 'inbox' AND `is_read` = 0 AND `is_trash` = 0");
    if ($qUnread && $r = $qUnread->fetch_assoc()) $counts['unread'] = (int)$r['c'];

    // Total inbox
    $qInbox = @$conn->query("SELECT COUNT(*) AS c FROM `mailbox_messages` WHERE `account_id` = $accId AND `folder` = 'inbox' AND `is_trash` = 0");
    if ($qInbox && $r = $qInbox->fetch_assoc()) $counts['inbox'] = (int)$r['c'];

    // Starred
    $qStar = @$conn->query("SELECT COUNT(*) AS c FROM `mailbox_messages` WHERE `account_id` = $accId AND `is_starred` = 1 AND `is_trash` = 0");
    if ($qStar && $r = $qStar->fetch_assoc()) $counts['starred'] = (int)$r['c'];

    // Sent
    $qSent = @$conn->query("SELECT COUNT(*) AS c FROM `mailbox_messages` WHERE `account_id` = $accId AND `folder` = 'sent' AND `is_trash` = 0");
    if ($qSent && $r = $qSent->fetch_assoc()) $counts['sent'] = (int)$r['c'];

    // Drafts
    $qDraft = @$conn->query("SELECT COUNT(*) AS c FROM `mailbox_messages` WHERE `account_id` = $accId AND `folder` = 'drafts' AND `is_trash` = 0");
    if ($qDraft && $r = $qDraft->fetch_assoc()) $counts['drafts'] = (int)$r['c'];

    // Trash
    $qTrash = @$conn->query("SELECT COUNT(*) AS c FROM `mailbox_messages` WHERE `account_id` = $accId AND (`folder` = 'trash' OR `is_trash` = 1)");
    if ($qTrash && $r = $qTrash->fetch_assoc()) $counts['trash'] = (int)$r['c'];

    return $counts;
}

/**
 * Retrieve messages for a folder with pagination and search
 */
function mb_get_messages($accountId, $folder = 'inbox', $search = '', $page = 1, $perPage = 25) {
    global $conn;
    mb_init_database();

    $accId = (int)$accountId;
    $folder = strtolower(trim($folder));
    $page = max(1, (int)$page);
    $perPage = max(5, min(100, (int)$perPage));
    $offset = ($page - 1) * $perPage;

    $where = "`account_id` = $accId";

    if ($folder === 'starred') {
        $where .= " AND `is_starred` = 1 AND `is_trash` = 0";
    } elseif ($folder === 'trash') {
        $where .= " AND (`folder` = 'trash' OR `is_trash` = 1)";
    } else {
        $safeFolder = $conn->real_escape_string($folder);
        $where .= " AND `folder` = '$safeFolder' AND `is_trash` = 0";
    }

    if (!empty($search)) {
        $s = $conn->real_escape_string($search);
        $where .= " AND (`subject` LIKE '%$s%' OR `sender_name` LIKE '%$s%' OR `sender_email` LIKE '%$s%' OR `recipient_email` LIKE '%$s%' OR `body_plain` LIKE '%$s%')";
    }

    // Count query
    $countQ = @$conn->query("SELECT COUNT(*) AS total FROM `mailbox_messages` WHERE $where");
    $total = ($countQ && $row = $countQ->fetch_assoc()) ? (int)$row['total'] : 0;

    // Data query
    $dataQ = @$conn->query("SELECT `id`, `account_id`, `folder`, `sender_name`, `sender_email`, `recipient_name`, `recipient_email`, `subject`, `has_attachment`, `is_read`, `is_starred`, `date_received`, SUBSTRING(`body_plain`, 1, 150) AS snippet 
        FROM `mailbox_messages` 
        WHERE $where 
        ORDER BY `date_received` DESC 
        LIMIT $offset, $perPage");

    $items = array();
    if ($dataQ) {
        while ($msg = $dataQ->fetch_assoc()) {
            $items[] = $msg;
        }
    }

    return array(
        'items' => $items,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    );
}

/**
 * Retrieve full message by ID
 */
function mb_get_message($messageId, $accountId = null) {
    global $conn;
    mb_init_database();

    $id = (int)$messageId;
    $sql = "SELECT * FROM `mailbox_messages` WHERE `id` = $id";
    if ($accountId !== null) {
        $accId = (int)$accountId;
        $sql .= " AND `account_id` = $accId";
    }
    $sql .= " LIMIT 1";

    $q = @$conn->query($sql);
    if ($q && $msg = $q->fetch_assoc()) {
        if (!empty($msg['attachments'])) {
            $msg['attachments_list'] = json_decode($msg['attachments'], true) ?: array();
        } else {
            $msg['attachments_list'] = array();
        }
        return $msg;
    }
    return null;
}

/**
 * Update message flag (read, unread, star, unstar, trash, restore, delete)
 */
function mb_mark_message($messageId, $action, $accountId = null) {
    global $conn;
    mb_init_database();

    $id = (int)$messageId;
    $extraWhere = ($accountId !== null) ? " AND `account_id` = " . (int)$accountId : "";

    switch ($action) {
        case 'read':
            return @$conn->query("UPDATE `mailbox_messages` SET `is_read` = 1 WHERE `id` = $id $extraWhere");
        case 'unread':
            return @$conn->query("UPDATE `mailbox_messages` SET `is_read` = 0 WHERE `id` = $id $extraWhere");
        case 'star':
            return @$conn->query("UPDATE `mailbox_messages` SET `is_starred` = 1 WHERE `id` = $id $extraWhere");
        case 'unstar':
            return @$conn->query("UPDATE `mailbox_messages` SET `is_starred` = 0 WHERE `id` = $id $extraWhere");
        case 'trash':
            return @$conn->query("UPDATE `mailbox_messages` SET `is_trash` = 1, `folder` = 'trash' WHERE `id` = $id $extraWhere");
        case 'restore':
            return @$conn->query("UPDATE `mailbox_messages` SET `is_trash` = 0, `folder` = 'inbox' WHERE `id` = $id $extraWhere");
        case 'delete_permanent':
            // Delete attachments from disk first
            $msg = mb_get_message($id, $accountId);
            if ($msg && !empty($msg['attachments_list'])) {
                $base = dirname(__DIR__) . '/';
                foreach ($msg['attachments_list'] as $att) {
                    if (!empty($att['path']) && file_exists($base . $att['path'])) {
                        @unlink($base . $att['path']);
                    }
                }
            }
            return @$conn->query("DELETE FROM `mailbox_messages` WHERE `id` = $id $extraWhere");
    }
    return false;
}

/**
 * Send an email via PHPMailer & store in Sent folder
 */
function mb_send_email($accountId, $to, $subject, $bodyHtml, $options = array()) {
    global $conn, $sitename, $site_url, $emaillogo;
    mb_init_database();

    $account = mb_get_account($accountId);
    if (!$account) {
        return array('success' => false, 'message' => 'Mailbox account not found.');
    }

    $to = trim($to);
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'message' => 'Recipient email address is invalid.');
    }

    $subject = trim($subject);
    if ($subject === '') {
        $subject = '(No Subject)';
    }

    // Process attachments
    $attachments = isset($options['attachments']) && is_array($options['attachments']) ? $options['attachments'] : array();
    $cc = isset($options['cc']) ? trim($options['cc']) : '';
    $bcc = isset($options['bcc']) ? trim($options['bcc']) : '';
    $replyTo = isset($options['reply_to']) ? trim($options['reply_to']) : '';

    $bodyPlain = strip_tags(str_replace(array('<br>', '<br/>', '<br />', '</p>'), "\n", $bodyHtml));

    // Append signature if available
    if (!empty($account['signature'])) {
        $bodyHtml .= "<br><br>" . $account['signature'];
        $bodyPlain .= "\n\n" . strip_tags($account['signature']);
    }

    // Configure PHPMailer
    $mail = new PHPMailer(true);
    $sentOk = false;
    $errorMsg = '';

    try {
        $mail->isSMTP();
        $mail->Host = !empty($account['smtp_host']) ? $account['smtp_host'] : 'localhost';
        $mail->SMTPAuth = !empty($account['smtp_username']);
        $mail->Username = $account['smtp_username'];
        $mail->Password = $account['smtp_password'];
        $mail->Port = !empty($account['smtp_port']) ? (int)$account['smtp_port'] : 587;
        
        $sec = strtolower($account['smtp_secure']);
        if ($sec === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($sec === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        $mail->CharSet = 'UTF-8';
        $fromName = !empty($account['display_name']) ? $account['display_name'] : $sitename;
        $fromEmail = !empty($account['smtp_username']) && filter_var($account['smtp_username'], FILTER_VALIDATE_EMAIL) ? $account['smtp_username'] : $account['email'];
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        if (!empty($replyTo) && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        } else {
            $mail->addReplyTo($fromEmail, $fromName);
        }

        if (!empty($cc)) {
            $ccList = explode(',', $cc);
            foreach ($ccList as $c) {
                $c = trim($c);
                if (filter_var($c, FILTER_VALIDATE_EMAIL)) $mail->addCC($c);
            }
        }

        if (!empty($bcc)) {
            $bccList = explode(',', $bcc);
            foreach ($bccList as $b) {
                $b = trim($b);
                if (filter_var($b, FILTER_VALIDATE_EMAIL)) $mail->addBCC($b);
            }
        }

        // Attachments
        $baseDir = dirname(__DIR__) . '/';
        foreach ($attachments as $att) {
            if (!empty($att['path']) && file_exists($baseDir . $att['path'])) {
                $mail->addAttachment($baseDir . $att['path'], $att['name'] ?? basename($att['path']));
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;
        $mail->AltBody = $bodyPlain;

        // Try delivery
        $mail->send();
        $sentOk = true;

    } catch (Exception $e) {
        $errorMsg = $mail->ErrorInfo ?: $e->getMessage();
        // Even if outgoing SMTP relay fails or is not connected locally, we can still record in Sent or return error
    }

    // Save to Sent Folder in Database
    $attachmentsJson = !empty($attachments) ? json_encode($attachments) : null;
    $hasAtt = !empty($attachments) ? 1 : 0;
    $now = date('Y-m-d H:i:s');
    $senderName = $account['display_name'];
    $senderEmail = $account['email'];

    $stmt = $conn->prepare("INSERT INTO `mailbox_messages` 
        (`account_id`, `folder`, `sender_name`, `sender_email`, `recipient_name`, `recipient_email`, `cc`, `bcc`, `reply_to`, `subject`, `body_plain`, `body_html`, `has_attachment`, `attachments`, `is_read`, `date_received`) 
        VALUES (?, 'sent', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
    if ($stmt) {
        $stmt->bind_param('issssssssssiss', 
            $account['id'], $senderName, $senderEmail, $to, $to, $cc, $bcc, $replyTo, 
            $subject, $bodyPlain, $bodyHtml, $hasAtt, $attachmentsJson, $now
        );
        $stmt->execute();
        $sentMsgId = $conn->insert_id;
        $stmt->close();
    }

    if ($sentOk) {
        return array('success' => true, 'message' => 'Message sent successfully!', 'message_id' => $sentMsgId ?? 0);
    } else {
        return array(
            'success' => false, 
            'message' => 'Email saved to Sent, but SMTP delivery reported: ' . $errorMsg,
            'smtp_error' => $errorMsg,
            'message_id' => $sentMsgId ?? 0
        );
    }
}

/**
 * Synchronize Incoming Mail from configured IMAP / POP3 server
 * Supports native PHP IMAP extension OR pure PHP stream socket fallback.
 */
function mb_sync_incoming($accountId) {
    global $conn;
    mb_init_database();

    $account = mb_get_account($accountId);
    if (!$account) {
        return array('success' => false, 'message' => 'Account not found.');
    }

    $host = trim($account['incoming_host']);
    $user = trim($account['incoming_username']);
    $pass = $account['incoming_password'];
    $port = (int)$account['incoming_port'];
    $secure = strtolower($account['incoming_secure']);

    if (empty($host) || empty($user) || empty($pass)) {
        return array('success' => false, 'message' => 'Incoming mail server settings (Host, Username, Password) are not fully configured for this mailbox.');
    }

    $newMessagesCount = 0;

    // Approach 1: Native PHP IMAP extension
    if (function_exists('imap_open')) {
        $flags = '/imap';
        if ($secure === 'ssl') $flags .= '/ssl';
        elseif ($secure === 'tls') $flags .= '/tls';
        $flags .= '/novalidate-cert';

        $mailboxStr = "{" . $host . ":" . $port . $flags . "}INBOX";
        $mbox = @imap_open($mailboxStr, $user, $pass, 0, 1, array('DISABLE_AUTHENTICATOR' => 'GSSAPI'));

        if ($mbox) {
            $numMsgs = imap_num_msg($mbox);
            // Scan last 30 messages
            $start = max(1, $numMsgs - 30);
            for ($i = $numMsgs; $i >= $start; $i--) {
                $uid = (string)imap_uid($mbox, $i);
                // Check if already fetched
                $chk = @$conn->query("SELECT id FROM `mailbox_messages` WHERE `account_id` = " . (int)$account['id'] . " AND `msg_uid` = '" . $conn->real_escape_string($uid) . "' LIMIT 1");
                if ($chk && $chk->num_rows > 0) {
                    continue; // Already downloaded
                }

                $header = imap_headerinfo($mbox, $i);
                $fromInfo = $header->from[0] ?? null;
                $senderEmail = $fromInfo ? ($fromInfo->mailbox . '@' . $fromInfo->host) : 'unknown@sender.com';
                $senderName = $fromInfo ? (isset($fromInfo->personal) ? mb_decode_mime_str($fromInfo->personal) : $senderEmail) : 'Unknown';

                $subject = isset($header->subject) ? mb_decode_mime_str($header->subject) : '(No Subject)';
                $dateReceived = isset($header->date) ? date('Y-m-d H:i:s', strtotime($header->date)) : date('Y-m-d H:i:s');

                // Extract body
                $structure = imap_fetchstructure($mbox, $i);
                $bodyHtml = '';
                $bodyPlain = '';
                $attachments = array();

                mb_parse_imap_parts($mbox, $i, $structure, '', $bodyPlain, $bodyHtml, $attachments);

                if (empty($bodyHtml) && !empty($bodyPlain)) {
                    $bodyHtml = nl2br(htmlspecialchars($bodyPlain));
                }
                if (empty($bodyPlain) && !empty($bodyHtml)) {
                    $bodyPlain = strip_tags($bodyHtml);
                }

                $hasAtt = !empty($attachments) ? 1 : 0;
                $attJson = !empty($attachments) ? json_encode($attachments) : null;
                $recipientEmail = $account['email'];
                $recipientName = $account['display_name'];

                $stmt = $conn->prepare("INSERT INTO `mailbox_messages` 
                    (`account_id`, `msg_uid`, `folder`, `sender_name`, `sender_email`, `recipient_name`, `recipient_email`, `subject`, `body_plain`, `body_html`, `has_attachment`, `attachments`, `is_read`, `date_received`) 
                    VALUES (?, ?, 'inbox', ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)");
                if ($stmt) {
                    $stmt->bind_param('isssssssisss', 
                        $account['id'], $uid, $senderName, $senderEmail, $recipientName, $recipientEmail, 
                        $subject, $bodyPlain, $bodyHtml, $hasAtt, $attJson, $dateReceived
                    );
                    $stmt->execute();
                    $stmt->close();
                    $newMessagesCount++;
                }
            }
            imap_close($mbox);

            // Update last_sync
            @$conn->query("UPDATE `mailbox_accounts` SET `last_sync` = NOW() WHERE `id` = " . (int)$account['id']);
            return array('success' => true, 'new_count' => $newMessagesCount, 'message' => "Sync complete! Fetched $newMessagesCount new message(s).");
        } else {
            $err = imap_last_error();
            return array('success' => false, 'message' => 'IMAP connection failed: ' . ($err ?: 'Unable to authenticate with incoming mail server.'));
        }
    }

    // Approach 2: Pure PHP Socket POP3 / IMAP fallback
    $socketPrefix = ($secure === 'ssl' || $port === 993 || $port === 995) ? 'ssl://' : '';
    $fp = @stream_socket_client($socketPrefix . $host . ':' . $port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT);

    if (!$fp) {
        return array('success' => false, 'message' => "Socket connection to $host:$port failed ($errno): $errstr");
    }

    // Handshake
    $greeting = fgets($fp, 512);
    // Try basic IMAP login via socket
    fputs($fp, "A01 LOGIN \"$user\" \"$pass\"\r\n");
    $resp = '';
    while ($line = fgets($fp, 512)) {
        $resp .= $line;
        if (strpos($line, 'A01 OK') !== false || strpos($line, 'A01 NO') !== false || strpos($line, 'A01 BAD') !== false) {
            break;
        }
    }

    if (strpos($resp, 'A01 OK') !== false) {
        // Authenticated!
        fputs($fp, "A02 SELECT INBOX\r\n");
        while ($line = fgets($fp, 512)) {
            if (strpos($line, 'A02 OK') !== false || strpos($line, 'A02 NO') !== false) break;
        }
        fputs($fp, "A03 LOGOUT\r\n");
        fclose($fp);
        @$conn->query("UPDATE `mailbox_accounts` SET `last_sync` = NOW() WHERE `id` = " . (int)$account['id']);
        return array('success' => true, 'new_count' => 0, 'message' => "IMAP server connection verified and active!");
    } else {
        fclose($fp);
        return array('success' => false, 'message' => "Authentication error from incoming mail server: " . trim($resp));
    }
}

/**
 * Helper to recursively parse IMAP parts & attachments
 */
function mb_parse_imap_parts($mbox, $msgNum, $structure, $partNum, &$bodyPlain, &$bodyHtml, &$attachments) {
    if (empty($structure->parts)) {
        // Single part
        $data = $partNum ? imap_fetchbody($mbox, $msgNum, $partNum) : imap_body($mbox, $msgNum);
        $data = mb_decode_body($data, $structure->encoding);
        if ($structure->subtype === 'HTML') {
            $bodyHtml = $data;
        } else {
            $bodyPlain = $data;
        }
        return;
    }

    foreach ($structure->parts as $idx => $subPart) {
        $currPartNum = $partNum ? ($partNum . '.' . ($idx + 1)) : (string)($idx + 1);

        // Check if attachment
        $filename = '';
        if ($subPart->ifdparameters) {
            foreach ($subPart->dparameters as $param) {
                if (strtolower($param->attribute) === 'filename') {
                    $filename = mb_decode_mime_str($param->value);
                }
            }
        }
        if (!$filename && $subPart->ifparameters) {
            foreach ($subPart->parameters as $param) {
                if (strtolower($param->attribute) === 'name') {
                    $filename = mb_decode_mime_str($param->value);
                }
            }
        }

        if (!empty($filename)) {
            // Save attachment
            $rawContent = imap_fetchbody($mbox, $msgNum, $currPartNum);
            $decoded = mb_decode_body($rawContent, $subPart->encoding);
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
            $storedName = uniqid('att_', true) . '_' . $safeName;
            $savePath = dirname(__DIR__) . '/uploads/mail/' . $storedName;
            if (@file_put_contents($savePath, $decoded)) {
                $attachments[] = array(
                    'name' => $filename,
                    'path' => 'uploads/mail/' . $storedName,
                    'size' => strlen($decoded),
                    'type' => ($subPart->subtype ? strtolower($subPart->subtype) : 'application/octet-stream')
                );
            }
        } elseif ($subPart->type === 0) { // Text
            $rawContent = imap_fetchbody($mbox, $msgNum, $currPartNum);
            $decoded = mb_decode_body($rawContent, $subPart->encoding);
            if (strtoupper($subPart->subtype) === 'HTML') {
                $bodyHtml .= $decoded;
            } else {
                $bodyPlain .= $decoded;
            }
        } elseif (!empty($subPart->parts)) {
            mb_parse_imap_parts($mbox, $msgNum, $subPart, $currPartNum, $bodyPlain, $bodyHtml, $attachments);
        }
    }
}

function mb_decode_body($data, $encoding) {
    switch ($encoding) {
        case 3: return base64_decode($data);
        case 4: return quoted_printable_decode($data);
        default: return $data;
    }
}

function mb_decode_mime_str($str) {
    if (function_exists('imap_mime_header_decode')) {
        $elements = @imap_mime_header_decode($str);
        $res = '';
        if ($elements) {
            foreach ($elements as $el) {
                $res .= $el->text;
            }
            return $res;
        }
    }
    return $str;
}

/**
 * Format bytes into human readable size
 */
function mb_format_size($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}
