<?php
require_once "../includes/db_connect.php";
require_once "../includes/session_check.php";

// Ensure only members can access this page
if ($_SESSION['role'] !== 'member') {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Get member details and goal
$sql = "SELECT m.*, u.first_name, u.last_name 
        FROM members m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $member = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Get all progress records
$sql = "SELECT * FROM member_progress 
        WHERE member_id = ? 
        ORDER BY measurement_date DESC";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $progress_data = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

// Prepare data for charts
$chart_data = [
    'dates' => [],
    'weight' => [],
    'body_fat' => [],
    'muscle_mass' => []
];

while ($progress = mysqli_fetch_assoc($progress_data)) {
    $chart_data['dates'][] = date('M d', strtotime($progress['measurement_date']));
    $chart_data['weight'][] = $progress['weight'];
    $chart_data['body_fat'][] = $progress['body_fat'];
    $chart_data['muscle_mass'][] = $progress['muscle_mass'];
}

// Reverse arrays for chronological order
$chart_data['dates'] = array_reverse($chart_data['dates']);
$chart_data['weight'] = array_reverse($chart_data['weight']);
$chart_data['body_fat'] = array_reverse($chart_data['body_fat']);
$chart_data['muscle_mass'] = array_reverse($chart_data['muscle_mass']);

// Get latest progress for comparison
$latest_progress = null;
if (!empty($chart_data['weight'])) {
    $latest_progress = [
        'weight' => end($chart_data['weight']),
        'body_fat' => end($chart_data['body_fat']),
        'muscle_mass' => end($chart_data['muscle_mass'])
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracking - FlexFit Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">FlexFit Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="workout_plan.php">Workout Plan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="diet_plan.php">Diet Plan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="progress.php">Progress</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Current Weight</h5>
                        <h2 class="card-text"><?php echo $latest_progress ? $latest_progress['weight'] : 'N/A'; ?> kg</h2>
                        <p class="text-muted">Latest measurement</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Body Fat</h5>
                        <h2 class="card-text"><?php echo $latest_progress ? $latest_progress['body_fat'] : 'N/A'; ?>%</h2>
                        <p class="text-muted">Latest measurement</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Muscle Mass</h5>
                        <h2 class="card-text"><?php echo $latest_progress ? $latest_progress['muscle_mass'] : 'N/A'; ?> kg</h2>
                        <p class="text-muted">Latest measurement</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Progress Charts</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="weightChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Body Composition</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="compositionChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Progress History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Weight</th>
                                        <th>Body Fat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_combine($chart_data['dates'], array_map(null, $chart_data['weight'], $chart_data['body_fat'])) as $date => $data): ?>
                                        <tr>
                                            <td><?php echo $date; ?></td>
                                            <td><?php echo $data[0]; ?> kg</td>
                                            <td><?php echo $data[1]; ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Your Goal</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Current Goal:</strong> <?php echo htmlspecialchars($member['goal']); ?></p>
                        <?php if ($latest_progress): ?>
                            <?php
                            $weight_change = $latest_progress['weight'] - $chart_data['weight'][0];
                            $body_fat_change = $latest_progress['body_fat'] - $chart_data['body_fat'][0];
                            ?>
                            <p><strong>Progress since start:</strong></p>
                            <ul>
                                <li>Weight: <?php echo $weight_change > 0 ? '+' : ''; ?><?php echo $weight_change; ?> kg</li>
                                <li>Body Fat: <?php echo $body_fat_change > 0 ? '+' : ''; ?><?php echo $body_fat_change; ?>%</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> FlexFit Pro. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Weight Chart
        const weightCtx = document.getElementById('weightChart').getContext('2d');
        new Chart(weightCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_data['dates']); ?>,
                datasets: [{
                    label: 'Weight (kg)',
                    data: <?php echo json_encode($chart_data['weight']); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });

        // Body Composition Chart
        const compositionCtx = document.getElementById('compositionChart').getContext('2d');
        new Chart(compositionCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_data['dates']); ?>,
                datasets: [
                    {
                        label: 'Body Fat (%)',
                        data: <?php echo json_encode($chart_data['body_fat']); ?>,
                        borderColor: 'rgb(255, 99, 132)',
                        tension: 0.1
                    },
                    {
                        label: 'Muscle Mass (kg)',
                        data: <?php echo json_encode($chart_data['muscle_mass']); ?>,
                        borderColor: 'rgb(54, 162, 235)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
</body>
</html> 