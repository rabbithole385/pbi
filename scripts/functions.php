<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
// Preload PHPMailer classes safely once so all scripts and templates have them
$phpMailerDir = dirname(__DIR__) . '/includes';
if (!class_exists('PHPMailer\PHPMailer\PHPMailer') && file_exists($phpMailerDir . '/PHPMailer.php')) {
    require_once($phpMailerDir . '/Exception.php');
    require_once($phpMailerDir . '/PHPMailer.php');
    require_once($phpMailerDir . '/SMTP.php');
}
require_once("connect.php");
 //WEBSITE SETTINGS
 $query = @$conn->query("SELECT * FROM setting WHERE id = 1");
 if (!$query || mysqli_num_rows($query) === 0) {
    $sitename = "Online Banking";
    $logo = "images/logo.png";
    $tagline = "Modern & Secure Banking";
    $favicon = "images/favicon.ico";
    $register = 1;
    $sitephone = "+1 (800) 555-0199";
    $siteemail = "support@pbigroups.com";
    $siteaddress = "100 Financial Way, New York, NY";
    $description = "Secure online banking platform";
    $seo = "online banking, secure transfer";
    $darklogo = "images/logo.png";
    $rate1 = "1.00";
    $rate2 = "1.00";
    $money = "$";
    $sitecountry = "United States";
    $visa_picture = "";
    $tawk  = "";
    $shortname = "Bank";
    $blocked_msg = "";
    $blocked_title = "";
    $imfMsg = "";
    $cotMsg = "";
    $icMsg = "";
    $tacMsg = "";
    $tinMsg = "";
    $userstac = "1999";
    $usersic = "1999";
    $userstin = "1999";
    $transfercharge = "0";
    $localmsg = "";
    $wiremsg = "";
    $footerlogo = "images/footerlogo.png";
    $imf_cot_counter = 0;
    $cot_error = "";
    $imf_error = "";
    $enable_cot_imf = 0;
    $sitedescription = "Secure online banking platform";
    $rest_msg = "";
    $enable_tin_ic_tac = 0;
    $enable_tin = 0;
    $enable_ic = 0;
    $crypto = 1;
    $bots = 0;
    $site_url = "";
    $kyc = 1;
    $visual_card = 1;
    $routine = "Routing number";
    $stockrate = "";
    $loan = 0;
    $loan_mini = 100;
    $loan_maxi = 100000;
    $interest_rate = 5;
    $mana_fee = 0;
    $insurance = 0;
    $penal_charge = 0;
    $loan_period = 12;
    $sms = 0;
    $api = "";
    $sender_id = "";

    $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($currentScript !== 'railway-seed.php' && $currentScript !== 'install.php') {
        if (!headers_sent()) {
            header("Location: railway-seed.php");
            exit;
        }
    }
 } else {
     while($rows = mysqli_fetch_array($query)){
        $sitename = $rows["name"];
        $logo = !empty($rows["logo"]) ? $rows["logo"] : "images/logo.png";
        $tagline = $rows["tagline"];
        $favicon = !empty($rows["favicon"]) ? $rows["favicon"] : "images/favicon.ico";
        $register = $rows["register"];
        $sitephone = $rows["phone"];
        $siteemail = $rows["email"];
        $siteaddress = $rows["address"];
        $description = $rows["description"];
        $seo = $rows["seo"];
        $darklogo = !empty($rows["darklogo"]) ? $rows["darklogo"] : (!empty($rows["logo"]) ? $rows["logo"] : "images/logo.png");
        $rate1 = $rows["stockrate"];
        $rate2 = $rows['stockrate2'];
        $money = $rows["money"];
        $sitecountry = $rows["country"];
        $visa_picture = $rows["visa_picture"];
        $tawk  = $rows['tawk'];
        $shortname = $rows['shortname'];
        $blocked_msg = $rows['blocked_msg'];
        //$blocked_icon = $rows['blocked_icon'];
        $blocked_title = $rows['blocked_title'];
        $imfMsg = $rows['imfmsg'];
        $cotMsg = $rows['cotmsg'];
        $icMsg = $rows['icmsg'];
        $tacMsg = $rows['tacmsg'];
        $tinMsg = $rows['tinmsg'];
        $userstac = $rows['userstac'];
        $usersic = $rows['usersic'];
        $userstin = $rows['userstin'];
        $transfercharge = $rows['charges'];
        $localmsg = $rows['localmsg'];
        $wiremsg = $rows['wiremsg'];
        $footerlogo = !empty($rows['footerlogo']) ? $rows['footerlogo'] : 'images/footerlogo.png';
        $imf_cot_counter = $rows['cot_imf_counter'];
        $cot_error = $rows['cot_error'];
        $imf_error = $rows['imf_error'];
        $enable_cot_imf = $rows['enable_cot_imf'];
        $sitedescription = $rows['description'];
        $rest_msg = $rows['rest_msg'];
        $enable_tin_ic_tac = $rows['enable_tin_ic_tac'];
        $enable_tin = $rows['enable_tin'];
        $enable_ic = $rows['enable_ic'];
        $crypto = $rows['crypto'];
        $bots = $rows['bots'];
        $site_url = $rows['site_url'];
        $kyc = $rows['kyc'];
        $visual_card = $rows['visual_card'];
        if ($sitecountry == "United Kingdom") {
            $routine = "Sort code";
        }
        else{
            $routine = "Routing number";
        }
        if ($rows['stock'] == 1) {
            $stockrate = $rate1;
        }else {$stockrate = $rate2;}
    }

    //LOAN MANAGEMENT SETTING
    $queryLoan = @$conn->query("SELECT * FROM loan WHERE id = 1");
    if ($queryLoan && mysqli_num_rows($queryLoan) > 0) {
        while($loanData = mysqli_fetch_array($queryLoan)){
            $loan = $loanData['status'];
            $loan_mini = $loanData['loan_mini'];
            $loan_maxi = $loanData['loan_maxi'];
            $interest_rate = $loanData['interest_rate'];
            $mana_fee = $loanData['mana_fee'];
            $insurance = $loanData['insurance'];
            $penal_charge = $loanData['penal_charge'];
            $loan_period = $loanData['loan_period'];
        }
    } else {
        $loan = 0; $loan_mini = 100; $loan_maxi = 100000; $interest_rate = 5; $mana_fee = 0; $insurance = 0; $penal_charge = 0; $loan_period = 12;
    }

    //SMS CONFIGURATION
    $querySms = @$conn->query("SELECT * FROM sms WHERE id = 1");
    if ($querySms && mysqli_num_rows($querySms) > 0) {
        while($smsData = mysqli_fetch_array($querySms)){
            $sms = $smsData['status'];
            $api = $smsData['api'];
            $sender_id = $smsData['sender_id'];
        }
    } else {
        $sms = 0; $api = ""; $sender_id = "";
    }
 }
   


