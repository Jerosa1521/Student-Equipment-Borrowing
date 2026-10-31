<?php
session_start();
require_once __DIR__ . "/config/db.php";

// Only logged-in students can access this page.
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "student"
) {
    header("Location: index.html");
    exit;
}

$fullName = $_SESSION["full_name"] ?? "Student";
$firstName = explode(" ", trim($fullName))[0];

$approvedRequest = false;
$pendingRequest = false;

try {
    $stmt = $pdo->prepare(
        "SELECT status
         FROM password_reset_requests
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 1"
    );

    $stmt->execute([$_SESSION["user_id"]]);
    $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resetRequest) {
        $approvedRequest = $resetRequest["status"] === "approved";
        $pendingRequest = $resetRequest["status"] === "pending";
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Student Equipment Borrowing Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-circle small-logo">SEB</div>
            <h2>Equipment System</h2>
            <p>Student Organization</p>
        </div>

        <nav class="sidebar-menu">
            <a href="student-dashboard.php" class="active">🏠 Dashboard</a>
            <a href="equipment.html">💻 Equipment</a>
            <a href="borrow.html">📝 Borrow Equipment</a>
            <a href="my-borrowings.html">📚 My Borrowings</a>
            <a href="profile.html">👤 My Profile</a>
            <a href="index.html" class="logout-link">🚪 Logout</a>
        </nav>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Student Dashboard</h1>
                <p>Welcome to the Student Equipment Borrowing System.</p>
            </div>

            <div class="admin-profile">
                <div class="admin-avatar">
                    <?= htmlspecialchars(strtoupper(substr($firstName, 0, 1)), ENT_QUOTES, "UTF-8") ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($fullName, ENT_QUOTES, "UTF-8") ?></strong>
                    <small>Organization Member</small>
                </div>
            </div>
        </header>

        <section class="dashboard-content">

            <?php if ($approvedRequest): ?>
                <div class="welcome-box">
                    <h2>Password Reset Approved!</h2>
                    <p>Your administrator approved your password reset request.
                       You can now set your new password.</p>
                    <p>
                        <a href="reset-password.php" class="view-all">
                            🔐 Set New Password
                        </a>
                    </p>
                </div>
            <?php elseif ($pendingRequest): ?>
                <div class="welcome-box">
                    <h2>Password Reset Request Pending</h2>
                    <p>Your request is waiting for the administrator's approval.</p>
                </div>
            <?php endif; ?>

            <div class="welcome-box">
                <h2>Welcome, <?= htmlspecialchars($firstName, ENT_QUOTES, "UTF-8") ?>!</h2>
                <p>
                    You can view available equipment, submit borrowing requests,
                    and monitor your borrowing status here.
                </p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💻</div>
                    <div>
                        <h3>25</h3>
                        <p>Total Equipment</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div>
                        <h3>17</h3>
                        <p>Available Equipment</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div>
                        <h3>1</h3>
                        <p>Pending Request</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div>
                        <h3>1</h3>
                        <p>Active Borrowing</p>
                    </div>
                </div>
            </div>

            <div class="section-header">
                <div>
                    <h2>Quick Actions</h2>
                    <p>Frequently used student features.</p>
                </div>
            </div>

            <div class="quick-actions">
                <a href="equipment.html" class="action-card">
                    <div class="action-icon">💻</div>
                    <h3>View Equipment</h3>
                    <p>Check the equipment currently available for borrowing.</p>
                </a>

                <a href="borrow.html" class="action-card">
                    <div class="action-icon">📝</div>
                    <h3>Borrow Equipment</h3>
                    <p>Submit a request to borrow organization equipment.</p>
                </a>

                <a href="my-borrowings.html" class="action-card">
                    <div class="action-icon">📚</div>
                    <h3>My Borrowings</h3>
                    <p>View your current and previous borrowing records.</p>
                </a>

                <a href="profile.html" class="action-card">
                    <div class="action-icon">👤</div>
                    <h3>My Profile</h3>
                    <p>View your student and organization member information.</p>
                </a>
            </div>

            <div class="section-header">
                <div>
                    <h2>My Recent Borrowing</h2>
                    <p>Your latest equipment borrowing activity.</p>
                </div>
                <a href="my-borrowings.html" class="view-all">View All</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Equipment</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>REQ-001</td>
                            <td>Laptop</td>
                            <td>Sept. 22, 2026</td>
                            <td>Sept. 24, 2026</td>
                            <td><span class="status pending">Pending</span></td>
                        </tr>
                        <tr>
                            <td>REQ-005</td>
                            <td>HDMI Cable</td>
                            <td>Sept. 18, 2026</td>
                            <td>Sept. 19, 2026</td>
                            <td><span class="status returned">Returned</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>
    </main>
</body>
</html>
