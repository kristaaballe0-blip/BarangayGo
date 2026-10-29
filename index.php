<?php
require __DIR__ . '/db.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/modules/requests/data.php';
require __DIR__ . '/modules/requests/table.php';

// The registry is the single source of truth for navigation, roles, views, and actions.
$modules = require __DIR__ . '/modules/registry.php';
$pages = array_map(fn(array $module): string => $module['title'], $modules);
$page = $_GET['page'] ?? 'dashboard';
if (!isset($modules[$page])) {
    $page = 'dashboard';
}
$authMode = ($_GET['auth'] ?? '') === 'register' ? 'register' : 'login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            throw new RuntimeException('This form expired. Refresh the page and try again.');
        }
        $action = $_POST['action'] ?? '';
        $id = (int) ($_POST['id'] ?? 0);
        $handler = null;
        foreach ($modules as $definition) {
            if (isset($definition['actions'][$action])) {
                $handler = $definition['actions'][$action];
                break;
            }
        }
        if (!$handler) {
            throw new RuntimeException('Unknown action.');
        }
        if (!in_array($action, ['login', 'register'], true) && !$user) {
            throw new RuntimeException('Please sign in.');
        }
        if (in_array($action, ['login', 'register'], true) && $user) {
            throw new RuntimeException('Sign out before using another account.');
        }
        require_once $handler['file'];
        $handler['function']($id, $user);
        $_SESSION['flash'] =
            $action === 'register'
                ? 'Account created. You can now sign in.'
                : 'Changes saved successfully.';
        header(
            'Location: ' .
                ($action === 'register'
                    ? 'index.php?auth=login'
                    : 'index.php?page=' . urlencode($page)),
        );
        exit();
    } catch (Throwable $exception) {
        $error =
            $exception instanceof PDOException
                ? 'Unable to save. Check for duplicate records and valid selections.'
                : $exception->getMessage();
        if (($_POST['action'] ?? '') === 'register') {
            $authMode = 'register';
        }
    }
}

$staff = $user && $user['role'] === 'staff';
if ($user && $modules[$page]['staffOnly'] && !$staff) {
    $page = 'dashboard';
}
$module = $modules[$page];
$q = trim($_GET['q'] ?? '');
$filter = $_GET['status'] ?? '';
$requests =
    $user && in_array($page, ['dashboard', 'requests', 'payments', 'reports'], true)
        ? loadRequests($user, $staff, $q, $filter)
        : [];

if ($user && $staff && $page === 'reports' && ($_GET['export'] ?? '') === 'excel') {
    require __DIR__ . '/modules/reports/export.php';
}

require __DIR__ . '/views/layout.php';
