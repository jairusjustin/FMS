<?php
include 'config.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $farm_id = $_POST['farm_id'];
    $farmName = trim($_POST['farmName']);
    $farmLocation = trim($_POST['farmLocation']);

    // Validate farmName and farmLocation
    if(empty($farmName) || empty($farmLocation)) {
        $response = [
            'status' => 'error',
            'message' => 'Farm name and location are required.'
        ];
    } else {
        // Update the farm details in the database
        $sql = "UPDATE farm_details SET farm_name = ?, farm_location = ? WHERE farm_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssi", $farmName, $farmLocation, $farm_id);

        if ($stmt->execute()) {
            $response = [
                'status' => 'success',
                'message' => 'Farm details updated successfully!'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Error updating farm details: ' . $stmt->error
            ];
        }
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Farm ID, farm name, and location are required.'
    ];
}

$mysqli->close();

echo json_encode($response);
exit;
?>
