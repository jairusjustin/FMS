<?php
include 'config.php';

$response = [];

if (isset($_POST['cropsfield_id'])) {
    $cropsfieldId = $_POST['cropsfield_id'];

    $sql = "SELECT cf.cropsfield_id, c.crop_name, f.field_name
            FROM cropsfield cf
            LEFT JOIN crops c ON cf.crops_id = c.crops_id
            LEFT JOIN fields f ON cf.field_id = f.field_id
            WHERE cf.cropsfield_id = ? AND f.is_deleted = 0";

    $stmt = mysqli_prepare($mysqli, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $cropsfieldId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $response = [
                'status' => 'success',
                'data' => $row
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'No crop field found with the given ID'
            ];
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Database query preparation failed'
        ];
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Missing cropsfield_id parameter'
    ];
}

echo json_encode($response);
?>
