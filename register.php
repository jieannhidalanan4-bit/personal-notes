
<?php
session_start();
require_once 'db.php';

$message = '';
$success = false;
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Allow letters, spaces, apostrophes, and hyphens in names.
    if (
        $name === '' ||
        !preg_match("/^[\p{L}\p{M}]+(?:[ '-][\p{L}\p{M}]+)*$/u", $name)
    ) {
        $message = 'Please enter a valid name using letters only.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
    } else {
        $check = $pdo->prepare(
            'SELECT id FROM users WHERE email = ?'
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            $message = 'This email is already registered.';
        } else {
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)'
            );

            $stmt->execute([
                $name,
                $email,
                $hashedPassword
            ]);

            $success = true;
            $message = 'Registration successful! You can now log in.';
            $name = '';
            $email = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Personal Notes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="frame">
        <header class="topbar">
            <a class="brand" href="index.html">📝 PERSONAL NOTES</a>
            <nav>
                <a href="index.html">Home</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </nav>
        </header>

        <main class="form-page">
            <section class="form-card">
                <h2>Create an Account</h2>

                <?php if ($message !== ''): ?>
                    <p role="status">
                        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <?php if (!$success): ?>
                    <form method="POST" action="register.php">
                        <label for="name">Full Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            required
                        >

                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Enter your email"
                            autocomplete="email"
                            required
                        >

                        <label for="password">Password (at least 8 characters)</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                        <button type="submit">Register</button>
                    </form>
                <?php else: ?>
                    <p><a class="button primary" href="login.php">Go to Login</a></p>
                <?php endif; ?>

                <p><a href="login.php">Back to Login</a></p>
                <p><a href="index.html">Back to Home</a></p>
            </section>
        </main>

        <footer>© 2026 Personal Notes Management System</footer>
    </div>

    <script>
        const nameInput = document.getElementById('name');

        if (nameInput) {
            nameInput.addEventListener('input', function () {
                // Remove numbers and other unsupported characters as the user types.
                this.value = this.value.replace(/[^\p{L}\p{M} '\-]/gu, '');
            });
        }
    </script>
</body>
</html>
