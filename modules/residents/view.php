<?php
$edit = query('SELECT * FROM residents WHERE id=?', [
                (int) ($_GET['edit'] ?? 0),
            ])->fetch(); ?>
<section class="card">
    <div class="card-head">
        <h2>Resident directory</h2><a class="button" href="#new">+ Add resident</a>
    </div>
    <form class="filters"><input type="hidden" name="page" value="residents"><input name="q" placeholder="Search name or address…" aria-label="Search residents" value="<?= e(
        $q,
    ) ?>"><button class="secondary">Search</button><a href="?page=residents">Reset</a></form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Birth date</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody><?php
            $residents = query('SELECT * FROM residents WHERE name LIKE ? OR address LIKE ? ORDER BY name', [
                '%' . $q . '%',
                '%' . $q . '%',
            ])->fetchAll();
            foreach ($residents as $r): ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong></td>
                    <td><?= e(
                        $r['address'],
                    ) ?></td>
                    <td><?= e($r['birthdate']) ?></td>
                    <td><?= e(
                        $r['phone'],
                    ) ?></td>
                    <td>
                        <div class="actions"><a href="?page=residents&edit=<?= $r[
                            'id'
                        ] ?>#new">Edit</a><?php formStart(
                            'delete_resident',
                            (int) $r['id'],
                        ); ?><button class="danger small" data-confirm="Delete this resident record? This cannot be undone.">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr><?php endforeach;
                if (
                    !$residents
                ): ?>
                <tr>
                    <td colspan="5" class="empty">No residents match your search.</td>
                </tr><?php endif;
                ?>
            </tbody>
        </table>
    </div>
</section>
<section class="card form-card" id="new">
    <h2><?= $edit
    ? 'Edit resident'
    : 'Add a resident' ?></h2><?php
    formStart('resident', (int) ($edit['id'] ?? 0));
    input('Full name', 'name', $edit['name'] ?? '');
    input('Complete address', 'address', $edit['address'] ?? '');
    input('Birth date', 'birthdate', $edit['birthdate'] ?? '', 'date');
    input('Phone number', 'phone', $edit['phone'] ?? '', 'tel', false);
    ?><button>Save resident</button><?php if (
        $edit
    ): ?><a href="?page=residents">Cancel edit</a><?php endif; ?></form>
</section>
