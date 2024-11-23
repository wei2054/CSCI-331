<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
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

    // Determine the request method
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        // Save a new house to the database

        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'];
        $bedrooms = $input['bedrooms'];
        $bathrooms = $input['bathrooms'];
        $floors = $input['floors'];
        $square_footage = $input['square_footage'];
        $wall_material = $input['wall_material'];
        $roof_material = $input['roof_material'];
        $flooring_material = $input['flooring_material'];
        $features = implode(",", $input['features']); // Convert features array to a string
        $total_cost = $input['total_cost'];

        // Validate input
        if (empty($user_id) || empty($bedrooms) || empty($bathrooms) || empty($floors) || empty($square_footage) || empty($total_cost)) {
            throw new Exception("Missing required fields.");
        }

        // Insert house into the database
        $stmt = $conn->prepare(
            "INSERT INTO saved_houses (user_id, bedrooms, bathrooms, floors, square_footage, wall_material, roof_material, flooring_material, features, total_cost)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param(
            "iiiisssssd",
            $user_id,
            $bedrooms,
            $bathrooms,
            $floors,
            $square_footage,
            $wall_material,
            $roof_material,
            $flooring_material,
            $features,
            $total_cost
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        echo json_encode(["success" => true, "message" => "House saved successfully."]);
    } elseif ($method === 'GET') {
        // Fetch saved houses for a user

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
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt?->close();
    $conn?->close();
}
?>
