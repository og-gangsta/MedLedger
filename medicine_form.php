<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$active = 'medicines';

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$isEdit = $id > 0;

$medicine = [
    'name' => '', 'brand' => '', 'category_id' => '', 'batch_no' => '',
    'quantity' => 0, 'reorder_level' => 10, 'unit_price' => '', 'expiry_date' => '',
];
$errors = [];

// Load categories for the select dropdown.
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

if ($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM medicines WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('error', 'Medicine not found.');
        header('Location: medicines.php');
        exit;
    }
    $medicine = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicine['name']          = trim($_POST['name'] ?? '');
    $medicine['brand']         = trim($_POST['brand'] ?? '');
    $medicine['category_id']   = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
    $medicine['batch_no']      = trim($_POST['batch_no'] ?? '');
    $medicine['quantity']      = (int) ($_POST['quantity'] ?? 0);
    $medicine['reorder_level'] = (int) ($_POST['reorder_level'] ?? 0);
    $medicine['unit_price']    = (float) ($_POST['unit_price'] ?? 0);
    $medicine['expiry_date']   = trim($_POST['expiry_date'] ?? '') ?: null;

    if ($medicine['name'] === '') {
        $errors[] = 'Medicine name is required.';
    }
    if ($medicine['quantity'] < 0) {
        $errors[] = 'Quantity cannot be negative.';
    }
    if ($medicine['unit_price'] < 0) {
        $errors[] = 'Unit price cannot be negative.';
    }

    if (empty($errors)) {
        if ($isEdit) {
            // UPDATE — prepared statement, bound parameters.
            $stmt = $pdo->prepare(
                'UPDATE medicines
                 SET name = ?, brand = ?, category_id = ?, batch_no = ?, quantity = ?, reorder_level = ?, unit_price = ?, expiry_date = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $medicine['name'], $medicine['brand'], $medicine['category_id'], $medicine['batch_no'],
                $medicine['quantity'], $medicine['reorder_level'], $medicine['unit_price'], $medicine['expiry_date'],
                $id,
            ]);
            set_flash('success', 'Medicine updated.');
        } else {
            // INSERT — prepared statement, bound parameters.
            $stmt = $pdo->prepare(
                'INSERT INTO medicines (name, brand, category_id, batch_no, quantity, reorder_level, unit_price, expiry_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $medicine['name'], $medicine['brand'], $medicine['category_id'], $medicine['batch_no'],
                $medicine['quantity'], $medicine['reorder_level'], $medicine['unit_price'], $medicine['expiry_date'],
            ]);
            set_flash('success', 'Medicine added.');
        }
        header('Location: medicines.php');
        exit;
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Add') . ' Medicine · MedLedger';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div>
    <p class="eyebrow">Inventory</p>
    <h1><?= $isEdit ? 'Edit medicine' : 'Add medicine' ?></h1>
    <p class="desc"><?= $isEdit ? 'Update stock details for this item.' : 'Register a new medicine into inventory.' ?></p>
  </div>
  <a href="medicines.php" class="btn btn-ghost">← Back to list</a>
</div>

<div class="panel">
  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= h($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="medicine_form.php" novalidate>
    <input type="hidden" name="id" value="<?= (int) $id ?>">
    <div class="form-grid">
      <div class="field full">
        <label for="name">Medicine name *</label>
        <input type="text" id="name" name="name" required value="<?= h($medicine['name']) ?>">
      </div>

      <div class="field">
        <label for="brand">Brand</label>
        <input type="text" id="brand" name="brand" value="<?= h($medicine['brand']) ?>">
      </div>

      <div class="field">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
          <option value="">— Uncategorized —</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= ((string) $medicine['category_id'] === (string) $cat['id']) ? 'selected' : '' ?>>
              <?= h($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="batch_no">Batch number</label>
        <input type="text" id="batch_no" name="batch_no" value="<?= h($medicine['batch_no']) ?>">
      </div>

      <div class="field">
        <label for="expiry_date">Expiry date</label>
        <input type="date" id="expiry_date" name="expiry_date" value="<?= h($medicine['expiry_date']) ?>">
      </div>

      <div class="field">
        <label for="quantity">Quantity in stock *</label>
        <input type="number" id="quantity" name="quantity" min="0" required value="<?= h((string) $medicine['quantity']) ?>">
      </div>

      <div class="field">
        <label for="reorder_level">Reorder level</label>
        <input type="number" id="reorder_level" name="reorder_level" min="0" value="<?= h((string) $medicine['reorder_level']) ?>">
      </div>

      <div class="field">
        <label for="unit_price">Unit price ($) *</label>
        <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" required value="<?= h((string) $medicine['unit_price']) ?>">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Add medicine' ?></button>
      <a href="medicines.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
