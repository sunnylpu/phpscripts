<?php
require_once "../includes/db_connect.php";
require_once "../includes/session_check.php";

// Ensure only members can access this page
if ($_SESSION['role'] !== 'member') {
    header("Location: ../login.php");
    exit();
}

// Get member details
$member_id = $_SESSION['user_id'];
$sql = "SELECT m.*, u.first_name, u.last_name, u.email 
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

// Get current workout plan
$sql = "SELECT wp.*, mwp.start_date 
        FROM member_workout_plans mwp 
        JOIN workout_plans wp ON mwp.plan_id = wp.id 
        WHERE mwp.member_id = ? 
        ORDER BY mwp.start_date DESC 
        LIMIT 1";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $workout_plan = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Get current diet plan
$sql = "SELECT dp.*, mdp.start_date 
        FROM member_diet_plans mdp 
        JOIN diet_plans dp ON mdp.plan_id = dp.id 
        WHERE mdp.member_id = ? 
        ORDER BY mdp.start_date DESC 
        LIMIT 1";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $diet_plan = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Get recent progress
$sql = "SELECT * FROM member_progress 
        WHERE member_id = ? 
        ORDER BY measurement_date DESC 
        LIMIT 5";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $progress_data = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

// Get attendance records
$sql = "SELECT * FROM attendance 
        WHERE member_id = ? 
        ORDER BY check_in_time DESC 
        LIMIT 10";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $attendance_data = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

// Get attendance statistics
$stats = [
    'this_week' => 0,
    'this_month' => 0,
    'total' => 0
];

$sql = "SELECT COUNT(*) as count FROM attendance 
        WHERE member_id = ? AND YEARWEEK(check_in_time) = YEARWEEK(CURDATE())";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['this_week'] = mysqli_fetch_assoc($result)['count'] ?? 0;
    mysqli_stmt_close($stmt);
}

$sql = "SELECT COUNT(*) as count FROM attendance 
        WHERE member_id = ? AND MONTH(check_in_time) = MONTH(CURRENT_DATE())";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['this_month'] = mysqli_fetch_assoc($result)['count'] ?? 0;
    mysqli_stmt_close($stmt);
}

$sql = "SELECT COUNT(*) as count FROM attendance WHERE member_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['total'] = mysqli_fetch_assoc($result)['count'] ?? 0;
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - FlexFit Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
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
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
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
                        <h5 class="card-title">This Week's Attendance</h5>
                        <h2 class="card-text"><?php echo $stats['this_week']; ?></h2>
                        <p class="text-muted">Check-ins this week</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">This Month's Attendance</h5>
                        <h2 class="card-text"><?php echo $stats['this_month']; ?></h2>
                        <p class="text-muted">Check-ins this month</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Attendance</h5>
                        <h2 class="card-text"><?php echo $stats['total']; ?></h2>
                        <p class="text-muted">All time check-ins</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Current Workout Plan</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($workout_plan): ?>
                            <h6><?php echo htmlspecialchars($workout_plan['name']); ?></h6>
                            <p><?php echo htmlspecialchars($workout_plan['description']); ?></p>
                            <p><strong>Difficulty:</strong> <?php echo ucfirst($workout_plan['difficulty']); ?></p>
                            <p><strong>Duration:</strong> <?php echo $workout_plan['duration_weeks']; ?> weeks</p>
                            <p><strong>Started:</strong> <?php echo date('M d, Y', strtotime($workout_plan['start_date'])); ?></p>
                            <a href="workout_plan.php" class="btn btn-primary">View Details</a>
                        <?php else: ?>
                            <p>No active workout plan assigned.</p>
                            <a href="workout_plan.php" class="btn btn-primary">View Available Plans</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Progress</h5>
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
                                    <?php while ($progress = mysqli_fetch_assoc($progress_data)): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($progress['measurement_date'])); ?></td>
                                        <td><?php echo $progress['weight']; ?> kg</td>
                                        <td><?php echo $progress['body_fat']; ?>%</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="progress.php" class="btn btn-primary">View All Progress</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Current Diet Plan</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($diet_plan): ?>
                            <h6><?php echo htmlspecialchars($diet_plan['name']); ?></h6>
                            <p><?php echo htmlspecialchars($diet_plan['description']); ?></p>
                            <p><strong>Daily Calories:</strong> <?php echo $diet_plan['calories']; ?> kcal</p>
                            <p><strong>Macros:</strong> P: <?php echo $diet_plan['protein']; ?>g C: <?php echo $diet_plan['carbs']; ?>g F: <?php echo $diet_plan['fats']; ?>g</p>
                            <p><strong>Started:</strong> <?php echo date('M d, Y', strtotime($diet_plan['start_date'])); ?></p>
                            <a href="diet_plan.php" class="btn btn-primary">View Details</a>
                        <?php else: ?>
                            <p>No active diet plan assigned.</p>
                            <a href="diet_plan.php" class="btn btn-primary">View Available Plans</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Attendance</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check-in Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($attendance = mysqli_fetch_assoc($attendance_data)): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($attendance['check_in_time'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($attendance['check_in_time'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="attendance.php" class="btn btn-primary">View All Attendance</a>
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
    <script src="../assets/js/main.js"></script>
</body>
</html> 