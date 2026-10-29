<?php

function requestTable(array $rows, bool $canEdit): void
{
    echo '<div class="table-wrap"><table><thead><tr><th>Request / resident</th><th>Document</th><th>Status</th><th>Fee / payment</th><th>Created</th>' .
        ($canEdit ? '<th>Next step</th>' : '') .
        '</tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr><td><strong>#' .
            str_pad((string) $r['id'], 4, '0', STR_PAD_LEFT) .
            '</strong><small>' .
            e($r['resident']) .
            '</small></td><td>' .
            e($r['document']) .
            '<small>' .
            e($r['purpose']) .
            '</small></td><td><span class="badge ' .
            ($r['status'] === 'Pending'
                ? 'amber'
                : ($r['status'] === 'Claimed'
                    ? 'gray'
                    : 'green')) .
            '">' .
            e($r['status']) .
            '</span></td><td>' .
            money((int) $r['fee']) .
            '<small>' .
            ($r['payment_id'] ? 'Paid' : ($r['fee'] ? 'Unpaid' : 'No fee')) .
            '</small></td><td>' .
            e(substr($r['created_at'], 0, 10)) .
            '</td>';
        if ($canEdit) {
            echo '<td>';
            if ($r['status'] !== 'Claimed') {
                formStart('status', (int) $r['id']);
                echo '<input type="hidden" name="status" value="' .
                    ($r['status'] === 'Pending' ? 'Ready for Pickup' : 'Claimed') .
                    '"><button class="secondary small">' .
                    ($r['status'] === 'Pending' ? 'Mark ready' : 'Mark claimed') .
                    '</button></form>';
            } else {
                echo 'Complete';
            }
            echo '</td>';
        }
        echo '</tr>';
    }
    if (!$rows) {
        echo '<tr><td colspan="6" class="empty">No requests found. Create a request to get started.</td></tr>';
    }
    echo '</tbody></table></div>';
}