///CHECK IF SITE IS BEING INDEXED BY A BOT/CRAWLER VIA HTTP USER AGENT

/*if($bots == 1){
  $CrawlerDetect = new CrawlerDetect;
  // Check the user agent of the current 'visitor'
  if($CrawlerDetect->isCrawler()) {
   echo "<br>Error</br>";
  }else{}

}
*/

//ADMIN DETAILS

 $adminquery = $conn->query("SELECT * FROM users WHERE id = 1");

 while($roo = mysqli_fetch_array($adminquery)){
    $adfirstname = $roo["firstname"];
    $admiddlename = $roo["middlename"];
    $adlastname = $roo["lastname"];
    $ademail = $roo["email"];
    $adaccounttype = $roo["accounttype"];
    $adpassport = $roo["passport"];
    $addayOFBirth = $roo["dayOFBirth"];
    $adaccountbalance = $roo["accountbalance"];
    $adaddress = $roo["address"];
    $adstate = $roo["state"];
    $adcountry = $roo["country"];
    $adphone = $roo["phone"];
    $adsecretCode = $roo["secretCode"];
    $adzipcode = $roo["zipcode"];
    $adincome = $roo["income"];
    $adoccupation = $roo["occupation"];
    $adnickname = $roo["nickname"];
    $adsecurityQuestion = $roo["securityquestion"];
    $adanswer = $roo["answer"];
    $aduserid = $roo["id"];

}

//SMTP SETTINGS
if ($conn) {
    static $smtp_cols_ensured = false;
    if (!$smtp_cols_ensured) {
        $chkCol1 = @$conn->query("SHOW COLUMNS FROM smtp_setting LIKE 'support_password'");
        if ($chkCol1 && mysqli_num_rows($chkCol1) == 0) {
            @$conn->query("ALTER TABLE smtp_setting ADD COLUMN support_password TEXT DEFAULT NULL");
        }
        $chkCol2 = @$conn->query("SHOW COLUMNS FROM smtp_setting LIKE 'info_password'");
        if ($chkCol2 && mysqli_num_rows($chkCol2) == 0) {
            @$conn->query("ALTER TABLE smtp_setting ADD COLUMN info_password TEXT DEFAULT NULL");
        }
        @$conn->query("ALTER TABLE smtp_setting MODIFY COLUMN password TEXT");
        $smtp_cols_ensured = true;
    }
}

$info_password = "";
$support_password = "";

$smtpquery = $conn->query("SELECT * FROM smtp_setting WHERE id = 1");
    while($rowsmtp = mysqli_fetch_assoc($smtpquery)){
    $smtp_host = $rowsmtp["host"];
    $smtp_username = $rowsmtp["username"];
    $smtp_password = $rowsmtp["password"];
    $info_password = !empty($rowsmtp["info_password"]) ? $rowsmtp["info_password"] : $smtp_password;
    $support_password = !empty($rowsmtp["support_password"]) ? $rowsmtp["support_password"] : $smtp_password;
    $smtp_port = $rowsmtp["port"];
    $display_name = $rowsmtp["display_name"];
    $smtp_auth = $rowsmtp["smtp_auth"];
    $emaillogo = $rowsmtp['emaillogo'];
}
//end of smtpsetting

// Environment variables override for zero-friction Railway / Cloud setup
if (!empty(get_cfg_env('RESEND_API_KEY'))) {
    if (empty($info_password)) $info_password = get_cfg_env('RESEND_API_KEY');
    if (empty($smtp_password)) $smtp_password = get_cfg_env('RESEND_API_KEY');
}
if (!empty(get_cfg_env('RESEND_KEY'))) {
    if (empty($info_password)) $info_password = get_cfg_env('RESEND_KEY');
    if (empty($smtp_password)) $smtp_password = get_cfg_env('RESEND_KEY');
}
if (!empty(get_cfg_env('SMTP_HOST'))) $smtp_host = get_cfg_env('SMTP_HOST');
if (!empty(get_cfg_env('SMTP_USER'))) $smtp_username = get_cfg_env('SMTP_USER');
if (!empty(get_cfg_env('SMTP_USERNAME'))) $smtp_username = get_cfg_env('SMTP_USERNAME');
if (!empty(get_cfg_env('SMTP_PASS'))) $smtp_password = get_cfg_env('SMTP_PASS');
if (!empty(get_cfg_env('SMTP_PASSWORD'))) $smtp_password = get_cfg_env('SMTP_PASSWORD');
if (!empty(get_cfg_env('RESEND_INFO_API_KEY'))) $info_password = get_cfg_env('RESEND_INFO_API_KEY');
if (!empty(get_cfg_env('SMTP_INFO_PASSWORD'))) $info_password = get_cfg_env('SMTP_INFO_PASSWORD');
if (!empty(get_cfg_env('RESEND_SUPPORT_API_KEY'))) $support_password = get_cfg_env('RESEND_SUPPORT_API_KEY');
if (!empty(get_cfg_env('SMTP_SUPPORT_PASSWORD'))) $support_password = get_cfg_env('SMTP_SUPPORT_PASSWORD');
if (!empty(get_cfg_env('SMTP_PORT'))) $smtp_port = get_cfg_env('SMTP_PORT');
if (!empty(get_cfg_env('SMTP_AUTH'))) $smtp_auth = get_cfg_env('SMTP_AUTH');
if (!empty(get_cfg_env('SMTP_FROM_NAME'))) $display_name = get_cfg_env('SMTP_FROM_NAME');
if (!empty(get_cfg_env('SITE_EMAIL'))) $siteemail = get_cfg_env('SITE_EMAIL');

if (empty($info_password)) $info_password = $smtp_password;
if (empty($support_password)) $support_password = $smtp_password;
if (empty($smtp_password)) $smtp_password = !empty($info_password) ? $info_password : $support_password;

// PBI Group Display Name & Sender resolution:
if (empty($display_name) || stripos($display_name, 'Pinnacle') !== false) {
    $display_name = "PBI Group";
}
// Resolved sender emails
$from_email = (filter_var($smtp_username, FILTER_VALIDATE_EMAIL)) ? $smtp_username : 'info@pbigroups.com';
$support_email = 'support@pbigroups.com';
$info_email = 'info@pbigroups.com';

if (!function_exists('get_smtp_password_for')) {
    function get_smtp_password_for($fromEmail = null) {
        global $smtp_password, $info_password, $support_password;
        if (!empty($fromEmail) && stripos($fromEmail, 'support') !== false) {
            return !empty($support_password) ? $support_password : $smtp_password;
        }
        return !empty($info_password) ? $info_password : $smtp_password;
    }
}

