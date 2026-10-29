<?php
?>
<section class="card">
    <div class="card-head">
        <h2>Payment ledger</h2><span class="badge green">Total collected <?= money(
            (int) query('SELECT COALESCE(SUM(amount),0) FROM payments')->fetchColumn(),
        ) ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt reference</th>
                    <th>Request</th>
                    <th>Resident</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody><?php
            $payments = query(
                'SELECT p.*,n.name FROM payments p JOIN requests r ON p.request_id=r.id JOIN residents n ON r.resident_id=n.id ORDER BY p.id DESC',
            )->fetchAll();
            foreach ($payments as $p): ?>
                <tr>
                    <td><?= e($p['reference']) ?></td>
                    <td>#<?= $p[
                        'request_id'
                    ] ?></td>
                    <td><?= e($p['name']) ?></td>
                    <td><?= money((int) $p['amount']) ?></td>
                    <td><?= e(
                        $p['created_at'],
                    ) ?></td>
                </tr><?php endforeach;
                if (!$payments): ?>
                <tr>
                    <td colspan="5" class="empty">No payments recorded yet.</td>
                </tr><?php endif;
                ?>
            </tbody>
        </table>
    </div>
</section>
<section class="card form-card" id="new">
    <h2>Record payment</h2>
    <p class="muted">Enter the full fee and the official receipt reference.</p><?php formStart(
        'payment',
    ); ?><label>Unpaid request<select name="request_id" required>
            <option value="">Select request</option><?php foreach (
                $requests
                as $r
            ):
                if ($r['payment_id'] || !$r['fee']) {
                    continue;
                } ?>
            <option value="<?= $r['id'] ?>">#<?= $r['id'] ?> · <?= e($r['resident']) ?> · <?= e(
                $r['document'],
            ) ?> · <?= money((int) $r['fee']) ?></option><?php
            endforeach; ?>
        </select></label><?php
        input('Amount received (PHP)', 'amount', '', 'number');
        input('Official receipt reference', 'reference');
        ?><button>Record payment</button></form>
</section>
