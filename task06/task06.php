<?php

header("Content-Type: application/json; charset=UTF-8");
function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}
function sendError(string $message, int $status = 400): void
{
    respond([
        "success" => false,
        "error" => $message
    ], $status);
}

$type = trim($_GET["type"] ?? "");
$brand = trim($_GET["brand"] ?? "");
$price = trim($_GET["price"] ?? "");
$number = trim($_GET["number"] ?? "");
$action = $_GET["action"] ?? "";


/* Validate shared fields */

if (strlen($type) < 3) {
    sendError("Error ($type): this type does not have enough characters.");
}

if (strlen($type) > 10) {
    sendError("Error ($type): this type has too many characters.");
}

if (!preg_match('/^[a-z-]+$/i', $type)) {
    sendError("Error ($type): this type has non-alphabetical characters (different from '-').");
}

if (strlen($brand) < 2) {
    sendError("Error ($brand): this brand does not have enough characters.");
}

if (strlen($brand) > 20) {
    sendError("Error ($brand): this brand has too much characters.");
}

if (!preg_match('/^[a-z0-9&-]+$/i', $brand)) {
    sendError("Error ($brand): this brand has invalid characters.");
}


try {
    $db = new PDO("sqlite:" . __DIR__ . "/ajax_products.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    /* SEARCH: Task05 behaviour */

    if ($action === "search") {
        if (strlen($price) < 2) {
            sendError("Error ($price): this price does not have enough characters.");
        }

        if (strlen($price) > 5) {
            sendError("Error ($price): this price has too many characters.");
        }

        if (!preg_match('/^[><=][0-9]+$/', $price)) {
            sendError("Error ($price): we cannot define a price - string invalid.");
        }

        if ($number === "" || !ctype_digit($number) || (int) $number <= 0) {
            sendError("Error ($number): not a positive number.");
        }

        $operator = substr($price, 0, 1);
        $priceValue = (int) substr($price, 1);
        $requestedNumber = (int) $number;

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM products WHERE LOWER(type) = LOWER(?)"
        );
        $stmt->execute([$type]);

        if ((int) $stmt->fetchColumn() === 0) {
            sendError("Error ($type): this type doesn't exist in our shop.");
        }

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM products WHERE LOWER(brand) = LOWER(?)"
        );
        $stmt->execute([$brand]);

        if ((int) $stmt->fetchColumn() === 0) {
            sendError("Error ($brand): this brand doesn't exist in our database.");
        }

        $sql = "SELECT type, brand, price, stock
                FROM products
                WHERE LOWER(type) = LOWER(?)
                AND LOWER(brand) = LOWER(?)
                AND price " . $operator . " ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$type, $brand, $priceValue]);

        $matchingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($matchingProducts) === 0) {
            sendError("Error ($price): no products found at this price.");
        }

        $products = [];

        foreach ($matchingProducts as $product) {
            $stock = (int) $product["stock"];

            if ($requestedNumber > $stock) {
                sendError(
                    "Error ($number): sorry, we don't have enough stock, we only have $stock in stock."
                );
            }

            $products[] = [
                "type" => $product["type"],
                "brand" => $product["brand"],
                "price" => (int) $product["price"],
                "number" => $requestedNumber,
                "stock" => $stock
            ];
        }

        respond([
            "success" => true,
            "products" => $products
        ]);
    }


    /* SAVE: insert new product or update existing product */

    if ($action === "save") {
        if ($price === "" || !ctype_digit($price)) {
            sendError("Price must be a non-negative whole number.");
        }

        if ($number === "" || !ctype_digit($number)) {
            sendError("Number must be a non-negative whole number.");
        }

        $priceValue = (int) $price;
        $stockValue = (int) $number;

        $stmt = $db->prepare(
            "SELECT id FROM products
             WHERE LOWER(type) = LOWER(?)
             AND LOWER(brand) = LOWER(?)"
        );

        $stmt->execute([$type, $brand]);
        $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingProduct) {
            $stmt = $db->prepare(
                "UPDATE products
                 SET price = ?, stock = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $priceValue,
                $stockValue,
                $existingProduct["id"]
            ]);

            respond([
                "success" => true,
                "message" => "Product already exists. Price and stock updated."
            ]);
        }

        $stmt = $db->prepare(
            "INSERT INTO products (type, brand, price, stock)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $type,
            $brand,
            $priceValue,
            $stockValue
        ]);

        respond([
            "success" => true,
            "message" => "New product inserted successfully."
        ]);
    }

    sendError("Unknown action.");

} catch (PDOException $e) {
    sendError("Database error.", 500);
}

?>