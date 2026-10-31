<?php
session_start();

require_once __DIR__ . "/config/db.php";

// Only logged-in students can reset their own password.
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "student"
) {
    header("Location: index.html");
    exit;
}

// Only accept POST requests.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: reset-password.php");
    exit;
}

$requestId = filter_input(
    INPUT_POST,
    "request_id",
    FILTER_VALIDATE_INT
);

$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if (!$requestId) {
    exit("Invalid password reset request.");
}

if (strlen($newPassword) < 8) {
    exit("Password must be at least 8 characters long.");
}

if ($newPassword !== $confirmPassword) {
    exit("Passwords do not match. Please go back and try again.");
}

try {
    $pdo->beginTransaction();

    // Check if the request is approved and belongs to this student.
    $stmt = $pdo->prepare(
        "SELECT id
         FROM password_reset_requests
         WHERE id = ?
           AND user_id = ?
           AND status = 'approved'
         LIMIT 1
         FOR UPDATE"
    );

    $stmt->execute([
        $requestId,
        $_SESSION["user_id"]
    ]);

    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        $pdo->rollBack();
        exit("This request is invalid or has already been used.");
    }

    // Hash the new password.
    $passwordHash = password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );

    // Update the student's password.
    $updateUser = $pdo->prepare(
        "UPDATE users
         SET password_hash = ?
         WHERE id = ? AND role = 'student'"
    );

    $updateUser->execute([
        $passwordHash,
        $_SESSION["user_id"]
    ]);

    if ($updateUser->rowCount() !== 1) {
        $pdo->rollBack();
        exit("Unable to update your password.");
    }

    // Mark the request as completed.
    $updateRequest = $pdo->prepare(
        "UPDATE password_reset_requests
         SET status = 'completed',
             handled_at = NOW()
         WHERE id = ?
           AND user_id = ?
           AND status = 'approved'"
    );

    $updateRequest->execute([
        $requestId,
        $_SESSION["user_id"]
    ]);

    if ($updateRequest->rowCount() !== 1) {
        $pdo->rollBack();
        exit("Unable to complete the password reset.");
    }

    $pdo->commit();

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport'
              content='width=device-width, initial-scale=1.0'>
        <title>Password Updated</title>
    </head>
    <body style='font-family: Arial, sans-serif;
                 max-width: 500px;
                 margin: 60px auto;
                 padding: 20px;
                 line-height: 1.6;'>

        <h2>Password Updated Successfully!</h2>

        <p>Your new password has been saved.</p>

        <p>You can now log out and log in again
           using your new password.</p>

        <a href='index.html'>Back to Login</a>

    </body>
    </html>";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);

    exit(
        "Unable to reset your password right now. Please try again later."
    );
}