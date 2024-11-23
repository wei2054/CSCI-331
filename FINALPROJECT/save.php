<?php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$stmt = $pdo->prepare('INSERT INTO saved_houses (user_id, bedrooms, bathrooms, floors, square_footage, wall_material, roof_material, flooring_material, features, total_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $data['user_id'],
    $data['bedrooms'],
    $data['bathrooms'],
    $data['floors'],
    $data['squareFootage'],
    $data['wallMaterial'],
    $data['roofMaterial'],
    $data['flooringMaterial'],
    json_encode($data['features']),
    $data['total_cost']
]);

echo json_encode(['success' => true]);
?>
