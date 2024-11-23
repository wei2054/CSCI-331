<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $input = json_decode(file_get_contents('php://input'), true);

    // Debugging: Log the input data
    file_put_contents('debug_save_build.log', print_r($input, true), FILE_APPEND);

    // Check for missing fields
    $required_fields = ['user_id', 'bedrooms', 'bathrooms', 'floors', 'square_footage', 'wall_material', 'roof_material', 'flooring_material', 'total_cost'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '$field' is required.");
        }
    }

    // Database connection
    $conn = new mysqli("localhost", "user48", "48kivu", "db48");

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO saved_houses (user_id, bedrooms, bathrooms, floors, square_footage, wall_material, roof_material, flooring_material, features, total_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $features = isset($input['features']) ? json_encode($input['features']) : null;

    $stmt->bind_param(
        "iiiiissssd",
        $input['user_id'], $input['bedrooms'], $input['bathrooms'],
        $input['floors'], $input['square_footage'], $input['wall_material'],
        $input['roof_material'], $input['flooring_material'],
        $features, $input['total_cost']
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to save build: " . $stmt->error);
    }

    echo json_encode(["success" => true, "message" => "Build saved successfully."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
