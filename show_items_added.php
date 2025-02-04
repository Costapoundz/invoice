<?php
require "db.php";

try {
    $stmt = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Transaction History</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Transaction Type</th>
                    <th>Quantity</th>
                    <th>Date </th>
                    <th>Model</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['id']) ?></td>
                            <td><?= htmlspecialchars($transaction['item_name']) ?></td>
                            <td><?= htmlspecialchars($transaction['transaction_type']) ?></td>
                            <td><?= htmlspecialchars($transaction['quantity']) ?></td>
                            <td><?= htmlspecialchars($transaction['created_at']) ?></td>
                            <td><?= htmlspecialchars($transaction['model']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
