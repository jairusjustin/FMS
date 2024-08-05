<?php
include 'config.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cropsfieldId = $_POST['edit_cropsfield_id'];
    $activityId = $_POST['edit_activity_id'];
    $activityType = $_POST['edit_other_activity_type'];
    $activityDate = $_POST['edit_activity_date_other'];
    $harvestQuantity = $_POST['edit_harvest_quantity'];
    $expensesAmount = $_POST['edit_expenses_amount'];
    $seededArea = $_POST['edit_seeded_area'];

    // Validate expense
    if ($expensesAmount < 0) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a valid expense amount!'
        ];
    } elseif ($activityType == 'Harvest' && $harvestQuantity < 0) {
        $response = [
            'status' => 'error',
            'message' => 'Please enter a valid harvest quantity!'
        ];
    } else {
        // Update activities table
        $sql_activity = "UPDATE activities SET 
                        activity_type = ?,
                        activity_date = ?
                        WHERE activity_id = ?";

        $stmt_activity = mysqli_prepare($mysqli, $sql_activity);

        if ($stmt_activity) {
            mysqli_stmt_bind_param($stmt_activity, "ssi", $activityType, $activityDate, $activityId);
            if (mysqli_stmt_execute($stmt_activity)) {
                if ($activityType == 'Harvest') {
                    // Update harvest table
                    $sql_harvest = "UPDATE harvest SET 
                                    harvest_quantity = ?
                                    WHERE activity_id = ?";

                    $stmt_harvest = mysqli_prepare($mysqli, $sql_harvest);

                    if ($stmt_harvest) {
                        mysqli_stmt_bind_param($stmt_harvest, "di", $harvestQuantity, $activityId);

                        if (mysqli_stmt_execute($stmt_harvest)) {
                            // Update expenses table for harvest activities
                            // (no need to check seeded area for harvest)
                            updateExpenses($mysqli, $activityId, $expensesAmount);

                            $response = [
                                'status' => 'success',
                                'message' => 'Harvest activity and expenses updated successfully!'
                            ];
                        } else {
                            $response = [
                                'status' => 'error',
                                'message' => 'Error occurred while updating the harvest: ' . mysqli_error($mysqli)
                            ];
                        }

                        mysqli_stmt_close($stmt_harvest);
                    } else {
                        $response = [
                            'status' => 'error',
                            'message' => 'Database query preparation for harvest failed: ' . mysqli_error($mysqli)
                        ];
                    }
                } else if ($activityType == 'Planting') {
                    // Validate seeded area for planting activities
                    if ($seededArea < 0) {
                        $response = [
                            'status' => 'error',
                            'message' => 'Please enter a valid seeded area!'
                        ];
                    } else {
                        // Update seeded area for planting activities
                        $sql_seeded_area = "UPDATE seeded_area SET 
                                            area = ?
                                            WHERE cropsfield_id = ?";

                        $stmt_seeded_area = mysqli_prepare($mysqli, $sql_seeded_area);

                        if ($stmt_seeded_area) {
                            mysqli_stmt_bind_param($stmt_seeded_area, "di", $seededArea, $cropsfieldId);

                            if (mysqli_stmt_execute($stmt_seeded_area)) {
                                // Update expenses table for planting activities
                                updateExpenses($mysqli, $activityId, $expensesAmount);

                                $response = [
                                    'status' => 'success',
                                    'message' => 'Planting activity and expenses updated successfully!'
                                ];
                            } else {
                                $response = [
                                    'status' => 'error',
                                    'message' => 'Error occurred while updating the seeded area: ' . mysqli_error($mysqli)
                                ];
                            }

                            mysqli_stmt_close($stmt_seeded_area);
                        } else {
                            $response = [
                                'status' => 'error',
                                'message' => 'Database query preparation for seeded area failed: ' . mysqli_error($mysqli)
                            ];
                        }
                    }
                } else {
                    // Update expenses table for other activities
                    updateExpenses($mysqli, $activityId, $expensesAmount);

                    $response = [
                        'status' => 'success',
                        'message' => 'Activity and expenses updated successfully!'
                    ];
                }
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Error occurred while updating the activity: ' . mysqli_error($mysqli)
                ];
            }

            mysqli_stmt_close($stmt_activity);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Database query preparation for activity failed: ' . mysqli_error($mysqli)
            ];
        }
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method'
    ];
}

echo json_encode($response);

// Function to update expenses table
function updateExpenses($mysqli, $activityId, $expensesAmount) {
    $sql_expenses_check = "SELECT expense_id FROM expenses WHERE activity_id = ?";
    $stmt_expenses_check = mysqli_prepare($mysqli, $sql_expenses_check);

    if ($stmt_expenses_check) {
        mysqli_stmt_bind_param($stmt_expenses_check, "i", $activityId);
        mysqli_stmt_execute($stmt_expenses_check);
        mysqli_stmt_store_result($stmt_expenses_check);

        // If there isn't an expense for the activity, insert one
        if (mysqli_stmt_num_rows($stmt_expenses_check) == 0) {
            $sql_expenses_insert = "INSERT INTO expenses (activity_id, expense_amount) VALUES (?, ?)";
            $stmt_expenses_insert = mysqli_prepare($mysqli, $sql_expenses_insert);

            if ($stmt_expenses_insert) {
                mysqli_stmt_bind_param($stmt_expenses_insert, "id", $activityId, $expensesAmount);
                mysqli_stmt_execute($stmt_expenses_insert);
                mysqli_stmt_close($stmt_expenses_insert);
            }
        } else {
            // Update expenses amount if an expense record exists
            $sql_expenses_update = "UPDATE expenses SET 
                                    expense_amount = ?
                                    WHERE activity_id = ?";
            $stmt_expenses_update = mysqli_prepare($mysqli, $sql_expenses_update);

            if ($stmt_expenses_update) {
                mysqli_stmt_bind_param($stmt_expenses_update, "di", $expensesAmount, $activityId);
                mysqli_stmt_execute($stmt_expenses_update);
                mysqli_stmt_close($stmt_expenses_update);
            }
        }

        mysqli_stmt_close($stmt_expenses_check);
    }
}
?>
