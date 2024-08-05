<?php
    include 'config.php';

    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('location:index.php');
        exit;
    }

    // Fetch crop details
    $sql_crops = "SELECT * FROM crops WHERE is_deleted = 0";
    $stmt_crops = mysqli_prepare($mysqli, $sql_crops);
    $crops = [];
    if ($stmt_crops) {
        mysqli_stmt_execute($stmt_crops);
        $result_crops = mysqli_stmt_get_result($stmt_crops);
        while ($row_crop = mysqli_fetch_assoc($result_crops)) {
            $crops[] = $row_crop;
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error fetching crop details'
        ];
        sendResponse($response);
    }

    // Fetch field details
    $sql_fields = "SELECT * FROM fields WHERE is_deleted = 0";
    $stmt_fields = mysqli_prepare($mysqli, $sql_fields);
    $fields = [];
    if ($stmt_fields) {
        mysqli_stmt_execute($stmt_fields);
        $result_fields = mysqli_stmt_get_result($stmt_fields);
        while ($row_field = mysqli_fetch_assoc($result_fields)) {
            $fields[] = $row_field;
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error fetching field details'
        ];
        sendResponse($response);
    }
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
        
        <!-- Field and Crop Management Cards -->
        <div class="row">
            <!-- Manage Crops Card -->
            <div class="col-md-6">
                <div class="my-recentOrders">
                    <div class="my-cardHeader">
                        <h3>Manage Crops</h3>
                        <div class="my-actions">
                            <button class="btn addCropsBtn" onclick="addCrop()">Add Crops</button> 
                        </div>
                    </div>
                    <!-- Table for Crop Management -->
                    <div class="table-responsive">
                        <table id="cropTable" class="table table-striped data-table table-bordered">
                            <thead>
                                <tr>
                                    <th>Crop Name</th>
                                    <th>Sale Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($crops as $crop): ?>
                                    <tr>
                                        <td><?php echo $crop['crop_name']; ?></td>
                                        <td>₱<?php echo $crop['default_sale_price']; ?></td>
                                        <td>
                                            <div class='my-action-icons'>
                                                <button class='my-icon-btn editBtn' onclick='editCrop(<?php echo $crop['crops_id']; ?>)'><ion-icon name='create-outline'></ion-icon></button>
                                                <button class='my-icon-btn deleteBtn' onclick='deleteCrop(<?php echo $crop['crops_id']; ?>)'><ion-icon name='trash-outline'></ion-icon></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Manage Fields Card -->
            <div class="col-md-6">
                <div class="my-recentOrders">
                    <div class="my-cardHeader">
                        <h3>Manage Fields</h3>
                        <div class="my-actions">
                            <button class="btn addFieldBtn" onclick="addField()">Add Field</button> 
                        </div>
                    </div>
                    <!-- Table for Field Management -->
                    <div class="table-responsive">
                        <table id="fieldTable" class="table table-striped data-table table-bordered">
                            <thead>
                                <tr>
                                    <th>Field Name</th>
                                    <th>Field Area (ha)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fields as $field): ?>
                                    <tr>
                                        <td><?php echo $field['field_name']; ?></td>
                                        <td><?php echo $field['field_area']; ?></td>
                                        <td><?php echo $field['field_status']; ?></td>
                                        <td>
                                            <div class='my-action-icons'>
                                                <button class='my-icon-btn editBtn' onclick='editField(<?php echo $field['field_id']; ?>)'><ion-icon name='create-outline'></ion-icon></button>
                                                <button class='my-icon-btn deleteBtn' onclick='deleteField(<?php echo $field['field_id']; ?>)'><ion-icon name='trash-outline'></ion-icon></button>
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

<!-- Add Crop Modal -->
    <div class="modal fade" id="addCropModal" tabindex="-1" aria-labelledby="addCropModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCropModalLabel">Add Crop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCropForm" method="post" action="add_crop.php">
                    <div class="mb-3">
                        <label for="cropName" class="form-label">Crop Name</label>
                        <input type="text" class="form-control" id="cropName" name="cropName" required>
                    </div>
                    <div class="mb-3">
                        <label for="defaultSalePrice" class="form-label">Sale Price (₱)</label>
                        <input type="number" step="0.01" class="form-control" id="defaultSalePrice" name="defaultSalePrice" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Crop</button>             
                    </div>
                </form>
        </div>
    </div>
</div>
</div>

<!-- Edit Crop Modal -->
<div class="modal fade" id="editCropModal" tabindex="-1" aria-labelledby="editCropModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCropModalLabel">Edit Crop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCropForm" method="post" action="update_crops.php">
                    <input type="hidden" id="editCropId" name="crops_id">
                    <div class="mb-3">
                        <label for="editCropName" class="form-label">Crop Name</label>
                        <input type="text" class="form-control" id="editCropName" name="cropName">
                    </div>
                    <div class="mb-3">
                        <label for="defaultSalePrice" class="form-label">Sale Price (₱)</label>
                        <input type="number" step="0.01" class="form-control" id="editDefaultSalePrice" name="defaultSalePrice" required>
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

