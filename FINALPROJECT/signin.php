<?php
// Database connection
$db = new mysqli('localhost', 'user48', '48kivu', 'db48');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'];
    $password = $input['password'];

    // Validate email and password inputs
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit;
    }

    // Query the user
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        $userData = ['id' => $user['id'], 'name' => $user['name']];

        // Set the cookie for 7 days
        setcookie('user', json_encode($userData), time() + (86400 * 7), '/'); // Cookie expires in 7 days

        echo json_encode(['success' => true, 'user' => $userData]);
    } else {
        // Invalid credentials
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }

    $stmt->close();
}

$db->close();
?>
