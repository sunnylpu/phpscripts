<?php
require_once "header.php";
require_once "../includes/db_connect.php";

$message = '';
$error = '';

// Handle attendance recording
if (isset($_POST['record_attendance'])) {
    $member_id = $_POST['member_id'];
    $check_in_time = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO attendance (member_id, check_in_time) VALUES (?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "is", $member_id, $check_in_time);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Attendance recorded successfully.";
        } else {
            $error = "Error recording attendance.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Get today's attendance
$sql = "SELECT a.*, m.first_name, m.last_name, m.email 
        FROM attendance a 
        JOIN members m ON a.member_id = m.id 
        WHERE DATE(a.check_in_time) = CURDATE() 
        ORDER BY a.check_in_time DESC";
$today_attendance = mysqli_query($conn, $sql);

// Get attendance statistics
$stats = [
    'today_count' => 0,
    'week_count' => 0,
    'month_count' => 0
];

$sql = "SELECT COUNT(*) as count FROM attendance WHERE DATE(check_in_time) = CURDATE()";
$result = mysqli_query($conn, $sql);
$stats['today_count'] = mysqli_fetch_assoc($result)['count'] ?? 0;

$sql = "SELECT COUNT(*) as count FROM attendance WHERE YEARWEEK(check_in_time) = YEARWEEK(CURDATE())";
$result = mysqli_query($conn, $sql);
$stats['week_count'] = mysqli_fetch_assoc($result)['count'] ?? 0;

$sql = "SELECT COUNT(*) as count FROM attendance WHERE MONTH(check_in_time) = MONTH(CURRENT_DATE())";
$result = mysqli_query($conn, $sql);
$stats['month_count'] = mysqli_fetch_assoc($result)['count'] ?? 0;

// Get active members for attendance form
$sql = "SELECT m.id, u.first_name, u.last_name, u.email 
        FROM members m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.expiry_date >= CURDATE() 
        ORDER BY u.first_name";
$active_members = mysqli_query($conn, $sql);
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Today's Attendance</h5>
                <h2 class="card-text"><?php echo $stats['today_count']; ?></h2>
                <p class="text-muted">Members checked in today</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Weekly Attendance</h5>
                <h2 class="card-text"><?php echo $stats['week_count']; ?></h2>
                <p class="text-muted">This week's total</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Monthly Attendance</h5>
                <h2 class="card-text"><?php echo $stats['month_count']; ?></h2>
                <p class="text-muted">This month's total</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Record Attendance</h5>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Member</label>
                        <select class="form-select" name="member_id" required>
                            <option value="">Select a member</option>
                            <?php while ($member = mysqli_fetch_assoc($active_members)): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name'] . ' (' . $member['email'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="record_attendance" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Record Check-in
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Today's Attendance Records</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Check-in Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($record = mysqli_fetch_assoc($today_attendance)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                <td><?php echo date('h:i A', strtotime($record['check_in_time'])); ?></td>
                                <td>
                                    <span class="badge bg-success">Present</span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Attendance Reports</h5>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Member</label>
                        <select class="form-select" name="member_id">
                            <option value="">All Members</option>
                            <?php 
                            mysqli_data_seek($active_members, 0);
                            while ($member = mysqli_fetch_assoc($active_members)): 
                            ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    </div>
                </form>

                <?php if (isset($_GET['start_date']) && isset($_GET['end_date'])): 
                    $start_date = $_GET['start_date'];
                    $end_date = $_GET['end_date'];
                    $member_id = $_GET['member_id'] ?? '';
                    
                    $sql = "SELECT a.*, m.first_name, m.last_name, m.email 
                            FROM attendance a 
                            JOIN members m ON a.member_id = m.id 
                            WHERE DATE(a.check_in_time) BETWEEN ? AND ?";
                    if ($member_id) {
                        $sql .= " AND a.member_id = ?";
                    }
                    $sql .= " ORDER BY a.check_in_time DESC";
                    
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        if ($member_id) {
                            mysqli_stmt_bind_param($stmt, "ssi", $start_date, $end_date, $member_id);
                        } else {
                            mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
                        }
                        mysqli_stmt_execute($stmt);
                        $report_data = mysqli_stmt_get_result($stmt);
                ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Date</th>
                                <th>Check-in Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($record = mysqli_fetch_assoc($report_data)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($record['check_in_time'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($record['check_in_time'])); ?></td>
                                <td>
                                    <span class="badge bg-success">Present</span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php } endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?> 