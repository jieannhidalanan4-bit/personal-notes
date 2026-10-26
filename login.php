
<?php
session_start();
require_once 'db.php';

$message = '';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $message = 'Please enter a valid email and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password
             FROM users
             WHERE email = ?'
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: dashboard.php');
            exit;
        } else {
            $message = 'Incorrect email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Personal Notes Management System</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="frame">

        <header class="topbar">
            <a class="brand" href="index.html">
                📝 PERSONAL NOTES
            </a>

            <nav>
                <a href="index.html">Home</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </nav>
        </header>

        <main class="form-page">
            <section class="form-card">

                <h2>Welcome Back!</h2>

                <p>
                    Log in to access your Personal Notes Management System.
                </p>

                <?php if ($message !== ''): ?>
                    <p role="alert">
                        <?= htmlspecialchars(
                            $message,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>
                <?php endif; ?>

                <form method="POST" action="login.php">

                    <label for="email">Email Address</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        value="<?= htmlspecialchars(
                            $_POST['email'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >

                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button type="submit">Login</button>
                </form>

                <p>
                    Don't have an account?
                    <a href="register.php">Create an account</a>
                </p>

                <p>
                    <a href="index.html">← Back to Home</a>
                </p>

            </section>
        </main>

        <footer>
            © 2026 Personal Notes Management System
        </footer>

    </div>
</body>
</html>
