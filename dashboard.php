<?php
// Start the session
session_start();
if (!isset($_SESSION['user'])) {
    header('location: index.php');
}

$user = $_SESSION['user'];

// Check if the user has the 'dashboard_view' permission
if (!in_array('dashboard_view', explode(',', $user['permissions']))) {
    // User does not have access
    $accessDenied = true;
} else {
    // User has access, continue to get graph data
    include('database/po_status_pie_graph.php');
    $accessDenied = false; // Set accessDenied to false when the user has permission
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS Dashboard</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div id="dashboardMainContainer">
        <?php include('partials/sidebar.php') ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/topnav.php') ?>
            <div class="dashboard_content">
            <?php if ($accessDenied): ?>
                <div style="margin: 50px;">
                    <h2>You have no access to this page.</h2>
                </div>
            <?php else: ?>
                <div class="dashboard_content_main">
                     <div>
                        <canvas id="salesCategoryChart" width="400" height="200"></canvas>
                    </div>
                    <select id="categoryFilter">
                        <option value="">Select a Category</option>
                        <?php foreach (array_keys($product_data_by_category) as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div>
                        <canvas id="salesProductChart" width="400" height="200"></canvas>
                    </div>
                    <div>
                        <figure class="highcharts-figure">
                            <div id="container"></div>
                            <p class="highcharts-description">
                                Here is the breakdown of purchase orders by status.
                            </p>
                        </figure>
                    </div id="deliveryHistory">
                    <div>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script>
    var graphData = <?= json_encode($results) ?>;
    
    // Define the color mapping for each status
    var statusColors = {
        'COMPLETE': '#90EE90', // Light Green
        'INCOMPLETE': '#FFFFE0', // Light Yellow
        'PENDING': '#F08080' // Light Coral
    };

    Highcharts.chart('container', {
        chart: {
            type: 'pie',
            backgroundColor: '#f4f4f4', // Light background
        },
        title: {
            text: 'Purchase Order By Status',
            style: {
                color: '#333',
                fontSize: '20px',
                fontWeight: 'bold'
            }
        },
        tooltip: {
            pointFormatter: function() {
                return `<b>${this.name}</b>: ${this.y} (${this.percentage.toFixed(1)}%)`;
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '{point.name}: {point.y} ({point.percentage:.1f}%)',
                    style: {
                        fontSize: '14px',
                        color: '#333',
                    },
                    distance: -30, // Adjust label distance from the pie slices
                    filter: {
                        property: 'percentage',
                        operator: '>',
                        value: 5
                    }
                },
                showInLegend: false // Disable legend
            }
        },
        series: [{
            name: 'Status',
            colorByPoint: true,
            data: graphData.map(function(point) {
                // Assign colors based on status
                return {
                    name: point.name,
                    y: point.y,
                    color: statusColors[point.name] || '#FFFFFF' // Default to white if status is unknown
                };
            })
        }],
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    plotOptions: {
                        pie: {
                            dataLabels: {
                                distance: -20
                            }
                        }
                    }
                }
            }]
        }
    });
</script>


<script>
    // Prepare the data for Chart.js
    const labels1 = <?= json_encode($dates) ?>;

    const datasets = [
        <?php foreach ($categories as $category): ?>
        {
            label: '<?= $category ?>',
            data: <?= json_encode(array_values(array_map(function($date) use ($data_by_category, $category) {
                return isset($data_by_category[$category][$date]) ? $data_by_category[$category][$date] : 0;
            }, $dates))) ?>,
            fill: false,
            borderColor: '<?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)) ?>',
            tension: 0.1
        },
        <?php endforeach; ?>
    ];

    // Create the line chart for categories
    const ctx1 = document.getElementById('salesCategoryChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: labels1,
            datasets: datasets
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Total Sales'
                    },
                    beginAtZero: true
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Sales per Category',
                    font: {
                        size: 24 // Adjust the font size here
                    }
                }
            }
        }
    });
</script>

<script>
    // Ensure allProductData and data_by_date are defined correctly
    const allProductData = <?= json_encode($product_data_by_category) ?>; // Product sales data by category
    const labels = <?= json_encode($data_by_date) ?>; // Date labels for the x-axis

    // Global variable to hold the chart instance for sales by product
    let salesProductChartInstance = null;

    function aggregateProductData() {
        const aggregatedData = {};

        // Loop through each category and aggregate product sales data
        for (const products of Object.values(allProductData)) {
            for (const [product, salesData] of Object.entries(products)) {
                if (!aggregatedData[product]) {
                    aggregatedData[product] = {};
                }
                labels.forEach(date => {
                    if (salesData[date]) {
                        // Convert string to number for accurate aggregation
                        const salesAmount = parseInt(salesData[date], 10) || 0;
                        if (!aggregatedData[product][date]) {
                            aggregatedData[product][date] = 0;
                        }
                        aggregatedData[product][date] += salesAmount;
                    }
                });
            }
        }

        console.log('Aggregated Data:', aggregatedData); // Debugging line
        return aggregatedData;
    }

    // Function to update the product sales chart
    function updateChart(category) {
        const ctx = document.getElementById('salesProductChart').getContext('2d');

        // If a chart already exists, destroy it before creating a new one
        if (salesProductChartInstance !== null) {
            salesProductChartInstance.destroy();
        }

        const datasets = [];
        const uniqueLabels = [...new Set(labels)]; // Ensure unique labels

        let dataToDisplay = {};

        if (category === '') {
            // Show sales for all products by default
            dataToDisplay = aggregateProductData();
        } else {
            // Show sales for products in the selected category
            if (allProductData[category]) {
                // Create a map to avoid duplicate aggregation
                const categoryDataMap = {};

                for (const [product, salesData] of Object.entries(allProductData[category])) {
                    if (!categoryDataMap[product]) {
                        categoryDataMap[product] = {};
                    }
                    uniqueLabels.forEach(date => {
                        if (salesData[date]) {
                            // Convert string to number for accurate aggregation
                            const salesAmount = parseInt(salesData[date], 10) || 0;
                            if (!categoryDataMap[product][date]) {
                                categoryDataMap[product][date] = 0;
                            }
                            categoryDataMap[product][date] += salesAmount;
                        }
                    });
                }

                dataToDisplay = categoryDataMap;
            }
        }

        console.log('Data to Display:', dataToDisplay); // Debugging line

        // Create datasets for each product
        for (const [product, salesData] of Object.entries(dataToDisplay)) {
            datasets.push({
                label: product,
                data: uniqueLabels.map(date => salesData[date] || 0),
                fill: false,
                borderColor: '#' + Math.floor(Math.random() * 16777215).toString(16), // Random color
                tension: 0.1
            });
        }

        // Create a new chart with filtered data
        salesProductChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: uniqueLabels, // Ensure unique labels
                datasets: datasets
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Sales'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Sales per Product',
                        font: {
                            size: 24
                        }
                    }
                }
            }
        });
    }

    // Initialize chart with all products
    updateChart('');

    // Handle category filter change
    document.getElementById('categoryFilter').addEventListener('change', function () {
        const selectedCategory = this.value;
        updateChart(selectedCategory);
    });

</script>




</body>
</html>
