<?php
include 'config.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if (isset($_POST['expense_id'])) {
        $expense_id = $_POST['expense_id'];

        // Delete the expense record
        $sql_delete_expense = "DELETE FROM expenses WHERE expense_id = ?";
        $stmt_delete_expense = mysqli_prepare($mysqli, $sql_delete_expense);

        if ($stmt_delete_expense) {
            mysqli_stmt_bind_param($stmt_delete_expense, "i", $expense_id);
            if (mysqli_stmt_execute($stmt_delete_expense)) {
                $response['status'] = 'success';
                $response['message'] = 'Expense deleted successfully!';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error deleting expense: ' . mysqli_error($mysqli);
            }
            mysqli_stmt_close($stmt_delete_expense);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error in prepared statement for deleting expense: ' . mysqli_error($mysqli);
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Missing expense ID in the request';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
