
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['user_name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Personal Notes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="frame">
        <header class="topbar">
            <a class="brand" href="dashboard.php">📒 PERSONAL NOTES</a>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="notes.php">My Notes</a>
                <a href="add_note.php">Add Note</a>
                <a href="report.php">Print Report</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main class="home">
            <div class="welcome">
                <p class="eyebrow">YOUR IDEAS, IN ONE PLACE</p>
                <h1>Welcome, <?= htmlspecialchars($name) ?>!</h1>
                <h2>Personal Notes Dashboard</h2>
                <p class="intro">
                    Organize your notes, assignments, ideas, and reminders
                    in one simple place.
                </p>
                <div class="buttons">
                    <a class="button primary" href="add_note.php">Add a Note</a>
                    <a class="button light" href="notes.php">View My Notes</a>
                </div>
            </div>
        </main>

        <footer>
            © 2026 Personal Notes Management System
        </footer>
    </div>
</body>
</html>
