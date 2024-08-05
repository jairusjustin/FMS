<?php
    include 'config.php';

    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('location:index.php');
        exit;
    }
    $sql_inventory = "SELECT 
                        c.crop_name, 
                        c.crops_id,
                        (SELECT COALESCE(SUM(h2.harvest_quantity), 0) 
                            FROM harvest h2 
                            INNER JOIN cropsfield cf2 ON h2.cropsfield_id = cf2.cropsfield_id 
                            WHERE cf2.crops_id = c.crops_id) 
                            - COALESCE((SELECT SUM(s.sales_quantity) 
                                        FROM sales s 
                                        INNER JOIN harvest h2 ON s.harvest_id = h2.harvest_id 
                                        INNER JOIN cropsfield cf2 ON h2.cropsfield_id = cf2.cropsfield_id 
                                        WHERE cf2.crops_id = c.crops_id), 0) 
                        as remaining_quantity,
                        SUM(h.harvest_quantity) as total_quantity
                    FROM 
                        harvest h
                    INNER JOIN 
                        cropsfield cf ON h.cropsfield_id = cf.cropsfield_id
                    INNER JOIN 
                        crops c ON cf.crops_id = c.crops_id
                    GROUP BY 
                        c.crop_name, c.crops_id
                    ORDER BY 
                        remaining_quantity ASC";

    $inventory = $mysqli->query($sql_inventory);

    // Fetch harvest records
    $sql_harvest = "SELECT 
                        h.harvest_id, 
                        CONCAT(f.field_name, ' - ', c.crop_name) AS crop_field, 
                        a.activity_date, 
                        h.harvest_quantity, 
                        c.default_sale_price,
                        h.harvest_quantity - COALESCE(SUM(s.sales_quantity), 0) AS remaining_quantity,
                        cf.cropsfield_id
                    FROM 
                        harvest h
                    INNER JOIN 
                        cropsfield cf ON h.cropsfield_id = cf.cropsfield_id
                    INNER JOIN 
                        crops c ON cf.crops_id = c.crops_id
                    INNER JOIN 
                        fields f ON cf.field_id = f.field_id
                    INNER JOIN 
                        activities a ON h.activity_id = a.activity_id
                    LEFT JOIN 
                        sales s ON h.harvest_id = s.harvest_id
                    GROUP BY 
                        h.harvest_id
                    ORDER BY 
                        a.activity_date DESC";

    $harvestRecords = $mysqli->query($sql_harvest);
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

<!-- Harvest Inventory and Record Management -->
<div class="row">
    <!-- Harvest Inventory Card -->
    <div class="col-md-5">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Harvest Inventory</h3>
            </div>
            <div class="my-card-body">
                <!-- Inventory Table -->
                <div class="table-responsive">
                    <table id="inventoryTable" class="table table-striped data-table table-bordered">
                        <thead>
                            <tr>
                                <th>Crop Name</th>
                                <th>Available Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $item): ?>
                                <tr>
                                    <td><?php echo $item['crop_name']; ?></td>
                                    <td><?php echo $item['remaining_quantity']; ?> kg</td>
                                    <td>
                                        <button class="my-btn viewActivityBtn" onclick="viewHarvest(<?php echo $item['crops_id']; ?>)"><ion-icon name="filter-outline"></ion-icon></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Harvest Record Management -->
    <div class="col-md-7">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Harvest Records</h3>
                <div class="my-actions">
                    <button class="btn clearFilterBtn" onclick="loadAllHarvest()">Clear Filter</button>
                </div>
            </div>
            <!-- Harvest Record Table -->
            <div class="table-responsive">
                <table id="harvestTable" class="table table-striped data-table table-bordered">
                    <thead>
                        <tr>
                            <th>Crop Field</th>
                            <th>Harvest Date</th>
                            <th>Harvest Quantity</th>
                            <th>Remaining Quantity</th>
                            <th>Sale Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($harvestRecords as $record): ?>
                            <tr class="harvestRow" data-cropsfield-id="<?php echo $record['cropsfield_id']; ?>">
                                <td><?php echo $record['crop_field']; ?></td>
                                <td><?php echo $record['activity_date']; ?></td>
                                <td><?php echo $record['harvest_quantity']; ?> kg</td>
                                <td><?php echo $record['remaining_quantity']; ?> kg</td>
                                <td>₱<?php echo $record['default_sale_price']; ?></td>
                                <td>
                                    <div class="my-action-icons">
                                        <button class="my-icon-btn editBtn" onclick="saleHarvestRecord(<?php echo $record['harvest_id']; ?>)"><ion-icon name="cart-outline"></ion-icon></button>
                                        <button class="my-icon-btn deleteBtn" onclick="deleteHarvest(<?php echo $record['harvest_id']; ?>)"><ion-icon name="trash-outline"></ion-icon></button>
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

