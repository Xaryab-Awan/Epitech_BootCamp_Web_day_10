<?php

header("Content-Type: application/json");

$type = trim($_GET["type"] ?? "");
$brand = trim($_GET["brand"] ?? "");

$typeMessage = "No type sent yet!";
$typeValid = false;

$brandMessage = "No brand sent yet!";
$brandValid = false;
if ($type !== "") {
    if (strlen($type) < 3) {
        $typeMessage = "$type: this type does not have enough characters.";
    } elseif (strlen($type) > 10) {
        $typeMessage = "$type: this type has too many characters.";
    } elseif (!preg_match('/^[a-z-]+$/i', $type)) {
        $typeMessage = "$type: this type has non-alphabetical characters (different from '-').";
    } else {
        $typeMessage = "$type: this type doesn't exist in our shop.";
    }
}

try {
    $db = new PDO("sqlite:" . __DIR__ . "/ajax_products.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $typeExists = false;

    if (
        $type !== "" && strlen($type) >= 3 &&
        strlen($type) <= 10 &&
        preg_match('/^[a-z-]+$/i', $type)
    ) {

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM products WHERE LOWER(type) = LOWER(?)"
        );
        $stmt->execute([$type]);

        $typeExists = $stmt->fetchColumn() > 0;

        if ($typeExists) {
            $typeMessage = "$type: this type exists in databases.";
            $typeValid = true;
        }
    }


    if ($brand !== "") {
        if (strlen($brand) < 2) {
            $brandMessage = "$brand: this brand does not have enough characters.";
        } elseif (strlen($brand) > 20) {
            $brandMessage = "$brand: this brand has too many characters.";
        } elseif (!preg_match('/^[a-z0-9&-]+$/i', $brand)) {
            $brandMessage = "$brand: this brand has invalid characters.";
        } elseif (!$typeValid) {

            $brandMessage = "Enter an existing type to validate this brand.";
        } else {

            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM products
                 WHERE LOWER(type) = LOWER(?)
                 AND LOWER(brand) = LOWER(?)"
            );
            $stmt->execute([$type, $brand]);

            $brandExists = $stmt->fetchColumn() > 0;

            if ($brandExists) {
                $brandMessage = "$brand: this brand already exists in databases.";
            } else {
                $brandMessage = "$brand: this brand is valid for the type $type.";
                $brandValid = true;
            }
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "typeMessage" => "Database error.",
        "typeValid" => false,
        "brandMessage" => "Database error.",
        "brandValid" => false
    ]);
    exit;
}

echo json_encode([
    "typeMessage" => $typeMessage,
    "typeValid" => $typeValid,
    "brandMessage" => $brandMessage,
    "brandValid" => $brandValid
]);

?>