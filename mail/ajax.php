<?php
/**
 * Asynchronous AJAX Controller for Webmail operations
 */
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/scripts/mailbox_functions.php';

$account = mb_get_current_session();
if (!$account) {
    echo json_encode(array('success' => false, 'message' => 'Session expired. Please log in again.'));
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_counts':
        $counts = mb_get_counts($account['id']);
        echo json_encode(array('success' => true, 'counts' => $counts));
        break;

    case 'get_messages':
        $folder = $_GET['folder'] ?? 'inbox';
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 25;
        $data = mb_get_messages($account['id'], $folder, $search, $page, $perPage);
        echo json_encode(array('success' => true, 'data' => $data));
        break;

    case 'get_message':
        $msgId = (int)($_GET['id'] ?? 0);
        $msg = mb_get_message($msgId, $account['id']);
        if ($msg) {
            // Automatically mark as read if inbox
            if ($msg['folder'] === 'inbox' && $msg['is_read'] == 0) {
                mb_mark_message($msgId, 'read', $account['id']);
                $msg['is_read'] = 1;
            }
            echo json_encode(array('success' => true, 'message' => $msg));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Email not found.'));
        }
        break;

    case 'mark_message':
        $msgId = (int)($_POST['id'] ?? 0);
        $markAction = $_POST['mark_action'] ?? '';
        $res = mb_mark_message($msgId, $markAction, $account['id']);
        echo json_encode(array('success' => (bool)$res, 'action' => $markAction));
        break;

    case 'batch_mark':
        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : array();
        $markAction = $_POST['mark_action'] ?? '';
        $count = 0;
        foreach ($ids as $id) {
            if (mb_mark_message((int)$id, $markAction, $account['id'])) {
                $count++;
            }
        }
        echo json_encode(array('success' => true, 'affected' => $count));
        break;

    case 'send_message':
        $to = trim($_POST['to'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = $_POST['body'] ?? '';
        $cc = trim($_POST['cc'] ?? '');
        $bcc = trim($_POST['bcc'] ?? '');
        $replyTo = trim($_POST['reply_to'] ?? '');

        // Handle uploaded attachments
        $attachments = array();
        if (!empty($_FILES['attachments']['name'][0])) {
            $uploadBase = dirname(__DIR__) . '/uploads/mail/';
            if (!is_dir($uploadBase)) {
                @mkdir($uploadBase, 0755, true);
            }

            foreach ($_FILES['attachments']['name'] as $idx => $origName) {
                if ($_FILES['attachments']['error'][$idx] === UPLOAD_ERR_OK) {
                    $tmpPath = $_FILES['attachments']['tmp_name'][$idx];
                    $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $origName);
                    // Prevent executable files
                    $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
                    if (in_array($ext, array('php', 'phtml', 'exe', 'sh', 'bat', 'cmd', 'js', 'vbs'), true)) {
                        $safeName .= '.txt';
                    }

                    $storedFile = uniqid('att_', true) . '_' . $safeName;
                    $targetPath = $uploadBase . $storedFile;

                    if (move_uploaded_file($tmpPath, $targetPath)) {
                        $attachments[] = array(
                            'name' => $origName,
                            'path' => 'uploads/mail/' . $storedFile,
                            'size' => filesize($targetPath),
                            'type' => $_FILES['attachments']['type'][$idx] ?? 'application/octet-stream'
                        );
                    }
                }
            }
        }

        $options = array(
            'cc' => $cc,
            'bcc' => $bcc,
            'reply_to' => $replyTo,
            'attachments' => $attachments
        );

        $result = mb_send_email($account['id'], $to, $subject, $body, $options);
        echo json_encode($result);
        break;

    case 'sync_mail':
        $syncResult = mb_sync_incoming($account['id']);
        echo json_encode($syncResult);
        break;

    case 'save_settings':
        $inHost = trim($_POST['incoming_host'] ?? '');
        $inPort = (int)($_POST['incoming_port'] ?? 993);
        $inSecure = trim($_POST['incoming_secure'] ?? 'ssl');
        $inUser = trim($_POST['incoming_username'] ?? '');
        $inPass = $_POST['incoming_password'] ?? '';

        $outHost = trim($_POST['smtp_host'] ?? '');
        $outPort = (int)($_POST['smtp_port'] ?? 587);
        $outSecure = trim($_POST['smtp_secure'] ?? 'tls');
        $outUser = trim($_POST['smtp_username'] ?? '');
        $outPass = $_POST['smtp_password'] ?? '';

        $dispName = trim($_POST['display_name'] ?? '');
        $signature = $_POST['signature'] ?? '';
        $newPass = trim($_POST['new_password'] ?? '');

        $sql = "UPDATE `mailbox_accounts` SET 
            `incoming_host` = ?, 
            `incoming_port` = ?, 
            `incoming_secure` = ?, 
            `incoming_username` = ?, 
            `smtp_host` = ?, 
            `smtp_port` = ?, 
            `smtp_secure` = ?, 
            `smtp_username` = ?, 
            `display_name` = ?, 
            `signature` = ?";

        $params = array($inHost, $inPort, $inSecure, $inUser, $outHost, $outPort, $outSecure, $outUser, $dispName, $signature);
        $types = 'sisssissss';

        if (!empty($inPass)) {
            $sql .= ", `incoming_password` = ?";
            $params[] = $inPass;
            $types .= 's';
        }
        if (!empty($outPass)) {
            $sql .= ", `smtp_password` = ?";
            $params[] = $outPass;
            $types .= 's';
        }
        if (!empty($newPass)) {
            $sql .= ", `password` = ?";
            $params[] = md5($newPass);
            $types .= 's';
        }

        $sql .= " WHERE `id` = ?";
        $params[] = $account['id'];
        $types .= 'i';

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
            echo json_encode(array('success' => true, 'message' => 'Mailbox settings saved successfully!'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to update settings.'));
        }
        break;

    case 'create_account':
        // Only admin accounts can create new mailboxes
        if ($account['role'] !== 'admin' && empty($_SESSION['loggedAdmin'])) {
            echo json_encode(array('success' => false, 'message' => 'Permission denied.'));
            exit;
        }

        $newEmail = trim($_POST['email'] ?? '');
        $newName = trim($_POST['name'] ?? '');
        $newPassword = trim($_POST['password'] ?? '');
        $newRole = trim($_POST['role'] ?? 'staff');

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array('success' => false, 'message' => 'Valid email address is required.'));
            exit;
        }
        if (empty($newPassword)) {
            echo json_encode(array('success' => false, 'message' => 'Password is required.'));
            exit;
        }

        $passHash = md5($newPassword);
        $inHost = $account['incoming_host'];
        $inPort = $account['incoming_port'];
        $inSec = $account['incoming_secure'];
        $outHost = $account['smtp_host'];
        $outPort = $account['smtp_port'];
        $outSec = $account['smtp_secure'];

        $stmt = $conn->prepare("INSERT INTO `mailbox_accounts` 
            (`name`, `email`, `password`, `display_name`, `role`, `incoming_type`, `incoming_host`, `incoming_port`, `incoming_secure`, `incoming_username`, `incoming_password`, `smtp_host`, `smtp_port`, `smtp_secure`, `smtp_username`, `smtp_password`, `is_default`, `status`) 
            VALUES (?, ?, ?, ?, ?, 'imap', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'active')");
        if ($stmt) {
            $stmt->bind_param('ssssssisssissss',
                $newName, $newEmail, $passHash, $newName, $newRole,
                $inHost, $inPort, $inSec, $newEmail, $newPassword,
                $outHost, $outPort, $outSec, $newEmail, $newPassword
            );
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                $stmt->close();
                mb_seed_welcome_messages($newId, $newEmail, $newName);
                echo json_encode(array('success' => true, 'message' => 'New Mailbox account created successfully! Credentials ready to share.', 'account_id' => $newId));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Error: This email is already registered in the mailbox system.'));
            }
        }
        break;

    case 'get_accounts':
        $accounts = mb_get_all_accounts();
        echo json_encode(array('success' => true, 'accounts' => $accounts));
        break;

    case 'switch_account':
        $targetId = (int)($_POST['account_id'] ?? 0);
        $targetAcc = mb_get_account($targetId);
        if ($targetAcc) {
            $_SESSION['webmail_account_id'] = $targetAcc['id'];
            $_SESSION['webmail_email'] = $targetAcc['email'];
            $_SESSION['webmail_name'] = $targetAcc['display_name'];
            $_SESSION['webmail_role'] = $targetAcc['role'];
            echo json_encode(array('success' => true, 'message' => 'Switched to ' . $targetAcc['email']));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Account not found.'));
        }
        break;

    default:
        echo json_encode(array('success' => false, 'message' => 'Invalid action.'));
        break;
}
