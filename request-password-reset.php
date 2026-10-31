<?php
require_once __DIR__ . "/config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forgot-password.html");
    exit;
}

$username = trim($_POST["username"] ?? "");

if ($username === "") {
    exit("Please enter your username.");
}

$stmt = $pdo->prepare(
    "SELECT id, role FROM users WHERE username = ? LIMIT 1"
);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user["role"] !== "student") {
    exit("If the student account exists, the request will be processed accordingly.");
}

// Check for an existing request that has not been completed.
$stmt = $pdo->prepare(
    "SELECT status
     FROM password_reset_requests
     WHERE user_id = ?
       AND status IN ('pending', 'approved')
     ORDER BY id DESC
     LIMIT 1"
);
$stmt->execute([$user["id"]]);
$existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingRequest) {
    if ($existingRequest["status"] === "approved") {
        echo "Your request is already approved. Please log in to your student account, then open reset-password.php to change your password.";
    } else {
        echo "Your password reset request is still pending admin approval.";
    }
    exit;
}

// Create a new request only if no pending or approved request exists.
$stmt = $pdo->prepare(
    "INSERT INTO password_reset_requests (user_id, status)
     VALUES (?, 'pending')"
);
$stmt->execute([$user["id"]]);

echo "Your password reset request has been submitted. Please wait for admin approval.";