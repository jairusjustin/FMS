<?php

include 'config.php'; 

session_start();

$response = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $cropName = trim($_POST['cropName']);
    $fieldName = trim($_POST['field_Name']); 

    // Validate crop name
    if (empty($cropName)) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a crop name!'
        ];
        sendResponse($response);
    }

    // Validate field name
    if (empty($fieldName)) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a field name!'
        ];
        sendResponse($response);
    }

    // Get the field ID from the selected field name
    $sql_field = "SELECT field_id FROM fields WHERE field_name = ?";
    $stmt_field = mysqli_prepare($mysqli, $sql_field);
    
    if (!$stmt_field) {
        $response = [
            'status' => 'error',
            'message' => 'Prepare failed: ' . mysqli_error($mysqli)
        ];
        sendResponse($response);
    }
    
    mysqli_stmt_bind_param($stmt_field, "s", $fieldName);
    mysqli_stmt_execute($stmt_field);
    $result_field = mysqli_stmt_get_result($stmt_field);
    $row_field = mysqli_fetch_assoc($result_field);
    $fieldId = $row_field['field_id'];

    // Get the crop ID from the selected crop name
    $sql_crop = "SELECT crops_id FROM crops WHERE crop_name = ?";
    $stmt_crop = mysqli_prepare($mysqli, $sql_crop);
    
    if (!$stmt_crop) {
        $response = [
            'status' => 'error',
            'message' => 'Prepare failed: ' . mysqli_error($mysqli)
        ];
        sendResponse($response);
    }
    
    mysqli_stmt_bind_param($stmt_crop, "s", $cropName);
    mysqli_stmt_execute($stmt_crop);
    $result_crop = mysqli_stmt_get_result($stmt_crop);
    $row_crop = mysqli_fetch_assoc($result_crop);
    $cropId = $row_crop['crops_id'];

    // Insert the new record into the cropsfield table
    $insert_cropsfield = "INSERT INTO cropsfield (crops_id, field_id) VALUES (?, ?)";
    $stmt_cropsfield = mysqli_prepare($mysqli, $insert_cropsfield);

    if (!$stmt_cropsfield) {
        $response = [
            'status' => 'error',
            'message' => 'Prepare failed: ' . mysqli_error($mysqli)
        ];
        sendResponse($response);
    }

    // Bind parameters
    $bind_cropsfield = mysqli_stmt_bind_param($stmt_cropsfield, "ii", $cropId, $fieldId);

    if (!$bind_cropsfield) {
        $response = [
            'status' => 'error',
            'message' => 'Binding parameters failed: ' . mysqli_error($mysqli)
        ];
        sendResponse($response);
    }

    // Execute the statement
    $execute_result_cropsfield = mysqli_stmt_execute($stmt_cropsfield);

    if ($execute_result_cropsfield) {
        // Crop addition success
        $response = [
            'status' => 'success',
            'message' => 'Crop Field addition successful'
        ];
        sendResponse($response);
    } else {
        // Crop addition failed
        $response = [
            'status' => 'error',
            'message' => 'Crop Field addition failed: ' . mysqli_error($mysqli)
        ];
        sendResponse($response);
    }
}

// Function to send JSON response
function sendResponse($response) {
    // Set Content-Type header for JSON
    header('Content-Type: application/json');

    // Send JSON response for AJAX
    echo json_encode($response);
    exit();
}
?>
