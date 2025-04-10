<?php
// associate_media.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include('dbcon.php');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(["message" => "CORS preflight successful"]);
    exit;
}

// Function to validate product and media existence
function validate_product_media($product_id, $media_id, $connection) {
    // Validate product
    $productCheck = mysqli_prepare($connection, "SELECT id FROM products WHERE id = ?");
    mysqli_stmt_bind_param($productCheck, "i", $product_id);
    mysqli_stmt_execute($productCheck);
    mysqli_stmt_store_result($productCheck);
    if (mysqli_stmt_num_rows($productCheck) === 0) {
        mysqli_stmt_close($productCheck);
        return ["error" => "Invalid product ID."];
    }
    mysqli_stmt_close($productCheck);

    // Validate media
    $mediaCheck = mysqli_prepare($connection, "SELECT id FROM media WHERE id = ?");
    mysqli_stmt_bind_param($mediaCheck, "i", $media_id);
    mysqli_stmt_execute($mediaCheck);
    mysqli_stmt_store_result($mediaCheck);
    if (mysqli_stmt_num_rows($mediaCheck) === 0) {
        mysqli_stmt_close($mediaCheck);
        return ["error" => "Invalid media ID."];
    }
    mysqli_stmt_close($mediaCheck);

    return ["success" => true];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Associate media with product
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['product_id'], $data['media_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields: product_id, media_id."]);
        exit;
    }

    $product_id = intval($data['product_id']);
    $media_id = intval($data['media_id']);
    $is_cover = isset($data['is_cover']) ? boolval($data['is_cover']) : false;

    // Validate existence
    $validation = validate_product_media($product_id, $media_id, $connection);
    if (isset($validation['error'])) {
        http_response_code(400);
        echo json_encode($validation);
        exit;
    }

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        if ($is_cover) {
            // Unset existing cover for this product
            $unset_cover = mysqli_prepare($connection, "UPDATE product_media SET is_cover = FALSE WHERE product_id = ?");
            mysqli_stmt_bind_param($unset_cover, "i", $product_id);
            mysqli_stmt_execute($unset_cover);
            mysqli_stmt_close($unset_cover);
        }

        // Check if association already exists
        $check_assoc = mysqli_prepare($connection, "SELECT * FROM product_media WHERE product_id = ? AND media_id = ?");
        mysqli_stmt_bind_param($check_assoc, "ii", $product_id, $media_id);
        mysqli_stmt_execute($check_assoc);
        mysqli_stmt_store_result($check_assoc);
        if (mysqli_stmt_num_rows($check_assoc) === 0) {
            // Insert new association
            $stmt = mysqli_prepare($connection, "INSERT INTO product_media (product_id, media_id, is_cover) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iii", $product_id, $media_id, $is_cover);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            // Update existing association
            $stmt = mysqli_prepare($connection, "UPDATE product_media SET is_cover = ? WHERE product_id = ? AND media_id = ?");
            mysqli_stmt_bind_param($stmt, "iii", $is_cover, $product_id, $media_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($connection);
        echo json_encode(["message" => "Media associated successfully."]);
    } catch (Exception $e) {
        mysqli_roll_back($connection);
        http_response_code(500);
        echo json_encode(["error" => "Failed to associate media: " . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Remove association between media and product
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['product_id'], $data['media_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields: product_id, media_id."]);
        exit;
    }

    $product_id = intval($data['product_id']);
    $media_id = intval($data['media_id']);

    // Validate existence
    $validation = validate_product_media($product_id, $media_id, $connection);
    if (isset($validation['error'])) {
        http_response_code(400);
        echo json_encode($validation);
        exit;
    }

    $stmt = mysqli_prepare($connection, "DELETE FROM product_media WHERE product_id = ? AND media_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $media_id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["message" => "Media association removed successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to remove media association: " . mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method."]);
}
?>
