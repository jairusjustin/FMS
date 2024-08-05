<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php'; 

session_start();

$response = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $cropName = trim($_POST['cropName']);
    $defaultSalePrice = floatval($_POST['defaultSalePrice']); 

    // Validate crop name
    if (empty($cropName)) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a crop name!'
        ];
        sendResponse($response);
    }

    // Validate default sale price
    if (!filter_var($defaultSalePrice, FILTER_VALIDATE_FLOAT) || $defaultSalePrice < 0) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a valid sale price!'
        ];
        sendResponse($response);
    }

    // Check if crop name already exists
    $check_crop = "SELECT crops_id, is_deleted FROM crops WHERE crop_name = ?";
    $stmt_check = mysqli_prepare($mysqli, $check_crop);

    // Check for SQL error
    if (!$stmt_check) {
        $response = [
            'status' => 'error',
            'message' => 'Prepare failed: ' . mysqli_error($mysqli)
        ];
        sendResponse($response);
    }

    // Bind parameter
    $bind_check = mysqli_stmt_bind_param($stmt_check, "s", $cropName);

    // Check for binding error
    if (!$bind_check) {
        $response = [
            'status' => 'error',
            'message' => 'Binding parameters failed: ' . mysqli_error($mysqli)
        ];
        sendResponse($response);
    }

    // Execute the statement
    mysqli_stmt_execute($stmt_check);

    // Store result
    mysqli_stmt_store_result($stmt_check);

    // Check if crop name already exists
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        // Bind result variables
        mysqli_stmt_bind_result($stmt_check, $cropId, $isDeleted);
        mysqli_stmt_fetch($stmt_check);

        if ($isDeleted == 1) {
            // Update the existing crop record
            $update_crop = "UPDATE crops SET is_deleted = 0, default_sale_price = ? WHERE crops_id = ?";
            $stmt_update = mysqli_prepare($mysqli, $update_crop);

            // Check for SQL error
            if (!$stmt_update) {
                $response = [
                    'status' => 'error',
                    'message' => 'Prepare failed: ' . mysqli_error($mysqli)
                ];
                sendResponse($response);
            }

            // Bind parameters
            $bind_update = mysqli_stmt_bind_param($stmt_update, "di", $defaultSalePrice, $cropId);

            // Check for binding error
            if (!$bind_update) {
                $response = [
                    'status' => 'error',
                    'message' => 'Binding parameters failed: ' . mysqli_error($mysqli)
                ];
                sendResponse($response);
            }

            // Execute the statement
            $execute_result = mysqli_stmt_execute($stmt_update);

            if ($execute_result) {
                // Crop update success
                $response = [
                    'status' => 'success',
                    'message' => 'Crop added successfully'
                ];
                sendResponse($response);
            } else {
                // Crop update failed
                $response = [
                    'status' => 'error',
                    'message' => 'Crop addition failed: ' . mysqli_error($mysqli)
                ];
                sendResponse($response);
            }
        } else {
            // Crop already exists and is not deleted
            $response = [
                'status' => 'error',
                'message' => 'Crop name already exists'
            ];
            sendResponse($response);
        }
    } else {
        // Insert the new crop into the database
        $insert_crop = "INSERT INTO crops (crop_name, default_sale_price) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($mysqli, $insert_crop);

        // Check for SQL error
        if (!$stmt_insert) {
            $response = [
                'status' => 'error',
                'message' => 'Prepare failed: ' . mysqli_error($mysqli)
            ];
            sendResponse($response);
        }

        // Bind parameters
        $bind_result = mysqli_stmt_bind_param($stmt_insert, "sd", $cropName, $defaultSalePrice);

        // Check for binding error
        if (!$bind_result) {
            $response = [
                'status' => 'error',
                'message' => 'Binding parameters failed: ' . mysqli_error($mysqli)
            ];
            sendResponse($response);
        }

        // Execute the statement
        $execute_result = mysqli_stmt_execute($stmt_insert);

        if ($execute_result) {
            // Crop addition success
            $response = [
                'status' => 'success',
                'message' => 'Crop addition successful'
            ];
            sendResponse($response);
        } else {
            // Crop addition failed
            $response = [
                'status' => 'error',
                'message' => 'Crop addition failed: ' . mysqli_error($mysqli)
            ];
            sendResponse($response);
        }
    }
}


function sendResponse($response) {
    header('Content-Type: application/json');

    echo json_encode($response);
    exit();
}

?>
