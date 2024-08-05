<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crops_id'])) {
    $cropsId = $_POST['crops_id'];

    if (!is_numeric($cropsId) || $cropsId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid crops_id.']);
        exit;
    }

    $sqlCheck = "SELECT * FROM cropsfield WHERE crops_id = ?";
    $stmtCheck = mysqli_prepare($mysqli, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "i", $cropsId);
    mysqli_stmt_execute($stmtCheck);
    mysqli_stmt_store_result($stmtCheck);

    if (mysqli_stmt_num_rows($stmtCheck) > 0) {
        if (!mysqli_query($mysqli, "UPDATE crops SET is_deleted = 1 WHERE crops_id = $cropsId")) {
            $errorMessage = 'Failed to soft delete crop: ' . mysqli_error($mysqli);
            echo json_encode(['status' => 'error', 'message' => $errorMessage]);
            error_log($errorMessage);
            exit;
        }
        echo json_encode(['status' => 'success']);
        error_log("Crop with ID $cropsId soft deleted successfully due to foreign key constraint.");
    } else {
        if (!mysqli_query($mysqli, "DELETE FROM crops WHERE crops_id = $cropsId")) {
            $errorMessage = 'Failed to delete crop: ' . mysqli_error($mysqli);
            echo json_encode(['status' => 'error', 'message' => $errorMessage]);
            error_log($errorMessage);
            exit;
        }
        echo json_encode(['status' => 'success']);
        error_log("Crop with ID $cropsId deleted successfully.");
    }

    mysqli_stmt_close($stmtCheck);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    error_log('Invalid request.');
}

mysqli_close($mysqli);
?>
