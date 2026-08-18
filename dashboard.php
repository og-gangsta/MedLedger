<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$active = 'dashboard';
$pageTitle = 'Dashboard · MedLedger';

$totalMedicines = (int) $pdo->query('SELECT COUNT(*) FROM medicines')->fetchColumn();
$totalCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$lowStockCount = (int) $pdo->query('SELECT COUNT(*) FROM medicines WHERE quantity <= reorder_level')->fetchColumn();
$expiredCount = (int) $pdo->query('SELECT COUNT(*) FROM medicines WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()')->fetchColumn();

$stockValueStmt = $pdo->query('SELECT COALESCE(SUM(quantity * unit_price), 0) FROM medicines');
$stockValue = (float) $stockValueStmt->fetchColumn();

// Items needing attention: low stock or expired/near-expiry, soonest first.
$attentionStmt = $pdo->query(
    "SELECT m.id, m.name, m.brand, m.batch_no, m.quantity, m.reorder_level, m.expiry_date, c.name AS category_name
     FROM medicines m
     LEFT JOIN categories c ON c.id = m.category_id
     WHERE m.quantity <= m.reorder_level
        OR (m.expiry_date IS NOT NULL AND m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY))
     ORDER BY m.expiry_date ASC
     LIMIT 8"
);
$attention = $attentionStmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div>
    <p class="eyebrow">Overview</p>
    <h1>Dashboard</h1>
    <p class="desc">Live snapshot of stock levels, expiry risk and inventory value.</p>
  </div>
  <a href="medicine_form.php" class="btn btn-amber">+ Add Medicine</a>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <p class="label">Medicines in stock</p>
    <p class="value"><?= $totalMedicines ?></p>
    <p class="sub"><?= $totalCategories ?> categories tracked</p>
  </div>
  <div class="stat-card warn">
    <p class="label">Low stock</p>
    <p class="value"><?= $lowStockCount ?></p>
    <p class="sub">At or below reorder level</p>
  </div>
  <div class="stat-card danger">
    <p class="label">Expired</p>
    <p class="value"><?= $expiredCount ?></p>
    <p class="sub">Past expiry date — remove from shelf</p>
  </div>
  <div class="stat-card">
    <p class="label">Stock value</p>
    <p class="value">$<?= number_format($stockValue, 2) ?></p>
    <p class="sub">Quantity × unit price</p>
  </div>
</div>

<div class="panel">
  <h2>Needs attention</h2>
  <div class="table-wrap">
  <?php if (empty($attention)): ?>
    <div class="empty-state">
      <div class="glyph">✓</div>
      Nothing urgent — all stock levels and expiry dates look healthy.
    </div>
  <?php else: ?>
    <table class="ledger">
      <thead>
        <tr>
          <th>Medicine</th>
          <th>Category</th>
          <th>Batch</th>
          <th>Qty</th>
          <th>Expiry</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($attention as $m):
            $isExpired = $m['expiry_date'] && $m['expiry_date'] < date('Y-m-d');
            $isLow = $m['quantity'] <= $m['reorder_level'];
            $rowClass = $isExpired ? 'status-expired' : ($isLow ? 'status-low' : 'status-ok');
        ?>
        <tr class="<?= $rowClass ?>">
          <td>
            <div class="med-name"><?= h($m['name']) ?></div>
            <div class="med-brand"><?= h($m['brand'] ?: '—') ?></div>
          </td>
          <td><?= h($m['category_name'] ?: 'Uncategorized') ?></td>
          <td class="batch"><?= h($m['batch_no'] ?: '—') ?></td>
          <td><?= (int) $m['quantity'] ?></td>
          <td class="batch"><?= h($m['expiry_date'] ?: '—') ?></td>
          <td>
            <?php if ($isExpired): ?>
              <span class="badge badge-expired">Expired</span>
            <?php elseif ($isLow): ?>
              <span class="badge badge-low">Low stock</span>
            <?php else: ?>
              <span class="badge badge-ok">OK</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
