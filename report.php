<?php
// Start the session
session_start();
if (!isset($_SESSION['user'])) header('location: index.php');

$user = $_SESSION['user'];

// Check if user has the "reports_view" permission
$has_permission = in_array("reports_view", explode(',', $user['permissions']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS Dashboard</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .startDateLabel, .endDateLabel {
            padding-bottom: 10px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
    </style>
</head>
<body>
    <div id="dashboardMainContainer">
        <?php include('partials/sidebar.php') ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/topnav.php') ?>
            <div id="reportsContainer">
                <?php if ($has_permission): ?>
                    <div class="reportTypeContainer">
                        <div class="reportType">
                            <p>Export Products</p>
                            <form action="database/report_csv.php" method="GET">
                                <div>
                                    <div class="startDateLabel">
                                        <label>Start Date:</label>
                                        <input type="date" name="start_date">
                                    </div>
                                    <div class="endDateLabel">
                                        <label>End Date:</label>
                                        <input type="date" name="end_date">
                                    </div>
                                </div>
                                <input type="hidden" name="report" value="product">
                                <div class="alignRight">
                                    <input type="submit" value="Excel" class="reportExportBtn">
                                </div>
                            </form>
                        </div>
                        <div class="reportType">
                            <p>Export Categories</p>
                            <form action="database/report_csv.php" method="GET">
                                <div>
                                    <div class="startDateLabel">
                                        <label>Start Date:</label>
                                        <input type="date" name="start_date">
                                    </div>
                                    <div class="endDateLabel">
                                        <label>End Date:</label>
                                        <input type="date" name="end_date">
                                    </div>
                                </div>
                                <input type="hidden" name="report" value="category">
                                <div class="alignRight">
                                    <input type="submit" value="Excel" class="reportExportBtn">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="reportTypeContainer">
                        <div class="reportType">
                            <p>Export Deliveries</p>
                            <form action="database/report_csv.php" method="GET">
                                <div>
                                    <div class="startDateLabel">
                                        <label>Start Date:</label>
                                        <input type="date" name="start_date">
                                    </div>
                                    <div class="endDateLabel">
                                        <label>End Date:</label>
                                        <input type="date" name="end_date">
                                    </div>
                                </div>
                                <input type="hidden" name="report" value="delivery">
                                <div class="alignRight">
                                    <input type="submit" value="Excel" class="reportExportBtn">
                                </div>
                            </form>
                        </div>
                        <div class="reportType">
                            <p>Export Purchase Orders</p>
                            <form action="database/report_csv.php" method="GET">
                                <div>
                                    <div class="startDateLabel">
                                        <label>Start Date:</label>
                                        <input type="date" name="start_date">
                                    </div>
                                    <div class="endDateLabel">
                                        <label>End Date:</label>
                                        <input type="date" name="end_date">
                                    </div>
                                </div>
                                <input type="hidden" name="report" value="purchase">
                                <div class="alignRight">
                                    <input type="submit" value="Excel" class="reportExportBtn">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="reportTypeContainer2">
                        <div class="reportType one">
                            <p>Export Sales</p>
                            <form action="database/report_csv.php" method="GET">
                                <div>
                                    <div class="startDateLabel">
                                        <label>Start Date:</label>
                                        <input type="date" name="start_date">
                                    </div>
                                    <div class="endDateLabel">
                                        <label>End Date:</label>
                                        <input type="date" name="end_date">
                                    </div>
                                </div>
                                <input type="hidden" name="report" value="sale">
                                <div class="alignRight">
                                    <input type="submit" value="Excel" class="reportExportBtn">
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 100px; margin-left: 50px; margin-bottom: 50px;">
                        <h2>You have no access to this page.</h2>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
