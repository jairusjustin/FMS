<?php
include 'config.php';

if(isset($_POST['crops_id'])) {
    $crops_id = $_POST['crops_id'];

    $sql = "SELECT * FROM crops WHERE crops_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $crops_id);
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
            'message' => 'Crop not found.'
        ];
    }

    echo json_encode($response);
}
?>