<!-- Add Field Modal -->
<div class="modal fade" id="addFieldModal" tabindex="-1" aria-labelledby="addFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFieldModalLabel">Add Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addFieldForm" method="post" action="add_field.php">
                    <div class="mb-3">
                        <label for="fieldName" class="form-label">Field Name</label>
                        <input type="text" class="form-control" id="fieldName" name="fieldName">
                    </div>
                    <div class="mb-3">
                        <label for="fieldArea" class="form-label">Field Area (ha)</label>
                        <input type="number" step="0.01" min="0.01" max="1000.00" class="form-control" id="fieldArea" name="fieldArea">
                    </div>
                    <div class="mb-3">
                        <label for="fieldStatus" class="form-label">Status</label>
                        <select class="form-select" id="fieldStatus" name="fieldStatus">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Field</button> 
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div class="modal fade" id="editFieldModal" tabindex="-1" aria-labelledby="editFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFieldModalLabel">Edit Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFieldForm" method="post" action="update_field.php">
                    <input type="hidden" id="editFieldId" name="field_id">
                    <div class="mb-3">
                        <label for="editFieldName" class="form-label">Field Name</label>
                        <input type="text" class="form-control" id="editFieldName" name="field_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editFieldArea" class="form-label">Field Area (ha)</label>
                        <input type="number" step="0.01" min="0.01" max="1000.00" class="form-control" id="editFieldArea" name="field_area" required>
                    </div>
                    <div class="mb-3">
                        <label for="editFieldStatus" class="form-label">Field Status</label>
                        <select class="form-control" id="editFieldStatus" name="field_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
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

<!-- Scripts -->
<script src="js/script.js"></script>

<!-- ionicons -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    
<script>
$(document).ready(function () {
    var cropTable = $('#cropTable').DataTable({
        "info": false
    });
    var fieldTable = $('#fieldTable').DataTable({
        "info": false
    });

    // Add click event to pagination buttons to synchronize active page highlighting
    $('#cropTable, #fieldTable').on('click', '.pagination a', function() {
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
    cropTable.draw();
    fieldTable.draw();
});

function addCrop() {
    $('#addCropModal').modal('show');
}

function addField() {
    $('#addFieldModal').modal('show');
}

$('#addCropForm').submit(function(e) {
    e.preventDefault();  
    
    var formData = $(this).serialize(); 

    $.ajax({
        type: "POST",
        url: "add_crop.php", 
        data: formData,
        dataType: "json",
        success: function(response) {
            console.log(response);
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    confirmButtonColor: '#436850',
                    text: 'Crop added successfully!'
                }).then((result) => {
                    if (result.isConfirmed) {
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
                text: 'Error occurred while adding the crop.'
            });
        }
    });
});

$('#addFieldForm').submit(function(e) {
    e.preventDefault();  
    
    var formData = $(this).serialize(); 

    $.ajax({
        type: "POST",
        url: "add_field.php",
        data: formData,
        dataType: "json",
        success: function(response) {
            console.log(response);
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    confirmButtonColor: '#436850',
                    text: 'Field added successfully!'
                }).then((result) => {
                    if (result.isConfirmed) {
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
                text: 'Error occurred while adding the field.'
            });
        }
    });
});

function deleteCrop(cropId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this crop.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "delete_crop.php",
                data: { crops_id: cropId },
                dataType: "json",
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The crop has been deleted.',
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
                        'Error occurred while deleting the crop.',
                        'error'
                    );
                }
            });
        }
    });
}

function deleteField(fieldId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this field.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#436850',  
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "delete_field.php",
                data: { field_id: fieldId },
                dataType: "json",
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The field has been deleted.',
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
                        'Error occurred while deleting the field.',
                        'error'
                    );
                },
            });
        }
    });
}

function editCrop(cropId) {
    $('#editCropModal').modal('show').on('shown.bs.modal', function() {
        $.ajax({
            type: "POST",
            url: "get_crops.php",
            data: { crops_id: cropId },
            dataType: "json",
            success: function(response) {
                if (response.status == 'success') {
                    $('#editCropId').val(response.data.crops_id);
                    $('#editCropName').val(response.data.crop_name);
                    $('#editDefaultSalePrice').val(parseFloat(response.data.default_sale_price).toFixed(2));
                } else {
                    alert(response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                alert("Error occurred while fetching the crop details.");
            }
        });
    });
}

$('#editCropForm').submit(function(e) {
    e.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        type: "POST",
        url: "update_crops.php",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    confirmButtonColor: '#436850',
                    text: 'Crop updated successfully!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#editCropModal').modal('hide');
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
                text: 'Error occurred while updating the crop.'
            });
        }
    });
});

function editField(fieldId) {
    $('#editFieldModal').modal('show');
    $.ajax({
        type: "POST",
        url: "get_fields.php",
        data: { field_id: fieldId },
        dataType: "json",
        success: function(response) {
            if (response.status == 'success') {
                $('#editFieldId').val(response.data.field_id);
                $('#editFieldName').val(response.data.field_name);
                $('#editFieldArea').val(response.data.field_area);
                $('#editFieldStatus').val(response.data.field_status);
            } else {
                alert(response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
            alert("Error occurred while fetching the field details.");
        }
    });
}

$('#editFieldForm').submit(function(e) {
    e.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        type: "POST",
        url: "update_field.php",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    confirmButtonColor: '#436850',
                    text: 'Field updated successfully!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#editFieldModal').modal('hide');
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
                text: 'Error occurred while updating the field.'
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
</body>
</html>