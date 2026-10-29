<?php
?>
<section class="card">
    <div class="card-head">
        <h2>Recent system activity</h2><span class="muted">Latest 200 events</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Account</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody><?php foreach (
                query(
                    'SELECT l.*,u.name FROM logs l LEFT JOIN users u ON l.user_id=u.id ORDER BY l.id DESC LIMIT 200',
                )
                as $l
            ): ?>
                <tr>
                    <td><?= e($l['created_at']) ?></td>
                    <td><?= e($l['name'] ?? 'System') ?></td>
                    <td><?= e(
                        $l['action'],
                    ) ?></td>
                </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
