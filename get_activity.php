<?php
include 'config.php';

$response = [];

if (isset($_POST['activity_id'])) {
    $activityId = $_POST['activity_id'];

    $sql = "SELECT 
    a.activity_id,
    cf.cropsfield_id, 
    CONCAT(f.field_name, ' - ', c.crop_name) as crop_field,
    a.activity_type,
    a.activity_date,
    e.expense_amount,
    h.harvest_quantity,
    sa.area AS seeded_area
FROM activities a
LEFT JOIN cropsfield cf ON cf.cropsfield_id = a.cropsfield_id
LEFT JOIN seeded_area sa ON cf.cropsfield_id = sa.cropsfield_id
LEFT JOIN crops c ON c.crops_id = cf.crops_id
LEFT JOIN fields f ON f.field_id = cf.field_id
LEFT JOIN expenses e ON a.activity_id = e.activity_id
LEFT JOIN harvest h ON a.activity_id = h.activity_id
WHERE (c.is_deleted = 0 OR c.crops_id IS NULL) AND a.activity_date IS NOT NULL AND a.activity_id = ?";

    $stmt = mysqli_prepare($mysqli, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $activityId);
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
                'message' => 'No activity found with the given ID'
            ];
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Database query preparation failed: ' . mysqli_error($mysqli)
        ];
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Missing activity_id parameter'
    ];
}

echo json_encode($response);
?>