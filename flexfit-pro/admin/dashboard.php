<?php
require_once "header.php";
require_once "../includes/db_connect.php";

// Get statistics
$stats = [
    'total_members' => 0,
    'active_members' => 0,
    'total_trainers' => 0,
    'total_payments' => 0,
    'recent_payments' => []
];

// Get total members
$sql = "SELECT COUNT(*) as count FROM members";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_members'] = $row['count'];
}

// Get active members
$sql = "SELECT COUNT(*) as count FROM members WHERE expiry_date >= CURDATE()";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['active_members'] = $row['count'];
}

// Get total trainers
$sql = "SELECT COUNT(*) as count FROM trainers";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_trainers'] = $row['count'];
}

// Get total payments
$sql = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_payments'] = $row['total'] ?? 0;
}

// Get recent payments
$sql = "SELECT p.*, m.first_name, m.last_name 
        FROM payments p 
        JOIN members m ON p.member_id = m.id 
        ORDER BY p.payment_date DESC 
        LIMIT 5";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $stats['recent_payments'][] = $row;
}
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Total Members</h5>
                <h2 class="card-text"><?php echo $stats['total_members']; ?></h2>
                <p class="text-muted">Active: <?php echo $stats['active_members']; ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Total Trainers</h5>
                <h2 class="card-text"><?php echo $stats['total_trainers']; ?></h2>
                <p class="text-muted">Active trainers</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h2 class="card-text">$<?php echo number_format($stats['total_payments'], 2); ?></h2>
                <p class="text-muted">All time</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Attendance Today</h5>
                <h2 class="card-text"><?php 
                    $sql = "SELECT COUNT(*) as count FROM attendance WHERE DATE(check_in) = CURDATE()";
                    $result = mysqli_query($conn, $sql);
                    echo mysqli_fetch_assoc($result)['count'];
                ?></h2>
                <p class="text-muted">Members checked in</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Payments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_payments'] as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $payment['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="manage_members.php?action=add" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Add New Member
                    </a>
                    <a href="manage_trainers.php?action=add" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Add New Trainer
                    </a>
                    <a href="membership_plans.php" class="btn btn-info">
                        <i class="bi bi-credit-card"></i> Manage Plans
                    </a>
                    <a href="attendance.php" class="btn btn-warning">
                        <i class="bi bi-calendar-check"></i> Check Attendance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?> 