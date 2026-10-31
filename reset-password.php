<?php
session_start();

require_once __DIR__ . "/config/db.php";

// Student account only.
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "student"
) {
    header("Location: index.html");
    exit;
}

try {
    // Find the student's latest approved reset request.
    $stmt = $pdo->prepare(
        "SELECT id
         FROM password_reset_requests
         WHERE user_id = ?
           AND status = 'approved'
         ORDER BY id DESC
         LIMIT 1"
    );

    $stmt->execute([$_SESSION["user_id"]]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Unable to load the password reset page. Please try again.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 420px;
            margin: 60px auto;
            background: #ffffff;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-top: 0;
        }

        p {
            color: #555;
            line-height: 1.5;
        }

        label {
            display: block;
            margin-top: 16px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 22px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .message {
            text-align: center;
        }

        a {
            color: #2563eb;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Reset Password</h2>

    <?php if (!$request): ?>

        <div class="message">
            <p>
                Your password reset request has not yet been approved,
                or it has already been used.
            </p>

            <p>
                Please wait for admin approval or contact the administrator
                if you need assistance.
            </p>

            <a href="index.html">Back to Login</a>
        </div>

    <?php else: ?>

        <p>
            Your request has been approved. Enter your new password below.
        </p>

        <form action="save-new-password.php" method="POST">

            <input
                type="hidden"
                name="request_id"
                value="<?= htmlspecialchars((string)$request["id"], ENT_QUOTES, "UTF-8") ?>"
            >

            <label for="new_password">New Password</label>
            <input
                type="password"
                id="new_password"
                name="new_password"
                minlength="8"
                required
                autocomplete="new-password"
                placeholder="At least 8 characters"
            >

            <label for="confirm_password">Confirm New Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                required
                autocomplete="new-password"
                placeholder="Enter your new password again"
            >

            <button type="submit">Save New Password</button>

        </form>

    <?php endif; ?>

</div>
</body>
</html>