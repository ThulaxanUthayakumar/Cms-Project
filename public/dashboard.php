<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Dashboard';

$pdo = getDB();

// Stats
$totalPosts      = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$publishedPosts  = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$draftPosts      = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn();
$totalUsers      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalViews      = $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn();

// Recent Posts
$recentPosts = $pdo->query("
    SELECT p.*, u.username, c.name as cat_name
    FROM posts p
    LEFT JOIN users u ON u.id = p.author_id
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

// Activity Log
$activity = $pdo->query("
    SELECT a.*, u.username
    FROM activity_log a
    LEFT JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC LIMIT 10
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon orange">
      <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2 2h14v14H2zM5 6h8M5 9h8M5 12h5"/></svg>
    </div>
    <div class="stat-value"><?= number_format($totalPosts) ?></div>
    <div class="stat-label">Total Posts</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">
      <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="9" r="7"/><path d="M6 9l2 2 4-4"/></svg>
    </div>
    <div class="stat-value"><?= number_format($publishedPosts) ?></div>
    <div class="stat-label">Published</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">
      <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="7" r="3.5"/><path d="M2 16c0-3.866 3.134-6 7-6s7 2.134 7 6"/></svg>
    </div>
    <div class="stat-value"><?= number_format($totalUsers) ?></div>
    <div class="stat-label">Users</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple">
      <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M1 12l4-4 3 3 4-5 5 6"/></svg>
    </div>
    <div class="stat-value"><?= number_format($totalViews) ?></div>
    <div class="stat-label">Total Views</div>
  </div>
</div>

<!-- Grid -->
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;">

  <!-- Recent Posts -->
  <div class="card">
    <div class="card-header">
      <h3>Recent Posts</h3>
      <a href="<?= APP_URL ?>/public/posts.php" class="btn btn-sm" style="background:var(--paper);color:var(--ink);border:1px solid var(--border);">View all</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentPosts as $post): ?>
          <tr>
            <td class="truncate"><?= e($post['title']) ?></td>
            <td><?= e($post['username']) ?></td>
            <td><?= $post['cat_name'] ? e($post['cat_name']) : '<span class="text-muted">—</span>' ?></td>
            <td><span class="badge <?= e($post['status']) ?>"><?= e($post['status']) ?></span></td>
            <td class="text-muted"><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
            <td>
              <a href="<?= APP_URL ?>/public/post_edit.php?id=<?= $post['id'] ?>" class="btn btn-sm" style="background:var(--paper);color:var(--ink);border:1px solid var(--border);">Edit</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recentPosts)): ?>
          <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--muted);">No posts yet. <a href="<?= APP_URL ?>/public/post_edit.php">Create one →</a></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Activity Log -->
  <div class="card">
    <div class="card-header">
      <h3>Activity</h3>
    </div>
    <?php if (empty($activity)): ?>
      <p class="text-muted">No activity yet.</p>
    <?php else: ?>
    <ul style="list-style:none;display:flex;flex-direction:column;gap:12px;">
      <?php foreach ($activity as $log): ?>
      <li style="display:flex;flex-direction:column;gap:2px;padding-bottom:12px;border-bottom:1px solid var(--border);">
        <span style="font-size:.82rem;color:var(--ink);font-weight:500;"><?= e($log['action']) ?></span>
        <span style="font-size:.75rem;color:var(--muted);">
          <?= $log['username'] ? e($log['username']) : 'System' ?> · <?= date('M j, g:ia', strtotime($log['created_at'])) ?>
        </span>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
