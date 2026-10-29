<?php
?>
<section class="card form-card">
    <h2>Account details</h2>
    <p class="muted">Your current password is required to save changes.</p><?php
    formStart('account');
    input('Full name', 'name', $user['name']);
    input('Email address', 'email', $user['email'], 'email');
    input('Current password', 'current_password', '', 'password');
    input('New password (optional, at least 12 characters)', 'new_password', '', 'password', false);
    ?><button>Save account details</button></form>
</section>
