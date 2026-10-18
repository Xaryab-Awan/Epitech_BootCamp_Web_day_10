<?php

header("Content-Type: application/json");

$name = $_GET["name"] ?? "";

echo json_encode([
    "name" => $name
]);

?>