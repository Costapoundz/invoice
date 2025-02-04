<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $quantity = intval($_POST['quantity']);
    $model = trim($_POST['model']);
    $amount = floatval($_POST['amount']);
    $category = trim($_POST['category']);

    if (empty($name) || empty($model) || $quantity <= 0 || empty($category) || $amount <= 0) {
        echo "Invalid input data!";
        exit;
    }

    try {
        $pdo->beginTransaction(); // Start transaction for data integrity

        $stmt = $pdo->prepare("SELECT id, quantity FROM items WHERE name = :name AND model = :model");
        $stmt->execute([
            'name' => $name,
            'model' => $model
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $newQuantity = $item['quantity'] + $quantity;
            $updateStmt = $pdo->prepare("UPDATE items SET quantity = :quantity, category = :category, amount = :amount WHERE id = :id");
            $updateStmt->execute([
                'quantity' => $newQuantity,
                'category' => $category,
                'amount' => $amount,
                'id' => $item['id']
            ]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO items (name, amount, quantity, model, category) VALUES (:name, :amount, :quantity, :model, :category)");
            $insertStmt->execute([
                'name' => $name,
                'quantity' => $quantity,
                'model' => $model,
                'category' => $category,
                'amount' => $amount
            ]);
        }

        $logStmt = $pdo->prepare("INSERT INTO transactions (item_name, amount, transaction_type, model, quantity) VALUES (:name, :amount, :transaction_type, :model, :quantity)");
        $logStmt->execute([
            'name' => $name,
            'transaction_type' => 'Add',
            'model' => $model,
            'amount' => $amount,
            'quantity' => $quantity
        ]);

        $pdo->commit(); // Commit the transaction

        echo "Successfully updated or added the item '{$name}' (Model: '{$model}') with a quantity of {$quantity}.   
            <a href='index.html'>Home</a>";

    } catch (PDOException $e) {
        $pdo->rollBack(); // Rollback in case of error
        echo "Error: " . $e->getMessage();
    }
}
?>
