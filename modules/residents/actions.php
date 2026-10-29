<?php

function saveResidentAction(int $id, array|false $user): void
{
    staff();
    $name = required('name');
    $address = required('address');
    $dob = required('birthdate');
    $date = DateTime::createFromFormat('!Y-m-d', $dob);
    if (!$date || $date->format('Y-m-d') !== $dob || $dob > date('Y-m-d')) {
        throw new RuntimeException('Enter a valid birth date.');
    }
    // Transaction 1: resident record plus staff audit.
    transaction(function () use ($id, $name, $address, $dob) {
        if ($id) {
            query('UPDATE residents SET name=?,address=?,birthdate=?,phone=? WHERE id=?', [
                $name,
                $address,
                $dob,
                trim($_POST['phone'] ?? ''),
                $id,
            ]);
        } else {
            query('INSERT INTO residents(name,address,birthdate,phone) VALUES(?,?,?,?)', [
                $name,
                $address,
                $dob,
                trim($_POST['phone'] ?? ''),
            ]);
        }
        audit(($id ? 'Updated' : 'Created') . ' resident: ' . $name);
    });
}

function deleteResidentAction(int $id, array|false $user): void
{
    staff();
    transaction(function () use ($id) {
        if (
            query('SELECT COUNT(*) FROM requests WHERE resident_id=?', [$id])->fetchColumn() ||
            query('SELECT COUNT(*) FROM users WHERE resident_id=?', [$id])->fetchColumn()
        ) {
            throw new RuntimeException(
                'Residents with accounts or document requests cannot be deleted.',
            );
        }
        query('DELETE FROM residents WHERE id=?', [$id]);
        audit('Deleted resident #' . $id);
    });
}
