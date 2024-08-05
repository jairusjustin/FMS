<?php
include 'config.php';

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sale_id = $_POST['sale_id'];

    // Delete sale from the database
    $sql = "DELETE FROM sales WHERE sale_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $sale_id);

    if ($stmt->execute()) {
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error deleting the sale: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
?>
