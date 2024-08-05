<?php
include 'config.php';

$sql_activities = "SELECT 
    cf.cropsfield_id, 
    CONCAT(f.field_name, ' - ', c.crop_name) as crop_field,
    a.activity_type as activity_type,
    a.activity_date as activity_date,
    a.activity_id as activity_id
FROM cropsfield cf
LEFT JOIN crops c ON c.crops_id = cf.crops_id
LEFT JOIN fields f ON cf.field_id = f.field_id
LEFT JOIN activities a ON cf.cropsfield_id = a.cropsfield_id
WHERE (c.is_deleted = 0 OR c.crops_id IS NULL) AND a.activity_date IS NOT NULL
ORDER BY a.activity_date DESC, crop_field ASC;";


$result = mysqli_query($mysqli, $sql_activities);

$activities = [];
while($row = mysqli_fetch_assoc($result)) {
    $activities[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $activities]);

mysqli_close($mysqli);
?>
