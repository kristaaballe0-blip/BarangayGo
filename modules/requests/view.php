<?php
?>
<section class="card">
    <div class="card-head">
        <h2><?= $page === 'reports'
        ? 'Transaction report'
        : 'All requests' ?> <span class="count"><?= count($requests) ?></span></h2><?php if (
            $page === 'reports'
        ): ?>
        <div class="actions"><a class="button secondary" href="?<?= e(
            http_build_query(array_merge($_GET, ['export' => 'excel'])),
        ) ?>">Export Excel</a><button class="secondary" onclick="window.print()">Print / Save PDF</button>
        </div><?php endif; ?>
    </div>
    <form class="filters" method="get"><input type="hidden" name="page" value="<?= e(
        $page,
    ) ?>"><input aria-label="Search requests" name="q" placeholder="Search resident or document type…" value="<?= e(
        $q,
    ) ?>"><select name="status" aria-label="Filter status">
            <option value="">All statuses</option><?php foreach (
                ['Pending', 'Ready for Pickup', 'Claimed']
                as $s
            ): ?>
            <option <?= $filter === $s ? 'selected' : '' ?>><?= e(
                $s,
            ) ?></option><?php endforeach; ?>
        </select><button class="secondary">Filter</button><a href="?page=<?= e(
            $page,
        ) ?>">Reset</a></form><?php requestTable(
            $requests,
            $staff && $page === 'requests',
        ); ?>
</section><?php if (
    $page === 'requests'
): ?>
<section class="card form-card" id="new">
    <h2>New document request</h2>
    <p class="muted">Review the requirements before submitting your request.</p><?php
    formStart('request');
    if (
        $staff
    ): ?><label>Resident<select name="resident_id" required>
            <option value="">Select resident</option><?php foreach (
                query('SELECT * FROM residents ORDER BY name')
                as $r
            ): ?>
            <option value="<?= $r['id'] ?>"><?= e(
                $r['name'],
            ) ?></option><?php endforeach; ?>
        </select></label><?php endif;
        ?><label>Document type<select name="type_id" required>
            <option value="">Select document</option><?php foreach (
                query('SELECT * FROM types WHERE active=1')
                as $t
            ): ?>
            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> · <?= money((int) $t['fee']) ?> — <?= e(
                $t['requirements'],
            ) ?></option><?php endforeach; ?>
        </select></label><?php input(
            'Purpose of request',
            'purpose',
        ); ?><button>Submit request</button></form>
</section><?php endif; ?>
