<?php
include 'config.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cropsfield_id = $_POST['field_and_crop'];
    $activity_type = $_POST['other_activity_type'];
    $activity_date = $_POST['activity_date'];
    $expenseAmount = $_POST['expenseAmount'];

    if (empty($activity_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a date for the activity!']);
        exit;
    }

    if ($expenseAmount <= 0 || $expenseAmount === "") {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid expense amount!']);
        exit;
    }
    
    // Insert activity into the activities table
    $sql = "INSERT INTO activities (cropsfield_id, activity_type, activity_date) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($mysqli, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $cropsfield_id, $activity_type, $activity_date);

        if (mysqli_stmt_execute($stmt)) {
            $activityId = mysqli_insert_id($mysqli);

            // Insert expense into the expenses table
            $sqlExpense = "INSERT INTO expenses (activity_id, expense_amount) VALUES (?, ?)";
            $stmtExpense = mysqli_prepare($mysqli, $sqlExpense);
            mysqli_stmt_bind_param($stmtExpense, "id", $activityId, $expenseAmount);

            if (mysqli_stmt_execute($stmtExpense)) {
                $response['status'] = 'success';
                $response['message'] = 'Activity saved successfully!';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error occurred while saving the expense.';
            }

            mysqli_stmt_close($stmtExpense);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error adding activity: ' . mysqli_error($mysqli);
        }

        mysqli_stmt_close($stmt);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error in prepared statement: ' . mysqli_error($mysqli);
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>