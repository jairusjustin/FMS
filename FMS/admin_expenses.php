<?php
include 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:index.php');
    exit;
}

$sql_expenses = "SELECT e.expense_id, CONCAT(f.field_name, ' - ', c.crop_name) AS crop_field, a.activity_date, a.activity_type, e.expense_amount
                FROM expenses e
                JOIN activities a ON e.activity_id = a.activity_id
                JOIN cropsfield cf ON a.cropsfield_id = cf.cropsfield_id
                JOIN crops c ON cf.crops_id = c.crops_id
                JOIN fields f ON cf.field_id = f.field_id
                ORDER BY e.expense_id DESC";
$result_expenses = $mysqli->query($sql_expenses);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saka-Insights</title>
    <!-- ======= Styles ====== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/dtable_style.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/icon.png"/>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.js"></script>
</head>

<body>
    <div class="my-container">
        <div class="row">
            <!-- Navigation Sidebar -->
            <div class="col-md-2">
                <div class="my-navigation active">
                    <ul class="my-nav">
                        <li class="my-nav-item">
                            <a class="my-nav-link" href="#">
                                <span class="my-icon">
                                    <img src="images/logo.png" alt="Your Logo">
                                </span>
                                <span class="my-title">Saka-Insights</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_dashboard.php">
                                <span class="my-icon">
                                    <ion-icon name="stats-chart-outline"></ion-icon>
                                </span>
                                <span class="my-title">Dashboard</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_weather.php">
                                <span class="my-icon">
                                    <ion-icon name="cloudy-night-outline"></ion-icon>
                                </span>
                                <span class="my-title">Weather</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_crops.php">
                                <span class="my-icon">
                                    <ion-icon name="leaf-outline"></ion-icon>
                                </span>
                                <span class="my-title">Crops</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_activity.php">
                                <span class="my-icon">
                                    <ion-icon name="reader-outline"></ion-icon>
                                </span>
                                <span class="my-title">Activities</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_harvest.php">
                                <span class="my-icon">
                                    <ion-icon name="basket-outline"></ion-icon>
                                </span>
                                <span class="my-title">Harvest</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_sales.php">
                                <span class="my-icon">
                                    <ion-icon name="cash-outline"></ion-icon>
                                </span>
                                <span class="my-title">Sales</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_expenses.php">
                                <span class="my-icon">
                                    <ion-icon name="receipt-outline"></ion-icon>
                                </span>
                                <span class="my-title">Expenses</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_users.php">
                                <span class="my-icon">
                                    <ion-icon name="people-outline"></ion-icon>
                                </span>
                                <span class="my-title">Users</span>
                            </a>
                        </li>
                        
                        <li class="my-nav-item">
                            <a class="my-nav-link" href="admin_settings.php">
                                <span class="my-icon">
                                    <ion-icon name="settings-outline"></ion-icon>
                                </span>
                                <span class="my-title">Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ========================= Main ==================== -->
            <div class="col-md-10">
                <div class="my-main active">
                    <div class="my-topbar">
                        <div class="my-toggle">
                            <ion-icon name="menu-outline"></ion-icon>
                        </div>
                        <div class="my-user" onclick="signOut()">
                            <ion-icon name="log-out-outline" class="my-settings-icon"></ion-icon>
                        </div>
                    </div>
