<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e(
        $pages[$page],
    ) ?> · BarangayGo</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php if (
        !$user
    ): ?>
    <?php require __DIR__ . '/../modules/auth/view.php'; ?>
    <?php else: ?>
    <aside class="sidebar"><a class="brand" href="index.php">◈ BarangayGo</a>
        <div class="workspace">COMMUNITY WORKSPACE</div>
        <nav><?php
        foreach ($pages as $key => $label):
            if (!$staff && $modules[$key]['staffOnly']) {
                continue;
            } ?><a class="<?= $page === $key ? 'active' : '' ?>" href="?page=<?= $key ?>"><span><?= $modules[$key]['icon'] ?></span><?= e($label) ?></a><?php
        endforeach;
        ?></nav>
        <div class="sidebar-bottom">
            <div class="avatar"><?= e(
                mb_substr($user['name'], 0, 1),
            ) ?></div>
            <div><strong><?= e($user['name']) ?></strong><small><?= ucfirst(
                $user['role'],
            ) ?> account</small></div><?php formStart(
                'logout',
            ); ?><button class="signout" title="Sign out" aria-label="Sign out">↗</button></form>
        </div>
    </aside>
    <div class="app">
        <header>
            <div>Barangay services <span>/ <?= e(
                $pages[$page],
            ) ?></span></div>
            <div class="header-right"><span class="online">● System online</span><span><?= date(
                'M j, Y',
            ) ?></span></div>
        </header>
        <main class="content">
            <div class="page-title">
                <div><span class="eyebrow">BARANGAY MANAGEMENT</span>
                    <h1><?= e(
                        $pages[$page],
                    ) ?></h1>
                    <p class="muted"><?= e(
                        $module['description'],
                    ) ?></p>
                </div><?php if (
                    $page === 'dashboard'
                ): ?><a class="button" href="?page=requests#new">+ New request</a><?php endif; ?>
            </div>
            <?php
            if ($error): ?>
            <div class="alert error" role="alert"><?= e($error) ?></div><?php endif;
            if (isset($_SESSION['flash'])): ?>
            <div class="alert" role="status"><?= e(
                $_SESSION['flash'],
            ) ?></div><?php unset($_SESSION['flash']);endif;
            ?>
            <?php require $module['view']; ?>
            <footer>BarangayGo <span>Community service, made simpler.</span></footer>
        </main>
    </div><?php endif; ?>
    <script src="assets/app.js"></script>
</body>

</html>
