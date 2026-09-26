<?php
/**
 * Railway Database Seeder — run ONCE after first deploy.
 *
 * Visit https://YOUR-DOMAIN/railway-seed.php in your browser.
 * This will:
 *   1. Create all 16 database tables
 *   2. Insert default settings (51 rows)
 *   3. Create the admin account
 *
 * After it runs successfully, DELETE this file from your repo for security:
 *   git rm railway-seed.php && git commit -m "Remove seed script" && git push
 *
 * Credentials after seeding:
 *   URL:      /admin/login.php
 *   Email:    admin@pbigroup.com
 *   Password: BankDOM#2026
 */

// Prevent running in a non-Railway environment accidentally
header('Content-Type: text/html; charset=utf-8');

echo '<!doctype html><html><head><meta charset="utf-8"><title>Railway Seed</title>';
echo '<style>body{font:16px/1.7 -apple-system,sans-serif;max-width:640px;margin:60px auto;padding:0 20px;color:#1a1a2e;background:#f0f0f5}';
echo '.ok{color:#059669}.err{color:#dc2626}pre{background:#1a1a2e;color:#e0e0e0;padding:16px;border-radius:8px;overflow-x:auto;font-size:13px}</style></head><body>';
echo '<h1>🚀 Railway Database Seeder</h1>';

// Load the app config
$root = __DIR__;
if (!file_exists($root . '/config.php')) {
    echo '<p class="err">❌ config.php not found. Make sure DATABASE_URL or MYSQL* env vars are set in Railway.</p>';
    echo '</body></html>';
    exit;
}

require_once $root . '/config.php';

echo '<p>Driver: <strong>' . DB_DRIVER . '</strong></p>';

if (DB_DRIVER === 'sqlite') {
    echo '<p class="err">⚠️ You\'re using SQLite. On Railway, this works but data resets on each deploy. Consider adding a MySQL plugin.</p>';
}

// Load the DB helper
require_once $root . '/inc/db.php';
require_once $root . '/inc/helpers.php';

