<?php
include 'config.php';

if(isset($_POST['field_id'])) {
    $field_id = $_POST['field_id'];

    $sql = "SELECT * FROM fields WHERE field_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $field_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()) {
        $response = [
            'status' => 'success',
            'data' => $row
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Field not found.'
        ];
    }

    echo json_encode($response);
}
?>
