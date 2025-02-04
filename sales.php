<?php

// connect to database
require "db.php"; // Database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $staff_id = $_POST["staff_id"];
    $number = $_POST["number"];
    $station = $_POST["station"];

    // Validation
    if (empty($name) || empty($staff_id) || empty($number) || empty($station)) {
        echo "All fields are required.";
        exit;
    }

    try {
        // Insert the costumer details into the database
        $stmt = $pdo->prepare("INSERT INTO costumers (name, staff_id, number, station) VALUES (:name, :staff_id, :number, :station)");
        $stmt->execute([
            ':name' => $name,
            ':staff_id' => $staff_id,
            ':number' => $number,
            ':station' => $station
        ]);

        // Redirect to the invoice page with the costumer data
        header("Location: invoice.php?name=" . urlencode($name) . "&staff_id=" . urlencode($staff_id) . "&number=" . urlencode($number) . "&station=" . urlencode($station));
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
