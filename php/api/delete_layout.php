<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

include('dbcon.php');

// Get JSON body from request
$layoutData = file_get_contents("php://input");
$layout = json_decode($layoutData, true);

if ($layout && isset($layout['id'])) {
    $id = intval($layout['id']);
    $query = "DELETE FROM layouts WHERE id = $id";
    if (mysqli_query($connection, $query)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to delete layout."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid layout ID."]);
}
?>