try {
    // Step 1: Create tables
    echo '<h2>Step 1: Creating tables...</h2>';
    db_install();
    echo '<p class="ok">✅ All 16 tables created successfully.</p>';

    // Step 2: Seed settings
    echo '<h2>Step 2: Seeding settings...</h2>';
    $pdo = db();
    $check = $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();

    if ((int)$check > 0) {
        echo '<p>⏩ Settings table already has ' . $check . ' rows — skipping (not overwriting your data).</p>';
    } else {
        $defaults = default_settings();
        $st = $pdo->prepare('INSERT INTO settings (skey, svalue) VALUES (?, ?)');
        foreach ($defaults as $k => $v) {
            $st->execute(array($k, $v));
        }
        echo '<p class="ok">✅ ' . count($defaults) . ' default settings inserted.</p>';
    }

    // Step 3: Create admin user
    echo '<h2>Step 3: Creating admin account...</h2>';
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute(array('admin@pbigroup.com'));

    if ($check->fetch()) {
        echo '<p>⏩ Admin account already exists — skipping.</p>';
    } else {
        $hash = password_hash('BankDOM#2026', PASSWORD_DEFAULT);
        $acct = str_pad(mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
        $now  = date('Y-m-d H:i:s');

        $st = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password, role, status,
            account_number, account_type, currency, balance, email_verified, kyc_status, country, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $st->execute(array(
            'Site', 'Administrator', 'admin@pbigroup.com', $hash, 'admin', 'active',
            $acct, 'Administration', 'USD', 0, 1, 'verified', '', $now
        ));
        echo '<p class="ok">✅ Admin account created.</p>';
    }

    // Step 4: Seed custom customer accounts
    echo '<h2>Step 4: Seeding custom user accounts...</h2>';

    $users_to_seed = array(
        array(
            'id'             => 24,
            'first_name'     => 'Chaek Jae',
            'last_name'      => 'Wan',
            'email'          => 'eqrglobal5@gmail.com',
            'phone'          => '3455433421',
            'dob'            => '08/21/1972',
            'gender'         => '',
            'avatar'         => 'PINIMG202503221311-YDFPR.jpg',
            'address'        => '216 Thompson RD',
            'state'          => 'South Carolina',
            'city'           => 'Wellford',
            'country'        => 'United States',
            'zip'            => '29385',
            'occupation'     => '',
            'account_number' => '8857325598',
            'account_type'   => 'Checking Account',
            'currency'       => 'USD',
            'balance'        => 285270099.91,
            'status'         => 'active',
            'secret_code'    => '0987',
            'two_factor'     => 0,
            'email_verified' => 1,
            'transfer_locked'=> 1,
            'lock_reason'    => 'Administrative review pending',
            'created_at'     => '2025-03-22 13:00:00',
            'codes'          => array('cot' => '4321', 'imf' => '1100')
        ),
        array(
            'id'             => 25,
            'first_name'     => 'Choi Woo',
            'last_name'      => 'Cheol',
            'email'          => 'limjaechoon5050@gmail.com',
            'phone'          => '+12025368686',
            'dob'            => '1942-12-25',
            'gender'         => 'Male',
            'avatar'         => 'PINIMG202504201140-6YYP0.jpg',
            'address'        => 'Connecticut, USA',
            'state'          => 'Kyonggi-do',
            'city'           => 'Seoul',
            'country'        => 'Korea, South',
            'zip'            => '06928',
            'occupation'     => 'Self Employed',
            'account_number' => '7180047307',
            'account_type'   => 'Fixed Deposit Account',
            'currency'       => 'USD',
            'balance'        => 77745189.18,
            'status'         => 'active',
            'secret_code'    => '4040',
            'two_factor'     => 1,
            'email_verified' => 1,
            'transfer_locked'=> 0,
            'lock_reason'    => '',
            'created_at'     => '2025-04-20 00:00:00',
            'codes'          => array('cot' => '00', 'imf' => '00')
        ),
        array(
            'id'             => 26,
            'first_name'     => 'Victoria',
            'last_name'      => 'Meloff',
            'email'          => 'realrialiti@gmail.com',
            'phone'          => '+1(530)235-6784',
            'dob'            => '02/23/1985',
            'gender'         => 'Female',
            'avatar'         => 'PINIMG202505031456-YEJNL.jpeg',
            'address'        => '1275 EAST DATE ST APT 208 SN BERNRDNO, CA 92402',
            'state'          => 'Florida',
            'city'           => 'New Port Richey',
            'country'        => 'United States',
            'zip'            => '34653',
            'occupation'     => '',
            'account_number' => '7902501075',
            'account_type'   => 'Investment Account',
            'currency'       => 'USD',
            'balance'        => 882811.91,
            'status'         => 'active',
            'secret_code'    => '0987',
            'two_factor'     => 0,
            'email_verified' => 0,
            'transfer_locked'=> 0,
            'lock_reason'    => '',
            'created_at'     => '2025-05-03 13:36:00',
            'codes'          => array('cot' => '0987', 'imf' => '0987')
        )
    );

    foreach ($users_to_seed as $u_data) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE id = ? OR email = ?');
        $chk->execute(array($u_data['id'], $u_data['email']));
        $existing = $chk->fetch();

        if ($existing) {
            echo '<p>⏩ User ' . htmlspecialchars($u_data['first_name'] . ' ' . $u_data['last_name']) . ' (ID: ' . $u_data['id'] . ') already exists — skipping.</p>';
            continue;
        }

        $pw_hash  = password_hash('BankDOM#2026', PASSWORD_DEFAULT);
        $pin_hash = password_hash($u_data['secret_code'], PASSWORD_DEFAULT);

        $st = $pdo->prepare('INSERT INTO users (
            id, first_name, last_name, email, phone, password, role, status,
            account_number, account_type, currency, balance, pin, email_verified,
            two_factor, kyc_status, avatar, dob, gender, occupation, address,
            city, state, country, zip, transfer_locked, lock_reason, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?
        )');

        $st->execute(array(
            $u_data['id'],
            $u_data['first_name'],
            $u_data['last_name'],
            $u_data['email'],
            $u_data['phone'],
            $pw_hash,
            'user',
            $u_data['status'],
            $u_data['account_number'],
            $u_data['account_type'],
            $u_data['currency'],
            $u_data['balance'],
            $pin_hash,
            $u_data['email_verified'],
            $u_data['two_factor'],
            'verified',
            $u_data['avatar'],
            $u_data['dob'],
            $u_data['gender'],
            $u_data['occupation'],
            $u_data['address'],
            $u_data['city'],
            $u_data['state'],
            $u_data['country'],
            $u_data['zip'],
            $u_data['transfer_locked'],
            $u_data['lock_reason'],
            $u_data['created_at']
        ));

        // Insert transfer codes (COT, IMF, etc.)
        if (!empty($u_data['codes'])) {
            $code_st = $pdo->prepare('INSERT INTO transfer_codes (user_id, code_type, code, used, created_at) VALUES (?, ?, ?, 0, ?)');
            foreach ($u_data['codes'] as $ctype => $cval) {
                $code_st->execute(array($u_data['id'], $ctype, $cval, $u_data['created_at']));
            }
        }

        // Insert initial deposit transaction for ledger consistency
        $ref = 'DEP' . strtoupper(substr(md5($u_data['id'] . $u_data['account_number']), 0, 10));
        $tx_st = $pdo->prepare('INSERT INTO transactions (
            user_id, ref, direction, category, amount, fee, balance_after,
            description, channel, counterparty, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $tx_st->execute(array(
            $u_data['id'],
            $ref,
            'credit',
            'deposit',
            $u_data['balance'],
            0.00,
            $u_data['balance'],
            'Opening Balance / Initial Deposit',
            'wire',
            'Federal Reserve Direct Wire',
            'completed',
            $u_data['created_at']
        ));

        echo '<p class="ok">✅ Added ' . htmlspecialchars($u_data['first_name'] . ' ' . $u_data['last_name']) . ' (ID: ' . $u_data['id'] . ', Acct: ' . $u_data['account_number'] . ', Bal: $' . number_format($u_data['balance'], 2) . ')</p>';
    }

    // Done!
    echo '<hr>';
    echo '<h2 class="ok">🎉 Database is ready!</h2>';
    echo '<p><strong>Sign in at:</strong> <a href="/admin/login.php">/admin/login.php</a></p>';
    echo '<pre>';
    echo "Admin Email:    admin@pbigroup.com\n";
    echo "Admin Password: BankDOM#2026\n\n";
    echo "User Passwords: BankDOM#2026 (for all seeded users)\n";
    echo "User PINs:      As specified per account (e.g. 0987, 4040)\n";
    echo '</pre>';
    echo '<p style="color:#6b7280;margin-top:24px">⚠️ <strong>Security reminder:</strong> Delete this file from your repo now:</p>';
    echo '<pre>git rm railway-seed.php && git commit -m "Remove seed script" && git push</pre>';

} catch (Exception $e) {
    echo '<p class="err">❌ Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Check that your MySQL plugin is added in Railway and the environment variables are linked to your web service.</p>';
}

echo '</body></html>';
