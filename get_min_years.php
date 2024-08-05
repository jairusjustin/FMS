<?php
include 'config.php';

$query = "SELECT DISTINCT YEAR(activity_date) AS min_year FROM activities";

$result = mysqli_query($mysqli, $query);

if ($result) {
    $years = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $years[] = $row['min_year'];
    }

    echo json_encode(array('status' => 'success', 'data' => $years));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Error occurred while fetching minimum years.'));
}

mysqli_close($mysqli);
?>
