<?php

function recordPaymentAction(int $id, array|false $user): void
{
    staff();
    $rid = (int) required('request_id');
    $paid = amount('amount');
    $reference = required('reference');
    // Transaction 3: validated payment and audit, with rollback on duplicate or error.
    transaction(function () use ($rid, $paid, $reference) {
        $r = query('SELECT * FROM requests WHERE id=?', [$rid])->fetch();
        if (!$r || $r['fee'] <= 0 || $paid !== (int) $r['fee']) {
            throw new RuntimeException('Payment must equal the document fee.');
        }
        if (query('SELECT id FROM payments WHERE request_id=?', [$rid])->fetch()) {
            throw new RuntimeException('This request has already been paid.');
        }
        query('INSERT INTO payments(request_id,amount,reference) VALUES(?,?,?)', [
            $rid,
            $paid,
            $reference,
        ]);
        audit('Recorded payment for request #' . $rid);
    });
}
