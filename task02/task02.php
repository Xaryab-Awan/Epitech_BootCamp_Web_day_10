<?php

header("Content-Type: application/json");

if (!isset($_POST["email"])) {
    http_response_code(400);
    echo json_encode(["valid" => false]);
    exit;
}

$email = $_POST["email"];

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["valid" => true]);
} else {
    http_response_code(400);
    echo json_encode(["valid" => false]);
}

?>