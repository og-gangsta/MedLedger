<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$active = 'categories';
$pageTitle = 'Categories · MedLedger';
$errors = [];

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editing = null;

// --- Handle create / update / delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        // Prepared statement.
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Category deleted.');
        header('Location: categories.php');
        exit;
    }

    if ($name === '') {
        $errors[] = 'Category name is required.';
    }

    if (empty($errors)) {
        if ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
            $stmt->execute([$name, $description, $id]);
            set_flash('success', 'Category updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            $stmt->execute([$name, $description]);
            set_flash('success', 'Category added.');
        }
        header('Location: categories.php');
        exit;
    }
}

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
}

$categories = $pdo->query(
    "SELECT c.*, (SELECT COUNT(*) FROM medicines m WHERE m.category_id = c.id) AS medicine_count
     FROM categories c ORDER BY c.name ASC"
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div>
    <p class="eyebrow">Inventory</p>
    <h1>Categories</h1>
    <p class="desc">Drug classes used to group medicines.</p>
  </div>
</div>

<div class="panel">
  <h2><?= $editing ? 'Edit category' : 'Add category' ?></h2>
  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= h($err) ?></div>
  <?php endforeach; ?>
  <form method="post" action="categories.php">
    <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>
    <div class="form-grid">
      <div class="field">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" required value="<?= h($editing['name'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="description">Description</label>
        <input type="text" id="description" name="description" value="<?= h($editing['description'] ?? '') ?>">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Save changes' : 'Add category' ?></button>
      <?php if ($editing): ?><a href="categories.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="panel">
  <h2>All categories</h2>
  <div class="table-wrap">
  <?php if (empty($categories)): ?>
    <div class="empty-state"><div class="glyph">⌀</div>No categories yet.</div>
  <?php else: ?>
    <table class="ledger">
      <thead><tr><th>Name</th><th>Description</th><th>Medicines</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
          <td class="med-name"><?= h($cat['name']) ?></td>
          <td><?= h($cat['description'] ?: '—') ?></td>
          <td><?= (int) $cat['medicine_count'] ?></td>
          <td>
            <div class="row-actions">
              <a href="categories.php?edit=<?= (int) $cat['id'] ?>">Edit</a>
              <form class="confirm-delete" method="post" action="categories.php" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
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
