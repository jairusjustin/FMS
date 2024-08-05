<?php
include 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:index.php');
    exit;
}

$selected_cropsfield_id = isset($_GET['cropsfield_id']) ? $_GET['cropsfield_id'] : null;
$selected_year = isset($_GET['year']) ? $_GET['year'] : 'all';

// Query to get the minimum and maximum years from activities
$sql_years = "SELECT MIN(YEAR(activity_date)) AS min_year, MAX(YEAR(activity_date)) AS max_year FROM activities WHERE activity_type = 'Planting'";

$stmt_years = mysqli_prepare($mysqli, $sql_years);

$min_year = null;
$max_year = null;

if ($stmt_years) {
    mysqli_stmt_execute($stmt_years);
    $result_years = mysqli_stmt_get_result($stmt_years);
    $row_years = mysqli_fetch_assoc($result_years);

    // Extract min and max years
    $min_year = $row_years['min_year'];
    $max_year = $row_years['max_year'];
} else {
    // Handle error if query fails
    $min_year = date('Y');
    $max_year = date('Y');
}

$years = range($max_year, $min_year);

$sql_crops_base = "SELECT 
    cf.cropsfield_id, 
    c.crops_id, 
    cf.field_id, 
    sa.area as seeded_area, 
    cf.is_deleted, 
    c.crop_name, 
    f.field_name, 
    CONCAT(f.field_name, ' - ', c.crop_name) as crop_field,
    COALESCE(COUNT(DISTINCT a.activity_id), 0) as activity_count,
    MAX(CASE WHEN a.activity_type = 'Planting' THEN a.activity_date END) as planting_date,
    MAX(CASE WHEN a.activity_type = 'Harvest' THEN a.activity_date END) as harvest_date,
    CASE WHEN MAX(a.activity_date) IS NULL THEN 1 ELSE 0 END as na_order, 
    MAX(a.activity_date) as max_date,
    MIN(a.activity_date) as min_activity_date
FROM cropsfield cf
LEFT JOIN crops c ON c.crops_id = cf.crops_id
LEFT JOIN fields f ON cf.field_id = f.field_id
LEFT JOIN activities a ON cf.cropsfield_id = a.cropsfield_id
LEFT JOIN seeded_area sa ON cf.cropsfield_id = sa.cropsfield_id 
WHERE c.is_deleted = 0 OR c.crops_id IS NULL
GROUP BY cf.cropsfield_id";

$sql_crops = "SELECT * FROM ($sql_crops_base) AS subquery";

if ($selected_year !== 'all') {
    $sql_crops .= " HAVING YEAR(min_activity_date) = ? OR na_order = 1";
}

$sql_crops .= " ORDER BY na_order ASC, CASE WHEN na_order = 1 THEN 0 ELSE 1 END, min_activity_date DESC";
$stmt_crops = mysqli_prepare($mysqli, $sql_crops);

$crops = [];

if ($stmt_crops) {
    if ($selected_year !== 'all') {
        mysqli_stmt_bind_param($stmt_crops, 's', $selected_year);
    }

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
}

// Update the SQL query for activities based on the selected year
$sql_activities = "SELECT 
    cf.cropsfield_id, 
    CONCAT(f.field_name, ' - ', c.crop_name) as crop_field,
    a.activity_type as activity_type,
    a.activity_date as activity_date,
    a.activity_id as activity_id
FROM cropsfield cf
LEFT JOIN crops c ON c.crops_id = cf.crops_id
LEFT JOIN fields f ON cf.field_id = f.field_id
LEFT JOIN activities a ON cf.cropsfield_id = a.cropsfield_id
WHERE (c.is_deleted = 0 OR c.crops_id IS NULL) AND a.activity_date IS NOT NULL";

// Apply the selected year filter if it is not 'all'
if ($selected_year !== 'all') {
    $sql_activities .= " AND YEAR(a.activity_date) = ?";
}

