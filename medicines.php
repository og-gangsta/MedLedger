<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$active = 'medicines';
$pageTitle = 'Medicines · MedLedger';

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    // Prepared statement with bound parameter — safe against SQL injection.
    $stmt = $pdo->prepare(
        "SELECT m.*, c.name AS category_name
         FROM medicines m
         LEFT JOIN categories c ON c.id = m.category_id
         WHERE m.name LIKE ? OR m.brand LIKE ? OR m.batch_no LIKE ?
         ORDER BY m.name ASC"
    );
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query(
        "SELECT m.*, c.name AS category_name
         FROM medicines m
         LEFT JOIN categories c ON c.id = m.category_id
         ORDER BY m.name ASC"
    );
}
$medicines = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div>
    <p class="eyebrow">Inventory</p>
    <h1>Medicines</h1>
    <p class="desc">Full stock list — search, add, edit or remove items.</p>
  </div>
  <a href="medicine_form.php" class="btn btn-amber">+ Add Medicine</a>
</div>

<div class="panel">
  <div class="toolbar">
    <form method="get" class="search-box">
      <input type="text" name="q" placeholder="Search name, brand or batch no…" value="<?= h($search) ?>">
    </form>
    <span class="desc"><?= count($medicines) ?> item(s)</span>
  </div>

  <div class="table-wrap">
  <?php if (empty($medicines)): ?>
    <div class="empty-state">
      <div class="glyph">⌀</div>
      No medicines found. <a href="medicine_form.php">Add the first one</a>.
    </div>
  <?php else: ?>
    <table class="ledger">
      <thead>
        <tr>
          <th>Medicine</th>
          <th>Category</th>
          <th>Batch</th>
          <th>Qty</th>
          <th>Unit price</th>
          <th>Expiry</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($medicines as $m):
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
          <td class="batch">$<?= number_format((float) $m['unit_price'], 2) ?></td>
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
          <td>
            <div class="row-actions">
              <a href="medicine_form.php?id=<?= (int) $m['id'] ?>">Edit</a>
              <form class="confirm-delete" method="post" action="medicine_delete.php" style="display:inline">
                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                <button type="submit" class="del">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
