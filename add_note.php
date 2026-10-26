
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];

    if ($title === '' || $content === '') {
        $message = 'Please fill in both fields.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO notes (user_id, title, content)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$user_id, $title, $content]);

        header('Location: notes.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add a Note - Personal Notes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="frame">
        <header class="topbar">
            <a class="brand" href="dashboard.php">📝 PERSONAL NOTES</a>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="notes.php">My Notes</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main class="form-page">
            <form class="form-card" method="POST" action="add_note.php">
                <h2>Add a Note</h2>
                <p>Create a new note, assignment, or reminder.</p>

                <?php if ($message !== ''): ?>
                    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <label for="title">Note Title</label>
                <input id="title" name="title" type="text"
                       placeholder="Enter note title" required>

                <label for="content">Note Content</label>
                <textarea id="content" name="content" rows="6"
                          placeholder="Write your note here..." required></textarea>

                <button class="button primary" type="submit">Save Note</button>
                <a href="dashboard.php">Cancel</a>
            </form>
        </main>

        <footer>© 2026 Personal Notes Management System</footer>
    </div>
</body>
</html>