if (!function_exists('configure_pbi_mailer')) {
    function configure_pbi_mailer($mail, $fromEmail = null, $fromName = null) {
        global $smtp_host, $smtp_username, $smtp_password, $smtp_port, $smtp_auth, $display_name, $info_password, $support_password;
        $resolvedName = !empty($fromName) ? $fromName : (!empty($display_name) ? $display_name : 'PBI Group');
        $resolvedEmail = !empty($fromEmail) ? $fromEmail : ((filter_var($smtp_username, FILTER_VALIDATE_EMAIL)) ? $smtp_username : 'info@pbigroups.com');
        $resolvedPassword = get_smtp_password_for($resolvedEmail);

        // Normalize the encryption string: 'ssl' -> SMTPS constant, 'tls' -> STARTTLS constant
        $enc = strtolower(trim((string)$smtp_auth));
        if ($enc === 'ssl' || $enc === 'smtps') {
            $encConst = 'ssl';
        } elseif ($enc === 'tls' || $enc === 'starttls') {
            $encConst = 'tls';
        } else {
            $encConst = 'ssl'; // default for Resend
        }

        $mail->isSMTP();
        $mail->Host = !empty($smtp_host) ? $smtp_host : 'smtp.resend.com';
        $mail->SMTPAuth = true;
        $mail->CharSet = "UTF-8";
        // Resend SMTP always requires literal 'resend' as username
        $smtpUser = !empty($smtp_username) ? $smtp_username : 'resend';
        // If the stored username is an email (not 'resend'), still keep it for other SMTP providers
        $mail->Username = $smtpUser;
        $mail->Password = $resolvedPassword;
        $mail->SMTPSecure = $encConst;
        $mail->Port = !empty($smtp_port) ? (int)$smtp_port : 465;
        $mail->Timeout = 15;
        $mail->SMTPDebug = 0;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom($resolvedEmail, $resolvedName);
        $mail->addReplyTo($resolvedEmail, $resolvedName);
    }
}

// Global bulletproof email sender: tries Resend HTTPS REST API (port 443) first if re_ key, falls back to SMTP
if (!function_exists('send_pbi_mail')) {
    function send_pbi_mail($toEmail, $subject, $htmlBody, $fromEmail = null, $fromName = null, &$debugOut = null) {
        global $smtp_host, $smtp_username, $smtp_password, $smtp_port, $smtp_auth, $display_name, $info_password, $support_password;
        
        $resolvedName = !empty($fromName) ? $fromName : (!empty($display_name) ? $display_name : 'PBI Group');
        $resolvedEmail = !empty($fromEmail) ? $fromEmail : ((filter_var($smtp_username, FILTER_VALIDATE_EMAIL)) ? $smtp_username : 'info@pbigroups.com');
        $resolvedPassword = get_smtp_password_for($resolvedEmail);
        if (empty($resolvedPassword)) {
            $resolvedPassword = !empty($smtp_password) ? $smtp_password : (!empty($info_password) ? $info_password : $support_password);
        }
        
        // 1. Direct Resend HTTPS REST API (Port 443) - fastest and bypasses SMTP port blocking
        if (strpos(trim((string)$resolvedPassword), 're_') === 0 && function_exists('curl_init')) {
            $payload = array(
                'from' => "$resolvedName <$resolvedEmail>",
                'to' => array($toEmail),
                'subject' => $subject,
                'html' => $htmlBody,
            );
            $ch = curl_init('https://api.resend.com/emails');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . trim((string)$resolvedPassword),
                'Content-Type: application/json'
            ));
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                $debugOut = "Sent via Resend HTTPS API (HTTP $httpCode): " . $res;
                return true;
            } else {
                $debugOut = "Resend HTTPS API returned HTTP $httpCode: $res. Curl Err: $curlErr. Falling back to SMTP...";
            }
        }
        
        // 2. PHPMailer SMTP Fallback
        try {
            $phpMailerDir = dirname(__DIR__) . '/includes';
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer') && file_exists($phpMailerDir . '/PHPMailer.php')) {
                require_once($phpMailerDir . '/Exception.php');
                require_once($phpMailerDir . '/PHPMailer.php');
                require_once($phpMailerDir . '/SMTP.php');
            }
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            configure_pbi_mailer($mail, $resolvedEmail, $resolvedName);
            $mail->clearAddresses();
            $mail->addAddress($toEmail);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->send();
            $debugOut = ($debugOut ? $debugOut . " | " : "") . "Sent via SMTP successfully.";
            return true;
        } catch (\Throwable $e) {
            $debugOut = ($debugOut ? $debugOut . " | " : "") . "SMTP Error: " . $e->getMessage();
            return false;
        }
    }
}


//function authMail(){
 //   include("email/authenticator.php");
//}

//currency Converter
function currencyConverter($amount){
    include("userdata.php");
    $url = 'https://api.exchangerate-api.com/v4/latest/USD';
    $content = @file_get_contents($url);
    if($content === FALSE) {
        return 1 * $amount;
    } else {
    $json = file_get_contents($url);
    $exp = json_decode($json);
    $convert = $exp->rates->$usercurrency;
    return $convert * $amount;
    //return 1 * $amount;
}
}



//End of currency converter
//END OF SMTP SETTING

/*
*Email address body modifiers
*/

function emailHeader(){
    include("../email/email-header.php");
}

function emailBody(){
    include("../email/email-content.php"); 
}
function emailFooter(){
    include("../email/email-footer.php");
}
function otpMail(){
    require_once("../email/otp-mail.php");
}
function debitAlertMail(){
    require_once("../email/debit-alert-mail.php");
}

/*
*End of EMail address Body modifiers
*/
//USER SETTINGS

//Greetings

function greetings(){
 
   if(date("H") < 12){
 
     return "Good morning";
 
   }elseif(date("H") > 11 && date("H") < 18){
 
     return "Good afternoon";
 
   }elseif(date("H") > 17){
 
     return "Good evening";
 
   }
 
} 
//end of greetings


