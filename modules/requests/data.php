<?php

function loadRequests(array $user, bool $staff, string $q = '', string $filter = ''): array
{
    $where = $staff ? '1=1' : 'r.resident_id=' . (int) ($user['resident_id'] ?? 0);
    $args = [];
    if ($q !== '') {
        $where .= ' AND (n.name LIKE ? OR t.name LIKE ?)';
        $args = ['%' . $q . '%', '%' . $q . '%'];
    }
    if (in_array($filter, ['Pending', 'Ready for Pickup', 'Claimed'], true)) {
        $where .= ' AND r.status=?';
        $args[] = $filter;
    }
    return $user
        ? query(
            "SELECT r.*,n.name resident,t.name document,p.id payment_id,p.amount paid,p.reference FROM requests r JOIN residents n ON n.id=r.resident_id JOIN types t ON t.id=r.type_id LEFT JOIN payments p ON p.request_id=r.id WHERE $where ORDER BY r.id DESC",
            $args,
        )->fetchAll()
        : [];
}
