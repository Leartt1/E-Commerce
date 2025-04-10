<?php
// add_product.php

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
    if (!isset($_POST['name'], $_POST['price'], $_POST['description'], $_POST['stock'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields."]);
        exit;
    }

    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $price = floatval($_POST['price']);
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $stock = intval($_POST['stock']);

    // Insert into products table
    $productStmt = mysqli_prepare($connection, "INSERT INTO products (productName, productPrice, productDescription, stock) VALUES (?, ?, ?, ?)");
    if ($productStmt === false) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare failed: " . $connection->error]);
        exit;
    }

    mysqli_stmt_bind_param($productStmt, "sdsi", $name, $price, $description, $stock);

    if (!mysqli_stmt_execute($productStmt)) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to add product: " . mysqli_stmt_error($productStmt)]);
        mysqli_stmt_close($productStmt);
        exit;
    }

    $product_id = mysqli_stmt_insert_id($productStmt);
    mysqli_stmt_close($productStmt);

    // Handle multiple image uploads
    // Expecting multiple files under 'images[]'
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $coverSet = false;

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                // Skip files with errors
                continue;
            }

            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                // Skip invalid file types
                continue;
            }

            // Validate file size (e.g., max 5MB)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                // Skip oversized files
                continue;
            }

            // Read the binary data
            $imageData = file_get_contents($file['tmp_name']);
            if ($imageData === false) {
                // Skip files that couldn't be read
                continue;
            }

            // Insert into media table
            $mediaStmt = mysqli_prepare($connection, "INSERT INTO media (filename, filetype, filesize, media_data) VALUES (?, ?, ?, ?)");
            if ($mediaStmt === false) {
                // Handle prepare error
                continue;
            }

            $filename = basename($file['name']);
            $filesize = $file['size'];
            $null = NULL; // Placeholder for BLOB data
            mysqli_stmt_bind_param($mediaStmt, "ssib", $filename, $mimeType, $filesize, $null);
            mysqli_stmt_send_long_data($mediaStmt, 3, $imageData);

            if (!mysqli_stmt_execute($mediaStmt)) {
                // Handle execution error
                mysqli_stmt_close($mediaStmt);
                continue;
            }

            $media_id = mysqli_stmt_insert_id($mediaStmt);
            mysqli_stmt_close($mediaStmt);

            // Insert into product_media table
            $is_cover = !$coverSet; // First valid image is set as cover
            $pmStmt = mysqli_prepare($connection, "INSERT INTO product_media (product_id, media_id, is_cover) VALUES (?, ?, ?)");
            if ($pmStmt === false) {
                continue;
            }

            mysqli_stmt_bind_param($pmStmt, "iii", $product_id, $media_id, $is_cover);
            if (mysqli_stmt_execute($pmStmt)) {
                if ($is_cover) {
                    $coverSet = true;
                }
            }

            mysqli_stmt_close($pmStmt);
        }
    }

    echo json_encode(["message" => "Product added successfully"]);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method."]);
}
?>
