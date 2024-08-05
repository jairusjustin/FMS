<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropsfieldId = $_POST['field_and_crop'];
    $activityDate = $_POST['activity_date'];
    $harvestQuantity = $_POST['harvest_quantity'];
    $expenseAmount = $_POST['expenseAmount'];

    if (empty($activityDate)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a date for the activity!']);
        exit;
    }

    if ($harvestQuantity <= 0 || $harvestQuantity === "") {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid harvest quantity!']);
        exit;
    }

    if ($expenseAmount <= 0 || $expenseAmount === "") {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid expense amount!']);
        exit;
    }

    // Check if there is a planting activity for the same field with a later date
    $sql_check_planting = "SELECT 1 FROM activities WHERE cropsfield_id = ? AND activity_type = 'Planting' AND activity_date < ?";
    $stmt_check_planting = mysqli_prepare($mysqli, $sql_check_planting);
    mysqli_stmt_bind_param($stmt_check_planting, "is", $cropsfieldId, $activityDate);
    mysqli_stmt_execute($stmt_check_planting);
    mysqli_stmt_store_result($stmt_check_planting);

    if (mysqli_stmt_num_rows($stmt_check_planting) > 0) {
        // Insert harvest activity into the activities table
        $sql_activity = "INSERT INTO activities (cropsfield_id, activity_type, activity_date) VALUES (?, 'Harvest', ?)";
        
        $stmt_activity = mysqli_prepare($mysqli, $sql_activity);

        if ($stmt_activity) {
            mysqli_stmt_bind_param($stmt_activity, "is", $cropsfieldId, $activityDate);
            
            if (mysqli_stmt_execute($stmt_activity)) {
                $activityId = mysqli_insert_id($mysqli);  

                // Insert harvest quantity into the harvest table
                $sql_harvest = "INSERT INTO harvest (cropsfield_id, activity_id, harvest_quantity) VALUES (?, ?, ?)";
                
                $stmt_harvest = mysqli_prepare($mysqli, $sql_harvest);

                if ($stmt_harvest) {
                    mysqli_stmt_bind_param($stmt_harvest, "idi", $cropsfieldId, $activityId, $harvestQuantity);
                    
                    if (mysqli_stmt_execute($stmt_harvest)) {
                        // Insert expense
                        $sqlExpense = "INSERT INTO expenses (activity_id, expense_amount) VALUES (?, ?)";
                        $stmtExpense = mysqli_prepare($mysqli, $sqlExpense);
                        mysqli_stmt_bind_param($stmtExpense, "id", $activityId, $expenseAmount);
                        
                        if (mysqli_stmt_execute($stmtExpense)) {
                            $response = [
                                'status' => 'success',
                                'message' => 'Harvest and expense saved successfully!'
                            ];
                        } else {
                            $response = [
                                'status' => 'error',
                                'message' => 'Error occurred while saving the expense.',
                                'debug' => mysqli_error($mysqli)
                            ];
                        }
                    } else {
                        $response = [
                            'status' => 'error',
                            'message' => 'Error adding harvest quantity: ' . mysqli_error($mysqli)
                        ];
                    }

                    mysqli_stmt_close($stmt_harvest);
                    mysqli_stmt_close($stmtExpense);
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Error in prepared statement for harvest: ' . mysqli_error($mysqli)
                    ];
                }

            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Error adding harvest activity: ' . mysqli_error($mysqli)
                ];
            }

            mysqli_stmt_close($stmt_activity);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Error in prepared statement for harvest activity: ' . mysqli_error($mysqli)
            ];
        }
    } else {
        // There is no planting activity with a later date
        $response = [
            'status' => 'error',
            'message' => 'Please add a planting activity before adding a harvest for the same field'
        ];
    }

    echo json_encode($response);
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request'
    ];
    echo json_encode($response);
}
?>