$sql_activities .= " ORDER BY a.activity_date DESC";

$stmt_activities = mysqli_prepare($mysqli, $sql_activities);

$activities = [];

if ($stmt_activities) {
    if ($selected_year !== 'all') {
        mysqli_stmt_bind_param($stmt_activities, 's', $selected_year);
    }

    mysqli_stmt_execute($stmt_activities);

    $result_activities = mysqli_stmt_get_result($stmt_activities);

    while ($row_activity = mysqli_fetch_assoc($result_activities)) {
        $activities[] = $row_activity;
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Error fetching activity details'
    ];
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
            
<!-- Crops and Activity Management Cards -->
<div class="row">
    <!-- Crops List Card -->
    <div class="col-md-7">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h2>Crop Field List</h2>
                <div class="my-actions">
                    <?php
                    ?>
                    <select class='btn' id="addActivityBtn" name="year" onchange="filterByYear(this.value)">
                        <option value="all">All</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn addCropsBtn" onclick="addCrop()">Add Crop Field</button> 
                </div>
            </div>
            <!-- Table for Crop Management -->
            <div class="table-responsive">
                <table id="cropTable" class="table table-striped data-table table-bordered">
                    <thead>
                        <tr>
                            <th>Crop Field</th>
                            <th>Seeded Area</th>
                            <th>Planting Date</th>
                            <th>Harvest Date</th>
                            <th>Activities</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($crops as $crop): ?>
                            <tr>
                                <td><?php echo $crop['crop_field']; ?></td>
                                <td><?php echo $crop['seeded_area'] ?? 'N/A'; ?></td>
                                <td><?php echo $crop['planting_date'] ?? 'N/A'; ?></td>
                                <td><?php echo $crop['harvest_date'] ?? 'N/A'; ?></td>
                                <td><?php echo $crop['activity_count']; ?></td>
                                <td>
                                    <div class='my-action-icons'>
                                        <button class="my-btn viewActivityBtn" onclick="viewActivities(<?= $crop['cropsfield_id'] ?>)"><ion-icon name="filter-outline"></ion-icon></button>
                                        <button class='my-icon-btn editBtn' onclick='editCropsField(<?php echo $crop['cropsfield_id']; ?>)'><ion-icon name='create-outline'></ion-icon></button>
                                        <button class='my-icon-btn deleteBtn' onclick='deleteCropsField(<?php echo $crop['cropsfield_id']; ?>)'><ion-icon name='trash-outline'></ion-icon></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Manage Activities Card -->
    <div class="col-md-5">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h2>Activities</h2>
                <div class="my-actions">
                    <div class="dropdown">
                        <button class="btn clearFilterBtn" onclick="loadAllActivities()">Clear Filter</button>
                        <button class="btn dropdown-toggle" type="button" id="addActivityBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            Add Activity
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="addActivityBtn" id="activityDropdown">
                            <li><button class="dropdown-item" onclick="addPlanting()">Add Planting</button></li>
                            <li><button class="dropdown-item" onclick="addHarvest()">Add Harvest</button></li>
                            <li><button class="dropdown-item" onclick="addActivity()">Other Activity</button></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Table for Activity Management -->
            <div class="table-responsive">
                <table id="activityTable" class="table table-striped data-table table-bordered">
                    <thead>
                        <tr>
                            <th>Crop Field</th>
                            <th>Activity Type</th>
                            <th>Activity Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo $activity['crop_field']; ?></td>
                                <td><?php echo $activity['activity_type']; ?></td>
                                <td><?php echo $activity['activity_date']; ?></td>
                                <td>
                                    <div class='my-action-icons'>
                                        <button class='my-icon-btn editBtn' onclick='editActivity(<?php echo $activity['activity_id']; ?>)'><ion-icon name='create-outline'></ion-icon></button>
                                        <button class='my-icon-btn deleteBtn' onclick='deleteActivity(<?php echo $activity['activity_id']; ?>, <?php echo $activity['cropsfield_id']; ?>, "<?php echo $activity['activity_type']; ?>")'><ion-icon name='trash-outline'></ion-icon></button>
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


        <!-- Add Crop Field Modal -->
        <div class="modal fade" id="addCropModal" tabindex="-1" aria-labelledby="addCropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCropModalLabel">Add Crop</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCropFieldForm" method="post" action="add_crop.php">
                        <div class="mb-3">
                            <label for="cropName" class="form-label">Crop Name</label>
                            <select class="form-select" id="cropName" name="cropName">
                                <?php
                                $sql = "SELECT crop_name FROM crops WHERE is_deleted = 0";
                                $result = mysqli_query($mysqli, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='" . $row['crop_name'] . "'>" . $row['crop_name'] . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>No crops available</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                        <label for="field_Name" class="form-label">Field Name</label>
                            <select class="form-select" id="field_Name" name="field_Name">
                                <?php
                                $sql = "SELECT field_name FROM fields WHERE is_deleted = 0  AND field_status = 'Active'";
                                $result = mysqli_query($mysqli, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='" . $row['field_name'] . "'>" . $row['field_name'] . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>No fields available</option>";
                                }
                                ?>
                            </select>
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
<div class="modal fade" id="editCropsFieldModal" tabindex="-1" aria-labelledby="editCropModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCropModalLabel">Edit Crop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCropsFieldForm" method="post" action="update_cropsfield.php">
                    <input type="hidden" id="editCropsFieldId" name="cropsfield_id">
                    <div class="mb-3">
                        <label for="editCropName" class="form-label">Crop Name</label>
                        <select class="form-select" id="cropNameEditDropdown" name="cropName">
                            <?php
                            $sql = "SELECT crop_name FROM crops WHERE is_deleted = 0";
                            $result = mysqli_query($mysqli, $sql);
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='" . $row['crop_name'] . "'>" . $row['crop_name'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>No crops available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editFieldNameDropdown" class="form-label">Field Name</label>
                        <select class="form-select" id="editFieldNameDropdown" name="field_Name">
                            <?php
                            $sql = "SELECT field_name 
                                    FROM fields 
                                    WHERE is_deleted = 0";
                            $result = mysqli_query($mysqli, $sql);
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='" . $row['field_name'] . "'>" . $row['field_name'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>No fields available</option>";
                            }
                            ?>
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

<!-- Add Planting Modal -->
<div class="modal fade" id="addPlantingModal" tabindex="-1" aria-labelledby="addPlantingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPlantingModalLabel">Add Planting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Planting Form Here -->
                <form id="plantingForm">
                    <div class="mb-3">
                    <label for="field_and_crop" class="form-label">Field and Crop</label>
                    <select class="form-select" id="field_and_crop" name="field_and_crop">
                        <?php
                        include 'config.php';

                        $sql = "SELECT cf.cropsfield_id, CONCAT(f.field_name, ' > ', c.crop_name) AS field_and_crop
                        FROM cropsfield cf
                        JOIN fields f ON cf.field_id = f.field_id
                        JOIN crops c ON cf.crops_id = c.crops_id
                        WHERE cf.is_deleted = 0
                        AND NOT EXISTS (
                            SELECT 1
                            FROM activities a
                            WHERE cf.cropsfield_id = a.cropsfield_id
                            AND a.activity_type = 1
                        )";

                        $result = mysqli_query($mysqli, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . $row['cropsfield_id'] . "'>" . $row['field_and_crop'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No fields available</option>";
                        }
                        ?>
                    </select>
                    </div>
                    <div class="mb-3">
                        <label for="activity_date" class="form-label">Activity Date</label>
                        <input type="date" class="form-control" id="activity_date" name="activity_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="seededArea" class="form-label">Seeded Area</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="seeded_area" name="seededArea" required>
                    </div>
                    <!-- Expense Input -->
                    <div class="mb-3">
                        <label for="expenseAmount" class="form-label">Expense Amount</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="expenseAmount" name="expenseAmount">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="savePlanting()">Save changes</button>
            </div>
        </div>
    </div>
</div>



<!-- Add Harvest Modal -->
<div class="modal fade" id="addHarvestModal" tabindex="-1" aria-labelledby="addHarvestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addHarvestModalLabel">Add Harvest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Harvest Form Here -->
                <form id="harvestForm">
                    <div class="mb-3">
                        <label for="field_and_crop_harvest" class="form-label">Field and Crop</label>
                        <select class="form-select" id="field_and_crop_harvest" name="field_and_crop">
                            <?php
                            include 'config.php';

                            $sql = "SELECT cf.cropsfield_id, CONCAT(f.field_name, ' > ', c.crop_name) AS field_and_crop 
                            FROM cropsfield cf
                            JOIN fields f ON cf.field_id = f.field_id
                            JOIN crops c ON cf.crops_id = c.crops_id
                            WHERE cf.is_deleted = 0
                            AND cf.cropsfield_id NOT IN (
                                SELECT DISTINCT a.cropsfield_id
                                FROM activities a
                                WHERE a.activity_type = 'Completion'
                            )";
                    
                    $result = mysqli_query($mysqli, $sql);
                    
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['cropsfield_id'] . "'>" . $row['field_and_crop'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>No fields available</option>";
                    }
                            ?>
                        </select>
                    </div>
                    <!-- Harvest Input -->
                    <div class="mb-3">
                        <label for="activity_date_harvest" class="form-label">Activity Date</label>
                        <input type="date" class="form-control" id="activity_date_harvest" name="activity_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="harvest_quantity" class="form-label">Harvest Quantity</label>
                        <input type="number" class="form-control" id="harvest_quantity" name="harvest_quantity" required>
                    </div>
                    <!-- Expense Input -->
                    <div class="mb-3">
                        <label for="expenseAmount" class="form-label">Expense Amount</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="expenseAmount" name="expenseAmount">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveHarvest()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Activity Modal -->
<div class="modal fade" id="addOtherActivityModal" tabindex="-1" aria-labelledby="addOtherActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addOtherActivityModalLabel">Add Other Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Other Activity Form Here -->
                <form id="otherActivityForm">
                    <div class="mb-3">
                        <label for="field_and_crop_other_activity" class="form-label">Field and Crop</label>
                        <select class="form-select" id="field_and_crop_other_activity" name="field_and_crop">
                            <?php
                            include 'config.php';

                            $sql = "SELECT cf.cropsfield_id, CONCAT(f.field_name, ' > ', c.crop_name) AS field_and_crop 
                            FROM cropsfield cf
                            JOIN fields f ON cf.field_id = f.field_id
                            JOIN crops c ON cf.crops_id = c.crops_id
                            WHERE cf.is_deleted = 0
                            AND cf.cropsfield_id NOT IN (
                                SELECT DISTINCT a.cropsfield_id
                                FROM activities a
                                WHERE a.activity_type = 'Completion'
                            )";
                    
                            $result = mysqli_query($mysqli, $sql);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='" . $row['cropsfield_id'] . "'>" . $row['field_and_crop'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>No fields available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="other_activity_type" class="form-label">Activity Type</label>
                        <select class="form-select" id="other_activity_type" name="other_activity_type" required>
                        <?php
                            // Fetch ENUM values for activity_type
                            $enum = "SELECT COLUMN_TYPE 
                                    FROM information_schema.COLUMNS 
                                    WHERE TABLE_NAME = 'activities' AND COLUMN_NAME = 'activity_type'";
                            $result = mysqli_query($mysqli, $enum);
                            $row = mysqli_fetch_assoc($result);
                            $enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));

                            // Exclude specific values from the ENUM list
                            $excludedValues = ['Harvest', 'Planting'];
                            foreach ($enumList as $value) {
                                if (!in_array($value, $excludedValues)) {
                                    echo "<option value='$value'>$value</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="activity_date_other" class="form-label">Activity Date</label>
                        <input type="date" class="form-control" id="activity_date_other" name="activity_date" required>
                    </div>
                    <!-- Expense Input -->
                    <div class="mb-3">
                        <label for="expenseAmount" class="form-label">Expense Amount</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="expenseAmount" name="expenseAmount">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveOtherActivity()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Activity Modal -->
<div class="modal fade" id="editOtherActivityModal" tabindex="-1" aria-labelledby="editOtherActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOtherActivityModalLabel">Edit Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Edit Activity Form Here -->
                <form id="editOtherActivityForm">
                    <input type="hidden" id="edit_activity_id" name="activity_id">
                    <div class="mb-3">
                        <label for="edit_field_and_crop_other_activity" class="form-label">Field and Crop</label>
                        <input type="text" class="form-control" id="edit_field_and_crop_other_activity" name="field_and_crop" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_other_activity_type" class="form-label">Activity Type</label>
                        <select class="form-select" id="edit_other_activity_type" name="other_activity_type" required>
                            <?php
                            $enum = "SELECT COLUMN_TYPE 
                            FROM information_schema.COLUMNS 
                            WHERE TABLE_NAME = 'activities' AND COLUMN_NAME = 'activity_type'";
                            $result = mysqli_query($mysqli, $enum);
                            $row = mysqli_fetch_assoc($result);
                            $enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
                            
                            foreach ($enumList as $value) {
                                echo "<option value='$value'>$value</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_activity_date_other" class="form-label">Activity Date</label>
                        <input type="date" class="form-control" id="edit_activity_date_other" name="activity_date" required>
                    </div>
                    <!-- Harvest Quantity Input -->
                    <div class="mb-3" id="edit_harvest_quantity" style="display: none;">
                        <label for="edit_harvest_quantity" class="form-label">Harvest Quantity</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="edit_harvest_quantity" name="harvest_quantity">
                    </div>
                    <!-- Seeded Area Input -->
                    <div class="mb-3" id="edit_seeded_area" style="display: none;">
                        <label for="edit_seeded_area" class="form-label">Seeded Area</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="edit_seeded_area" name="seeded_area">
                    </div>
                    <!-- Expense Input -->
                    <div class="mb-3">
                        <label for="edit_expenses_amount" class="form-label">Expense Amount</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="edit_expenses_amount" name="expenseAmount">
                    </div>
                    <input type="hidden" id="editCropsFieldId" name="cropsfield_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateOtherActivity()">Save changes</button>

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
        "info": false,
        "order": [
            [2, 'desc'],
            [3, 'desc']
        ],
        "columnDefs": [
            { "orderable": false, "targets": [0, 5] }
        ]
    });
    var activityTable = $('#activityTable').DataTable({
        "info": false,
        "order": [
            [2, 'desc'] 
    ]
    });

    $('#cropTable, #activityTable').on('click', '.pagination a', function() {
        var table = $(this).closest('table').DataTable();
        
        $(this).closest('.pagination').find('a').removeClass('active').css({
            'background-color': 'var(--tertiary-color)',
            'border': '1px solid var(--tertiary-color)'
        });

        $(this).addClass('active').css({
            'background-color': 'var(--secondary-color)',
            'border': '1px solid var(--secondary-color)'
        });

        table.page($(this).data('page')).draw('page');
    });


    cropTable.draw();
    activityTable.draw();
});

