<?php
include 'config.php';

session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch user details excluding admins and declined users
$sql_users = "SELECT * FROM user WHERE role = 'user'";
$stmt_users = mysqli_prepare($mysqli, $sql_users);
$users = [];

if ($stmt_users) {
    mysqli_stmt_execute($stmt_users);
    $result_users = mysqli_stmt_get_result($stmt_users);
    
    while ($row_user = mysqli_fetch_assoc($result_users)) {
        $users[] = $row_user;
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Error fetching user details'
    ];
    sendResponse($response);
}

// Your sendResponse function
function sendResponse($response) {
    echo json_encode($response);
}


$sqlApproved = "SELECT * FROM `user` WHERE role = 'admin' OR role = 'user'";
$resultApproved = $mysqli->query($sqlApproved);

$sqlPendingDeclined = "SELECT * FROM `user` WHERE role = 'pending' OR role = 'declined'";
$resultPendingDeclined = $mysqli->query($sqlPendingDeclined);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saka-Insights - Users</title>
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

<!-- User Management Cards -->
<div class="row">
    <!-- Approved Users Card -->
    <div class="col-md-6">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Manage Users</h3>
            </div>
            <!-- Table for User Management -->
            <div class="table-responsive">
                <table id="userTable" class="table table-striped data-table table-bordered">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . $user['user_id'] . "</td>";
                        echo "<td>" . $user['email'] . "</td>";
                        echo "<td>" . $user['role'] . "</td>";
                        echo "<td>";
                        echo "<div class='my-action-icons'>";
                        echo "<button class='my-icon-btn editBtn' onclick='editUser(" . $user['user_id'] . ")'><ion-icon name='create-outline'></ion-icon></button>";
                        echo "<button class='my-icon-btn deleteBtn' onclick='deleteUser(" . $user['user_id'] . ")'><ion-icon name='trash-outline'></ion-icon></button>";
                        if ($user['role'] == 'pending') {
                            echo "<button class='my-icon-btn approveBtn' onclick='approveUser(" . $user['user_id'] . ")'><ion-icon name='checkmark-outline'></ion-icon></button>";
                            echo "<button class='my-icon-btn declineBtn' onclick='declineUser(" . $user['user_id'] . ")'><ion-icon name='close-outline'></ion-icon></button>";
                        }
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending and Declined Users Card -->
    <div class="col-md-6">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Manage Approvals</h3>
                <div class="my-actions">
                    <select class='btn' id="statusFilter" class="form-select">
                        <option value="all">All</option>
                        <option value="pending">Pending</option>
                        <option value="declined">Declined</option>
                    </select>
                </div>
            </div>
            <!-- Table for Pending and Declined User Management -->
            <div class="table-responsive">
                <table id="pendingDeclinedUserTable" class="table table-striped data-table table-bordered">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($resultPendingDeclined as $row): ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo ($row['role'] == 'pending' ? 'Pending' : 'Declined'); ?></td>
                            <td>
                                <div class='my-action-icons'>
                                <?php if ($row['role'] == 'pending'): ?>
                                    <button class='my-icon-btn approveBtn' onclick='approveUser(<?php echo $row['user_id']; ?>)'><ion-icon name='checkmark-outline'></ion-icon></button>
                                    <button class='my-icon-btn declineBtn' onclick='declineUser(<?php echo $row['user_id']; ?>)'><ion-icon name='close-outline'></ion-icon></button>
                                <?php elseif ($row['role'] == 'declined'): ?>
                                    <button class='my-icon-btn deleteBtn' onclick='deleteUser(<?php echo $row['user_id']; ?>)'><ion-icon name='trash-outline'></ion-icon></button>
                                <?php endif; ?>
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

<!-- Edit User Form Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" readonly required>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-control" id="editRole" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <input type="hidden" id="editUserId" name="user_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">Save changes</button>
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
            var userTable = $('#userTable').DataTable({
                "info": false
            });

            var pendingDeclinedUserTable = $('#pendingDeclinedUserTable').DataTable({
                "info": false
            });

            // Add click event to pagination buttons to synchronize active page highlighting
            $('#userTable, #pendingDeclinedUserTable').on('click', '.pagination a', function () {
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
            userTable.draw();
            pendingDeclinedUserTable.draw();

            // Filter users based on status
            $('#statusFilter').change(function () {
                var status = $(this).val();

                if (status === 'all') {
                    pendingDeclinedUserTable.columns(2).search('').draw(); // Change column index to 2
                } else {
                    pendingDeclinedUserTable.columns(2).search(status).draw(); // Change column index to 2
                }
            }).val('pending').trigger('change'); // Set default value to 'pending' and trigger change event
        });

        function showAddUserModal() {
            $('#addUserModal').modal('show');
        }

        function addUser() {
            var email = $('#addEmail').val();
            var role = $('#addRole').val();

            $.ajax({
                type: "POST",
                url: "add_user.php",
                data: { email: email, role: role },
                dataType: "json",
                success: function (response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: 'User added successfully.',
                            icon: 'success',
                            confirmButtonColor: '#436850'
                        }).then(() => {
                            $('#addUserModal').modal('hide');
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
                        'Error occurred while adding the user.',
                        'error'
                    );
                }
            });
        }

        function editUser(userId) {
            $('#editUserModal').modal('show').on('shown.bs.modal', function () {
                $.ajax({
                    type: "POST",
                    url: "get_user.php",
                    data: { user_id: userId },
                    dataType: "json",
                    success: function (response) {
                        if (response.status == 'success') {
                            $('#editUserId').val(response.data.user_id);
                            $('#editEmail').val(response.data.email);
                            $('#editRole').val(response.data.role);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                        alert("Error occurred while fetching the user details.");
                    }
                });
            });
        }

        function updateUser() {
            $.ajax({
                type: "POST",
                url: "update_user.php",
                data: $('#editUserForm').serialize(),
                dataType: "json",
                success: function (response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            title: 'Updated!',
                            text: 'The user has been updated.',
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
                        'Error occurred while updating the user.',
                        'error'
                    );
                }
            });
        }

        function deleteUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this user.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "delete_user.php",
                        data: { user_id: userId },
                        dataType: "json",
                        success: function (response) {
                            if (response.status == 'success') {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The user has been deleted.',
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
                                'Error occurred while deleting the user.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // Approve User
        function approveUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to approve this user.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#436850',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "approve_user.php",
                        data: { user_id: userId },
                        dataType: "json",
                        success: function (response) {
                            if (response.status == 'success') {
                                Swal.fire({
                                    title: 'Approved!',
                                    text: 'The user has been approved.',
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
                                'Error occurred while approving the user.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // Decline User
        function declineUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to decline this user.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, decline!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // AJAX request to decline_user.php
                    $.ajax({
                        url: 'decline_user.php',
                        type: 'POST',
                        data: {
                            userId: userId
                        },
                        success: function (response) {
                            var data = JSON.parse(response);
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Declined!',
                                    text: 'User has been declined.',
                                    icon: 'success',
                                    showCancelButton: false,
                                    confirmButtonColor: '#436850',
                                    confirmButtonText: 'Ok',
                                }).then(() => {
                                    // Reload the page
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Failed to decline user.',
                                    'error'
                                );
                            }
                        },
                        error: function () {
                            Swal.fire(
                                'Error!',
                                'Failed to decline user.',
                                'error'
                            );
                        }
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
</body>
</html>
