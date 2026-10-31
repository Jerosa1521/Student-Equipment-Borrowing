
<?php
header("Content-Type: application/json");

require_once __DIR__ . "/config/db.php";

function respond($status, $message) {
    http_response_code($status);
    echo json_encode([
        "success" => $status >= 200 && $status < 300,
        "message" => $message
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed.");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    respond(400, "Invalid request.");
}

$fullName = trim($data["full_name"] ?? "");
$studentId = trim($data["student_id"] ?? "");
$courseSection = trim($data["course_section"] ?? "");
$username = trim($data["username"] ?? "");
$password = $data["password"] ?? "";

if ($fullName === "" || $username === "" || $password === "") {
    respond(400, "Please fill in all required fields.");
}

if (
    strlen($fullName) > 100 ||
    strlen($username) > 50 ||
    strlen($studentId) > 30 ||
    strlen($courseSection) > 50
) {
    respond(400, "One or more fields are too long.");
}

if (strlen($password) < 8) {
    respond(400, "Password must be at least 8 characters.");
}

try {
    $check = $pdo->prepare(
        "SELECT id FROM users WHERE username = ? LIMIT 1"
    );
    $check->execute([$username]);

    if ($check->fetch()) {
        respond(409, "Username already exists. Please choose another.");
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "INSERT INTO users
        (full_name, username, password_hash, role, student_id, course_section)
        VALUES (?, ?, ?, 'student', ?, ?)"
    );

    $stmt->execute([
        $fullName,
        $username,
        $passwordHash,
        $studentId !== "" ? $studentId : null,
        $courseSection !== "" ? $courseSection : null
    ]);

    respond(201, "Account created successfully! Redirecting to login...");

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() === "23000") {
        respond(409, "Username or student ID may already exist. Check your details.");
    }

    respond(500, "Could not create account. Please try again.");
}
?>