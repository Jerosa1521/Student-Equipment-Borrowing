<?php
session_start();
require_once __DIR__ . "/config/db.php";

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: index.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin-password-requests.php");
    exit;
}

$requestId = filter_input(INPUT_POST, "request_id", FILTER_VALIDATE_INT);
$action = $_POST["action"] ?? "";

if (!$requestId || !in_array($action, ["approve", "reject"], true)) {
    exit("Invalid request.");
}

$newStatus = $action === "approve" ? "approved" : "rejected";

try {
    $stmt = $pdo->prepare(
        "UPDATE password_reset_requests
         SET status = ?, handled_at = NOW()
         WHERE id = ? AND status = 'pending'"
    );

    $stmt->execute([$newStatus, $requestId]);

    header("Location: admin-password-requests.php");
    exit;
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Unable to process the request.");
}