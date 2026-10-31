
<?php
require_once __DIR__ . "/config/db.php";

$username = "admin";
$newPassword = "Admin@12345";

try {
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "UPDATE users
         SET password_hash = ?, role = 'admin'
         WHERE username = ?"
    );

    $stmt->execute([$hash, $username]);

    $check = $pdo->prepare(
        "SELECT username, role, password_hash
         FROM users
         WHERE username = ?
         LIMIT 1"
    );

    $check->execute([$username]);
    $user = $check->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Admin account not found.";
    } elseif (password_verify($newPassword, $user["password_hash"])) {
        echo "SUCCESS! Admin password reset successfully.";
        echo "<br>Username: admin";
        echo "<br>Password: Admin@12345";
        echo "<br>Role: " . htmlspecialchars($user["role"]);
    } else {
        echo "Password verification failed.";
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . htmlspecialchars($e->getMessage());
}
?>
