<?php
/**
 * Shared shell: sidebar nav + topbar.
 * Expects $active to be set by the including page (e.g. 'dashboard').
 */
$active = $active ?? '';
$user = current_user();
$flash = get_flash();
$initial = strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle ?? 'MedLedger') ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="topbar">
  <span class="brand">MedLedger</span>
  <button id="navToggle" aria-label="Toggle menu">☰ Menu</button>
</div>
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <div class="brand">MedLedger <span class="tag">Rx Stock</span></div>

    <nav class="nav-group">
      <p class="nav-label">Overview</p>
      <a class="nav-link <?= $active === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
        <span class="dot"></span> Dashboard
      </a>
    </nav>

    <nav class="nav-group">
      <p class="nav-label">Inventory</p>
      <a class="nav-link <?= $active === 'medicines' ? 'active' : '' ?>" href="medicines.php">
        <span class="dot"></span> Medicines
      </a>
      <a class="nav-link <?= $active === 'categories' ? 'active' : '' ?>" href="categories.php">
        <span class="dot"></span> Categories
      </a>
    </nav>

    <div class="sidebar-foot">
      <div class="user-chip">
        <div class="avatar"><?= h($initial) ?></div>
        <div class="who">
          <div class="name"><?= h($user['full_name'] ?: $user['username']) ?></div>
          <div class="role"><?= h($user['role']) ?></div>
        </div>
      </div>
      <a class="logout-link" href="logout.php">Log out</a>
    </div>
  </aside>

  <main class="main">
    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
        <?= h($flash['message']) ?>
      </div>
    <?php endif; ?>
