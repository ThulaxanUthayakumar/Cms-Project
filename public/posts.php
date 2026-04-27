<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Posts';

requireLogin();
$pdo = getDB();

// Delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verifyCsrf();
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([(int)$_POST['delete_id']]);
    setFlash('success', 'Post deleted.');
    header('Location: ' . APP_URL . '/public/posts.php');
    exit;
}

// Filters & Pagination
$search   = trim($_GET['q'] ?? '');
$status   = $_GET['status'] ?? '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = "(p.title LIKE ? OR p.excerpt LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status && in_array($status, ['published','draft','archived'])) {
    $where[]  = "p.status = ?";
    $params[] = $status;
}

$whereSQL = implode(' AND ', $where);

$total = $pdo->prepare("SELECT COUNT(*) FROM posts p WHERE $whereSQL");
$total->execute($params);
$totalCount = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalCount / $perPage));

$stmt = $pdo->prepare("
    SELECT p.*, u.username, c.name as cat_name
    FROM posts p
    LEFT JOIN users u ON u.id = p.author_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE $whereSQL
    ORDER BY p.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$topbarActions = '<a href="' . APP_URL . '/public/post_edit.php" class="topbar-btn">
  <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 1v12M1 7h12"/></svg>
  New Post
</a>';

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Filters -->
<div class="card mb-6">
  <form method="get" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search posts…"
           style="padding:8px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.875rem;flex:1;min-width:180px;background:var(--paper);color:var(--ink);outline:none;">
    <select name="status" style="padding:8px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.875rem;background:var(--paper);color:var(--ink);outline:none;">
      <option value="">All Statuses</option>
      <option value="published" <?= $status==='published'?'selected':'' ?>>Published</option>
      <option value="draft"     <?= $status==='draft'    ?'selected':'' ?>>Draft</option>
      <option value="archived"  <?= $status==='archived' ?'selected':'' ?>>Archived</option>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <?php if ($search || $status): ?>
      <a href="<?= APP_URL ?>/public/posts.php" class="btn" style="background:var(--paper);border:1px solid var(--border);color:var(--ink);">Clear</a>
    <?php endif; ?>
  </form>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <h3>All Posts <span class="text-muted" style="font-weight:400;font-family:'DM Sans',sans-serif;">(<?= $totalCount ?>)</span></h3>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Author</th>
          <th>Category</th>
          <th>Status</th>
          <th>Views</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
        <tr>
          <td class="text-muted"><?= $post['id'] ?></td>
          <td>
            <div style="font-weight:500;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($post['title']) ?></div>
            <?php if ($post['featured']): ?>
              <span style="font-size:.68rem;color:var(--accent);font-weight:600;text-transform:uppercase;letter-spacing:.06em;">★ Featured</span>
            <?php endif; ?>
          </td>
          <td><?= e($post['username']) ?></td>
          <td><?= $post['cat_name'] ? e($post['cat_name']) : '<span class="text-muted">—</span>' ?></td>
          <td><span class="badge <?= e($post['status']) ?>"><?= e($post['status']) ?></span></td>
          <td class="text-muted"><?= number_format($post['views']) ?></td>
          <td class="text-muted" style="white-space:nowrap;"><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="<?= APP_URL ?>/public/post_edit.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
              <form method="post" onsubmit="return confirm('Delete this post?')">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="delete_id" value="<?= $post['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Del</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:40px;color:var(--muted);">
            No posts found. <a href="<?= APP_URL ?>/public/post_edit.php">Create your first post →</a>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <?php
        $url = APP_URL . '/public/posts.php?' . http_build_query(['q'=>$search,'status'=>$status,'page'=>$i]);
      ?>
      <?php if ($i === $page): ?>
        <span><?= $i ?></span>
      <?php else: ?>
        <a href="<?= e($url) ?>"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
