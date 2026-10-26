
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$noteId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noteId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!$noteId || $title === '' || $content === '') {
        $message = 'Please complete all fields.';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE notes SET title = ?, content = ?
             WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$title, $content, $noteId, $userId]);

        if ($stmt->rowCount() > 0) {
            header('Location: notes.php');
            exit;
        }

        $check = $pdo->prepare(
            'SELECT id FROM notes WHERE id = ? AND user_id = ?'
        );
        $check->execute([$noteId, $userId]);

        if ($check->fetch()) {
            header('Location: notes.php');
            exit;
        }

        $message = 'Note not found.';
    }
} else {
    if (!$noteId) {
        exit('Invalid note ID.');
    }

    $stmt = $pdo->prepare(
        'SELECT title, content FROM notes
         WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([$noteId, $userId]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$note) {
        exit('Note not found.');
    }

    $title = $note['title'];
    $content = $note['content'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note - Personal Notes</title>
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

    <main>
        <section class="report">
            <h1>Edit Note</h1>

            <?php if ($message !== ''): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form method="POST" action="edit_note.php">
                <input type="hidden" name="id"
                       value="<?= (int)$noteId ?>">

                <label for="title">Note Title</label>
                <input id="title" name="title" type="text"
                       value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                       required>

                <label for="content">Note Content</label>
                <textarea id="content" name="content" rows="7"
                          required><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>

                <button type="submit">Save Changes</button>
            </form>

            <p><a href="notes.php">Cancel</a></p>
        </section>
    </main>

    <footer>© 2026 Personal Notes Management System</footer>
</div>
</body>
</html>
