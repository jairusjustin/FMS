<?php
include 'config.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $expense_id = $_POST['expense_id'];
    $expense_amount = $_POST['expense_amount'];
    $activity_date = $_POST['activity_date'];

    if ($expense_amount <= 0 || !is_numeric($expense_amount)) {
        $response['status'] = 'error';
        $response['message'] = 'Please enter a valid expense amount!';
        echo json_encode($response);
        exit;
    }

    // Update expense amount
    $sql_update_expense = "UPDATE expenses 
                           SET expense_amount = ? 
                           WHERE expense_id = ?";
                          
    $stmt_update_expense = mysqli_prepare($mysqli, $sql_update_expense);

    if ($stmt_update_expense) {
        mysqli_stmt_bind_param($stmt_update_expense, "di", $expense_amount, $expense_id);
        
        if (mysqli_stmt_execute($stmt_update_expense)) {
            mysqli_stmt_close($stmt_update_expense);

            // Update activity date
            $sql_update_activity = "UPDATE activities 
                                    SET activity_date = ? 
                                    WHERE activity_id = (SELECT activity_id FROM expenses WHERE expense_id = ?)";
                                  
            $stmt_update_activity = mysqli_prepare($mysqli, $sql_update_activity);

            if ($stmt_update_activity) {
                mysqli_stmt_bind_param($stmt_update_activity, "si", $activity_date, $expense_id);

                if (mysqli_stmt_execute($stmt_update_activity)) {
                    $response['status'] = 'success';
                    $response['message'] = 'Expense and activity updated successfully!';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Error updating activity: ' . mysqli_error($mysqli);
                }

                mysqli_stmt_close($stmt_update_activity);
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error in prepared statement for updating activity: ' . mysqli_error($mysqli);
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error updating expense: ' . mysqli_error($mysqli);
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error in prepared statement for updating expense: ' . mysqli_error($mysqli);
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
