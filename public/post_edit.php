<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
$pdo  = getDB();
$user = currentUser();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
$post = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) { setFlash('error','Post not found.'); header('Location:'.APP_URL.'/public/posts.php'); exit; }
    // Editors can only edit own posts
    if ($user['role'] === 'editor' && $post['author_id'] != $user['id']) {
        setFlash('error','You cannot edit this post.');
        header('Location:'.APP_URL.'/public/posts.php'); exit;
    }
}

$pageTitle = $post ? 'Edit Post' : 'New Post';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title      = trim($_POST['title'] ?? '');
    $body       = $_POST['body'] ?? '';
    $excerpt    = trim($_POST['excerpt'] ?? '');
    $status     = in_array($_POST['status'], ['draft','published','archived']) ? $_POST['status'] : 'draft';
    $category   = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
    $featured   = isset($_POST['featured']) ? 1 : 0;
    $postSlug   = slug($title);

    if (!$title) {
        setFlash('error','Title is required.');
    } else {
        if ($post) {
            $stmt = $pdo->prepare("UPDATE posts SET title=?,slug=?,excerpt=?,body=?,status=?,category_id=?,featured=?,updated_at=NOW() WHERE id=?");
            $stmt->execute([$title, $postSlug, $excerpt, $body, $status, $category, $featured, $post['id']]);
            $logMsg = "Updated post: $title";
            $logId  = $post['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO posts (title,slug,excerpt,body,status,category_id,featured,author_id) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$title, $postSlug, $excerpt, $body, $status, $category, $featured, $user['id']]);
            $logId  = $pdo->lastInsertId();
            $logMsg = "Created post: $title";
        }
        $log = $pdo->prepare("INSERT INTO activity_log (user_id, action, entity, entity_id) VALUES (?,?,?,?)");
        $log->execute([$user['id'], $logMsg, 'post', $logId]);
        setFlash('success', $post ? 'Post updated.' : 'Post created.');
        header('Location:'.APP_URL.'/public/posts.php'); exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="max-width:800px;">
  <div class="card">
    <div class="card-header">
      <h3><?= $post ? 'Edit Post' : 'Create New Post' ?></h3>
      <a href="<?= APP_URL ?>/public/posts.php" class="btn btn-sm" style="background:var(--paper);border:1px solid var(--border);color:var(--ink);">← Back</a>
    </div>

    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-grid">

        <div class="field">
          <label>Title *</label>
          <input type="text" name="title" value="<?= e($post['title'] ?? $_POST['title'] ?? '') ?>" required autofocus>
        </div>

        <div class="field">
          <label>Excerpt</label>
          <textarea name="excerpt" style="min-height:72px;"><?= e($post['excerpt'] ?? $_POST['excerpt'] ?? '') ?></textarea>
        </div>

        <div class="field">
          <label>Body (HTML supported)</label>
          <textarea name="body" style="min-height:280px;"><?= e($post['body'] ?? $_POST['body'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
          <div class="field">
            <label>Status</label>
            <select name="status">
              <?php foreach (['draft','published','archived'] as $s): ?>
              <option value="<?= $s ?>" <?= (($post['status']??'draft')===$s)?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Category</label>
            <select name="category_id">
              <option value="">— None —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= (($post['category_id']??'')==$cat['id'])?'selected':'' ?>><?= e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
          <input type="checkbox" id="featured" name="featured" value="1" <?= ($post['featured']??0)?'checked':'' ?> style="width:16px;height:16px;accent-color:var(--accent);">
          <label for="featured" style="font-size:.9rem;text-transform:none;letter-spacing:0;font-weight:400;margin:0;cursor:pointer;">Mark as featured post</label>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-primary">
            <?= $post ? '✓ Save Changes' : '+ Publish Post' ?>
          </button>
          <a href="<?= APP_URL ?>/public/posts.php" class="btn" style="background:var(--paper);border:1px solid var(--border);color:var(--ink);">Cancel</a>
        </div>

      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
