<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        // Prepared statement — safe deletion by primary key.
        $stmt = $pdo->prepare('DELETE FROM medicines WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Medicine deleted.');
    }
}

header('Location: medicines.php');
exit;
