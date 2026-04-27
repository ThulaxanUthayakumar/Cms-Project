<?php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            // Log activity
            $log = $pdo->prepare("INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
            $log->execute([$user['id'], 'Logged in']);
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --ink:    #0a0a0f;
    --paper:  #f5f4ef;
    --accent: #ff4d1c;
    --muted:  #8a8a94;
    --border: #e2e1da;
    --card:   #ffffff;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--paper);
    min-height: 100vh;
    display: grid;
    place-items: center;
    position: relative;
    overflow: hidden;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse 80% 60% at 10% 10%, rgba(255,77,28,.08) 0%, transparent 60%),
      radial-gradient(ellipse 60% 80% at 90% 90%, rgba(10,10,15,.05) 0%, transparent 60%);
    pointer-events: none;
  }

  .grid-bg {
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(10,10,15,.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(10,10,15,.04) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
  }

  .login-wrap {
    width: 100%;
    max-width: 420px;
    padding: 24px;
    position: relative;
    z-index: 1;
    animation: fadeUp .5s ease both;
  }

  @keyframes fadeUp {
    from { opacity:0; transform: translateY(20px); }
    to   { opacity:1; transform: translateY(0); }
  }

  .brand {
    text-align: center;
    margin-bottom: 36px;
  }

  .brand-mark {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 52px; height: 52px;
    background: var(--ink);
    border-radius: 14px;
    margin-bottom: 16px;
  }

  .brand-mark svg { width: 28px; height: 28px; }

  .brand h1 {
    font-family: 'Syne', sans-serif;
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--ink);
    letter-spacing: -.03em;
  }

  .brand p {
    font-size: .875rem;
    color: var(--muted);
    margin-top: 4px;
  }

  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 36px;
    box-shadow: 0 2px 24px rgba(10,10,15,.06), 0 1px 2px rgba(10,10,15,.04);
  }

  .card h2 {
    font-family: 'Syne', sans-serif;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 24px;
  }

  .field { margin-bottom: 18px; }

  .field label {
    display: block;
    font-size: .8rem;
    font-weight: 500;
    color: var(--ink);
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: .06em;
  }

  .field input {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: inherit;
    font-size: .95rem;
    color: var(--ink);
    background: var(--paper);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
  }

  .field input:focus {
    border-color: var(--ink);
    box-shadow: 0 0 0 3px rgba(10,10,15,.08);
  }

  .error-box {
    background: #fff1ee;
    border: 1.5px solid #ffc4b3;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: .875rem;
    color: #c0340d;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .btn {
    width: 100%;
    padding: 14px;
    background: var(--ink);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s, background .15s;
    letter-spacing: .02em;
    margin-top: 8px;
  }

  .btn:hover {
    background: #1a1a24;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(10,10,15,.18);
  }

  .btn:active { transform: translateY(0); }

  .hint {
    text-align: center;
    margin-top: 20px;
    font-size: .8rem;
    color: var(--muted);
  }

  .hint code {
    background: var(--paper);
    border: 1px solid var(--border);
    padding: 2px 7px;
    border-radius: 5px;
    font-size: .78rem;
    color: var(--ink);
  }
</style>
</head>
<body>
<div class="grid-bg"></div>

<div class="login-wrap">
  <div class="brand">
    <div class="brand-mark">
      <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 4h8v8H4zM16 4h8v8h-8zM4 16h8v8H4zM16 16l8 8M24 16l-8 8" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </div>
    <h1><?= e(APP_NAME) ?></h1>
    <p>Content Management System</p>
  </div>

  <div class="card">
    <h2>Sign in to your account</h2>

    <?php if ($error): ?>
      <div class="error-box">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="#c0340d" stroke-width="1.5"/><path d="M8 5v4M8 11v.5" stroke="#c0340d" stroke-width="1.5" stroke-linecap="round"/></svg>
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="field">
        <label for="username">Username or Email</label>
        <input type="text" id="username" name="username"
               value="<?= e($_POST['username'] ?? '') ?>"
               autocomplete="username" required autofocus>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               autocomplete="current-password" required>
      </div>

      <button class="btn" type="submit">Sign In →</button>
    </form>

    <p class="hint">Default credentials: <code>admin</code> / <code>admin123</code></p>
  </div>
</div>
</body>
</html>
