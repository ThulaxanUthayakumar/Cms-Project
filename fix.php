<?php
$pdo = new PDO("mysql:host=localhost;dbname=cms_db", "root", "");
$hash = password_hash("admin123", PASSWORD_BCRYPT);
$pdo->prepare("UPDATE users SET password=? WHERE username='admin'")->execute([$hash]);
echo "Done: " . $hash;
?>