<!-- Sales Modal -->
<div class="modal fade" id="saleModal" tabindex="-1" aria-labelledby="saleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saleModalLabel">Add Sale for <span id="cropName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="harvestId">
                <div class="mb-3">
                    <label for="saleDate" class="form-label">Sale Date:</label>
                    <input type="date" class="form-control" id="saleDate" required>
                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity (kg):</label>
                    <input type="number" class="form-control" id="salesQuantity" min="0" required>
                </div>
                <div class="mb-3">
                    <label for="remainingQuantity" class="form-label">Remaining Quantity (kg):</label>
                    <input type="text" class="form-control" id="remainingQuantity" readonly>
                </div>
                <div class="mb-3">
                    <label for="salePrice" class="form-label">Sale Price (₱):</label>
                    <input type="number" class="form-control" id="salePrice" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSale()">Add Sale</button>
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
        var inventoryTable = $('#inventoryTable').DataTable({
            "info": false,
            "order": [[1, 'desc']]
        });
        var harvestTable = $('#harvestTable').DataTable({
            "info": false,
            "order": [[3, 'desc']]
        });

        // Add click event to pagination buttons to synchronize active page highlighting
        $('#inventoryTable, #harvestTable').on('click', '.pagination a', function() {
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
        inventoryTable.draw();
        harvestTable.draw();
    });

    function deleteHarvest(harvestId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this harvest.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "delete_harvest.php",
                    data: { harvest_id: harvestId },
                    dataType: "json",
                    success: function(response) {
                        if (response.status == 'success') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'The harvest has been deleted.',
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
                            'Error occurred while deleting the harvest.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function viewHarvest(crops_id) {
        $.ajax({
            url: 'get_filtered_harvest.php',
            method: 'POST',
            data: {
                crops_id: crops_id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#harvestTable tbody').empty();
                    response.data.forEach(record => {
                        $('#harvestTable tbody').append(`
                            <tr>
                                <td>${record.crop_field}</td>
                                <td>${record.activity_date}</td>
                                <td>${record.harvest_quantity}</td>
                                <td>${record.remaining_quantity}</td>
                                <td>${record.default_sale_price}</td>
                                <td>
                                    <div class='my-action-icons'>
                                        <button class='my-icon-btn editBtn' onclick='saleHarvestRecord(${record.harvest_id})'><ion-icon name='create-outline'></ion-icon></button>
                                        <button class='my-icon-btn deleteBtn' onclick='deleteHarvest(${record.harvest_id})'><ion-icon name='trash-outline'></ion-icon></button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message,
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!',
                });
            }
        });
    }

    function loadAllHarvest() {
        $('#harvestTable tbody').empty();
        <?php foreach ($harvestRecords as $record): ?>
            $('#harvestTable tbody').append(`
                <tr class="harvestRow" data-cropsfield-id="<?php echo $record['cropsfield_id']; ?>">
                    <td><?php echo $record['crop_field']; ?></td>
                    <td><?php echo $record['activity_date']; ?></td>
                    <td><?php echo $record['harvest_quantity']; ?></td>
                    <td><?php echo $record['remaining_quantity']; ?></td>
                    <td><?php echo $record['default_sale_price']; ?></td>
                    <td>
                        <div class='my-action-icons'>
                            <button class='my-icon-btn editBtn' onclick='saleHarvestRecord(<?php echo $record['harvest_id']; ?>)'><ion-icon name='create-outline'></ion-icon></button>
                            <button class='my-icon-btn deleteBtn' onclick='deleteHarvest(<?php echo $record['harvest_id']; ?>)'><ion-icon name='trash-outline'></ion-icon></button>
                        </div>
                    </td>
                </tr>
            `);
        <?php endforeach; ?>
    }

    function saleHarvestRecord(harvestId) {
        $.ajax({
            type: "POST",
            url: "get_harvest_details.php",  
            data: { harvest_id: harvestId },
            dataType: "json",
            success: function(response) {
                if (response.status == 'success' && response.data.length > 0) {
                    const harvestData = response.data[0];
                    
                    $('#saleModal').modal('show');
                    $('#saleModal #harvestId').val(harvestData.harvest_id);
                    $('#saleModal #cropName').text(harvestData.crop_field);
                    $('#saleModal #remainingQuantity').val(harvestData.remaining_quantity);
                    $('#saleModal #salePrice').val(harvestData.default_sale_price);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        confirmButtonColor: '#436850',
                        text: 'No harvest details found for the selected crop.'
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    confirmButtonColor: '#436850',
                    text: 'Error occurred while fetching harvest details.'
                });
            }
        });
    }

    function saveSale() {
        const harvestId = $('#saleModal #harvestId').val();
        const saleDate = $('#saleModal #saleDate').val();
        const salePrice = $('#saleModal #salePrice').val();
        const salesQuantity = $('#saleModal #salesQuantity').val();
        const remainingQuantity = $('#saleModal #remainingQuantity').val();

        if (parseInt(salesQuantity) > parseInt(remainingQuantity)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                confirmButtonColor: '#436850',
                text: 'Sales quantity cannot be greater than remaining quantity!',
            });
            return;
        }

        $.ajax({
            type: "POST",
            url: "add_sale.php",
            data: {
                harvest_id: harvestId,
                sale_date: saleDate,
                sale_price: salePrice,
                sales_quantity: salesQuantity,
                remaining_quantity: remainingQuantity
            },
            dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    $('#saleModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Sale Saved!',
                        confirmButtonColor: '#436850',
                        text: 'Sale details saved successfully.'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        confirmButtonColor: '#436850',
                        text: response.message
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    confirmButtonColor: '#436850',
                    text: 'Something went wrong!'
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
