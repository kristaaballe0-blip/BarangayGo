<?php
$scope = $staff ? '1=1' : 'resident_id=' . (int) $user['resident_id'];
            $stats = query(
                "SELECT COUNT(*) total,COALESCE(SUM(status='Pending'),0) pending,COALESCE(SUM(status='Ready for Pickup'),0) ready,COALESCE(SUM(DATE(DATE_ADD(created_at, INTERVAL 8 HOUR))=DATE(DATE_ADD(UTC_TIMESTAMP(), INTERVAL 8 HOUR))),0) today FROM requests WHERE $scope",
            )->fetch();
            ?>
<div class="stats"><?php foreach (
    [
        ['Requests today', $stats['today'], 'Submitted today', 'blue'],
        ['Pending documents', $stats['pending'], 'Awaiting processing', 'amber'],
        ['Ready for pickup', $stats['ready'], 'Available for collection', 'green'],
        [
            $staff ? 'Registered residents' : 'Total requests',
            $staff ? query('SELECT COUNT(*) FROM residents')->fetchColumn() : $stats['total'],
            $staff ? 'People in your community' : 'Your document history',
            'purple',
        ],
    ]
    as [$title, $count, $note, $color]
): ?>
    <section class="stat">
        <div class="stat-label"><?= e(
            $title,
        ) ?><span class="stat-icon <?= $color ?>">◫</span></div><strong><?= $count ?></strong><small><?= e(
            $note,
        ) ?></small>
    </section><?php endforeach; ?>
</div>
<div class="overview-grid">
    <section class="card">
        <div class="card-head">
            <div>
                <h2>Recent requests</h2>
                <p class="muted">The latest document activity</p>
            </div><a href="?page=requests">View all →</a>
        </div><?php requestTable(
            array_slice($requests, 0, 5),
            false,
        ); ?>
    </section>
    <section class="card quick"><span class="eyebrow">GET THINGS DONE</span>
        <h2>Quick actions</h2><a href="?page=requests#new">＋ Request a document <span>→</span></a><?php if (
            $staff
        ): ?><a href="?page=residents#new">♙ Add a resident <span>→</span></a><a href="?page=payments#new">₱ Record a payment <span>→</span></a><a href="?page=reports">▥ Export a report <span>→</span></a><?php endif; ?>
        <div class="tip"><strong>A smoother pickup</strong>
            <p>Check document requirements and payment details before marking a request as claimed.</p>
        </div>
    </section>
</div>
