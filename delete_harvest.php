<?php
include 'config.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $harvest_id = $_POST['harvest_id'];

    // Delete related sales records first where type is Harvest
    $sql_delete_sales = "DELETE FROM sales WHERE harvest_id = ?";
    $stmt_delete_sales = mysqli_prepare($mysqli, $sql_delete_sales);

    if ($stmt_delete_sales) {
        mysqli_stmt_bind_param($stmt_delete_sales, "i", $harvest_id);

        if (mysqli_stmt_execute($stmt_delete_sales)) {
            // Fetch activity_id associated with the harvest_id
            $sql_fetch_activity_id = "SELECT activity_id FROM harvest WHERE harvest_id = ?";
            $stmt_fetch_activity_id = mysqli_prepare($mysqli, $sql_fetch_activity_id);

            if ($stmt_fetch_activity_id) {
                mysqli_stmt_bind_param($stmt_fetch_activity_id, "i", $harvest_id);
                mysqli_stmt_execute($stmt_fetch_activity_id);
                $result = mysqli_stmt_get_result($stmt_fetch_activity_id);
                $row = mysqli_fetch_assoc($result);
                $activity_id = $row['activity_id'] ?? null;

                mysqli_stmt_close($stmt_fetch_activity_id);

                // Delete related expenses records where activity_id is associated
                $sql_delete_expenses = "DELETE FROM expenses WHERE activity_id = ?";
                $stmt_delete_expenses = mysqli_prepare($mysqli, $sql_delete_expenses);

                if ($stmt_delete_expenses) {
                    mysqli_stmt_bind_param($stmt_delete_expenses, "i", $activity_id);

                    if (mysqli_stmt_execute($stmt_delete_expenses)) {
                        // Delete harvest record
                        $sql_delete_harvest = "DELETE FROM harvest WHERE harvest_id = ?";
                        $stmt_delete_harvest = mysqli_prepare($mysqli, $sql_delete_harvest);

                        // Delete activity record
                        $sql_delete_activity = "DELETE FROM activities WHERE activity_id = ?";
                        $stmt_delete_activity = mysqli_prepare($mysqli, $sql_delete_activity);

                        if ($stmt_delete_harvest && $stmt_delete_activity) {
                            mysqli_stmt_bind_param($stmt_delete_harvest, "i", $harvest_id);
                            mysqli_stmt_bind_param($stmt_delete_activity, "i", $activity_id);

                            if (mysqli_stmt_execute($stmt_delete_harvest) && mysqli_stmt_execute($stmt_delete_activity)) {
                                $response['status'] = 'success';
                                $response['message'] = 'Harvest and related expenses records deleted successfully!';
                            } else {
                                $response['status'] = 'error';
                                $response['message'] = 'Error deleting harvest, activity, or related expense record: ' . mysqli_error($mysqli);
                            }

                            mysqli_stmt_close($stmt_delete_harvest);
                            mysqli_stmt_close($stmt_delete_activity);
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'Error in prepared statement for deleting harvest or activity record: ' . mysqli_error($mysqli);
                        }
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Error deleting expenses records: ' . mysqli_error($mysqli);
                    }

                    mysqli_stmt_close($stmt_delete_expenses);
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Error in prepared statement for deleting expenses records: ' . mysqli_error($mysqli);
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error in prepared statement for fetching activity_id: ' . mysqli_error($mysqli);
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error deleting sales records: ' . mysqli_error($mysqli);
        }

        mysqli_stmt_close($stmt_delete_sales);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error in prepared statement for deleting sales records: ' . mysqli_error($mysqli);
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
