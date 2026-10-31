
<?php
session_start();
header("Content-Type: application/json");

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Admin access required."
    ]);
    exit;
}

require_once __DIR__ . "/config/db.php";

try {
    $stmt = $pdo->query(
        "SELECT student_id, full_name, username, course_section
         FROM users
         WHERE role = 'student'
         ORDER BY full_name ASC"
    );

    echo json_encode([
        "success" => true,
        "students" => $stmt->fetchAll()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to load student records."
    ]);
}
?>
