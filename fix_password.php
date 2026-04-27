<?php
$pdo = new PDO('mysql:host=localhost;dbname=cms_db;charset=utf8mb4', 'root', '');
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$hash]);
echo "Done! Password reset to: admin123\n";
echo "Hash used: " . $hash . "\n";
echo "<br><a href='index.php'>Go to Login</a>";