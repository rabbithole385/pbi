<?php
/**
 * Database Connection — Railway, Docker & Local compatible
 * Includes Seamless Zero-Config Auto-Seeder on first run.
 */

global $conn;

// Helper function to safely read environment variables across PHP configurations
if (!function_exists('get_cfg_env')) {
    function get_cfg_env($key, $default = '') {
        $v = getenv($key);
        if ($v !== false && $v !== '') return $v;
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
        return $default;
    }
}

// Reuse existing connection if already established in current request
if (isset($conn) && $conn instanceof mysqli && @$conn->ping()) {
    return;
}

// 1. Check for combined URL connection string (Railway MySQL standard)
$dbUrl = get_cfg_env('DATABASE_URL') ?: get_cfg_env('MYSQL_URL') ?: get_cfg_env('MYSQL_PUBLIC_URL');

$servername = '127.0.0.1';
$username   = 'root';
$password   = '';
$dbname     = 'railway';
$port       = 3306;
$urlDetected = false;

if (!empty($dbUrl)) {
    $parsed = parse_url($dbUrl);
    if ($parsed !== false) {
        $urlDetected = true;
        if (!empty($parsed['host'])) $servername = $parsed['host'];
        if (!empty($parsed['user'])) $username   = $parsed['user'];
        if (isset($parsed['pass']))  $password   = $parsed['pass'];
        if (!empty($parsed['path'])) $dbname     = ltrim($parsed['path'], '/');
        if (!empty($parsed['port'])) $port       = (int)$parsed['port'];
    }
} else {
    // 2. Check individual Railway / cloud environment variables
    $envHost = get_cfg_env('MYSQLHOST') ?: get_cfg_env('MYSQL_HOST') ?: get_cfg_env('DB_HOST');
    $envUser = get_cfg_env('MYSQLUSER') ?: get_cfg_env('MYSQL_USER') ?: get_cfg_env('DB_USER');
    $envPass = get_cfg_env('MYSQLPASSWORD') ?: get_cfg_env('MYSQL_PASSWORD') ?: get_cfg_env('DB_PASSWORD') ?: get_cfg_env('DB_PASS');
    $envDb   = get_cfg_env('MYSQLDATABASE') ?: get_cfg_env('MYSQL_DATABASE') ?: get_cfg_env('DB_NAME') ?: get_cfg_env('DB_DATABASE');
    $envPort = (int)(get_cfg_env('MYSQLPORT') ?: get_cfg_env('MYSQL_PORT') ?: get_cfg_env('DB_PORT') ?: 3306);

    if (!empty($envHost)) {
        $servername = $envHost;
        $username   = !empty($envUser) ? $envUser : 'root';
        $password   = $envPass;
        $dbname     = !empty($envDb) ? $envDb : 'railway';
        $port       = $envPort;
    }
}

// If host is 'localhost', change to 127.0.0.1 to avoid missing Unix socket in Docker
if ($servername === 'localhost') {
    $servername = '127.0.0.1';
}

// 3. Connect via mysqli
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($servername, $username, $password, $dbname, $port);

