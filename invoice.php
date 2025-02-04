<?php
require "db.php";

// Check if data is passed via GET
if (
    $_SERVER["REQUEST_METHOD"] === "GET" && 
    !empty($_GET['name']) && 
    !empty($_GET['staff_id']) && 
    !empty($_GET['number']) && 
    !empty($_GET['station']) && 
    !empty($_GET['model']) && 
    !empty($_GET['item_name']) && 
    !empty($_GET['quantity'])
) {
    $name = $_GET['name'];
    $staff_id = $_GET['staff_id'];
    $number = $_GET['number'];
    $station = $_GET['station'];
    $model = $_GET['model'];
    $item_name = $_GET['item_name'];
    $quantity = intval($_GET['quantity']);

    if ($quantity <= 0) {
        echo "Quantity must be greater than 0!";
        exit;
    }

    try {
        // Check the item stock and amount
        $itemStmt = $pdo->prepare("SELECT id, quantity, amount FROM items WHERE model = :model");
        $itemStmt->execute([':model' => $model]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            echo "Item model not found!";
            exit;
        }

        if ($item['quantity'] < $quantity) {
            echo "Insufficient stock for item model: {$model}!";
            exit;
        }

        $amount = $item['amount'] * $quantity;

        // Insert customer details
        $stmt = $pdo->prepare("INSERT INTO costumers (name, staff_id, number, station, amount) VALUES (:name, :staff_id, :number, :station, :amount)");
        $stmt->execute([
            ':name' => $name,
            ':staff_id' => $staff_id,
            ':number' => $number,
            ':station' => $station,
            ':amount' => $amount
        ]);

        // Update item stock
        $newQuantity = $item['quantity'] - $quantity;
        $updateStmt = $pdo->prepare("UPDATE items SET quantity = :quantity WHERE id = :id");
        $updateStmt->execute([
            ':quantity' => $newQuantity,
            ':id' => $item['id']
        ]);

       // Insert into transactions (with transaction_type as 'Removed' and including model)
$logStmt = $pdo->prepare("INSERT INTO transactions (item_name, quantity, amount, model, transaction_type) VALUES (:item_name, :quantity, :amount, :model, :transaction_type)");
$logStmt->execute([
    ':item_name' => $item_name,
    ':quantity' => $quantity,
    ':amount' => $amount,
    ':model' => $model,
    ':transaction_type' => 'Remove'  // Set ENUM value to 'Removed'

    ]);

    

        $message = "Customer details and sales data processed successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
} else {
    var_dump($_GET);
    echo "Invalid request!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .invoice-container { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 10px; max-width: 600px; margin: 0 auto; }
        .invoice-header { text-align: center; font-size: 24px; margin-bottom: 20px; }
        .invoice-details, .invoice-table { margin-bottom: 20px; }
        .invoice-details div { margin: 8px 0; }
        .invoice-details label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background-color: #f4f4f4; }
        .button { text-align: center; margin-top: 20px; }
        .button a { padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <a href ="index.html">home</a> 

<div class="invoice-container">
    <div class="invoice-header">
        <h2>Invoice Details</h2>
    </div>

    <div class="invoice-details">
        <?php if (isset($message)) { echo "<div>$message</div>"; } ?>
        <div><label>Name:</label> <?= htmlspecialchars($name) ?></div>
        <div><label>Staff ID:</label> <?= htmlspecialchars($staff_id) ?></div>
        <div><label>Phone Number:</label> <?= htmlspecialchars($number) ?></div>
        <div><label>Station/District:</label> <?= htmlspecialchars($station) ?></div>
        <div><label>Total Amount:</label> <?= htmlspecialchars($amount) ?> GHS</div>
    </div>

    <div class="invoice-table">
        <h3>Purchased Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Name of Item</th>
                    <th>Model</th>
                    <th>Quantity</th>
                    <th>Amount per Unit (GHS)</th>
                    <th>Total (GHS)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($item_name) ?></td>
                    <td><?= htmlspecialchars($model) ?></td>
                    <td><?= htmlspecialchars($quantity) ?></td>
                    <td><?= htmlspecialchars($item['amount']) ?></td>
                    <td><?= htmlspecialchars($item['amount'] * $quantity) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="button">
        <a href="index.html">Go Back to Home</a>
    </div>
</div>

</body>
</html>
