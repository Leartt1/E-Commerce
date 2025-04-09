<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json");

include('dbcon.php');

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['name']) || !isset($input['layout_data'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields."]);
    exit;
}

$layout_name = htmlspecialchars($input['name'], ENT_QUOTES, 'UTF-8');
$layout_data = json_encode($input['layout_data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


// Insert new layout into the database
$query = "INSERT INTO layouts (layout_name, layout_data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "ss", $layout_name, $layout_data);
if (mysqli_stmt_execute($stmt)) {
    $new_layout_id = mysqli_insert_id($connection);
    echo json_encode(["success" => true, "id" => $new_layout_id]);
} else {
    echo json_encode(["error" => "Failed to create layout: " . mysqli_error($connection)]);
}
mysqli_stmt_close($stmt);
?>
