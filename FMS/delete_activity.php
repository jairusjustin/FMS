<?php
include 'config.php';

// Initialize response array
$response = array();

if(isset($_POST['activity_id']) && isset($_POST['activity_type'])) {
    $activityId = $_POST['activity_id'];
    $activityType = $_POST['activity_type'];


    error_log("Received activity_id: $activityId, activity_type: $activityType");


    error_log("Attempting to delete activity with ID: $activityId and type: $activityType");

    if($activityType == 'Harvest') {
        // Delete associated sales
        $deleteSales = "DELETE FROM sales WHERE harvest_id IN (SELECT harvest_id FROM harvest WHERE activity_id = ?)";
        $stmt = mysqli_prepare($mysqli, $deleteSales);
        mysqli_stmt_bind_param($stmt, "i", $activityId);
        $deleteSalesResult = mysqli_stmt_execute($stmt);

        if(!$deleteSalesResult) {
            $response['status'] = 'error';
            $response['message'] = 'Error deleting associated sales!';
            error_log("Error deleting associated sales for activity ID: $activityId");
            echo json_encode($response);
            exit;
        } else {
            error_log("Deleted associated sales for activity ID: $activityId");
        }

        mysqli_stmt_close($stmt);

        // Delete associated harvests
        $deleteHarvests = "DELETE FROM harvest WHERE activity_id = ?";
        $stmt = mysqli_prepare($mysqli, $deleteHarvests);
        mysqli_stmt_bind_param($stmt, "i", $activityId);
        $deleteHarvestsResult = mysqli_stmt_execute($stmt);

        if(!$deleteHarvestsResult) {
            $response['status'] = 'error';
            $response['message'] = 'Error deleting associated harvests!';
            error_log("Error deleting associated harvests for activity ID: $activityId");
            echo json_encode($response);
            exit;
        } else {
            error_log("Deleted associated harvests for activity ID: $activityId");
        }

        mysqli_stmt_close($stmt);
    }

    // Delete associated expenses
    $deleteExpenses = "DELETE FROM expenses WHERE activity_id = ?";
    $stmt = mysqli_prepare($mysqli, $deleteExpenses);
    mysqli_stmt_bind_param($stmt, "i", $activityId);
    $deleteExpensesResult = mysqli_stmt_execute($stmt);

    if(!$deleteExpensesResult) {
        $response['status'] = 'error';
        $response['message'] = 'Error deleting associated expenses!';
        error_log("Error deleting associated expenses for activity ID: $activityId");
        echo json_encode($response);
        exit;
    } else {
        error_log("Deleted associated expenses for activity ID: $activityId");
    }

    mysqli_stmt_close($stmt);

    // Delete associated seeded area
    $deleteSeededArea = "DELETE FROM seeded_area WHERE cropsfield_id IN (SELECT cropsfield_id FROM activities WHERE activity_id = ?)";
    $stmt = mysqli_prepare($mysqli, $deleteSeededArea);
    mysqli_stmt_bind_param($stmt, "i", $activityId);
    $deleteSeededAreaResult = mysqli_stmt_execute($stmt);

    if(!$deleteSeededAreaResult) {
        $response['status'] = 'error';
        $response['message'] = 'Error deleting associated seeded area!';
        error_log("Error deleting associated seeded area for activity ID: $activityId");
        echo json_encode($response);
        exit;
    } else {
        error_log("Deleted associated seeded area for activity ID: $activityId");
    }

    mysqli_stmt_close($stmt);

    // Delete the activity
    $deleteActivity = "DELETE FROM activities WHERE activity_id = ?";
    $stmt = mysqli_prepare($mysqli, $deleteActivity);
    mysqli_stmt_bind_param($stmt, "i", $activityId);
    $deleteActivityResult = mysqli_stmt_execute($stmt);

    if($deleteActivityResult) {
        $response['status'] = 'success';
        $response['message'] = 'Activity deleted successfully!';
        error_log("Deleted activity with ID: $activityId");
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error deleting activity!';
        error_log("Error deleting activity with ID: $activityId");
    }

    mysqli_stmt_close($stmt);

} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request!';
    error_log("Invalid request received");
}

echo json_encode($response);
?>