function filterByYear(year) {
    if (year === 'all') {
        // Redirect to the page without the year parameter
        var url = window.location.href.split('?')[0]; 
        window.location.href = url;
    } else {
        // Redirect to the page with the selected year
        window.location.href = window.location.pathname + '?year=' + year;
    }
}

function addActivity() {
    $('#addOtherActivityModal').modal('show');
}

function addPlanting() {
    $('#addPlantingModal').modal('show');
}
function savePlanting() {
    var formData = {
        cropsfield_id: $('#field_and_crop').val(),
        activity_date: $('#activity_date').val(),
        expenseAmount: $('#expenseAmount').val(),
        seededArea: $('#seeded_area').val()
    };

    // Debugging: Print sent data
    console.log("Sent data: ", formData);

    $.ajax({
        type: "POST",
        url: "add_planting.php",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    confirmButtonColor: '#436850',
                    text: 'Planting details saved successfully!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#addPlantingModal').modal('hide');
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
                text: 'Error occurred while saving the planting details.'
            });
        }
    });
}

function saveHarvest() {
    var formData = $('#harvestForm').serialize();

    $.ajax({
        type: "POST",
        url: "add_harvest.php", 
        data: formData,
        dataType: "json",
        success: function(response) {
            console.log(response);
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    confirmButtonColor: '#436850',
                    text: response.message
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
                text: 'Error occurred while adding the harvest activity.'
            });
        }
    });
}

