
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT title, content, created_at
     FROM notes
     WHERE user_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report {
            background: white;
            padding: 30px;
            margin: 25px auto;
            max-width: 850px;
            border-radius: 12px;
        }

        .report-note {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
            overflow-wrap: anywhere;
        }

        .print-button {
            background: #0861c5;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 25px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        @media print {
            .topbar, footer, .print-button, .back-link {
                display: none !important;
            }

            body, .frame, main {
                background: white !important;
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
            }

            .report {
                margin: 0;
                padding: 0;
                max-width: none;
            }

            .report-note {
                break-inside: avoid;
            }
        }
    </style>
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
        <div class="report">
            <h1>Personal Notes Report</h1>
            <p>
                Prepared by:
                <?= htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p>Date: <?= date('F d, Y') ?></p>
            <hr>

            <?php if (!$notes): ?>
                <p>No notes available to print.</p>
            <?php else: ?>
                <?php foreach ($notes as $index => $note): ?>
                    <section class="report-note">
                        <h3>
                            <?= $index + 1 ?>.
                            <?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p><?= nl2br(htmlspecialchars($note['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <small>
                            Created:
                            <?= htmlspecialchars($note['created_at'], ENT_QUOTES, 'UTF-8') ?>
                        </small>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>

            <p>Total notes: <?= count($notes) ?></p>

            <button class="print-button" onclick="window.print()">
                Print Report
            </button>
            <p class="back-link">
                <a href="dashboard.php">Back to Dashboard</a>
            </p>
        </div>
    </main>

    <footer>© 2026 Personal Notes Management System</footer>
</div>
</body>
</html>
