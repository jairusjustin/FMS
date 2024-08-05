<?php
include 'config.php';

$response = [];

if(isset($_POST['crops_id'], $_POST['cropName'], $_POST['defaultSalePrice'])) {
    
    $crop_id = $_POST['crops_id'];
    $crop_name = trim($_POST['cropName']);
    $default_sale_price = $_POST['defaultSalePrice'];

    // Validate crop_name and default_sale_price
    if(empty($crop_name)) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a crop name!'
        ];
    } else {
        if ($default_sale_price <= 0 || $default_sale_price === "") {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid sale price!']);
            exit;
        }
        // Check if the crop_name already exists
        $check_sql = "SELECT crops_id FROM crops WHERE crop_name = ? AND crops_id != ?";
        $check_stmt = $mysqli->prepare($check_sql);
        $check_stmt->bind_param("si", $crop_name, $crop_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $response = [
                'status' => 'error',
                'message' => 'Crop name already exists.'
            ];
        } else {
            // Update the crop name and default_sale_price
            $sql = "UPDATE crops SET crop_name = ?, default_sale_price = ? WHERE crops_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sdi", $crop_name, $default_sale_price, $crop_id);

            if($stmt->execute()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Crop details updated successfully!'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to update crop details.'
                ];
            }
        }
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Crop name, default sale price, and crop ID are required.'
    ];
}

echo json_encode($response);
?>
