<?php
// add_media.php

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
    // Validate required fields
    if (!isset($_FILES['media'])) {
        http_response_code(400);
        echo json_encode(["error" => "No media file uploaded."]);
        exit;
    }

    $file = $_FILES['media'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["error" => "File upload error: " . $file['error']]);
        exit;
    }

    // Validate file type (allowing images and videos)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/mpeg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid file type. Only JPEG, PNG, GIF images and MP4, AVI, MPEG videos are allowed."]);
        exit;
    }

    // Validate file size (e.g., max 10MB)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(["error" => "File size exceeds the 10MB limit."]);
        exit;
    }

    // Read the binary data of the media file
    $mediaData = file_get_contents($file['tmp_name']);
    if ($mediaData === false) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to read uploaded file."]);
        exit;
    }

    // Prepare the SQL statement
    $stmt = $connection->prepare("INSERT INTO media (filename, filetype, filesize, media_data) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare failed: " . $connection->error]);
        exit;
    }

    // Bind parameters
    $filename = basename($file['name']);
    $filesize = $file['size'];
    $null = NULL; // Placeholder for BLOB
    $stmt->bind_param("ssib", $filename, $mimeType, $filesize, $null);

    // Send the binary data
    $stmt->send_long_data(3, $mediaData);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["message" => "Media uploaded successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to upload media: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method."]);
}
?>