// 4. Handle connection error gracefully
if ($conn->connect_error) {
    $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($currentScript === 'railway-seed.php') {
        return;
    }
    
    http_response_code(503);
    ?>
    <!doctype html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connect MySQL Database — Railway</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #070d1e; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 24px; }
        .card { background: #0f1935; border: 1px solid #1d2c52; border-radius: 16px; max-width: 620px; width: 100%; padding: 36px; box-shadow: 0 25px 60px rgba(0,0,0,0.6); }
        .badge { display: inline-block; background: #3b82f6; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 16px; letter-spacing: 0.5px; }
        h1 { color: #fff; font-size: 24px; margin-top: 0; margin-bottom: 12px; font-weight: 700; }
        p { color: #94a3b8; line-height: 1.6; margin-bottom: 18px; font-size: 15px; }
        .steps { background: #070d1e; border: 1px solid #1e293b; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .step { display: flex; gap: 14px; margin-bottom: 16px; }
        .step:last-child { margin-bottom: 0; }
        .step-num { width: 28px; height: 28px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
        .step-text { font-size: 14px; color: #cbd5e1; line-height: 1.6; }
        .step-text strong { color: #fff; }
        code { background: #1e293b; color: #38bdf8; padding: 2px 7px; border-radius: 4px; font-size: 13px; font-family: monospace; }
        .ref-box { background: rgba(59, 130, 246, 0.08); border: 1px dashed #3b82f6; border-radius: 8px; padding: 12px 16px; margin: 14px 0 6px; font-family: monospace; font-size: 13px; color: #93c5fd; }
        .status-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #f87171; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 4px 10px; border-radius: 6px; margin-bottom: 18px; }
    </style>
    </head>
    <body>
    <div class="card">
        <div class="badge">Railway Setup</div>
        <h1>Link Your MySQL Database</h1>
        
        <div class="status-pill">
            <span>●</span> MySQL variables not yet linked to web service
        </div>

        <p>Your web application is running cleanly, but needs to be linked to your Railway MySQL database service.</p>

        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">
                    In your Railway Project, click on your <strong>Web Service</strong> (this app).
                </div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">
                    Go to the <strong>Variables</strong> tab.
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">
                    Click <strong>+ New Variable</strong> &rarr; click <strong>Add Reference</strong>.<br>
                    Select <code>DATABASE_URL</code> (or <code>MYSQL_URL</code>) from your MySQL database service:
                    <div class="ref-box">DATABASE_URL = ${{MySQL.DATABASE_URL}}</div>
                </div>
            </div>
        </div>

        <p style="font-size: 13px; color: #64748b; margin-bottom: 0;">
            Railway will automatically redeploy in ~5 seconds. Once redeployed, the application will automatically connect, auto-seed all database tables, and show your active bank!
        </p>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$conn->set_charset("utf8mb4");

// 5. SEAMLESS AUTO-SEEDER: When database is connected, auto-seed if tables don't exist yet!
$checkTable = @$conn->query("SHOW TABLES LIKE 'setting'");
$justSeeded = false;
if ($checkTable && $checkTable->num_rows === 0) {
    $schemaFile = dirname(__DIR__) . '/schema.sql';
    if (file_exists($schemaFile)) {
        $sqlContent = file_get_contents($schemaFile);
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        $queries = array();
        $temp = '';
        $lines = explode("\n", $sqlContent);
        foreach ($lines as $line) {
            $lineTrim = trim($line);
            if ($lineTrim === '' || strpos($lineTrim, '--') === 0 || strpos($lineTrim, '/*') === 0) continue;
            $temp .= ' ' . $line;
            if (substr($lineTrim, -1) === ';') {
                $queries[] = $temp;
                $temp = '';
            }
        }
        foreach ($queries as $q) {
            $q = trim($q);
            if (!empty($q)) @$conn->query($q);
        }
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $justSeeded = true;
    }
}

// Ensure default Super Admin user (ID 1)
$adminPassHash = md5('Admin@2026');
$chkAdmin = @$conn->query("SELECT id FROM users WHERE id = 1");
if ($chkAdmin && $chkAdmin->num_rows > 0) {
    @$conn->query("UPDATE users SET email = 'admin@bank.com', password = '$adminPassHash', status = 'active' WHERE id = 1");
} else {
    @$conn->query("INSERT INTO users (id, username, password, email, phone, firstname, lastname, status, accountnumber, accounttype, accountbalance)
                   VALUES (1, 'Administrator', '$adminPassHash', 'admin@bank.com', '+1 (800) 555-0199', 'System', 'Admin', 'active', '1000000001', 'Checking', '50000.00')");
}

// Apply clean Aurelia Bank & Trust site setting defaults (only on fresh seed)
if ($justSeeded) {
    $stockrateDefault = '<script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js" async="">
{
  "symbols": [
    { "title": "S&P 500", "proName": "OANDA:SPX500USD" },
    { "title": "Nasdaq 100", "proName": "OANDA:NAS100USD" },
    { "title": "EUR/USD", "proName": "FX_IDC:EURUSD" },
    { "title": "BTC/USD", "proName": "BITSTAMP:BTCUSD" },
    { "title": "ETH/USD", "proName": "BITSTAMP:ETHUSD" }
  ],
  "colorTheme": "dark",
  "isTransparent": false,
  "displayMode": "compact",
  "locale": "en"
}
</script>';
    $stockrate2Default = '<iframe src="//www.exchangerates.org.uk/widget/ER-LRTICKER.php?w=1400&s=1&mc=GBP&mbg=F0F0F0&bs=yes&bc=000044&f=verdana&fs=10px&fc=000044&lc=000044&lhc=FE9A00&vc=FE9A00&vcu=008000&vcd=FF0000&" height="30" width="100%" frameborder="0" scrolling="no" marginwidth="0" marginheight="0"></iframe>';
    $blockedMsgDefault = "Dear Customer, we have discovered suspicious activities on your account. An unauthorized IP address attempted to carry out a transaction on your account. Consequently, your account has been flagged by our risk assessment department. Kindly visit our nearest branch with your identification card and utility bill to confirm your identity before it can be reactivated. For more information, kindly contact our online customer care representatives.";
    $imfMsgDefault = "You need to provide your IMF code before you can continue with this transaction. Please visit any of our nearest branches or contact our online customer care representative — they will help you with the appropriate IMF code for this transaction.";
    $cotMsgDefault = "You need to provide your COT code before you can continue with this transaction. You can visit any of our nearest branches or contact our online customer care representative — they will help you with the appropriate COT code for this transaction.";
    $cotErrorDefault = "Your account has been temporarily suspended for providing the wrong COT code. We are always committed to safeguarding your funds and therefore this is the right decision we can take for now. For more information, kindly contact our live customer care representatives.";
    $imfErrorDefault = "Your account has been temporarily suspended for providing the wrong IMF code. We are always committed to safeguarding your funds and therefore this is the right decision we can take for now. For more information, kindly contact our live customer care representatives.";
    $restMsgDefault = "Your account was temporarily restricted from carrying out transactions via our online banking channel. Kindly visit any of our nearest branches to resolve this issue. For more information, kindly contact our online customer care representative.";

    $sr  = $conn->real_escape_string($stockrateDefault);
    $sr2 = $conn->real_escape_string($stockrate2Default);
    $bm  = $conn->real_escape_string($blockedMsgDefault);
    $im  = $conn->real_escape_string($imfMsgDefault);
    $cm  = $conn->real_escape_string($cotMsgDefault);
    $ce  = $conn->real_escape_string($cotErrorDefault);
    $ie  = $conn->real_escape_string($imfErrorDefault);
    $rm  = $conn->real_escape_string($restMsgDefault);

    @$conn->query("INSERT INTO setting (
        id, name, logo, address, email, phone, favicon, tagline, register,
        darklogo, description, seo, footerlogo, securityalert,
        stockrate, stockrate2, stock, money, country, visa_picture, tawk,
        shortname, blocked_msg, crypto, blocked_title, imfmsg, cotmsg,
        icmsg, tinmsg, tacmsg, charges, wiremsg, localmsg,
        cot_imf_counter, cot_error, imf_error, enable_cot_imf, rest_msg,
        userstac, usersic, userstin, enable_tin_ic_tac, enable_tac, enable_ic, enable_tin,
        bots, site_url, kyc, loan, visual_card
    ) VALUES (
        1, 'Aurelia Bank & Trust', 'logo.png', '2800 Aurelia Plaza, Suite 1400, Boston, MA', 'support@aureliabank.com', '+1 (800) 555-0188', 'images/favicon.png', 'Modern & Trusted Digital Banking', '1',
        'logo.png', 'Aurelia Bank & Trust - Next-generation secure online banking platform. Federally-insured financial institution.', 'aurelia bank, online banking, secure wire transfer, digital trust', 'footerlogo.png', '',
        '$sr', '$sr2', 1, '\$', 'United States', 'images/visa.png', '',
        'Aurelia', '$bm', 1, 'Account Suspended', '$im', '$cm',
        NULL, NULL, NULL, '0.3', '', '',
        5, '$ce', '$ie', 'Yes', '$rm',
        '3690NH', '3690NH', '3690NH', 'No', 'Yes', 'Yes', 'No',
        1, '', 1, 1, 1
    ) ON DUPLICATE KEY UPDATE
        name=VALUES(name), logo=VALUES(logo), address=VALUES(address), email=VALUES(email),
        phone=VALUES(phone), favicon=VALUES(favicon), tagline=VALUES(tagline), register=VALUES(register),
        darklogo=VALUES(darklogo), description=VALUES(description), seo=VALUES(seo), footerlogo=VALUES(footerlogo),
        securityalert=VALUES(securityalert), stockrate=VALUES(stockrate), stockrate2=VALUES(stockrate2),
        stock=VALUES(stock), money=VALUES(money), country=VALUES(country), visa_picture=VALUES(visa_picture),
        tawk=VALUES(tawk), shortname=VALUES(shortname), blocked_msg=VALUES(blocked_msg),
        crypto=VALUES(crypto), blocked_title=VALUES(blocked_title), imfmsg=VALUES(imfmsg), cotmsg=VALUES(cotmsg),
        charges=VALUES(charges), cot_imf_counter=VALUES(cot_imf_counter), cot_error=VALUES(cot_error),
        imf_error=VALUES(imf_error), enable_cot_imf=VALUES(enable_cot_imf), rest_msg=VALUES(rest_msg),
        userstac=VALUES(userstac), usersic=VALUES(usersic), userstin=VALUES(userstin),
        enable_tin_ic_tac=VALUES(enable_tin_ic_tac), enable_tac=VALUES(enable_tac),
        enable_ic=VALUES(enable_ic), enable_tin=VALUES(enable_tin), bots=VALUES(bots),
        site_url=VALUES(site_url), kyc=VALUES(kyc), loan=VALUES(loan), visual_card=VALUES(visual_card)");
}