function saveOtherActivity() {
    var formData = $('#otherActivityForm').serialize();

    $.ajax({
        type: "POST",
        url: "add_activity.php", 
        data: formData,
        dataType: "json",
        success: function(response) {
            console.log(response);
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    confirmButtonColor: '#436850',
                    text: response.message
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
                text: 'Error occurred while adding the activity.'
            });
        }
    });
}

function updateOtherActivity() {
    var formData = {
        'edit_activity_id': $('#edit_activity_id').val(),
        'edit_field_and_crop_other_activity': $('#edit_field_and_crop_other_activity').val(),
        'edit_other_activity_type': $('#edit_other_activity_type').val(),
        'edit_activity_date_other': $('#edit_activity_date_other').val(),
        'edit_expenses_amount': $('#edit_expenses_amount').val(),
        'edit_harvest_quantity': $('#edit_harvest_quantity input').val(), 
        'edit_seeded_area': $('#edit_seeded_area input').val(), 
        'edit_cropsfield_id': $('#editCropsFieldId').val() 
        
    };

    console.log(formData);

    $.ajax({
        type: "POST",
        url: "update_activity.php",
        data: formData,
        dataType: "json",
        success: function(response) {
            console.log(response);

            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    confirmButtonColor: '#436850',
                    text: 'Activity updated successfully!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#editOtherActivityModal').modal('hide');
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
                text: 'Error occurred while updating the activity.'
            });
        }
    });
}

