<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['field_id'])) {
    $fieldId = $_POST['field_id'];

    if (!is_numeric($fieldId) || $fieldId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid field_id.']);
        exit;
    }

    $sqlCheck = "SELECT * FROM cropsfield WHERE field_id = ?";
    $stmtCheck = mysqli_prepare($mysqli, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "i", $fieldId);
    mysqli_stmt_execute($stmtCheck);
    mysqli_stmt_store_result($stmtCheck);

    if (mysqli_stmt_num_rows($stmtCheck) > 0) {
        if (!mysqli_query($mysqli, "UPDATE fields SET is_deleted = 1 WHERE field_id = $fieldId")) {
            $errorMessage = 'Failed to soft delete field: ' . mysqli_error($mysqli);
            echo json_encode(['status' => 'error', 'message' => $errorMessage]);
            error_log($errorMessage);
            exit;
        }
        echo json_encode(['status' => 'success']);
        error_log("Field with ID $fieldId soft deleted successfully due to foreign key constraint.");
    } else {
        if (!mysqli_query($mysqli, "DELETE FROM fields WHERE field_id = $fieldId")) {
            $errorMessage = 'Failed to delete field: ' . mysqli_error($mysqli);
            echo json_encode(['status' => 'error', 'message' => $errorMessage]);
            error_log($errorMessage);
            exit;
        }
        echo json_encode(['status' => 'success']);
        error_log("Field with ID $fieldId deleted successfully.");
    }

    mysqli_stmt_close($stmtCheck);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    error_log('Invalid request.');
}

mysqli_close($mysqli);
?>
