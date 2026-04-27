<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
$pdo = getDB();
$pageTitle = 'Categories';

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verifyCsrf();
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_POST['delete_id']]);
    setFlash('success','Category deleted.');
    header('Location:'.APP_URL.'/public/categories.php'); exit;
}

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    verifyCsrf();
    $name = trim($_POST['name']);
    $desc = trim($_POST['description'] ?? '');
    $catSlug = slug($name);
    $editId  = $_POST['edit_id'] ? (int)$_POST['edit_id'] : null;
    if ($name) {
        if ($editId) {
            $pdo->prepare("UPDATE categories SET name=?,slug=?,description=? WHERE id=?")->execute([$name,$catSlug,$desc,$editId]);
            setFlash('success','Category updated.');
        } else {
            $pdo->prepare("INSERT INTO categories (name,slug,description) VALUES (?,?,?)")->execute([$name,$catSlug,$desc]);
            setFlash('success','Category created.');
        }
    }
    header('Location:'.APP_URL.'/public/categories.php'); exit;
}

$editId   = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editCat  = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
}

$cats = $pdo->query("
    SELECT c.*, COUNT(p.id) as post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    GROUP BY c.id ORDER BY c.name
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

  <!-- List -->
  <div class="card">
    <div class="card-header"><h3>All Categories</h3></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Name</th><th>Slug</th><th>Posts</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($cats as $cat): ?>
          <tr>
            <td class="text-muted"><?= $cat['id'] ?></td>
            <td style="font-weight:500;"><?= e($cat['name']) ?></td>
            <td class="text-muted"><code style="font-size:.78rem;"><?= e($cat['slug']) ?></code></td>
            <td><?= $cat['post_count'] ?></td>
            <td>
              <div style="display:flex;gap:6px;">
                <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <form method="post" onsubmit="return confirm('Delete this category?')">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Del</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$cats): ?>
          <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--muted);">No categories yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Form -->
  <div class="card">
    <div class="card-header"><h3><?= $editCat ? 'Edit Category' : 'Add Category' ?></h3></div>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="edit_id" value="<?= $editCat ? $editCat['id'] : '' ?>">
      <div class="form-grid">
        <div class="field">
          <label>Name *</label>
          <input type="text" name="name" value="<?= e($editCat['name'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label>Description</label>
          <textarea name="description" style="min-height:80px;"><?= e($editCat['description'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;gap:8px;margin-top:4px;">
          <button type="submit" class="btn btn-primary"><?= $editCat ? 'Save' : 'Create' ?></button>
          <?php if ($editCat): ?>
          <a href="<?= APP_URL ?>/public/categories.php" class="btn" style="background:var(--paper);border:1px solid var(--border);color:var(--ink);">Cancel</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
