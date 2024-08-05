<?php
include 'config.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $sql = "SELECT * FROM user WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $response = [
            'status' => 'success',
            'data' => $row
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'User not found.'
        ];
    }

    echo json_encode($response);
}
?>