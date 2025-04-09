<?php
// Simple status endpoint to check if the PHP server is running
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

echo json_encode([
    "status" => "ok",
    "message" => "PHP backend server is running",
    "timestamp" => time()
]); 