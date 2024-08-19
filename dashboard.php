<?php
    // Start the session
    session_start();
    if(!isset($_SESSION['user'])) header('location: index.php');

    $user = $_SESSION['user'];

    // Get graph data - purchase order by status
    include('database/po_status_pie_graph.php');
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
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script>
        var graphData = <?= json_encode($results) ?>;
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
                data: graphData,
                colors: ['#a6cee3', '#b2df8a', '#fb9a99', '#fdbf6f', '#cab2d6', '#ffff99', '#1f78b4', '#33a02c', '#e31a1c']
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
            data: <?= json_encode(array_values($data_by_category[$category])) ?>,
            fill: false,
            borderColor: '<?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)) ?>',
            tension: 0.1
        },
        <?php endforeach; ?>
    ];

    // Create the line chart
    const ctx = document.getElementById('salesCategoryChart').getContext('2d');
    const salesCategoryChart = new Chart(ctx, {
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
function updateChart(category) {
    const ctx = document.getElementById('salesProductChart').getContext('2d');

    // If a chart already exists, destroy it before creating a new one
    if (salesProductChartInstance !== null) {
        salesProductChartInstance.destroy();
    }

    const datasets = [];
    // Check if the selected category has data
    if (category && allProductData[category]) {
        // Loop through products in the selected category
        for (const [product, salesData] of Object.entries(allProductData[category])) {
            datasets.push({
                label: product,
                data: labels.map(date => salesData[date] || 0), // Fill data based on the selected category and product
                fill: false,
                borderColor: '#' + Math.floor(Math.random() * 16777215).toString(16), // Random color
                tension: 0.1
            });
        }
    }

    // Create a new chart with filtered data
    salesProductChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels, // Dates are the same regardless of category
            datasets: datasets // Only datasets for the selected category are included
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

// Initialize chart with no category filter
updateChart('');

// Handle category filter change
document.getElementById('categoryFilter').addEventListener('change', function () {
    const selectedCategory = this.value;
    updateChart(selectedCategory);
});

</script>

</body>
</html>
