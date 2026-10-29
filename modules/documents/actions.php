<?php

function saveDocumentTypeAction(int $id, array|false $user): void
{
    staff();
    $name = required('name');
    $fee = amount('fee');
    $requirements = required('requirements');
    transaction(function () use ($id, $name, $fee, $requirements) {
        if ($id) {
            query('UPDATE types SET name=?,fee=?,requirements=?,active=? WHERE id=?', [
                $name,
                $fee,
                $requirements,
                isset($_POST['active']) ? 1 : 0,
                $id,
            ]);
        } else {
            query('INSERT INTO types(name,fee,requirements) VALUES(?,?,?)', [
                $name,
                $fee,
                $requirements,
            ]);
        }
        audit('Saved document type: ' . $name);
    });
}
