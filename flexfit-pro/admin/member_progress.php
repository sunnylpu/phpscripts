<?php
require_once "header.php";
require_once "../includes/db_connect.php";

$message = '';
$error = '';

// Handle progress recording
if (isset($_POST['record_progress'])) {
    $member_id = $_POST['member_id'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $bmi = $_POST['bmi'];
    $body_fat = $_POST['body_fat'];
    $muscle_mass = $_POST['muscle_mass'];
    $measurement_date = $_POST['measurement_date'];
    $notes = $_POST['notes'];
    
    $sql = "INSERT INTO member_progress (member_id, weight, height, bmi, body_fat, muscle_mass, measurement_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iddddsss", $member_id, $weight, $height, $bmi, $body_fat, $muscle_mass, $measurement_date, $notes);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Progress recorded successfully.";
        } else {
            $error = "Error recording progress.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Get active members for progress form
$sql = "SELECT m.id, u.first_name, u.last_name, u.email 
        FROM members m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.expiry_date >= CURDATE() 
        ORDER BY u.first_name";
$active_members = mysqli_query($conn, $sql);

// Get member progress data if member is selected
$member_id = isset($_GET['member_id']) ? $_GET['member_id'] : '';
$progress_data = [];
$member_details = null;

if ($member_id) {
    // Get member details
    $sql = "SELECT m.*, u.first_name, u.last_name, u.email 
            FROM members m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $member_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $member_details = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }

    // Get progress history
    $sql = "SELECT * FROM member_progress 
            WHERE member_id = ? 
            ORDER BY measurement_date DESC";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $member_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $progress_data[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Select Member</h5>
            </div>
            <div class="card-body">
                <form method="get">
                    <div class="mb-3">
                        <label class="form-label">Member</label>
                        <select class="form-select" name="member_id" onchange="this.form.submit()">
                            <option value="">Select a member</option>
                            <?php while ($member = mysqli_fetch_assoc($active_members)): ?>
                                <option value="<?php echo $member['id']; ?>" <?php echo $member_id == $member['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name'] . ' (' . $member['email'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($member_details): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Record Progress</h5>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
                    <div class="mb-3">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" step="0.1" class="form-control" name="weight" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Height (cm)</label>
                        <input type="number" step="0.1" class="form-control" name="height" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">BMI</label>
                        <input type="number" step="0.1" class="form-control" name="bmi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Body Fat (%)</label>
                        <input type="number" step="0.1" class="form-control" name="body_fat" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Muscle Mass (kg)</label>
                        <input type="number" step="0.1" class="form-control" name="muscle_mass" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Measurement Date</label>
                        <input type="date" class="form-control" name="measurement_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                    <button type="submit" name="record_progress" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> Record Progress
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <?php if ($member_details): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Member Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($member_details['first_name'] . ' ' . $member_details['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($member_details['email']); ?></p>
                        <p><strong>Join Date:</strong> <?php echo date('M d, Y', strtotime($member_details['join_date'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Membership Type:</strong> <?php echo htmlspecialchars($member_details['membership_type']); ?></p>
                        <p><strong>Expiry Date:</strong> <?php echo date('M d, Y', strtotime($member_details['expiry_date'])); ?></p>
                        <p><strong>Goal:</strong> <?php echo htmlspecialchars($member_details['goal']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
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
                                <th>Height</th>
                                <th>BMI</th>
                                <th>Body Fat</th>
                                <th>Muscle Mass</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($progress_data as $progress): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($progress['measurement_date'])); ?></td>
                                <td><?php echo $progress['weight']; ?> kg</td>
                                <td><?php echo $progress['height']; ?> cm</td>
                                <td><?php echo $progress['bmi']; ?></td>
                                <td><?php echo $progress['body_fat']; ?>%</td>
                                <td><?php echo $progress['muscle_mass']; ?> kg</td>
                                <td><?php echo htmlspecialchars($progress['notes']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Progress Charts</h5>
            </div>
            <div class="card-body">
                <canvas id="weightChart"></canvas>
                <canvas id="bodyFatChart" class="mt-4"></canvas>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center">
                <h5>Select a member to view their progress</h5>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($member_details && !empty($progress_data)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Weight Chart
const weightCtx = document.getElementById('weightChart').getContext('2d');
new Chart(weightCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($p) { return date('M d', strtotime($p['measurement_date'])); }, $progress_data)); ?>,
        datasets: [{
            label: 'Weight (kg)',
            data: <?php echo json_encode(array_map(function($p) { return $p['weight']; }, $progress_data)); ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Weight Progress'
            }
        }
    }
});

// Body Fat Chart
const bodyFatCtx = document.getElementById('bodyFatChart').getContext('2d');
new Chart(bodyFatCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($p) { return date('M d', strtotime($p['measurement_date'])); }, $progress_data)); ?>,
        datasets: [{
            label: 'Body Fat (%)',
            data: <?php echo json_encode(array_map(function($p) { return $p['body_fat']; }, $progress_data)); ?>,
            borderColor: 'rgb(255, 99, 132)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Body Fat Progress'
            }
        }
    }
});
</script>
<?php endif; ?>

<?php require_once "footer.php"; ?> 