<?php
require_once __DIR__ . "/config/db.php";

$username = "admin";
$newPassword = "Admin@12345";

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "UPDATE users
     SET password_hash = ?, role = 'admin'
     WHERE username = ?"
);

$stmt->execute([$hash, $username]);

if ($stmt->rowCount() > 0) {
    echo "Admin password reset successfully.";
} else {
    echo "No account updated. Check the admin username.";
}
?>