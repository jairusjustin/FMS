<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    if (!is_numeric($userId) || $userId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user_id.']);
        exit;
    }

    if (!mysqli_query($mysqli, "DELETE FROM user WHERE user_id = $userId")) {
        $errorMessage = 'Failed to delete user: ' . mysqli_error($mysqli);
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
        error_log($errorMessage);
        exit;
    }

    echo json_encode(['status' => 'success']);
    error_log("User deleted successfully.");
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    error_log('Invalid request.');
}

mysqli_close($mysqli);
?>
