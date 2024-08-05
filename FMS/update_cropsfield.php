<?php
include 'config.php';

$response = [];

if (isset($_POST['cropsfield_id'], $_POST['cropName'], $_POST['field_Name'])) {
    $cropfield_id = $_POST['cropsfield_id'];
    $crop_name = trim($_POST['cropName']);
    $field_name = trim($_POST['field_Name']);

    $sql = "UPDATE cropsfield 
            SET crops_id = (SELECT crops_id FROM crops WHERE crop_name = ?), 
                field_id = (SELECT field_id FROM fields WHERE field_name = ?)
            WHERE cropsfield_id = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssi", $crop_name, $field_name, $cropfield_id);
    
    if ($stmt->execute()) {
        $response = [
            'status' => 'success',
            'message' => 'Crop field updated successfully!'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error updating crop field: ' . $stmt->error
        ];
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request'
    ];
}

echo json_encode($response);
?>