//CRYPTO CURRENCY CONVERTER - Fast Resilient Implementation with Cache & Fallbacks
function get_crypto_rate_usd($symbol) {
    static $memoryRates = [];
    $sym = strtoupper(trim($symbol));
    
    // Fallback dictionary for common cryptocurrencies
    $fallbacks = [
        'BTC' => 85000.0,
        'ETH' => 3200.0,
        'USDT' => 1.0,
        'USDC' => 1.0,
        'BNB' => 600.0,
        'SOL' => 180.0,
        'XRP' => 0.60,
        'ADA' => 0.50,
        'DOGE' => 0.15,
        'TRX' => 0.15,
        'LTC' => 100.0,
        'BCH' => 400.0,
        'LINK' => 15.0,
        'GMC' => 592.0
    ];

    if ($sym === 'GMC') return 592.0;
    if ($sym === 'USDT' || $sym === 'USDC' || $sym === 'USD') return 1.0;

    // Check memory cache
    if (isset($memoryRates[$sym]) && $memoryRates[$sym] > 0) {
        return $memoryRates[$sym];
    }

    // Check session cache (valid for 10 minutes)
    if (isset($_SESSION['crypto_rates_cache'][$sym]) && isset($_SESSION['crypto_rates_time'][$sym])) {
        if ((time() - $_SESSION['crypto_rates_time'][$sym]) < 600) {
            $memoryRates[$sym] = $_SESSION['crypto_rates_cache'][$sym];
            return $memoryRates[$sym];
        }
    }

    // Fast HTTP fetch with 2-second timeout to prevent Cloudflare 524
    $rate = null;
    $req_url = "https://api.coinconvert.net/convert/" . urlencode($sym) . "/usd?amount=1";
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 2,
            'ignore_errors' => true,
            'header' => "User-Agent: Mozilla/5.0 (PBI Bank API client)\r\n"
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);

    $content = @file_get_contents($req_url, false, $ctx);
    if ($content !== false && !empty($content)) {
        $json = @json_decode($content, true);
        if ($json && isset($json['USD']) && is_numeric($json['USD']) && $json['USD'] > 0) {
            $rate = (float)$json['USD'];
        }
    }

    // If API failed or timed out, use fallback
    if ($rate === null || $rate <= 0) {
        $rate = isset($fallbacks[$sym]) ? $fallbacks[$sym] : 1.0;
    }

    // Cache rate
    $memoryRates[$sym] = $rate;
    if (!isset($_SESSION['crypto_rates_cache'])) $_SESSION['crypto_rates_cache'] = [];
    if (!isset($_SESSION['crypto_rates_time'])) $_SESSION['crypto_rates_time'] = [];
    $_SESSION['crypto_rates_cache'][$sym] = $rate;
    $_SESSION['crypto_rates_time'][$sym] = time();

    return $rate;
}

function cryptoConverter($amount, $symbol) {
    if ((float)$amount == 0) { echo 0; return; }
    $rate = get_crypto_rate_usd($symbol);
    echo round((float)$amount * $rate, 2);
}

function cryptoConverterB($amount, $symbol) {
    if ((float)$amount == 0) return 0;
    $rate = get_crypto_rate_usd($symbol);
    return round((float)$amount * $rate, 4);
}

function cryptoConverter2($amount, $symbol) {
    if ((float)$amount == 0) { echo 0; return; }
    $rate = get_crypto_rate_usd($symbol);
    $val = ($rate > 0) ? ((float)$amount / $rate) : 0;
    echo round($val, 6);
}

function cryptoConverter2B($amount, $symbol) {
    if ((float)$amount == 0) return 0;
    $rate = get_crypto_rate_usd($symbol);
    return ($rate > 0) ? round((float)$amount / $rate, 6) : 0;
}

function checkInstallUrl($site_url){
    if (filter_var($site_url, FILTER_VALIDATE_URL)) {
    } else {
        i_redirect("Settings");
    }
}

function loggedToken() {
    $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890";
    $usertoken = array();
    $alphaLength = strlen($alphabet) - 1; 
    for ($i = 0; $i < 82; $i++) {
    $n = rand(0, $alphaLength);
    $usertoken[] = $alphabet[$n];
    }
    return implode($usertoken); 
    }


    /*
    * End of user login token
    *
    */

    //Url getter function
    function url(){
    include("url.php"); 
    }
    //end of url getter function

 /*========================USER DATA SETTING-----------====================*/
    function userdata(){
    require_once("connect.php");
    $accountid = $_SESSION["loggedUser"];
    $userquery = $conn->query("SELECT * FROM users WHERE accountnumber = '$accountid'");
    while($userdetails = mysqli_fetch_array($userquery)){
    $firstname = $userdetails["firstname"];
    $middlename = $userdetails["middlename"];
    $lastname = $userdetails["lastname"];
    $email = $userdetails["email"];
    $accounttype = $userdetails["accounttype"];
    $passport = $userdetails["passport"];
    $dayOFBirth = $userdetails["dayOFBirth"];
    $accountbalance = $userdetails["accountbalance"];
    $address = $userdetails["address"];
    $state = $userdetails["state"];
    $country = $userdetails["country"];
    $phoe = $userdetails["phone"];
    $secretCode = $userdetails["secretCode"];
    $zipcode = $userdetails["zipcode"];
    $income = $userdetails["income"];
    $occupation = $userdetails["occupation"];
    $nickname = $userdetails["nickname"];
    $securityQuestion = $userdetails["securityquestion"];
    $answer = $userdetails["answer"];
    $userid = $userdetails["id"];
    }
}


