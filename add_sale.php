<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $harvestId = $_POST['harvest_id'];
    $saleDate = $_POST['sale_date'];
    $salePrice = $_POST['sale_price'];
    $salesQuantity = $_POST['sales_quantity'];
    $remainingQuantity = $_POST['remaining_quantity'];


    if (empty($saleDate)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a sale date!']);
        exit;
    }

    if ($salePrice <= 0 || $salePrice === "") {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid sale price!']);
        exit;
    }

    if ($salesQuantity <= 0 || $salesQuantity === "") {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid sale quantity!']);
        exit;
    }

    // Check if sales quantity is greater than remaining quantity
    if ($salesQuantity > $remainingQuantity) {
        echo json_encode(['status' => 'error', 'message' => 'Sales quantity cannot be greater than remaining quantity!']);
        exit;
    }

    $sql = "INSERT INTO sales (harvest_id, sale_date, sales_quantity, sale_price) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('issd', $harvestId, $saleDate, $salesQuantity, $salePrice);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error occurred while saving the sale.']);
    }

    $stmt->close();
    $mysqli->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
