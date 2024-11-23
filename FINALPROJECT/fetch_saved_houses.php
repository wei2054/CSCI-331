<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

try {
    // Database connection
    $servername = "localhost";
    $username = "user48";
    $password = "48kivu";
    $dbname = "db48";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Validate user ID
    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        throw new Exception("User ID is required.");
    }

    $user_id = intval($_GET['user_id']);

    // Fetch houses from the database
    $stmt = $conn->prepare("SELECT * FROM saved_houses WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $houses = [];
    while ($row = $result->fetch_assoc()) {
        $row['features'] = explode(",", $row['features']); // Convert features back to an array
        $houses[] = $row;
    }

    echo json_encode(["success" => true, "houses" => $houses]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt?->close();
    $conn?->close();
}
?>
