<?php
$edit = query('SELECT * FROM types WHERE id=?', [
                    (int) ($_GET['edit'] ?? 0),
                ])->fetch(); ?>
<div class="type-grid"><?php foreach (
    query('SELECT * FROM types ORDER BY name')
    as $t
): ?>
    <section class="card type-card">
        <div class="type-icon">▤</div><span class="badge <?= $t[
            'active'
        ]
            ? 'green'
            : 'gray' ?>"><?= $t['active'] ? 'Available' : 'Inactive' ?></span>
        <h2><?= e(
            $t['name'],
        ) ?></h2>
        <div class="price"><?= money(
            (int) $t['fee'],
        ) ?></div>
        <p class="muted">Requirements</p>
        <p><?= e(
            $t['requirements'],
        ) ?></p><a href="?page=types&edit=<?= $t[
            'id'
        ] ?>#new">Edit document →</a>
    </section><?php endforeach; ?>
</div>
<section class="card form-card" id="new">
    <h2><?= $edit
    ? 'Edit document type'
    : 'Add document type' ?></h2><?php
    formStart('type', (int) ($edit['id'] ?? 0));
    input('Document name', 'name', $edit['name'] ?? '');
    input('Fee (PHP)', 'fee', isset($edit['fee']) ? (string) ($edit['fee'] / 100) : '', 'number');
    input('Requirements', 'requirements', $edit['requirements'] ?? '');
    if ($edit): ?><label class="checkbox"><input type="checkbox" name="active" <?= $edit['active']
    ? 'checked'
    : '' ?>> Available for new requests</label><?php endif;
    ?><button>Save document type</button></form>
</section>
