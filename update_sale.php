<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saleId = $_POST['sale_id'] ?? null;
    $saleDate = $_POST['sale_date'] ?? null;
    $salePrice = $_POST['sale_price'] ?? null;
    $salesQuantity = $_POST['sales_quantity'] ?? null;
    $harvestId = $_POST['harvest_id'] ?? null;

    error_log('Received POST data: ' . json_encode($_POST));

    // Check if all required data is received
    if (!$saleId || !$saleDate || !$salePrice || !$salesQuantity || !$harvestId) {
        $response = [
            'status' => 'error',
            'message' => 'Required data is missing.'
        ];
        echo json_encode($response);
        exit;
    }
    
    if ($salePrice <= 0 || $salePrice === "") {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid sale price!']);
        exit;
    }

    // Calculate the new remaining quantity
    $getHarvest = "SELECT harvest_quantity - COALESCE((SELECT SUM(sales_quantity) FROM sales WHERE harvest_id = ? AND sale_id != ?), 0) AS remaining_quantity FROM harvest WHERE harvest_id = ?";
    
    $stmt2 = $mysqli->prepare($getHarvest);
    if (!$stmt2) {
        $response = [
            'status' => 'error',
            'message' => 'Prepare failed: ' . $mysqli->error
        ];
        echo json_encode($response);
        exit;
    }
    
    $stmt2->bind_param('iii', $harvestId, $saleId, $harvestId);
    
    if (!$stmt2->execute()) {
        $response = [
            'status' => 'error',
            'message' => 'Execute failed: ' . $stmt2->error
        ];
        echo json_encode($response);
        exit;
    }
    
    $result = $stmt2->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $remainingQuantity = $row['remaining_quantity'];
        
        // Calculate total quantity
        $totalQuantity = $remainingQuantity - $salesQuantity;

        // Check if the sales quantity is greater than the total quantity
        if ($salesQuantity > $remainingQuantity) {
            $response = [
                'status' => 'error',
                'message' => 'The sales quantity entered exceeds the remaining quantity.'
            ];
            echo json_encode($response);
            exit;
        }

        // Check if the remaining quantity will go negative
        if ($totalQuantity < 0) {
            $response = [
                'status' => 'error',
                'message' => 'The remaining quantity cannot go negative.'
            ];
            echo json_encode($response);
            exit;
        }

        // Update sale query
        $sql = "UPDATE sales 
                SET sale_date = ?, 
                    sale_price = ?, 
                    sales_quantity = ? 
                WHERE sale_id = ?";
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            $response = [
                'status' => 'error',
                'message' => 'Prepare failed: ' . $mysqli->error
            ];
            echo json_encode($response);
            exit;
        }

        $stmt->bind_param('ssii', $saleDate, $salePrice, $salesQuantity, $saleId);
        
        if (!$stmt->execute()) {
            $response = [
                'status' => 'error',
                'message' => 'Execute failed: ' . $stmt->error
            ];
            echo json_encode($response);
            exit;
        }

        $response = [
            'status' => 'success',
            'message' => 'Sale details updated successfully.'
        ];
        echo json_encode($response);

    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error fetching harvest details.',
            'debug' => $stmt2->error
        ];
        echo json_encode($response);
    }

} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request'
    ];
    echo json_encode($response);
}
?>
