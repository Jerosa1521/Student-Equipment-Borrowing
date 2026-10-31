<?php
session_start();
require_once __DIR__ . "/config/db.php";

// Allow admin accounts only.
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: index.html");
    exit;
}

try {
    $stmt = $pdo->query(
        "SELECT
            pr.id AS request_id,
            pr.status,
            pr.created_at,
            u.full_name,
            u.username,
            u.student_id,
            u.course_section
         FROM password_reset_requests pr
         INNER JOIN users u ON pr.user_id = u.id
         ORDER BY
            CASE WHEN pr.status = 'pending' THEN 0 ELSE 1 END,
            pr.created_at DESC"
    );

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Unable to load password reset requests.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 25px;
            color: #1e293b;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1 { color: #1e429f; }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th { background: #eff6ff; }

        .pending { color: #b45309; font-weight: bold; }
        .approved { color: #15803d; font-weight: bold; }
        .rejected { color: #b91c1c; font-weight: bold; }

        .back {
            display: inline-block;
            margin-top: 20px;
            color: #1d4ed8;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Password Reset Requests</h1>
    <p>Review student accounts that have requested a password reset.</p>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Username</th>
                    <th>Student ID</th>
                    <th>Course/Section</th>
                    <th>Date Requested</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="6">No password reset requests found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request["full_name"] ?? "") ?></td>
                        <td><?= htmlspecialchars($request["username"] ?? "") ?></td>
                        <td><?= htmlspecialchars($request["student_id"] ?? "") ?></td>
                        <td><?= htmlspecialchars($request["course_section"] ?? "") ?></td>
                        <td><?= htmlspecialchars($request["created_at"]) ?></td>
                        <td class="<?= htmlspecialchars($request["status"]) ?>">
    <?= htmlspecialchars(ucfirst($request["status"])) ?>
</td>

<td>
    <?php if ($request["status"] === "pending"): ?>
        <form action="handle-password-request.php" method="POST"
              style="display:inline;">
            <input type="hidden" name="request_id"
                   value="<?= (int)$request["request_id"] ?>">
            <button type="submit" name="action" value="approve">
                Approve
            </button>
            <button type="submit" name="action" value="reject">
                Reject
            </button>
        </form>
    <?php elseif ($request["status"] === "approved"): ?>
        Approved
    <?php else: ?>
        Rejected
    <?php endif; ?>
</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a class="back" href="admin-dashboard.html">Back to Dashboard</a>
</div>
</body>
</html>