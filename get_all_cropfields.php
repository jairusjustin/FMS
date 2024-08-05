<?php
// Include your database connection file here
include 'config.php';

$minYear = $_GET['minYear'];

$query = "SELECT * FROM cropsfield WHERE cropsfield_id IN (
    SELECT DISTINCT cropsfield_id FROM activities WHERE YEAR(activity_date) >= $minYear
)";

$result = mysqli_query($mysqli, $query);

if ($result) {
    $cropFields = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $cropFields[] = $row;
    }

    echo json_encode(['status' => 'success', 'cropFields' => $cropFields]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch crop fields']);
}
?>
