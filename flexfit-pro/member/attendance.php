<?php
require_once "../includes/db_connect.php";
require_once "../includes/session_check.php";

// Ensure only members can access this page
if ($_SESSION['role'] !== 'member') {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Get attendance statistics
$stats = [
    'this_week' => 0,
    'this_month' => 0,
    'total' => 0,
    'streak' => 0,
    'missed_days' => 0
];

// This week's attendance
$sql = "SELECT COUNT(*) as count FROM attendance 
        WHERE member_id = ? AND YEARWEEK(check_in_time) = YEARWEEK(CURDATE())";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['this_week'] = mysqli_fetch_assoc($result)['count'] ?? 0;
    mysqli_stmt_close($stmt);
}

// This month's attendance
$sql = "SELECT COUNT(*) as count FROM attendance 
        WHERE member_id = ? AND MONTH(check_in_time) = MONTH(CURRENT_DATE())";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['this_month'] = mysqli_fetch_assoc($result)['count'] ?? 0;
    mysqli_stmt_close($stmt);
}

// Total attendance
$sql = "SELECT COUNT(*) as count FROM attendance WHERE member_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['total'] = mysqli_fetch_assoc($result)['count'] ?? 0;
    mysqli_stmt_close($stmt);
}

// Current streak
$sql = "SELECT DATEDIFF(CURDATE(), DATE(check_in_time)) as days 
        FROM attendance 
        WHERE member_id = ? 
        ORDER BY check_in_time DESC 
        LIMIT 1";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['streak'] = mysqli_fetch_assoc($result)['days'] ?? 0;
    mysqli_stmt_close($stmt);
}

// Get attendance records for the current month
$sql = "SELECT * FROM attendance 
        WHERE member_id = ? 
        AND MONTH(check_in_time) = MONTH(CURRENT_DATE()) 
        ORDER BY check_in_time DESC";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $monthly_records = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

// Get attendance patterns (day of week)
$sql = "SELECT DAYNAME(check_in_time) as day, COUNT(*) as count 
        FROM attendance 
        WHERE member_id = ? 
        GROUP BY DAYNAME(check_in_time) 
        ORDER BY FIELD(DAYNAME(check_in_time), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $patterns = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

// Prepare data for attendance pattern chart
$pattern_data = [
    'days' => [],
    'counts' => []
];

while ($pattern = mysqli_fetch_assoc($patterns)) {
    $pattern_data['days'][] = $pattern['day'];
    $pattern_data['counts'][] = $pattern['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History - FlexFit Pro</title>
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
                        <a class="nav-link" href="progress.php">Progress</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="attendance.php">Attendance</a>
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
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">This Week</h5>
                        <h2 class="card-text"><?php echo $stats['this_week']; ?></h2>
                        <p class="text-muted">Check-ins</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">This Month</h5>
                        <h2 class="card-text"><?php echo $stats['this_month']; ?></h2>
                        <p class="text-muted">Check-ins</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total</h5>
                        <h2 class="card-text"><?php echo $stats['total']; ?></h2>
                        <p class="text-muted">Check-ins</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Current Streak</h5>
                        <h2 class="card-text"><?php echo $stats['streak']; ?></h2>
                        <p class="text-muted">Days</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Attendance Pattern</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="patternChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">This Month's Attendance</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Check-in Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = mysqli_fetch_assoc($monthly_records)): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($record['check_in_time'])); ?></td>
                                            <td><?php echo date('l', strtotime($record['check_in_time'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($record['check_in_time'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Attendance Tips</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <strong>Consistency is Key:</strong> Try to maintain a regular workout schedule.
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <strong>Morning Workouts:</strong> Consider working out in the morning to start your day energized.
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <strong>Track Progress:</strong> Regular attendance helps track your fitness progress better.
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <strong>Accountability:</strong> Share your attendance goals with your trainer for better accountability.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Average Weekly Attendance:</strong> <?php echo round($stats['total'] / (date('W') + 1), 1); ?> times</p>
                        <p><strong>Most Active Day:</strong> <?php echo !empty($pattern_data['days']) ? $pattern_data['days'][array_search(max($pattern_data['counts']), $pattern_data['counts'])] : 'N/A'; ?></p>
                        <p><strong>Monthly Goal Progress:</strong> <?php echo round(($stats['this_month'] / 20) * 100); ?>%</p>
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
        // Attendance Pattern Chart
        const patternCtx = document.getElementById('patternChart').getContext('2d');
        new Chart(patternCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($pattern_data['days']); ?>,
                datasets: [{
                    label: 'Check-ins',
                    data: <?php echo json_encode($pattern_data['counts']); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 