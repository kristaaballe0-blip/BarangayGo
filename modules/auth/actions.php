<?php

function registerAction(int $id, array|false $user): void
{
    $name = required('name');
    $address = required('address');
    $birthdate = required('birthdate');
    $phone = trim($_POST['phone'] ?? '');
    $email = strtolower(required('email'));
    $password = required('password');
    $confirmation = required('password_confirmation');

    if (mb_strlen($name) > 255 || mb_strlen($phone) > 100) {
        throw new RuntimeException('Please shorten your name or phone number.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        throw new RuntimeException('Enter a valid email address.');
    }
    $date = DateTime::createFromFormat('!Y-m-d', $birthdate);
    if (!$date || $date->format('Y-m-d') !== $birthdate || $birthdate > date('Y-m-d')) {
        throw new RuntimeException('Enter a valid birth date.');
    }
    if (strlen($password) < 12 || strlen($password) > 72) {
        throw new RuntimeException('Use a password between 12 and 72 bytes.');
    }
    if (!hash_equals($password, $confirmation)) {
        throw new RuntimeException('The passwords do not match.');
    }
    if (query('SELECT id FROM users WHERE email=?', [$email])->fetch()) {
        throw new RuntimeException('That email is already registered. Please sign in.');
    }

    // Account, linked resident profile, and audit entry must all succeed together.
    transaction(function () use ($name, $address, $birthdate, $phone, $email, $password) {
        global $db;
        query('INSERT INTO residents(name,address,birthdate,phone) VALUES(?,?,?,?)', [
            $name,
            $address,
            $birthdate,
            $phone,
        ]);
        $residentId = (int) $db->lastInsertId();
        query('INSERT INTO users(resident_id,name,email,password,role) VALUES(?,?,?,?,?)', [
            $residentId,
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            'resident',
        ]);
        $accountId = (int) $db->lastInsertId();
        query('INSERT INTO logs(user_id,action) VALUES(?,?)', [
            $accountId,
            'Created resident account',
        ]);
    });
}

function loginAction(int $id, array|false $user): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'local';
    $attempt = query('SELECT * FROM login_attempts WHERE ip=?', [$ip])->fetch();
    if ($attempt && $attempt['attempts'] >= 5 && time() - (int) $attempt['last_attempt'] < 900) {
        throw new RuntimeException('Too many attempts. Try again in 15 minutes.');
    }
    $u = query('SELECT * FROM users WHERE email=?', [strtolower(required('email'))])->fetch();
    if (!$u || !password_verify(required('password'), $u['password'])) {
        query(
            'INSERT INTO login_attempts VALUES(?,1,?) ON DUPLICATE KEY UPDATE attempts=CASE WHEN last_attempt < ? THEN 1 ELSE attempts+1 END,last_attempt=VALUES(last_attempt)',
            [$ip, time(), time() - 900],
        );
        throw new RuntimeException('Email or password is incorrect.');
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = $u['id'];
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    query('DELETE FROM login_attempts WHERE ip=?', [$ip]);
    audit('Signed in');
}

function logoutAction(int $id, array|false $user): void
{
    audit('Signed out');
    $_SESSION = [];
    session_regenerate_id(true);
}
