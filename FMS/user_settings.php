<?php
include 'config.php';
session_start();

if(!isset($_SESSION['user_id'])){
    header('location:index.php');
    exit;
}

$user_id = $_SESSION['user_id']; // Set the user_id from session

// Fetch user details
$sql_user = "SELECT *, CONCAT(firstname, ' ', lastname) AS full_name FROM user WHERE user_id = $user_id";
$result_user = $mysqli->query($sql_user);
$row_user = $result_user->fetch_assoc();

// Fetch all users
$sql_all_users = "SELECT *, CONCAT(firstname, ' ', lastname) AS full_name FROM user";
$result_all_users = $mysqli->query($sql_all_users);

// Fetch all users
$sql_all_users = "SELECT * FROM user";
$result_all_users = $mysqli->query($sql_all_users);

// Fetch sum of sizes of all fields
$sql_sum = "SELECT SUM(field_area) as total_size FROM fields WHERE is_deleted = 0 AND field_status = 'Active'";
$result_sum = mysqli_query($mysqli, $sql_sum);
$total_size = 0;
if (mysqli_num_rows($result_sum) > 0) {
    $row_sum = mysqli_fetch_assoc($result_sum);
    $total_size = $row_sum['total_size'];
}

// Fetch farm details
$farm_id_to_edit = 1; 
$sql_farm = "SELECT * FROM farm_details WHERE farm_id = $farm_id_to_edit";
$result_farm = $mysqli->query($sql_farm);
$row_farm = $result_farm->fetch_assoc();
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
                            <a class="my-nav-link" href="user_weather.php">
                                <span class="my-icon">
                                    <ion-icon name="cloudy-night-outline"></ion-icon>
                                </span>
                                <span class="my-title">Weather</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="user_crops.php">
                                <span class="my-icon">
                                    <ion-icon name="leaf-outline"></ion-icon>
                                </span>
                                <span class="my-title">Crops</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="user_activity.php">
                                <span class="my-icon">
                                    <ion-icon name="reader-outline"></ion-icon>
                                </span>
                                <span class="my-title">Activities</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="user_harvest.php">
                                <span class="my-icon">
                                    <ion-icon name="basket-outline"></ion-icon>
                                </span>
                                <span class="my-title">Harvest</span>
                            </a>
                        </li>

                        <li class="my-nav-item">
                            <a class="my-nav-link" href="user_settings.php">
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
                    <!-- Farm and Account Management Cards -->
                    <div class="row">
                        <!-- Manage Account Card -->
                        <div class="col-md-12">
                            <div class="my-recentOrders">
                                <div class="my-cardHeader">
                                    <h2>Manage Account</h2>
                                </div>
                                <div class="my-card-body">
                                    <!-- View and Edit Account Card -->
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Account Information</h5>
                                            <p class="card-text">Full Name: <?php echo $row_user['full_name']; ?></p>
                                            <p class="card-text">Email: <?php echo $row_user['email']; ?></p>
                                            <button class="btn editAccountBtn" onclick="editAccount()">Edit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Account Modal -->
                    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editAccountModalLabel">Edit Account</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="accountForm" method="post" action="update_account.php">
                                        <div class="mb-3">
                                            <label for="firstName" class="form-label">First Name:</label>
                                            <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo $row_user['firstname']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="lastName" class="form-label">Last Name:</label>
                                            <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo $row_user['lastname']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email:</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $row_user['email']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">New Password (optional):</label>
                                            <input type="password" class="form-control" id="newPassword" name="newPassword">
                                        </div>
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Current Password:</label>
                                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
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
                        function editFarm() {
                            $('#editFarmModal').modal('show');
                        }

                        function editAccount() {
                            $('#editAccountModal').modal('show');
                        }

                        $('#farmForm').submit(function(e) {
                            e.preventDefault();

                            var formData = {
                                'farm_id': $('#farmForm input[name=farm_id]').val(),
                                'farmName': $('#farmForm input[name=farmName]').val(),
                                'farmLocation': $('#farmForm input[name=farmLocation]').val(),
                            };

                            $.ajax({
                                type: "POST",
                                url: "update_farm.php",
                                data: formData,
                                dataType: "json",
                                success: function(response) {
                                    if (response.status == 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            confirmButtonColor: '#436850',
                                            text: 'Farm updated successfully!'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $('#editFarmModal').modal('hide');
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
                                error: function(jqXHR, textStatus, errorThrown) {
                                    console.log(jqXHR.responseText);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        confirmButtonColor: '#436850',
                                        text: 'Error occurred while updating the farm.'
                                    });
                                }
                            });
                        });
                        $('#accountForm').submit(function(e) {
                            e.preventDefault();

                            var formData = {
                                'currentPassword': $('#accountForm input[name=currentPassword]').val(),
                                'firstName': $('#accountForm input[name=firstName]').val(),
                                'lastName': $('#accountForm input[name=lastName]').val(),
                                'email': $('#accountForm input[name=email]').val(),
                                'newPassword': $('#accountForm input[name=newPassword]').val()
                            };

                            $.ajax({
                                type: "POST",
                                url: "update_account.php",
                                data: formData,
                                dataType: "json",
                                success: function(response) {
                                    Swal.fire({
                                        icon: response.stat_icon,
                                        title: response.stat_title,
                                        confirmButtonColor: '#436850',
                                        text: response.stat_message
                                    }).then((result) => {
                                        if (result.isConfirmed && response.status === 'success') {
                                            $('#editAccountModal').modal('hide');
                                            location.reload();
                                        }
                                    });
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    console.log(jqXHR.responseText);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        confirmButtonColor: '#436850',
                                        text: 'Error occurred while updating the account details.'
                                    });
                                }
                            });
                        });

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
                </div>
            </div>
        </div>
    </div>
</body>
</html>
