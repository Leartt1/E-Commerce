<?php
// Prevent directory listing and provide basic info
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

echo json_encode([
    "status" => "ok",
    "message" => "E-commerce API is running",
    "documentation" => "API endpoints are available in the api/ directory",
    "timestamp" => time()
]); 