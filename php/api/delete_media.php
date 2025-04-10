<?php
// delete_media.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include('dbcon.php');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(["message" => "CORS preflight successful"]);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if 'id' is set
    if (isset($input['id'])) {
        $id = intval($input['id']);

        // Prepare the DELETE statement to prevent SQL injection
        $stmt = $connection->prepare("DELETE FROM media WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    http_response_code(200);
                    echo json_encode(["message" => "Media deleted successfully"]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Media not found"]);
                }
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to delete media: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to prepare statement: " . $connection->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Missing media ID."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method."]);
}
?>
