<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $servername = "localhost";
    $username = "user48";
    $password = "48kivu";
    $dbname = "db48";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) throw new Exception("Connection failed: " . $conn->connect_error);

    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) throw new Exception("User ID is required.");

    $user_id = intval($_GET['user_id']);
    $stmt = $conn->prepare("SELECT * FROM saved_houses WHERE user_id = ?");
    if (!$stmt) throw new Exception("Prepare statement failed: " . $conn->error);

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $houses = [];
    while ($row = $result->fetch_assoc()) {
        $row['features'] = explode(",", $row['features']);
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
