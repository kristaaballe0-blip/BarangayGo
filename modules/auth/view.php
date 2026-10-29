<main class="login">
    <section class="login-intro">
        <div class="brand">◈ BarangayGo</div>
        <span class="eyebrow">YOUR COMMUNITY, CONNECTED</span>
        <h1>Better service.<br>Stronger community.</h1>
        <p>A simpler way to manage resident records, request documents, and keep barangay services moving.</p>
        <div class="intro-note">Resident services, thoughtfully organized.</div>
    </section>
    <section class="login-form">
        <span class="eyebrow"><?= $authMode === 'register' ? 'JOIN YOUR COMMUNITY' : 'WELCOME BACK' ?></span>
        <h2><?= $authMode === 'register' ? 'Create your resident account' : 'Sign in to BarangayGo' ?></h2>
        <p class="muted"><?= $authMode === 'register' ? 'Register to request documents and track their progress.' : 'Use your staff or resident account to continue.' ?></p>
        <?php if ($error): ?>
        <div class="alert error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert" role="status"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        <?php if ($authMode === 'register'): ?>
        <?php formStart('register'); ?>
        <?php input('Full name', 'name', $_POST['name'] ?? ''); ?>
        <?php input('Complete address', 'address', $_POST['address'] ?? ''); ?>
        <?php input('Birth date', 'birthdate', $_POST['birthdate'] ?? '', 'date'); ?>
        <?php input('Phone number', 'phone', $_POST['phone'] ?? '', 'tel', false); ?>
        <?php input('Email address', 'email', $_POST['email'] ?? '', 'email'); ?>
        <label>Password (at least 12 characters) <input type="password" name="password" minlength="12" maxlength="72" autocomplete="new-password" required>
        </label>
        <label>Confirm password <input type="password" name="password_confirmation" minlength="12" maxlength="72" autocomplete="new-password" required>
        </label>
        <button>Create account →</button>
        </form>
        <p class="auth-switch">Already have an account? <a href="?auth=login">Sign in</a></p>
        <?php else: ?>
        <?php formStart('login'); ?>
        <label>Email address <input type="email" name="email" autocomplete="username" required>
        </label>
        <label>Password <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button>Sign in →</button>
        </form>
        <p class="auth-switch">New to BarangayGo? <a href="?auth=register">Create account</a></p>
        <details class="demo">
            <summary>Local demo accounts</summary>
            <p>Staff: admin@barangay.local<br>BarangayAdmin!2026</p>
            <p>Resident: resident@barangay.local<br>Resident!2026</p>
            <small>Change these credentials before deployment.</small>
        </details>
        <?php endif; ?>
    </section>
</main>
