<?php
include 'config.php'; 
session_start();

$response = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $fieldName = trim($_POST['fieldName']);
    $fieldArea = trim($_POST['fieldArea']);
    $fieldStatus = trim($_POST['fieldStatus']);

    // Validate field name
    if (empty($fieldName)) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a field name!'
        ];
        sendResponse($response);
    }

    // Validate field area
    if (empty($fieldArea)) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a field area!'
        ];
        sendResponse($response);
    } elseif (!is_numeric($fieldArea) || $fieldArea <= 0) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a valid field area!'
        ];
        sendResponse($response);
    }

    // Validate field status
    if (empty($fieldStatus)) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a field status!'
        ];
        sendResponse($response);
    }

    // Check if field name already exists (including soft deleted)
    $check_field = "SELECT field_id FROM fields WHERE field_name = ?";
    $stmt_check = mysqli_prepare($mysqli, $check_field);

    if ($stmt_check) {
        mysqli_stmt_bind_param($stmt_check, "s", $fieldName);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            // Field already exists, update area and undelete if soft deleted
            $update_field = "UPDATE fields SET field_area = ?, field_status = ?, is_deleted = 0 WHERE field_name = ?";
            $stmt_update = mysqli_prepare($mysqli, $update_field);

            if ($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, "dss", $fieldArea, $fieldStatus, $fieldName);
                mysqli_stmt_execute($stmt_update);

                $response = [
                    'status' => 'success',
                    'message' => 'Field updated successfully'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to update field: ' . mysqli_error($mysqli)
                ];
            }

            mysqli_stmt_close($stmt_update);
        } else {
            // Field does not exist, insert new field
            $insert_field = "INSERT INTO fields (field_name, field_area, field_status) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($mysqli, $insert_field);

            if ($stmt_insert) {
                mysqli_stmt_bind_param($stmt_insert, "sds", $fieldName, $fieldArea, $fieldStatus);
                mysqli_stmt_execute($stmt_insert);

                $response = [
                    'status' => 'success',
                    'message' => 'Field added successfully'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to add field: ' . mysqli_error($mysqli)
                ];
            }

            mysqli_stmt_close($stmt_insert);
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Prepare failed: ' . mysqli_error($mysqli)
        ];
    }

    mysqli_stmt_close($stmt_check);
}

sendResponse($response);

// Function to send JSON response
function sendResponse($response) {
    // Set Content-Type header for JSON
    header('Content-Type: application/json');

    // Send JSON response for AJAX
    echo json_encode($response);
    exit();
}

?>
