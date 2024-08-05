<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $role = $_POST['role'];

    $sql = "INSERT INTO `user` (email, role) VALUES (?, ?)";
    $stmt = mysqli_prepare($mysqli, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $role);
        if (mysqli_stmt_execute($stmt)) {
            $response = [
                'status' => 'success',
                'message' => 'User added successfully'
            ];
            sendResponse($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Error adding user'
            ];
            sendResponse($response);
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error preparing statement'
        ];
        sendResponse($response);
    }
}
?>
