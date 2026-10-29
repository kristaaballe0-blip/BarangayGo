<?php

function createRequestAction(int $id, array|false $user): void
{
    $rid = $user['role'] === 'staff' ? (int) required('resident_id') : (int) $user['resident_id'];
    $type = query('SELECT * FROM types WHERE id=? AND active=1', [
        (int) required('type_id'),
    ])->fetch();
    if (!$type) {
        throw new RuntimeException('Select an available document type.');
    }
    $purpose = required('purpose');
    // Transaction 2: document request, captured fee and audit.
    transaction(function () use ($rid, $type, $purpose) {
        query('INSERT INTO requests(resident_id,type_id,purpose,fee) VALUES(?,?,?,?)', [
            $rid,
            $type['id'],
            $purpose,
            $type['fee'],
        ]);
        audit('Created document request #' . query('SELECT LAST_INSERT_ID()')->fetchColumn());
    });
}

function updateRequestStatusAction(int $id, array|false $user): void
{
    staff();
    $status = required('status');
    transaction(function () use ($id, $status) {
        $r = query('SELECT * FROM requests WHERE id=?', [$id])->fetch();
        if (!$r) {
            throw new RuntimeException('Request not found.');
        }
        $next = ['Pending' => 'Ready for Pickup', 'Ready for Pickup' => 'Claimed'];
        if (($next[$r['status']] ?? null) !== $status) {
            throw new RuntimeException('Choose the next status in the request workflow.');
        }
        if (
            $status === 'Claimed' &&
            $r['fee'] > 0 &&
            !query('SELECT id FROM payments WHERE request_id=?', [$id])->fetch()
        ) {
            throw new RuntimeException('Record payment before marking this request claimed.');
        }
        query('UPDATE requests SET status=? WHERE id=?', [$status, $id]);
        audit('Request #' . $id . ' marked ' . $status);
    });
}