function editActivity(activityId) {
    $('#editOtherActivityModal').modal('show');
    $.ajax({
        type: "POST",
        url: "get_activity.php",
        data: { activity_id: activityId },
        dataType: "json",
        success: function(response) {
            if (response.status == 'success') {
                $('#edit_activity_id').val(response.data.activity_id);
                $('#edit_field_and_crop_other_activity').val(response.data.crop_field);
                $('#edit_other_activity_type').val(response.data.activity_type);
                $('#edit_activity_date_other').val(response.data.activity_date);
                $('#edit_expenses_amount').val(response.data.expense_amount);

               // Set the value of the new hidden input field for cropsfield_id
                $('#editCropsFieldId').val(response.data.cropsfield_id);
                
                // Show or hide harvest quantity and seeded area input fields
                if (response.data.activity_type == 'Planting') {
                    $('#edit_seeded_area').show();
                    $('#edit_seeded_area input').val(response.data.seeded_area);
                    $('#edit_harvest_quantity').hide();
                } else if (response.data.activity_type == 'Harvest') {
                    $('#edit_harvest_quantity').show();
                    $('#edit_harvest_quantity input').val(response.data.harvest_quantity);
                    $('#edit_seeded_area').hide();
                } else {
                    $('#edit_harvest_quantity').hide();
                    $('#edit_seeded_area').hide();
                }
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
                text: 'Error occurred while fetching activity details.'
            });
        }
    });
}

