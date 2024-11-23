<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

file_put_contents('signup_debug.log', date('Y-m-d H:i:s') . " - Input: " . file_get_contents('php://input') . "\n", FILE_APPEND);

try {
    $conn = new mysqli("localhost", "user48", "48kivu", "db48");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    // Validate input
    if (empty($name) || empty($email) || empty($password)) {
        throw new Exception("All fields are required.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }
    if (strlen($password) < 8) {
        throw new Exception("Password must be at least 8 characters long.");
    }

    // Check for duplicate email
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        throw new Exception("Email already exists.");
    }
    $checkEmail->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare statement failed.");
    }
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if (!$stmt->execute()) {
        throw new Exception("Failed to create account: " . $stmt->error);
    }

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt?->close();
    $conn?->close();
}
?>
