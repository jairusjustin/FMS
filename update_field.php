<?php
include 'config.php';

$response = [];

if(isset($_POST['field_id'], $_POST['field_name'], $_POST['field_area'], $_POST['field_status'])) {
    
    $field_id = $_POST['field_id'];
    $field_name = trim($_POST['field_name']);
    $field_area = floatval($_POST['field_area']);
    $field_status = $_POST['field_status']; 

    // Validate field_name
    if(empty($field_name)) {
        $response = [
            'status' => 'error',
            'message' => 'Field name cannot be empty.'
        ];
    } 
    // Validate field_area
    elseif($field_area <= 0) {
        $response = [
            'status' => 'error',
            'message' => 'Field area should be greater than 0.'
        ];
    } 
    // Validate field_id
    elseif(empty($field_id)) {
        $response = [
            'status' => 'error',
            'message' => 'Field ID cannot be empty.'
        ];
    }
    else {
        // Check if the field_name already exists
        $check_sql = "SELECT field_id FROM fields WHERE field_name = ? AND field_id != ?";
        $check_stmt = $mysqli->prepare($check_sql);
        $check_stmt->bind_param("si", $field_name, $field_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $response = [
                'status' => 'error',
                'message' => 'Field name already exists.'
            ];
        } else {
            // Update the field
            $sql = "UPDATE fields SET field_name = ?, field_area = ?, field_status = ? WHERE field_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sdsi", $field_name, $field_area, $field_status, $field_id);

            if($stmt->execute()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Field updated successfully!'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to update field.'
                ];
            }
        }
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'All fields are required.'
    ];
}

echo json_encode($response);
?>