$(document).ready(function() {
    $('#editOtherActivityForm').submit(function(e) {
        e.preventDefault();
        updateOtherActivity();
    });
});

function addHarvest() {
    $('#addHarvestModal').modal('show');
}

function loadAllActivities() {
    $.ajax({
        url: 'get_all_activities.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                $('#activityTable tbody').empty();
                response.data.forEach(activity => {
                    $('#activityTable tbody').append(`
                        <tr>
                            <td>${activity.crop_field}</td>
                            <td>${activity.activity_type}</td>
                            <td>${activity.activity_date}</td>
                            <td>
                                <div class='my-action-icons'>
                                    <button class='my-icon-btn editBtn' onclick='editActivity(${activity.activity_id})'><ion-icon name='create-outline'></ion-icon></button>
                                    <button class='my-icon-btn deleteBtn' onclick='deleteActivity(${activity.activity_id}, ${activity.cropsfield_id}, "${activity.activity_type}")'><ion-icon name='trash-outline'></ion-icon></button>
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

function viewActivities(cropsfield_id) {
    $.ajax({
        url: 'get_activities.php',
        method: 'POST',
        data: {
            cropsfield_id: cropsfield_id
        },
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                $('#activityTable tbody').empty();
                response.data.forEach(activity => {
                    $('#activityTable tbody').append(`
                        <tr>
                            <td>${activity.crop_field}</td>
                            <td>${activity.activity_type}</td>
                            <td>${activity.activity_date}</td>
                            <td>
                            <div class='my-action-icons'>
                                <button class='my-icon-btn editBtn' onclick='editActivity(${activity.activity_id})'><ion-icon name='create-outline'></ion-icon></button>
                                <button class='my-icon-btn deleteBtn' onclick='deleteActivity(${activity.activity_id}, ${activity.cropsfield_id}, "${activity.activity_type}")'><ion-icon name='trash-outline'></ion-icon></button>
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

function deleteActivity(activityId, cropsfieldId, activityType) {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete this ${activityType} activity and all associated ${activityType.toLowerCase()} data. Are you sure?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "delete_activity.php",
                data: { 
                    activity_id: activityId,
                    activity_type: activityType 
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The activity has been deleted.',
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
                        'Error occurred while deleting the activity.',
                        'error'
                    );
                }
            });
        }
    });
}

