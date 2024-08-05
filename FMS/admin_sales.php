<?php
include 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:index.php');
    exit;
}

$sql_sales = "SELECT s.sale_id, CONCAT(f.field_name, ' - ', c.crop_name) AS crop_field, s.sale_date, s.sales_quantity, s.sale_price,
              s.sales_quantity * s.sale_price AS total_price
            FROM sales s
            JOIN harvest h ON s.harvest_id = h.harvest_id
            JOIN cropsfield cf ON h.cropsfield_id = cf.cropsfield_id
            JOIN crops c ON cf.crops_id = c.crops_id
            JOIN fields f ON cf.field_id = f.field_id";
$result_sales = $mysqli->query($sql_sales);
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
    <!-- Manage Sales Card -->
    <div class="col-md-12">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Sales Records</3>
            </div>
            <!-- Table for Sales Management -->
            <div class="table-responsive">
                <table id="salesTable" class="table table-striped data-table table-bordered">
                    <thead>
                        <tr>
                            <th>Sales ID</th>
                            <th>Crop Field</th>
                            <th>Sale Date</th>
                            <th>Quantity Sold</th>
                            <th>Price per Kg</th>
                            <th>Total Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result_sales as $sale): ?>
                        <tr>
                            <td><?php echo $sale['sale_id']; ?></td>
                            <td><?php echo $sale['crop_field']; ?></td>
                            <td><?php echo $sale['sale_date']; ?></td>
                            <td><?php echo $sale['sales_quantity']; ?> kg</td>
                            <td>₱<?php echo number_format($sale['sale_price'], 2); ?></td>
                            <td>₱<?php echo number_format($sale['total_price'], 2); ?></td>
                            <td>
                                <div class="my-action-icons">
                                    <button class="my-icon-btn editBtn" onclick="editSale(<?php echo $sale['sale_id']; ?>)"><ion-icon name="create-outline"></ion-icon></button>
                                    <button class="my-icon-btn deleteBtn" onclick="deleteSale(<?php echo $sale['sale_id']; ?>)"><ion-icon name="trash-outline"></ion-icon></button>
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

<!-- Sales Modal -->
<div class="modal fade" id="saleModal" tabindex="-1" aria-labelledby="saleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saleModalLabel">Edit Sale for <span id="cropName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="saleId">
                <input type="hidden" id="harvestId">
                <div class="mb-3">
                    <label for="saleDate" class="form-label">Sale Date:</label>
                    <input type="date" class="form-control" id="saleDate" required>
                </div>
                <div class="mb-3">
                    <label for="salesQuantity" class="form-label">Quantity (kg):</label>
                    <input type="number" class="form-control" id="salesQuantity" min="0" required>
                </div>
                <div class="mb-3">
                    <label for="remainingQuantity" class="form-label">Remaining Quantity (kg):</label>
                    <input type="text" class="form-control" id="remainingQuantity" readonly>
                </div>
                <div class="mb-3">
                    <label for="salePrice" class="form-label">Sale Price:</label>
                    <input type="number" class="form-control" id="salePrice" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSale()">Save Sale</button>
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
        var salesTable = $('#salesTable').DataTable({
            "info": false,
            "order": [[ 0, "desc" ]]
        });

        // Add click event to pagination buttons to synchronize active page highlighting
        $('#salesTable').on('click', '.pagination a', function() {
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
        salesTable.draw();
    });

    function deleteSale(saleId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this sale.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "delete_sale.php",
                    data: { sale_id: saleId },
                    dataType: "json",
                    success: function(response) {
                        if (response.status == 'success') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'The sale has been deleted.',
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
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                        Swal.fire(
                            'Error!',
                            'Error occurred while deleting the sale.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function editSale(saleId) {
        $('#saleModal').modal('show').on('shown.bs.modal', function() {
            $.ajax({
                type: "POST",
                url: "get_sale.php",
                data: { sale_id: saleId },
                dataType: "json",
                success: function(response) {
                    if (response.status == 'success') {
                        $('#saleId').val(response.data.sale_id);
                        $('#saleDate').val(response.data.sale_date);
                        $('#salesQuantity').val(response.data.sales_quantity);
                        $('#remainingQuantity').val(response.data.remaining_quantity);
                        $('#salePrice').val(response.data.sale_price);
                        $('#cropName').text(response.data.crop_field);
                        $('#harvestId').val(response.data.harvest_id);
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
                        text: 'Error occurred while fetching the sale details.'
                    });
                }
            });
        });
    }

    function saveSale() {
        var formData = {
            sale_id: $('#saleId').val(),
            sale_date: $('#saleDate').val(),
            sales_quantity: $('#salesQuantity').val(),
            sale_price: $('#salePrice').val(),
            harvest_id: $('#harvestId').val()
        };

        if (formData.sales_quantity <= 0 || isNaN(formData.sales_quantity)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                confirmButtonColor: '#436850',
                text: 'Please enter a valid sales quantity!'
            });
            return;
        }

        console.log("Sent data: ", formData);

        $.ajax({
            type: "POST",
            url: "update_sale.php",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        confirmButtonColor: '#436850',
                        text: 'Sale updated successfully!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#saleModal').modal('hide');
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
                    text: 'Error occurred while updating the sale.'
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