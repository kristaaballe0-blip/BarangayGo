<?php

function updateProfileAction(int $id, array|false $user): void
{
    $name = required('name');
    $email = strtolower(required('email'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Enter a valid email address.');
    }
    if (!password_verify(required('current_password'), $user['password'])) {
        throw new RuntimeException('Current password is incorrect.');
    }
    $password = $_POST['new_password'] ?? '';
    if ($password !== '' && strlen($password) < 12) {
        throw new RuntimeException('Use at least 12 characters for your new password.');
    }
    transaction(function () use ($name, $email, $password, $user) {
        query('UPDATE users SET name=?,email=?,password=? WHERE id=?', [
            $name,
            $email,
            $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : $user['password'],
            $user['id'],
        ]);
        audit('Updated account profile');
    });
    session_regenerate_id(true);
}