function addCrop() {
    $('#addCropModal').modal('show');
}

$('#addCropFieldForm').submit(function(e) {
    e.preventDefault();  
    
    var formData = $(this).serialize(); 

    $.ajax({
        type: "POST",
        url: "add_cropfield.php", 
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

function deleteCropsField(cropsfieldId) {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete this Crop Field and all associated activities and data. Are you sure?`,
        icon: 'warning',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "delete_cropsfield.php",
                data: { cropsfield_id: cropsfieldId },
                dataType: "json",
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The crop\'s field has been deleted.',
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
                        'Error occurred while deleting the crop\'s field.',
                        'error'
                    );
                }
            });
        }
    });
}

function editCropsField(cropsfieldId) {
    $.ajax({
        type: "POST",
        url: "get_cropsfield.php", 
        data: { cropsfield_id: cropsfieldId },
        dataType: "json",
        success: function(response) {
            if (response.status == 'success') {
                $('#editCropsFieldModal').modal('show').on('shown.bs.modal', function() {
                    $('#editCropsFieldId').val(response.data.cropsfield_id);
                    $('#cropNameEditDropdown').val(response.data.crop_name);
                    $('#editFieldNameDropdown').val(response.data.field_name);
                    $('#editSeededArea').val(response.data.seeded_area);
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
                text: 'Error occurred while fetching crop field details.'
            });
        }
    });
}

$('#editCropsFieldForm').submit(function(e) {
    e.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        type: "POST",
        url: "update_cropsfield.php",
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
                        $('#editCropsFieldModal').modal('hide');
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