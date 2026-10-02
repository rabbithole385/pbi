<?php
/**
 * Aurelia Bank & Trust — Railway Database Seeder
 * Run ONCE after deploying to Railway to initialize your database tables and default admin account.
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(300);

require_once __DIR__ . '/scripts/connect.php';

$message = '';
$error = '';
$seeded = false;

// Check connection
if (!$conn || $conn->connect_error) {
    $error = "Could not connect to MySQL: " . ($conn ? $conn->connect_error : "Connection object not available");
} elseif (isset($_POST['seed_database'])) {
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        $error = "Schema file 'schema.sql' not found in root directory.";
    } else {
        $sqlContent = file_get_contents($schemaFile);
        
        // Remove comments and split statements
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Parse and execute SQL queries
        $queries = array();
        $temp = '';
        $lines = explode("\n", $sqlContent);
        
        foreach ($lines as $line) {
            $lineTrim = trim($line);
            // Skip empty lines and comment lines
            if ($lineTrim === '' || strpos($lineTrim, '--') === 0 || strpos($lineTrim, '/*') === 0) {
                continue;
            }
            $temp .= ' ' . $line;
            if (substr($lineTrim, -1) === ';') {
                $queries[] = $temp;
                $temp = '';
            }
        }
        
        $executed = 0;
        $failed = 0;
        foreach ($queries as $q) {
            $q = trim($q);
            if (!empty($q)) {
                if (@$conn->query($q)) {
                    $executed++;
                } else {
                    $failed++;
                }
            }
        }
        
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Set up / update default Admin Account (User ID 1)
        $adminEmail = 'admin@bank.com';
        $adminPassRaw = 'Admin@2026';
        $adminPassHash = md5($adminPassRaw);
        
        // Check if user 1 exists
        $chk = $conn->query("SELECT id FROM users WHERE id = 1");
        if ($chk && $chk->num_rows > 0) {
            $conn->query("UPDATE users SET email = '$adminEmail', password = '$adminPassHash', status = 'active' WHERE id = 1");
        } else {
            $conn->query("INSERT INTO users (id, username, password, email, phone, firstname, lastname, status, accountnumber, accounttype, accountbalance) 
                          VALUES (1, 'Administrator', '$adminPassHash', '$adminEmail', '+1 (800) 555-0199', 'System', 'Admin', 'active', '1000000001', 'Checking', '50000.00')");
        }

        // Initialize built-in Mailbox tables and default admin mailbox
        if (file_exists(__DIR__ . '/scripts/mailbox_functions.php')) {
            require_once __DIR__ . '/scripts/mailbox_functions.php';
            mb_init_database();
        }

        // Apply clean Aurelia Bank & Trust defaults (UPSERT — works whether row exists from schema.sql or not)
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

        $sr = $conn->real_escape_string($stockrateDefault);
        $sr2 = $conn->real_escape_string($stockrate2Default);
        $bm = $conn->real_escape_string($blockedMsgDefault);
        $im = $conn->real_escape_string($imfMsgDefault);
        $cm = $conn->real_escape_string($cotMsgDefault);
        $ce = $conn->real_escape_string($cotErrorDefault);
        $ie = $conn->real_escape_string($imfErrorDefault);
        $rm = $conn->real_escape_string($restMsgDefault);

        $conn->query("INSERT INTO setting (
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

        $seeded = true;
        $message = "Database successfully initialized! Executed $executed SQL statements.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Railway Database Seeder — Aurelia Bank & Trust</title>
<style>
  * { box-sizing: border-box; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: #090f1d;
    color: #e2e8f0;
    margin: 0;
    padding: 30px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
  }
  .container {
    max-width: 640px;
    width: 100%;
    background: #131d35;
    border: 1px solid #233358;
    border-radius: 16px;
    padding: 36px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
  }
  h1 { font-size: 24px; color: #fff; margin-top: 0; margin-bottom: 8px; font-weight: 700; }
  .badge { display: inline-block; background: #2563eb; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 20px; }
  p { color: #94a3b8; line-height: 1.6; font-size: 15px; margin-bottom: 18px; }
  .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; }
  .alert-danger { background: #45131b; border: 1px solid #f87171; color: #fca5a5; }
  .alert-success { background: #064e3b; border: 1px solid #34d399; color: #a7f3d0; }
  .info-box { background: #0b1327; border: 1px solid #1c2b4e; border-radius: 10px; padding: 16px 20px; margin: 20px 0; }
  .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #162444; font-size: 14px; }
  .info-row:last-child { border-bottom: none; }
  .info-label { color: #64748b; font-weight: 500; }
  .info-val { color: #38bdf8; font-family: monospace; font-weight: 600; }
  .btn {
    display: inline-block;
    width: 100%;
    text-align: center;
    background: #2563eb;
    color: #fff;
    border: none;
    padding: 14px 24px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
  }
  .btn:hover { background: #1d4ed8; }
  .btn-success { background: #059669; }
  .btn-success:hover { background: #047857; }
  .btn-group { display: flex; gap: 12px; margin-top: 20px; }
  .btn-outline { background: transparent; border: 1px solid #3b82f6; color: #60a5fa; }
  .btn-outline:hover { background: #1e3a8a; }
</style>
</head>
<body>
<div class="container">
  <div class="badge">Railway Setup</div>
  <h1>Aurelia Bank & Trust Database Seeder</h1>
  <p>Initializes all required database tables, default system settings, and creates the default super administrator account.</p>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger">
      <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($message)): ?>
    <div class="alert alert-success">
      <strong>Success!</strong> <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <?php if (!$seeded): ?>
    <div class="info-box">
      <div class="info-row">
        <span class="info-label">Connected Host:</span>
        <span class="info-val"><?php echo htmlspecialchars($servername); ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Database Name:</span>
        <span class="info-val"><?php echo htmlspecialchars($dbname); ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Port:</span>
        <span class="info-val"><?php echo htmlspecialchars($port); ?></span>
      </div>
    </div>

    <form method="post">
      <button type="submit" name="seed_database" class="btn" <?php if (!empty($error)) echo 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>
        Initialize & Seed Database Now
      </button>
    </form>
  <?php else: ?>
    <div class="info-box">
      <div class="info-row">
        <span class="info-label">Admin Portal:</span>
        <span class="info-val"><a href="admin/login.php" style="color:#38bdf8;">/admin/login.php</a></span>
      </div>
      <div class="info-row">
        <span class="info-label">Admin Email:</span>
        <span class="info-val">admin@bank.com</span>
      </div>
      <div class="info-row">
        <span class="info-label">Admin Password:</span>
        <span class="info-val">Admin@2026</span>
      </div>
    </div>

    <div class="btn-group">
      <a href="admin/login.php" class="btn btn-success">Go to Admin Portal &rarr;</a>
      <a href="index.php" class="btn btn-outline">Go to Home Page</a>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
