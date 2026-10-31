<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/config/db.php";

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $username = trim($data["username"] ?? "");
    $password = $data["password"] ?? "";

    if ($username === "" || $password === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Please enter your username and password."
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id, full_name, username, password_hash, role
         FROM users
         WHERE username = ?
         LIMIT 1"
    );

    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password_hash"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password."
        ]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["full_name"] = $user["full_name"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"] = $user["role"];

    $resetApproved = false;

    if ($user["role"] === "student") {
        $resetStmt = $pdo->prepare(
            "SELECT id
             FROM password_reset_requests
             WHERE user_id = ?
               AND status = 'approved'
             ORDER BY id DESC
             LIMIT 1"
        );

        $resetStmt->execute([$user["id"]]);
        $resetApproved = (bool) $resetStmt->fetch(
            PDO::FETCH_ASSOC
        );
    }

    echo json_encode([
        "success" => true,
        "role" => $user["role"],
        "reset_approved" => $resetApproved
    ]);
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "A server error occurred. Please try again."
    ]);
    exit;
}
?>