<!-- Finance Management Cards -->
<div class="row">

    <!-- Manage Expenses Card -->
    <div class="col-md-12">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Expenses Records</h3>
            </div>
            <!-- Table for Expenses Management -->
            <div class="table-responsive">
                <table id="fieldTable" class="table table-striped data-table table-bordered">
                    <thead>
                        <tr>
                            <th>Expense ID</th>
                            <th>Crop Field</th>
                            <th>Expense Date</th>
                            <th>Expense Type</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result_expenses as $expense): ?>
                        <tr>
                            <td><?php echo $expense['expense_id']; ?></td>
                            <td><?php echo $expense['crop_field']; ?></td>
                            <td><?php echo $expense['activity_date']; ?></td>
                            <td><?php echo $expense['activity_type']; ?></td>
                            <td>₱<?php echo $expense['expense_amount']; ?></td>
                            <td>
                                <div class="my-action-icons">
                                    <button class="my-icon-btn editBtn" onclick="editExpense(<?php echo $expense['expense_id']; ?>)"><ion-icon name="create-outline"></ion-icon></button>
                                    <button class="my-icon-btn deleteBtn" onclick="deleteExpense(<?php echo $expense['expense_id']; ?>)"><ion-icon name="trash-outline"></ion-icon></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" role="dialog" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editExpenseForm">
                    <div class="form-group">
                        <label for="edit_expense_id">Expense ID</label>
                        <input type="text" class="form-control" id="edit_expense_id" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_crop_field">Crop Field</label>
                        <input type="text" class="form-control" id="edit_crop_field" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_activity_date">Expense Date</label>
                        <input type="date" class="form-control" id="edit_activity_date">
                    </div>
                    <div class="form-group">
                        <label for="edit_activity_type">Expense Type</label>
                        <input type="text" class="form-control" id="edit_activity_type" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_expense_amount">Amount</label>
                        <input type="number" class="form-control" id="edit_expense_amount" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateExpense()">Save changes</button>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- Bootstrap Data Tables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<!-- =========== Scripts =========  -->
<script src="js/script.js"></script>

<!-- ====== ionicons ======= -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<script>
    $(document).ready(function () {
        var fieldTable = $('#fieldTable').DataTable({
            "info": false,
            "order": [[0, "desc"]]
        });

        // Add click event to pagination buttons to synchronize active page highlighting
        $('#fieldTable').on('click', '.pagination a', function () {
            var table = $(this).closest('table').DataTable();

            // Remove active class from all pagination buttons
            $(this).closest('.pagination').find('a').removeClass('active').css({
                'background-color': 'var(--tertiary-color)',
                'border': '1px solid var(--tertiary-color)'
            });

            // Add active class to the clicked pagination button
            $(this).addClass('active').css({
                'background-color': 'var(--secondary-color)',
                'border': '1px solid var(--secondary-color)'
            });

            // Trigger the page change in the DataTable
            table.page($(this).data('page')).draw('page');
        });

        // Initialize DataTables
        fieldTable.draw();
    });

    function deleteExpense(expenseId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this expense.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "delete_expense.php",
                    data: {expense_id: expenseId},
                    dataType: "json",
                    success: function (response) {
                        console.log(response);
                        if (response.status == 'success') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'The expense and associated activity have been deleted.',
                                icon: 'success',
                                confirmButtonColor: '#436850'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                        Swal.fire(
                            'Error!',
                            'Error occurred while deleting the expense.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function editExpense(expenseId) {
        $('#editExpenseModal').modal('show').on('shown.bs.modal', function () {
            $.ajax({
                type: "POST",
                url: "get_expense.php",
                data: {expense_id: expenseId},
                dataType: "json",
                success: function (response) {
                    if (response.status == 'success') {
                        $('#edit_expense_id').val(response.data.expense_id);
                        $('#edit_crop_field').val(response.data.crop_field);
                        $('#edit_activity_date').val(response.data.activity_date);
                        $('#edit_activity_type').val(response.data.activity_type);
                        $('#edit_expense_amount').val(response.data.expense_amount);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            confirmButtonColor: '#436850',
                            text: response.message
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        confirmButtonColor: '#436850',
                        text: 'Error occurred while fetching the expense details.'
                    });
                }
            });
        });
    }

    function updateExpense() {
        var formData = {
            expense_id: $('#edit_expense_id').val(),
            expense_amount: $('#edit_expense_amount').val(),
            activity_date: $('#edit_activity_date').val()
        };

        if (formData.expense_amount <= 0 || isNaN(formData.expense_amount)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                confirmButtonColor: '#436850',
                text: 'Expense amount should be a positive number.'
            });
            return;
        }

        console.log("Sent data: ", formData);

        $.ajax({
            type: "POST",
            url: "update_expense.php",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        confirmButtonColor: '#436850',
                        text: 'Expense updated successfully!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#editExpenseModal').modal('hide');
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        confirmButtonColor: '#436850',
                        text: response.message
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    confirmButtonColor: '#436850',
                    text: 'Error occurred while updating the expense.'
                });
            }
        });
    }

    // Sign Out
    function signOut() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to sign out.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#436850',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, sign out!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "logout.php";
            }
        });
    }
</script>