/*GET BROWSER NAME================================*/
function getBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
   
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent))
    {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif(preg_match('/Safari/i',$u_agent))
    {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif(preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif(preg_match('/Netscape/i',$u_agent))
    {
        $bname = 'Netscape';
        $ub = "Netscape";
    }
   
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
   
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
   
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
   
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

// write out the browser name
function browser(){
$ua=getBrowser();
$yourbrowser= " " . $ua['name'] . " on ".$ua['platform']."";
}

//add time
function add_time($time,$plusMinutes){

   $endTime = strtotime("+{$plusMinutes} minutes", strtotime($time));
   return date('Y-m-d h:i:s', $endTime);
}


 /*--------=============END OF USER DATA SETTING--===================-*/

/*****GET AGE OF A USER*********/
 function getAge($date)
    {
        
        $dob = new DateTime($date);
        
        $now = new DateTime();
         
        $difference = $now->diff($dob);
         
        $age = $difference->y;
         
        return  $age;
    }

    /* End of age getter====================*/

function usserAccessCheck(){
    //require_once("connect.php");
    //$accesstoken = $_GET["accesstoken"];
    //$query = $conn->query("SELECT * FROM login WHERE token = '$accesstoken'");
    //$roww = mysqli_fetch_array($query);
    //$token = $roww["token"];
    //check if there is an active session
    if(empty($_SESSION["loggedToken"])){
        $url = loggedToken();
        header("location:../?action=login&callBacktoken=$url");
       
    }
    
}
 
/*
Generate Random Number

*/

function getLocation(){
    
    $ip = $_SERVER['REMOTE_ADDR']; 
    // Use JSON encoded string and converts 
    // it into a PHP variable 
     $url =  "http://www.geoplugin.net/json.gp?ip=$ip";
     $content = @file_get_contents($url);
     if($content === FALSE) {
         echo "";
     } else {
    $ipdat = @json_decode(file_get_contents( 
        "http://www.geoplugin.net/json.gp?ip=" . $ip)); 
    $country = $ipdat->geoplugin_countryName; 
    $city = $ipdat->geoplugin_city; 
    $continent_name = $ipdat->geoplugin_continentName; 
    $latitude = $ipdat->geoplugin_latitude; 
    $longitude = $ipdat->geoplugin_longitude; 
    $currency_symbol = $ipdat->geoplugin_currencySymbol; 
    $currency_code = $ipdat->geoplugin_currencyCode; 
    $timezone = $ipdat->geoplugin_timezone; 

    return "$city, $country ($continent_name)"; 
}}
   
    function getTimezone(){
    $ip = $_SERVER['REMOTE_ADDR']; 
    // Use JSON encoded string and converts 
    // it into a PHP variable 
     $url =  "http://www.geoplugin.net/json.gp?ip=$ip";
     $content = @file_get_contents($url);
     if($content === FALSE) {
         echo "No network";
     } else {
    $ipdat = @json_decode(file_get_contents( 
        "http://www.geoplugin.net/json.gp?ip=" . $ip));   
    $timezone = $ipdat->geoplugin_timezone; 
    
    echo $timezone;
    }
}
 
   function getFlag(){
    $ip = $_SERVER['REMOTE_ADDR']; 
    // Use JSON encoded string and converts 
    // it into a PHP variable 
     $url =  "http://www.geoplugin.net/json.gp?ip=$ip";
     $content = @file_get_contents($url);
     if($content === FALSE) {
         echo "127.1.1.0";
     } else {
    $ipdat = @json_decode(file_get_contents( 
        "http://www.geoplugin.net/json.gp?ip=" . $ip));   
    $ca = $ipdat->geoplugin_countryCode; 
    $ca = strtolower($ca);
    echo '<img src="https://flagcdn.com/24x18/'.$ca.'.png">';
}

}
function randomNumber($length = 25) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//END OF RANDOM NUMBER GENERATor/*
//Generate Random STRING


function randomString($length = 25) {
    $characters = '0123456789ABCDEFGHIJKLNMOPQRSTUVWXYZabcdefghijklnmopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function filterString($string) {
    require_once("connect.php");
    $string = stripslashes($string);
    $string = strip_tags($string);
    $string = htmlspecialchars($string);
    $string = mysqli_real_escape_string($conn, $string);
    return $string;
}

//database Connection
function db(){
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "amoswest";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

}    
/*
End of database connection
*/

//ADMIN LOGIN

function adminLogin(){ 
    
}

//Check Bankend Access
function checkAccess(){
    if(!isset($_SESSION['accessBanking'])){
    echo "<script>window.location.href='../';</script>";  
    }
}

//ADMIN LOGIN ACCESS CHECKER
function checkAdmin(){
    if(empty($_SESSION['userAdmin']) || empty($_SESSION['loggedAdmin'])){
        echo "<script>window.location.href='login';</script>";
    }
    
}

//redirect after delay
function redirect($delay, $url){
    return "<meta http-equiv='refresh' content='$delay; url=$url' >";
}

//redirect Immediately

function i_redirect($url){
    echo "<script>window.location.href='$url';</script>";
}

//Error Messages

function feedback($type, $mood, $feedback, $title){
    if($type == "toast"){
        echo "<script>toastr.$mood('$feedback', '$title');</script>";
    }
    if ($type == "sweet"){
      echo "<script>
      Swal.fire({
      icon: '$mood',
      title: '$title',
      text: '$feedback',
      showConfirmButton: true,
      });
    </script>";
    }
}

//Change Border Color
function borderError($color, $id){
    echo"<script>document.getElementById('$id').style.borderColor='$color';</script>";
}

//User Account Login
function loginUser() {
    $accountID = filterString($_POST["id"]);
    $password = filterString($_POST["password"]);
    $errorMsg = 0;

    if (empty($accountID) || empty($password)) {
         $errorMsg =1;
         echo"<script>
         toastr.error('All fields are required', 'Empty field(s)', {\"progressBar\": true});
         </script>";
         }
         elseif ($accountID AND $password != "") {
         require_once("connect.php");
         $password = md5($password);
         $querrry = $conn->query("SELECT * FROM users WHERE accountnumber = '$accountID' and password = '$password' and id != 1");
         $fetch = mysqli_fetch_assoc($querrry);
         $resultL = mysqli_num_rows($querrry);
         if ($resultL != 0) {
         
         if($fetch["approve"] == 0){
            $errorMsg = 1;
            echo '
             <div class="example-alert">
             <div class="alert alert-pro alert-danger alert-dismissible">
               <div class="alert-text">
                 <h6>Pending Approval!</h6>
                  <p>Sorry, your account have not been approved. Please try again later.</p>
                      </div>
                      <button class="close" data-dismiss="alert"></button>
                       </div>
                       </div>
                      <br>
                ';
            
        }
        if($fetch["status"] == "blocked"){
            $errorMsg = 1;
            echo '
            <div class="example-alert">
             <div class="alert alert-pro alert-danger alert-dismissible">
               <div class="alert-text">
                 <h6>Account Suspended!</h6>
                  <p>Dear Customer,
                     we have discovered suspicious activities on your account; because an unauthorized IP address carried out a transaction on your account, consequently, your account has been flagged by our risk assessment department. kindly visit our nearest branch with your identification card and utility bill to confirm your identity before it can be reactivated.</p>
                      </div>
                      <button class="close" data-dismiss="alert">ok</button>
                    </div>
                </div>
             <br>
            ';  
        }

       
        elseif ($errorMsg == 0) {

           /* if ($fetch["tfa"] == "active") {
                ?>
               <meta http-equiv="refresh" content="2; url=twa">
                <?php
                

            }*/
            //else{
            echo '
            <div class="example-alert">
             <div class="alert alert-pro alert-success alert-dismissible">
               <div class="alert-text">
                  <h6>Successful</h6>
                     <p>Your credentials matched our records</p>
                      </div>
                      <button class="close" data-dismiss="alert"></button>
                       </div>
                  </div>
                  <br>
                 ';

        $_SESSION["loggedUser"] = $accountID;
        $loggedtoken = loggedToken();
        $_SESSION["loggedToken"] = $loggedtoken;
        $userid = $fetch["id"];
        $ip = $_SERVER["REMOTE_ADDR"];
        $dated = date("d M y, H:i a");
        $browser = $_SERVER["HTTP_USER_AGENT"];
        $queryyy = $conn->query("INSERT INTO login(ip, browser, dated, token, userid) VALUES ('$ip', '$browser', '$dated', '$loggedtoken', '$userid')");
        ?>
        <meta http-equiv="refresh" content="2; url=../personal-banking/twa.php?accessToken=<?php echo$loggedtoken?>">
        <?php
        
    }
        }

        else{
            echo '
            <div class="example-alert">
             <div class="alert alert-pro alert-danger alert-dismissible">
               <div class="alert-text">
                 <h6>Invalid credentials</h6>
                  <p>Your account ID or passcode is incorrect</p>
                      </div>
                      <button class="close" data-dismiss="alert"></button>
                       </div>
                  </div>
                  <br>
            ';
        }
    }

        else{
            echo "
        <script>
        toastr.error('An error occured, try again later', 'Error occured', {\"progressBar\": true});
        </script>
        ";
        }

    }


    function enrollNewUserStepOne(){
        $firstname = filterString($_POST["firstname"]);
        $middlename = filterString($_POST["middlename"]);
        $lastname = filterString($_POST["lastname"]);
        $title = filterString($_POST["title"]);
        $gender = filterString($_POST["gender"]);
        $nickname = filterString($_POST["nickname"]);
        $dayOFBirth = filterString($_POST["dayOFBirth"]);
        $monthOfBirth = filterString($_POST["monthOfBirth"]);
        $yearOfBirth = filterString ($_POST["yearOfBirth"]);
        $maidenName = filterString($_POST["maidenName"]);


        $erroSignUp = 0;
        if (empty($firstname) || empty($middlename) || empty($lastname) || empty($nickname) || empty($maidenName)) {
            $errorSignUp = 1;
            
             echo "
             <script>
              toastr.error('All fields are required', 'Empty fields', {\"progressBar\": true});
             </script>
             ";
        }

        elseif ($errorSignUp == 0) {
            echo "
            <script>
            toastr.success('Going to the next Step', 'Successful', {\"progressBar\": true});
            </script>
            ";
             
             $_SESSION ["regToken"] = loggedToken(); 
             $_SESSION["firstname"] = $firstname;
             $_SESSION["middlename"] = $middlename;
             $_SESSION["lastname"] = $lastname;
             $_SESSION["title"] = $title;
             $_SESSION["gender"] = $gender;
             $_SESSION["nickname"] = $nickname;
             $_SESSION["dayOFBirth"] = $dayOFBirth;
             $_SESSION["monthOfBirth"] = $monthOfBirth;
             $_SESSION["yearOfBirth"] = $yearOfBirth;
             $_SESSION["maidenName"] = $maidenName;
             $regToken = $_SESSION["regToken"];

             header("Refresh:3; url=enroll.php?registerationToken=$regToken");  
        }

        else {
             echo "
        <script>
        toastr.error('An error occured, try again later', 'Error occured', {\"progressBar\": true});
        ";
        }
    }

    function enrollNewUserStepTwo(){
        $email = filterString($_POST["email"]);
        $phone = filterString($_POST["phone"]);
        $ssn = filterString($_POST["ssn"]);
        $country = filterString($_POST["country"]);
        $state = filterString($_POST["state"]);
        $zipcode = filterString($_POST["zipcode"]);
        $address = filterString($_POST["address"]);
        $occupation = filterString($_POST["occupation"]);
        $income = filterString($_POST["income"]);
        $nextOfKIn = filterString($_POST["nextOfKIn"]);
        


        $erroSignUpTwo = 0;

        if(empty($email) || empty($phone) || empty($address) || empty($occupation) || empty($nextOfKIn) || empty($income)){
            $erroSignUpTwo = 1;
            echo "
           <script>
             toastr.error('Some fields cannot be left Empty', 'Empty fields', {\"progressBar\": true});
           </script>
            ";
        }

        db();
        $query = $conn->query("SELECT * FROM users WHERE email = '$email'");
        $numRows = mysqli_num_rows($query);
        if ($numRows >=1) {
            $erroSignUpTwo = 1;
            echo "
           <script>
             toastr.error('Email address is already in use by another account', 'Email Address is taken', {\"progressBar\": true});
           </script>
            "; 
           
        }

        if($erroSignUpTwo == 0){
        $_SESSION["email"] = $email;
        $_SESSION["phone"] = $phone;
        $_SESSION["ssn"] = $ssn;
        $_SESSION["country"] = $country;
        $_SESSION["state"] = $state;
        $_SESSION["zipcode"] = $zipcode;
        $_SESSION["address"] = $address;
        $_SESSION["occupation"] = $occupation;
        $_SESSION["income"] = $income;
        $_SESSION["nextOfKIn"] = $nextOfKIn;
         echo "
            <script>
            toastr.success('Going to the next Step', 'Successful', {\"progressBar\": true});
            </script>
            ";
            header("Refresh:3; url=enroll.php?registerationToken=$regToken&&step=?three");

          

        }

    }

    /*
    END OF REGISTRATION STEP TWO
    */
    
    function enrollNewUSerStepThree(){
        $accounttype = filterString($_POST["accounttype"]);
        $securityQuestion = filterString($_POST["securityQuestion"]);
        $answer = filterString($_POST["answer"]);
        $secretCode = filterString($_POST["secretCode"]);
        $password = filterString($_POST["password"]);
        $erroSignUpThree = 0;

        if(empty($secretCode) || empty($password) || empty($answer)){
            $erroSignUpThree = 1;
            echo "
             <script>
              toastr.error('Some fields cannot be left Empty', 'Empty fields', {\"progressBar\": true});
             </script>
            ";
        }

        if (strlen($password <= 5)) {
            $erroSignUpThree = 1;
            echo "
             <script>
              toastr.error('Password must be at least six characters long', 'Weak password', {\"progressBar\": true});
             </script>
            ";
        }

        if($erroSignUpThree == 0){
            $_SESSION["accounttype"] = $accounttype;
            $_SESSION["securityQuestion"] = $securityQuestion;
            $_SESSION["answer"] = $answer;
            $_SESSION["secretCode"] = $secretCode;
            $_SESSION["password"] = $password;
            echo "
            <script>
            toastr.success('Going to the next Step', 'Successful', {\"progressBar\": true});
            </script>
            ";
            header("Refresh:3; url=enroll.php?registerationToken=$regToken&&step=fourth");

        }
}

function enrollNewUSerStepFour(){
$target_dir = "passport/";
$target_file = $target_dir . basename($_FILES["passport"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image

  $check = getimagesize($_FILES["passport"]["tmp_name"]);
  if($check !== false) {
    $uploadOk = 1;
  } else {
    echo "

   <script>
            toastr.error('File is not an image.', 'Unknown file', {\"progressBar\": true});
    </script>
    ";
    $uploadOk = 0;
  }

  // Check file size
  if ($_FILES["passport"]["size"] > 500000) {
  echo "
  <script>
            toastr.error('Sorry, your file is too large.', 'Image is too large', {\"progressBar\": true});
    </script>
  ";
  $uploadOk = 0;
  }

// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
  echo "
 <script>
            toastr.error('Sorry, only JPG, JPEG, PNG & GIF files are allowed.', 'File error', {\"progressBar\": true});
    </script>
";
  $uploadOk = 0;
  }

 // Check if $uploadOk is set to 0 by an error
 if ($uploadOk == 0) {
  echo "
        <script>
            toastr.error('Sorry, your passport was not uploaded', 'File error', {\"progressBar\": true});
    </script>
    ";
   // if everything is ok, try to upload file
   } else {
   if (move_uploaded_file($_FILES["passport"]["tmp_name"], $target_file)) {
   $passport = basename($_FILES["passport"]["name"]);

    echo "
            <script type='text/javascript'>
            swal(\"Passport Uploaded!\", \"Your Passport has been uploaded Successfully\", \"success\");
            </script>
            ";
            $_SESSION["passport"] = $passport;
            header("Refresh:3; url=enroll.php?registerationToken=$regToken&&step=fourth");
        }

     }
    }

    function  enrollNewUSerStepFive(){
            $firstname =   $_SESSION["firstname"];
            $middlename =  $_SESSION["middlename"];
            $lastname =  $_SESSION["lastname"];
            $title =  $_SESSION["title"];
            $gender =  $_SESSION["gender"];
            $nickname =  $_SESSION["nickname"];
            $dayOFBirth = $_SESSION["dayOFBirth"];
            $monthOfBirth =  $_SESSION["monthOfBirth"];
            $yearOfBirth = $_SESSION["yearOfBirth"];
            $maidenName = $_SESSION["maidenName"];
            $email = $_SESSION["email"];
            $phone =   $_SESSION["phone"];
            $ssn =  $_SESSION["ssn"];
            $country = $_SESSION["country"];
            $state =  $_SESSION["state"];
            $zipcode =  $_SESSION["zipcode"];
            $address =  $_SESSION["address"];
            $occupation =  $_SESSION["occupation"];
            $income = $_SESSION["income"];
            $nextOfKIn =  $_SESSION["nextOfKIn"];
            $accounttype =  $_SESSION["accounttype"];
            $securityQuestion = $_SESSION["securityQuestion"];
            $answer = $_SESSION["answer"];
            $secretCode = $_SESSION["secretCode"];
            $password = $_SESSION["password"];
            $passport = $_SESSION["passport"];
     
            db();
            $accountnumber = randomNumber(10);
            $dateCreated = date("d M Y, H:i");
            $query = $conn->query("INSERT INTO users(firstname, middlename, lastname, title, gender, nickname, dayOFBirth, monthOfBirth, yearOfBirth , maidensname, email, phone, ssn, country, state, zipcode, address, occupation, income, nextOfKIn, accounttype, securityquestion, answer, secretCode, password, passport, accountnumber, datecreated  )VALUES('$firstname', '$middlename', '$lastname', '$title', '$gender',$nickname '$dayOFBirth', '$monthOfBirth',
                '$yearOfBirth', '$maidenName', '$email', '$phone', '$ssn', '$country', '$state', '$zipcode', '$address', '$occupation', '$income', '$nextOfKIn', '$accounttype', '$securityQuestion', '$answer', '$secretCode', '$password', '$passport', '$accountnumber', '$dateCreated')");
     }

        function contactForm(){
        $email = filterString($_POST["email"]);
        $name = filterString($_POST["name"]);
        $subject = filterString($_POST["subject"]);
        $phone = filterString($_POST["phone"]);
        $message = filterString($_POST["message"]);
        $department = filterString($_POST["department"]);
        $dated = date("d M Y, H:i");
        $ip = $_SERVER["REMOTE_ADDR"];
        if (empty($email) || empty($name) || empty($message) || empty($subject)) {
             echo "
        <script>
            toastr.error('Please, fill in all the required fields', 'Empty fields', {\"progressBar\": true});
        </script> ";         
      }   
      else{
        db();
        $query = $conn->query("INSERT INTO contact (email, name, subject, phone, message, department, dated, ip) VALUES ('$email', '$name', '$subject', '$phone', '$message', '$department', '$dated', '$ip')");
         echo "
            <script type='text/javascript'>
            swal(\"Form submitted!\", \"Your enquiry have been delivered to our customer care representative desk. You will get a reply from us shortly.\", \"success\");
            </script>
            ";

      }     
     }

        function localTransferStepOne(){

        $bankname = filterString($_POST["bankname"]);
        $routineNumber = filterString($_POST["routineNumber"]);
        $swiftcode = filterString($_POST["swiftcode"]);
        $accountholder = filterString($_POST["accountholder"]);
        $accountnumberB = filterString($_POST["accountnumber"]);
        $errorMsgTA = 0;

        if (empty($bankname) || empty($routineNumber) || empty($accountnumberB) || empty($accountholder)) {
           $errorMsgTA = 1;
           echo "
        <script>
            toastr.error('Please, fill in all the required fields', 'Empty fields', {\"progressBar\": true});
        </script> ";         
         
        }

        if (strlen($accountnumberB) >= 9) {
            $errorMsgTA = 1;
            echo "
        <script>
            toastr.error('Account number is invalid', 'Invalid account number', {\"progressBar\": true});
        </script> ";   
            
        }

        if (strlen($routineNumber) <= 9) {
            $errorMsgTA = 1;
            echo "
        <script>
            toastr.error('routine number is invalid', 'Invalid routine number', {\"progressBar\": true});
        </script> ";   
        }

        if ($errorMsgTA == 0) {
            $_SESSION["bankname"] = $bankname;
            $_SESSION["routineNumber"] = $routineNumber;
            $_SESSION["swiftcode"]  = $swiftcode;
            $_SESSION["accountnumberB"] = $accountnumberB;
            $_SESSION["transferToken"] = randomString(82);
            $_SESSION["accountholder"] = $accountholder;
            $transferToken = $_SESSION["transferToken"];
            header("location:localTransfer.php?step=2&&transferToken=$transferToken"); 
        }    
  }

  function localTransferStepTwo(){
    userdata();
     $transferAmount = filterString($_POST["amount"]);
     $description = filterString($_POST["description"]);
     $errorMsgTB = 0;

     if (empty($transferAmount)) {

         $errorMsgTB = 1;
         echo "
         <script>
            toastr.error('routine number is invalid', 'Invalid routine number', {\"progressBar\": true});
         </script> ";

      }
    

     elseif ($transferAmount > $accountbalance) {

        $errorMsgTB = 1;
         echo "
         <script>
            toastr.error('Your $accounttype balance is $money $accountbalance', 'Insufficient balance', {\"progressBar\": true});
         </script> ";

     } 

     else{
        $_SESSION["transferAmount"]=$transferAmount;
        $_SESSION["description"]=$description;
        $transferToken = $_SESSION["transferToken"];
        header("location:localTransfer.php?step=3&&transferToken=$transferToken"); 

     }
   
  }

  function localTransferStepThree(){
    userdata();
    $otp = randomNumber(6);
    $_SESSION["otp"] = $otp;
    include("../email/php_mailer.php");
    $mail->addAddress($email);
    $mail->Subject = "Confirm transaction";
    $mail->isHTML(true);
    $otpMail = otpMail();
    $headerContent = emailHeader();
    $emailFooter = emailFooter();
    $mail->Body = "$headerContent $otpMail $emailFooter";
    if(!$mail->Send())
    {
    echo "
         <script>
            toastr.error('Unable to proccess your transaction right now', 'Try again later!', {\"progressBar\": true});
         </script> " . $mail->ErrorInfo;;
    }
    else
    {
     echo "
         <script>
            toastr.success('A One-Time password has been forwarded to your Email address, Kindly check your inbox and confirm this transaction', 'Otp forwarded', {\"progressBar\": true});
         </script> ";
         $otpTimer = 10;
         setcookie("timer", $otpTimer, time() + 600);
    }
}
    
  
  function  localTransferStepFour() {
        $otpCode = filterString($_POST["otp"]);
        $errorForOtp = 0;

        if (empty($_COOKIE["timer"])) {
            $errorForOtp = 1;
            echo"
               <script>
                toastr.warning('Sorry, OTP has expired', 'Request for new OTP', {\"progressBar\": true});
                </script> ";

        }
        elseif (empty($otpCode)) {
            $errorForOtp = 1;
            echo "
         <script>
            toastr.warning('Kindly suply your Otp Code', 'Otp forwarded', {\"progressBar\": true});
         </script> ";

        }
        elseif ($otpCode != $_SESSION["otp"]) {
            echo "
         <script>
            toastr.error('Invalid OTP supplied', 'OTP Error', {\"progressBar\": true});
         </script> ";
        } 
            $bankname =  $_SESSION["bankname"];
            $routineNumber = $_SESSION["routineNumber"];
            $swiftcode = $_SESSION["swiftcode"];
            $accountnumberB = $_SESSION["accountnumberB"];
            $transferToken = $_SESSION["transferToken"];
            $accountholder = $_SESSION["accountholder"];


        if ($otpCode == $_SESSION["otp"]) {
             
            db();
            userdata();
            $ref = randomString(9);
            $dd = date("my");
            $dc = substr($sitename, 0, 3);
            $refNumber = filterString("$dc/$ref-$dd");
            $otp = $_SESSION["otp"];
            $dated = date("d M Y, g:i a");
            $acctBal = ($accountbalance - ($amount));
            $queryForTransfer = $conn->query("INSERT INTO transactions (scope, type, bankname, routineNumber, swiftcode, accountnumber, accountholder, otp, refNumber, dated, amount, accountbalance, userid) VALUES ('Local Transfer', 'Debit', '$bankname', '$routineNumber', '$swiftcode', '$accountnumberB', '$userid')");
            $queryForBalUpdate = $conn->query("UPDATE users SET accountbalance = '$acctBal' WHERE id = '$userid'");
            //Notifying the user that his/her account have been debited Through Mail==

            include("../email/php_mailer.php");
            $mail->addAddress($email);
            $mail->Subject = "Debit alert Notification";
            $mail->isHTML(true);
            $debitAlertMail = debitAlertMail();
            $headerContent = emailHeader();
            $emailFooter = emailFooter();
            $mail->Body = "$headerContent $debitAlertMail $emailFooter";

        }


/*==============USER PASSPORT UPLOAD====================*/
function userPassportUpload(){
$target_dir = "passport/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if($check !== false) {
    $uploadOk = 1;
  } else {
    echo "<div class='alert alert-danger'>Only image is allowed</div>";
    $uploadOk = 0;
  }


// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
  echo "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
  $uploadOk = 0;
}

if ($uploadOk == 0) {
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    $passport = htmlspecialchars( basename( $_FILES["fileToUpload"]["name"]));
    $user = $_SESSION['act_to_register'];
    $query = $conn->query("UPDATE users SET passport = '$passport' WHERE accountnumber = '1316291918'");
    echo"<div class='alert alert-success'>Passport photograph uploaded</div>";
    ?>
   <meta http-equiv="refresh" content="email-verification.php?account_registration='<?php echo "".$_SESSION['account_registration']."";?>'">
    <?php
  } else {
    echo "<div class='alert alert-danger'>Sorry, there was an error uploading your passport.</div>";
  }
}
}    
}


function getKycStatus($userid){
    require_once('connect.php');
    $sql = $conn->query("SELECT * FROM kyc WHERE userid = '$userid'");
    if(mysqli_num_rows($sql) < 1){
        return "0";
    }
    elseif (mysqli_num_rows($sql) == 1) {
     return  $kycResult = mysqli_fetch_array($sql);
    }
}

function getCardStatus($userid){
    require_once('connect.php');
    $sql = $conn->query("SELECT * FROM visual_cards WHERE userid = '$userid'");
    if(mysqli_num_rows($sql) < 1){
        return "0";
    }
    elseif (mysqli_num_rows($sql) >= 1) { 
     return  $cardResult = mysqli_fetch_array($sql);
    }
}



//ALERT NOTIFICATION
function alertNotification($sms, $api, $sender_id, $msg, $recipient){
if($sms == 1){
$url = "https://gatewayapi.com/rest/mtsms";
$api_token = "$api";

//Set SMS recipients and content
$recipients = [$recipient];
$json = [
    'sender' => "$sender_id",
   // 'message' => "Credit alert\nAcct: $accountnumber\nAmt: $amount $money CR\nDesc: Purchase(NewEdgeCU$dex)\nSender: $fullname\nLedger: 200usd available\nDate: $dated",
    'message' => "$msg",
    'recipients' => [],
];
foreach ($recipients as $msisdn) {
    $json['recipients'][] = ['msisdn' => $msisdn];
}

//Make and execute the http request
//Using the built-in 'curl' library
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
curl_setopt($ch,CURLOPT_USERPWD, $api_token.":");
curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if($httpcode == 200 || $httpcode == 202){
    return 1;
}else{ return 0; }
}else{
    return 0;
}
}

function getIncomeValue($userid, $type){
    require_once("connect.php");
        $query = $conn->query("SELECT * FROM transactions WHERE userid = '$userid'");
        $total_tansaction = mysqli_num_rows($query);

        if($total_tansaction > 1){
            if($type == "credit"){
                $creditQuery = $conn->query("SELECT * FROM transactions WHERE type = 'Credit' and userid = '$userid'");
                return mysqli_num_rows($creditQuery)/$total_tansaction*100;
            }

             if($type == "debit"){
                $DebitQuery = $conn->query("SELECT * FROM transactions WHERE type = 'Debit' and userid = '$userid'");
                return mysqli_num_rows($DebitQuery)/$total_tansaction*100;
            }
        }
        else{
            return 0.00;
        }

}
?>