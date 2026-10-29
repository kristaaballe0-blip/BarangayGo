<?php

// Input validation and access checks.
function required(string $key): string
{
    $v = trim((string) ($_POST[$key] ?? ''));
    if ($v === '') {
        throw new RuntimeException('Please complete all required fields.');
    }
    if (mb_strlen($v) > 2000) {
        throw new RuntimeException('Please shorten the entered text.');
    }
    return $v;
}
function amount(string $key): int
{
    $v = required($key);
    if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $v)) {
        throw new RuntimeException('Enter a valid amount with up to two decimal places.');
    }
    return (int) round((float) $v * 100);
}
function staff(): void
{
    global $user;
    if (!$user || $user['role'] !== 'staff') {
        throw new RuntimeException('Staff access required.');
    }
}

// Shared form fields and request tables.
function formStart(string $action, int $id = 0): void
{
    echo '<form method="post"><input type="hidden" name="csrf" value="' .
        e($_SESSION['csrf']) .
        '"><input type="hidden" name="action" value="' .
        e($action) .
        '"><input type="hidden" name="id" value="' .
        $id .
        '">';
}
function input(
    string $label,
    string $name,
    string $value = '',
    string $type = 'text',
    bool $required = true,
): void {
    echo '<label>' .
        e($label) .
        '<input type="' .
        e($type) .
        '" name="' .
        e($name) .
        '" value="' .
        e($value) .
        '" ' .
        ($required ? 'required' : '') .
        ' ' .
        ($type === 'number' ? 'min="0" step="0.01"' : '') .
        '></label>';
}
