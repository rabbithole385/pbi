<?php
/**
 * Secure Attachment Downloader for Webmail
 */
require_once dirname(__DIR__) . '/scripts/mailbox_functions.php';

$account = mb_get_current_session();
if (!$account) {
    http_response_code(403);
    die("Access denied. Please log in.");
}

$msgId = isset($_GET['msg_id']) ? (int)$_GET['msg_id'] : 0;
$index = isset($_GET['att_idx']) ? (int)$_GET['att_idx'] : 0;

$msg = mb_get_message($msgId, $account['id']);
if (!$msg || empty($msg['attachments_list']) || !isset($msg['attachments_list'][$index])) {
    http_response_code(404);
    die("Attachment not found.");
}

$att = $msg['attachments_list'][$index];
$base = dirname(__DIR__) . '/';
$filepath = realpath($base . $att['path']);
$allowedBase = realpath(dirname(__DIR__) . '/uploads/mail');

// Path traversal protection
if (!$filepath || strpos($filepath, $allowedBase) !== 0 || !file_exists($filepath)) {
    http_response_code(404);
    die("File does not exist or invalid path.");
}

$filename = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '', $att['name'] ?? 'attachment');
$mime = !empty($att['type']) ? $att['type'] : 'application/octet-stream';

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
