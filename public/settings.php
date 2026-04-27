<?php
require_once __DIR__ . '/../includes/config.php';
requireRole('admin');
$pdo = getDB();
$pageTitle = 'Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $keys = ['site_name','site_tagline','posts_per_page','allow_comments'];
    foreach ($keys as $key) {
        $val = trim($_POST[$key] ?? '');
        $stmt = $pdo->prepare("INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=?");
        $stmt->execute([$key, $val, $val]);
    }
    setFlash('success','Settings saved.');
    header('Location:'.APP_URL.'/public/settings.php'); exit;
}

$settings = [];
foreach ($pdo->query("SELECT * FROM settings") as $row) {
    $settings[$row['key']] = $row['value'];
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="max-width:600px;">
  <div class="card">
    <div class="card-header"><h3>Site Settings</h3></div>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-grid">
        <div class="field">
          <label>Site Name</label>
          <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? APP_NAME) ?>">
        </div>
        <div class="field">
          <label>Tagline</label>
          <input type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>">
        </div>
        <div class="field">
          <label>Posts Per Page</label>
          <input type="number" name="posts_per_page" min="1" max="100" value="<?= e($settings['posts_per_page'] ?? '10') ?>">
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
          <input type="checkbox" id="allow_comments" name="allow_comments" value="1"
                 <?= ($settings['allow_comments'] ?? '1') ? 'checked' : '' ?>
                 style="width:16px;height:16px;accent-color:var(--accent);">
          <label for="allow_comments" style="font-size:.9rem;text-transform:none;letter-spacing:0;font-weight:400;margin:0;cursor:pointer;">Enable comments</label>
        </div>
        <div style="margin-top:8px;">
          <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Info Card -->
  <div class="card" style="margin-top:20px;">
    <div class="card-header"><h3>System Info</h3></div>
    <table style="font-size:.875rem;width:100%;border-collapse:collapse;">
      <tr><td style="padding:10px 0;color:var(--muted);border-bottom:1px solid var(--border);">PHP Version</td><td style="padding:10px 0;border-bottom:1px solid var(--border);"><?= phpversion() ?></td></tr>
      <tr><td style="padding:10px 0;color:var(--muted);border-bottom:1px solid var(--border);">App Version</td><td style="padding:10px 0;border-bottom:1px solid var(--border);"><?= APP_VERSION ?></td></tr>
      <tr><td style="padding:10px 0;color:var(--muted);">Database</td><td style="padding:10px 0;">MySQL</td></tr>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
