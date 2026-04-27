<?php
requireLogin();
$user = currentUser();
$flash = getFlash();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --ink:      #0a0a0f;
    --ink-2:    #2a2a35;
    --paper:    #f5f4ef;
    --accent:   #ff4d1c;
    --green:    #18b566;
    --blue:     #2d6cdf;
    --muted:    #8a8a94;
    --border:   #e2e1da;
    --card:     #ffffff;
    --sidebar-w: 240px;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--paper);
    color: var(--ink);
    min-height: 100vh;
    display: flex;
  }

  /* ── Sidebar ─────────────────────────────── */
  .sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: var(--ink);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
    padding: 0;
  }

  .sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 24px 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,.08);
  }

  .sidebar-logo {
    width: 34px; height: 34px;
    background: var(--accent);
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .sidebar-logo svg { width: 18px; height: 18px; }

  .sidebar-brand-text h2 {
    font-family: 'Syne', sans-serif;
    font-size: .95rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -.01em;
  }

  .sidebar-brand-text span {
    font-size: .7rem;
    color: rgba(255,255,255,.4);
    text-transform: uppercase;
    letter-spacing: .08em;
  }

  .sidebar-nav {
    flex: 1;
    padding: 16px 12px;
    overflow-y: auto;
  }

  .nav-section {
    font-size: .65rem;
    font-weight: 600;
    color: rgba(255,255,255,.3);
    text-transform: uppercase;
    letter-spacing: .1em;
    padding: 12px 8px 6px;
    margin-top: 8px;
  }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: 9px;
    text-decoration: none;
    color: rgba(255,255,255,.55);
    font-size: .875rem;
    font-weight: 400;
    transition: background .15s, color .15s;
    margin-bottom: 2px;
  }

  .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; }

  .nav-item:hover {
    background: rgba(255,255,255,.07);
    color: rgba(255,255,255,.9);
  }

  .nav-item.active {
    background: rgba(255,255,255,.1);
    color: #fff;
    font-weight: 500;
  }

  .nav-item .badge {
    margin-left: auto;
    background: var(--accent);
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 20px;
  }

  .sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid rgba(255,255,255,.08);
  }

  .user-chip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 9px;
    background: rgba(255,255,255,.06);
    text-decoration: none;
  }

  .user-avatar {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-size: .75rem;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
  }

  .user-info { overflow: hidden; }

  .user-info strong {
    display: block;
    font-size: .8rem;
    font-weight: 500;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .user-info span {
    font-size: .7rem;
    color: rgba(255,255,255,.35);
    text-transform: capitalize;
  }

  /* ── Main ────────────────────────────────── */
  .main {
    margin-left: var(--sidebar-w);
    flex: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .topbar {
    background: var(--card);
    border-bottom: 1px solid var(--border);
    padding: 0 32px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
  }

  .topbar-left {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .topbar-left h1 {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--ink);
  }

  .topbar-actions { display: flex; align-items: center; gap: 10px; }

  .topbar-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: var(--ink);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-family: inherit;
    font-size: .82rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s, transform .1s;
  }

  .topbar-btn:hover { background: var(--ink-2); transform: translateY(-1px); }
  .topbar-btn svg { width: 14px; height: 14px; }

  .topbar-btn.outline {
    background: transparent;
    color: var(--ink);
    border: 1.5px solid var(--border);
  }

  .topbar-btn.outline:hover { background: var(--paper); transform: none; }

  /* ── Page Content ────────────────────────── */
  .page-content {
    padding: 32px;
    flex: 1;
  }

  /* ── Flash ───────────────────────────────── */
  .flash {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 18px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: .875rem;
    font-weight: 500;
    animation: slideIn .3s ease;
  }

  @keyframes slideIn {
    from { opacity:0; transform: translateY(-8px); }
    to   { opacity:1; transform: translateY(0); }
  }

  .flash.success { background:#edfaf3; border:1.5px solid #a5e6c4; color:#127a44; }
  .flash.error   { background:#fff1ee; border:1.5px solid #ffc4b3; color:#c0340d; }
  .flash.info    { background:#eef3ff; border:1.5px solid #b3c8fa; color:#1a47a0; }

  /* ── Cards ───────────────────────────────── */
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 24px;
  }

  .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .card-header h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--ink);
  }

  /* ── Stat Cards ──────────────────────────── */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
  }

  .stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 22px;
    transition: transform .2s, box-shadow .2s;
  }

  .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(10,10,15,.08); }

  .stat-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
  }

  .stat-icon svg { width: 18px; height: 18px; }
  .stat-icon.orange { background: #fff1ee; }
  .stat-icon.orange svg { stroke: var(--accent); }
  .stat-icon.green  { background: #edfaf3; }
  .stat-icon.green  svg { stroke: var(--green); }
  .stat-icon.blue   { background: #eef3ff; }
  .stat-icon.blue   svg { stroke: var(--blue); }
  .stat-icon.purple { background: #f3eeff; }
  .stat-icon.purple svg { stroke: #7c3aed; }

  .stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 1.9rem;
    font-weight: 800;
    color: var(--ink);
    line-height: 1;
    margin-bottom: 4px;
  }

  .stat-label {
    font-size: .8rem;
    color: var(--muted);
    font-weight: 400;
  }

  /* ── Table ───────────────────────────────── */
  .table-wrap { overflow-x: auto; }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: .875rem;
  }

  thead th {
    text-align: left;
    padding: 10px 16px;
    font-size: .72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
  }

  tbody tr {
    transition: background .1s;
    border-bottom: 1px solid var(--border);
  }

  tbody tr:last-child { border-bottom: none; }
  tbody tr:hover { background: var(--paper); }

  tbody td {
    padding: 13px 16px;
    color: var(--ink);
    vertical-align: middle;
  }

  /* ── Badges ──────────────────────────────── */
  .badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 600;
    text-transform: capitalize;
  }

  .badge.published { background:#edfaf3; color:#127a44; }
  .badge.draft     { background:#f5f4ef; color:#8a8a94; border: 1px solid var(--border); }
  .badge.archived  { background:#fff1ee; color:#c0340d; }
  .badge.admin     { background:#f3eeff; color:#6d28d9; }
  .badge.editor    { background:#eef3ff; color:#1a47a0; }
  .badge.viewer    { background:#f5f4ef; color:#8a8a94; }

  /* ── Action Buttons ──────────────────────── */
  .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-family: inherit; font-size: .82rem; font-weight: 500; cursor: pointer; text-decoration: none; transition: all .15s; border: none; }
  .btn-primary { background: var(--ink); color: #fff; }
  .btn-primary:hover { background: var(--ink-2); transform: translateY(-1px); }
  .btn-danger  { background: #fff1ee; color: #c0340d; border: 1.5px solid #ffc4b3; }
  .btn-danger:hover { background: #ffe0d6; }
  .btn-sm { padding: 5px 11px; font-size: .78rem; border-radius: 6px; }
  .btn svg { width: 13px; height: 13px; }

  /* ── Forms ───────────────────────────────── */
  .form-grid { display: grid; gap: 18px; }
  .form-row  { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

  .field label {
    display: block;
    font-size: .78rem;
    font-weight: 500;
    color: var(--ink);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .06em;
  }

  .field input,
  .field select,
  .field textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    font-family: inherit;
    font-size: .9rem;
    color: var(--ink);
    background: var(--paper);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
    resize: vertical;
  }

  .field input:focus,
  .field select:focus,
  .field textarea:focus {
    border-color: var(--ink);
    box-shadow: 0 0 0 3px rgba(10,10,15,.07);
  }

  .field textarea { min-height: 120px; }

  /* ── Pagination ──────────────────────────── */
  .pagination { display: flex; align-items: center; gap: 6px; margin-top: 20px; }
  .pagination a, .pagination span {
    padding: 6px 12px; border-radius: 7px; font-size: .82rem;
    text-decoration: none; transition: background .15s;
  }
  .pagination a { color: var(--ink); border: 1px solid var(--border); }
  .pagination a:hover { background: var(--paper); }
  .pagination span { background: var(--ink); color: #fff; font-weight: 600; }

  /* ── Utils ───────────────────────────────── */
  .flex { display: flex; }
  .items-center { align-items: center; }
  .justify-between { justify-content: space-between; }
  .gap-2 { gap: 8px; }
  .gap-3 { gap: 12px; }
  .mb-4 { margin-bottom: 16px; }
  .mb-6 { margin-bottom: 24px; }
  .text-muted { color: var(--muted); font-size: .85rem; }
  .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px; }
</style>
</head>
<body>

<!-- ── Sidebar ──────────────────────────────────────────────── -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo">
      <svg viewBox="0 0 18 18" fill="none"><path d="M2 2h6v6H2zM10 2h6v6h-6zM2 10h6v6H2zM10 10l6 6M16 10l-6 6" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/></svg>
    </div>
    <div class="sidebar-brand-text">
      <h2><?= e(APP_NAME) ?></h2>
      <span>v<?= APP_VERSION ?></span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="1" y="1" width="6" height="6" rx="1.5"/><rect x="9" y="1" width="6" height="6" rx="1.5"/><rect x="1" y="9" width="6" height="6" rx="1.5"/><rect x="9" y="9" width="6" height="6" rx="1.5"/></svg>
      Dashboard
    </a>

    <div class="nav-section">Content</div>
    <a href="<?= APP_URL ?>/public/posts.php" class="nav-item <?= $currentPage === 'posts' ? 'active' : '' ?>">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 2h12v12H2zM5 5h6M5 8h6M5 11h4"/></svg>
      Posts
    </a>
    <a href="<?= APP_URL ?>/public/post_edit.php" class="nav-item <?= $currentPage === 'post_edit' ? 'active' : '' ?>">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9.5 2.5l4 4-8 8H1.5v-4l8-8z"/><path d="M7.5 4.5l4 4"/></svg>
      New Post
    </a>
    <a href="<?= APP_URL ?>/public/categories.php" class="nav-item <?= $currentPage === 'categories' ? 'active' : '' ?>">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 5h12M2 8h8M2 11h6"/></svg>
      Categories
    </a>

    <?php if ($user && $user['role'] === 'admin'): ?>
    <div class="nav-section">Admin</div>
    <a href="<?= APP_URL ?>/public/users.php" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5"/></svg>
      Users
    </a>
    <a href="<?= APP_URL ?>/public/settings.php" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="8" cy="8" r="2.5"/><path d="M8 1v2M8 13v2M1 8h2M13 8h2M3.05 3.05l1.41 1.41M11.54 11.54l1.41 1.41M3.05 12.95l1.41-1.41M11.54 4.46l1.41-1.41"/></svg>
      Settings
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= APP_URL ?>/public/logout.php" class="user-chip">
      <div class="user-avatar"><?= strtoupper(substr($user['username'], 0, 2)) ?></div>
      <div class="user-info">
        <strong><?= e($user['username']) ?></strong>
        <span><?= e($user['role']) ?></span>
      </div>
    </a>
  </div>
</aside>

<!-- ── Main ─────────────────────────────────────────────────── -->
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h1><?= isset($pageTitle) ? e($pageTitle) : e(APP_NAME) ?></h1>
    </div>
    <div class="topbar-actions">
      <?php if (isset($topbarActions)) echo $topbarActions; ?>
      <a href="<?= APP_URL ?>/public/logout.php" class="topbar-btn outline">
        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 2H2v10h3M9 4l3 3-3 3M12 7H5"/></svg>
        Logout
      </a>
    </div>
  </div>

  <div class="page-content">
    <?php if ($flash): ?>
      <div class="flash <?= e($flash['type']) ?>">
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>
