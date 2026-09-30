<?php
/**
 * PBI Groups — Resend Email Helper
 * Replaces PHPMailer/SMTP with Resend HTTP API
 * Domain: pbigroups.com (verified on Resend)
 * 
 * Usage:
 *   require_once(__DIR__ . '/resend.php');
 *   $result = pbi_send_email($to, $subject, $htmlBody, 'info'); // or 'support'
 */

if (!function_exists('get_cfg_env')) {
    function get_cfg_env($key, $default = '') {
        $v = getenv($key);
        if ($v !== false && $v !== '') return $v;
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
        return $default;
    }
}

/**
 * Send email via Resend API
 *
 * @param string $to         Recipient email address
 * @param string $subject    Email subject
 * @param string $html       HTML body
 * @param string $from_type  'info' or 'support' (defaults to 'info')
 * @param string $reply_to   Optional reply-to address
 * @return array ['success' => bool, 'id' => string|null, 'error' => string|null]
 */
function pbi_send_email($to, $subject, $html, $from_type = 'info', $reply_to = '') {
    $api_key = get_cfg_env('RESEND_API_KEY');
    
    if (empty($api_key)) {
        return ['success' => false, 'id' => null, 'error' => 'RESEND_API_KEY not configured'];
    }

    // Determine from address
    $from_map = [
        'info'    => 'PBI Groups International <info@pbigroups.com>',
        'support' => 'PBI Groups Support <support@pbigroups.com>',
    ];
    $from = isset($from_map[$from_type]) ? $from_map[$from_type] : $from_map['info'];

    if (empty($reply_to)) {
        $reply_to = ($from_type === 'support') ? 'support@pbigroups.com' : 'info@pbigroups.com';
    }

    $payload = json_encode([
        'from'     => $from,
        'to'       => [$to],
        'subject'  => $subject,
        'html'     => $html,
        'reply_to' => $reply_to,
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if (!empty($curlError)) {
        return ['success' => false, 'id' => null, 'error' => 'cURL error: ' . $curlError];
    }

    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && isset($data['id'])) {
        return ['success' => true, 'id' => $data['id'], 'error' => null];
    }

    $errorMsg = isset($data['message']) ? $data['message'] : ('HTTP ' . $httpCode);
    return ['success' => false, 'id' => null, 'error' => $errorMsg];
}

/**
 * Get the standard PBI email template wrapper
 * Provides consistent header/footer for all transactional emails
 */
function pbi_email_wrap($title, $bodyContent, $sitename = 'PBI Groups International', $siteaddress = '', $shortname = 'PBI') {
    $year = date('Y');
    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light dark">
<title>' . htmlspecialchars($title) . '</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
.wrapper { width: 100%; background-color: #f4f4f7; padding: 20px 0; }
.container { max-width: 580px; margin: 0 auto; }
.header { background: linear-gradient(135deg, #0a1628 0%, #1a2d50 100%); padding: 28px 32px; text-align: center; border-radius: 12px 12px 0 0; }
.header h1 { color: #fff; font-size: 20px; margin: 0; font-weight: 700; letter-spacing: 0.5px; }
.header .subtitle { color: #7eb8f0; font-size: 13px; margin-top: 6px; }
.body { background: #ffffff; padding: 32px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; }
.body h2 { color: #1a1a2e; font-size: 18px; margin: 0 0 16px; }
.body p { color: #4a4a5a; font-size: 15px; line-height: 1.6; margin: 0 0 14px; }
.body .highlight { background: #f0f4ff; border: 1px solid #d0daf0; border-radius: 8px; padding: 16px 20px; margin: 18px 0; }
.body .highlight b { color: #1a2d50; }
.otp-box { text-align: center; margin: 24px 0; }
.otp-code { display: inline-block; background: #0a1628; color: #fff; font-size: 28px; letter-spacing: 12px; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-family: monospace; }
.btn { display: inline-block; background: #2563eb; color: #fff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; }
.btn:hover { background: #1d4ed8; }
.footer { background: #0a1628; padding: 24px 32px; text-align: center; border-radius: 0 0 12px 12px; }
.footer p { color: #7a8ba8; font-size: 12px; line-height: 1.5; margin: 0 0 6px; }
.footer a { color: #7eb8f0; text-decoration: none; }
.divider { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
@media (max-width: 600px) {
  .container { width: 100% !important; }
  .body { padding: 20px !important; }
}
</style>
</head>
<body>
<div class="wrapper">
<div class="container">
  <div class="header">
    <h1>' . htmlspecialchars($sitename) . '</h1>
    <div class="subtitle">' . htmlspecialchars($title) . '</div>
  </div>
  <div class="body">
    ' . $bodyContent . '
  </div>
  <div class="footer">
    <p>&copy; ' . $year . ' ' . htmlspecialchars($sitename) . '. All rights reserved.</p>
    <p>' . htmlspecialchars($shortname) . ' &mdash; ' . htmlspecialchars($siteaddress) . '</p>
    <p><a href="https://pbigroups.com">pbigroups.com</a></p>
  </div>
</div>
</div>
</body>
</html>';
}
?>
