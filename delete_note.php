
<?php
session_start();
require_once 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only allow deletion through a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: notes.php');
    exit;
}

// Get the note ID
$noteId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$noteId || $noteId < 1) {
    header('Location: notes.php');
    exit;
}

// Delete only a note owned by the logged-in user
$stmt = $pdo->prepare(
    'DELETE FROM notes WHERE id = ? AND user_id = ?'
);

$stmt->execute([$noteId, $_SESSION['user_id']]);

// Return to the notes page
header('Location: notes.php');
exit;
?>
