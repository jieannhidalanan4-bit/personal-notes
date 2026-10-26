
<?php
session_start();
require_once 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the search keyword
$search = trim($_GET['search'] ?? '');

// Get the logged-in user's notes
if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT id, title, content, created_at
         FROM notes
         WHERE user_id = ?
         AND (title LIKE ? OR content LIKE ?)
         ORDER BY created_at DESC"
    );

    $keyword = '%' . $search . '%';
    $stmt->execute([
        $_SESSION['user_id'],
        $keyword,
        $keyword
    ]);
} else {
    $stmt = $pdo->prepare(
        'SELECT id, title, content, created_at
         FROM notes
         WHERE user_id = ?
         ORDER BY created_at DESC'
    );

    $stmt->execute([$_SESSION['user_id']]);
}

$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes - Personal Notes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="frame">

        <header class="topbar">
            <a class="brand" href="dashboard.php">
                📝 PERSONAL NOTES
            </a>

            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="add_note.php">Add Note</a>
                <a href="print_report.php">Print Report</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main class="form-page">
            <div class="form-card">

                <h2>My Notes</h2>
                <p>Manage and search your saved notes.</p>

                <p>
                    <a class="button primary" href="add_note.php">
                        + Add a Note
                    </a>
                </p>

                <!-- Search form -->
                <form method="GET" action="notes.php">
                    <label for="search">Search Notes</label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        placeholder="Search by title or content..."
                        value="<?= htmlspecialchars(
                            $search,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <button type="submit">Search</button>

                    <?php if ($search !== ''): ?>
                        <a href="notes.php">Clear Search</a>
                    <?php endif; ?>
                </form>

                <hr>

                <?php if (count($notes) === 0): ?>

                    <?php if ($search !== ''): ?>
                        <p>No notes found for
                            "<?= htmlspecialchars(
                                $search,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>".
                        </p>
                    <?php else: ?>
                        <p>
                            You don't have any notes yet.
                            Add your first note!
                        </p>
                    <?php endif; ?>

                <?php else: ?>

                    <?php foreach ($notes as $note): ?>

                        <article class="note-card">

                            <h3>
                                <?= htmlspecialchars(
                                    $note['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h3>

                            <p>
                                <?= nl2br(htmlspecialchars(
                                    $note['content'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )) ?>
                            </p>

                            <small>
                                Created:
                                <?= htmlspecialchars(
                                    $note['created_at'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </small>

                            <p>
                                <a
                                    class="button primary"
                                    href="edit_note.php?id=<?= (int)$note['id'] ?>"
                                >
                                    Edit
                                </a>
                            </p>

                            <form
                                method="POST"
                                action="delete_note.php"
                                onsubmit="return confirm('Are you sure you want to delete this note?');"
                            >
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$note['id'] ?>"
                                >

                                <button type="submit">Delete</button>
                            </form>

                        </article>

                    <?php endforeach; ?>

                <?php endif; ?>

                <p>
                    <a href="dashboard.php">Back to Dashboard</a>
                </p>

            </div>
        </main>

        <footer>
            © 2026 Personal Notes Management System
        </footer>

    </div>
</body>
</html>
