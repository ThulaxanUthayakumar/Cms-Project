<?php
require_once __DIR__ . '/../includes/config.php';
requireRole('admin');
$pdo = getDB();
$pageTitle = 'Users';

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verifyCsrf();
    $uid = (int)$_POST['delete_id'];
    if ($uid === (int)$_SESSION['user_id']) {
        setFlash('error','You cannot delete your own account.');
    } else {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        setFlash('success','User deleted.');
    }
    header('Location:'.APP_URL.'/public/users.php'); exit;
}

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    verifyCsrf();
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $role     = in_array($_POST['role'], ['admin','editor','viewer']) ? $_POST['role'] : 'editor';
    $password = $_POST['password'] ?? '';
    $editId   = $_POST['edit_id'] ? (int)$_POST['edit_id'] : null;

    if ($username && $email) {
        if ($editId) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
                $pdo->prepare("UPDATE users SET username=?,email=?,role=?,password=? WHERE id=?")->execute([$username,$email,$role,$hash,$editId]);
            } else {
                $pdo->prepare("UPDATE users SET username=?,email=?,role=? WHERE id=?")->execute([$username,$email,$role,$editId]);
            }
            setFlash('success','User updated.');
        } else {
            if (!$password) { setFlash('error','Password required for new users.'); goto redirect; }
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
            $pdo->prepare("INSERT INTO users (username,email,password,role) VALUES (?,?,?,?)")->execute([$username,$email,$hash,$role]);
            setFlash('success','User created.');
        }
    } else {
        setFlash('error','Username and email are required.');
    }
    redirect:
    header('Location:'.APP_URL.'/public/users.php'); exit;
}

$editId  = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editUser = null;
if ($editId) {
    $s = $pdo->prepare("SELECT * FROM users WHERE id=?"); $s->execute([$editId]);
    $editUser = $s->fetch();
}

$users = $pdo->query("SELECT u.*, COUNT(p.id) as post_count FROM users u LEFT JOIN posts p ON p.author_id=u.id GROUP BY u.id ORDER BY u.created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

  <div class="card">
    <div class="card-header"><h3>All Users</h3></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>Posts</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td class="text-muted"><?= $u['id'] ?></td>
            <td style="font-weight:500;"><?= e($u['username']) ?></td>
            <td class="text-muted"><?= e($u['email']) ?></td>
            <td><span class="badge <?= e($u['role']) ?>"><?= e($u['role']) ?></span></td>
            <td><?= $u['post_count'] ?></td>
            <td class="text-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:6px;">
                <a href="?edit=<?= $u['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <form method="post" onsubmit="return confirm('Delete user <?= e(addslashes($u['username'])) ?>?')">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Del</button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3><?= $editUser ? 'Edit User' : 'Add User' ?></h3></div>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="edit_id" value="<?= $editUser ? $editUser['id'] : '' ?>">
      <div class="form-grid">
        <div class="field">
          <label>Username *</label>
          <input type="text" name="username" value="<?= e($editUser['username'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label>Email *</label>
          <input type="email" name="email" value="<?= e($editUser['email'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label>Password <?= $editUser ? '(leave blank to keep)' : '*' ?></label>
          <input type="password" name="password" <?= !$editUser?'required':'' ?>>
        </div>
        <div class="field">
          <label>Role</label>
          <select name="role">
            <?php foreach (['admin','editor','viewer'] as $r): ?>
            <option value="<?= $r ?>" <?= (($editUser['role']??'editor')===$r)?'selected':'' ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:flex;gap:8px;margin-top:4px;">
          <button type="submit" class="btn btn-primary"><?= $editUser ? 'Save' : 'Create' ?></button>
          <?php if ($editUser): ?>
          <a href="<?= APP_URL ?>/public/users.php" class="btn" style="background:var(--paper);border:1px solid var(--border);color:var(--ink);">Cancel</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
