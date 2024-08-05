<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cropsfield_id'])) {
    $cropsfield_id = $_POST['cropsfield_id'];

    $sql = "SELECT 
    cf.cropsfield_id, 
    CONCAT(f.field_name, ' - ', c.crop_name) as crop_field,
    a.activity_type as activity_type,
    a.activity_date as activity_date,
    a.activity_id as activity_id
FROM cropsfield cf
LEFT JOIN crops c ON c.crops_id = cf.crops_id
LEFT JOIN fields f ON cf.field_id = f.field_id
LEFT JOIN activities a ON cf.cropsfield_id = a.cropsfield_id
WHERE (c.is_deleted = 0 OR c.crops_id IS NULL) AND cf.cropsfield_id = ? AND a.activity_date IS NOT NULL
ORDER BY a.activity_date DESC, crop_field ASC;";
    

    $stmt = mysqli_prepare($mysqli, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $cropsfield_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error fetching activities']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

?>
