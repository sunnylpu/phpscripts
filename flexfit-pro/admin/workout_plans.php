<?php
require_once "header.php";
require_once "../includes/db_connect.php";

$message = '';
$error = '';

// Handle workout plan creation/update
if (isset($_POST['save_plan'])) {
    $plan_id = $_POST['plan_id'] ?? null;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $difficulty = $_POST['difficulty'];
    $duration_weeks = $_POST['duration_weeks'];
    $exercises = $_POST['exercises'];
    
    if ($plan_id) {
        // Update existing plan
        $sql = "UPDATE workout_plans SET name = ?, description = ?, difficulty = ?, duration_weeks = ?, exercises = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssisi", $name, $description, $difficulty, $duration_weeks, $exercises, $plan_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Workout plan updated successfully.";
            } else {
                $error = "Error updating workout plan.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Create new plan
        $sql = "INSERT INTO workout_plans (name, description, difficulty, duration_weeks, exercises) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssis", $name, $description, $difficulty, $duration_weeks, $exercises);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Workout plan created successfully.";
            } else {
                $error = "Error creating workout plan.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Handle plan deletion
if (isset($_GET['delete'])) {
    $plan_id = $_GET['delete'];
    $sql = "DELETE FROM workout_plans WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $plan_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Workout plan deleted successfully.";
        } else {
            $error = "Error deleting workout plan.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle plan assignment
if (isset($_POST['assign_plan'])) {
    $member_id = $_POST['member_id'];
    $plan_id = $_POST['plan_id'];
    $start_date = $_POST['start_date'];
    
    $sql = "INSERT INTO member_workout_plans (member_id, plan_id, start_date) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iis", $member_id, $plan_id, $start_date);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Workout plan assigned successfully.";
        } else {
            $error = "Error assigning workout plan.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Get all workout plans
$sql = "SELECT * FROM workout_plans ORDER BY name";
$workout_plans = mysqli_query($conn, $sql);

// Get active members for assignment
$sql = "SELECT m.id, u.first_name, u.last_name, u.email 
        FROM members m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.expiry_date >= CURDATE() 
        ORDER BY u.first_name";
$active_members = mysqli_query($conn, $sql);

// Get assigned plans
$sql = "SELECT mwp.*, m.first_name, m.last_name, wp.name as plan_name 
        FROM member_workout_plans mwp 
        JOIN members m ON mwp.member_id = m.id 
        JOIN workout_plans wp ON mwp.plan_id = wp.id 
        ORDER BY mwp.start_date DESC";
$assigned_plans = mysqli_query($conn, $sql);
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create/Edit Workout Plan</h5>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="plan_id" value="<?php echo isset($_GET['edit']) ? $_GET['edit'] : ''; ?>">
                    <div class="mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Difficulty Level</label>
                        <select class="form-select" name="difficulty" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (weeks)</label>
                        <input type="number" class="form-control" name="duration_weeks" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Exercises (JSON format)</label>
                        <textarea class="form-control" name="exercises" rows="5" required></textarea>
                        <small class="text-muted">Format: [{"name":"Exercise 1","sets":3,"reps":12,"rest":"60s"},...]</small>
                    </div>
                    <button type="submit" name="save_plan" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Save Plan
                    </button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Assign Plan to Member</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Member</label>
                        <select class="form-select" name="member_id" required>
                            <option value="">Select a member</option>
                            <?php mysqli_data_seek($active_members, 0); while ($member = mysqli_fetch_assoc($active_members)): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Workout Plan</label>
                        <select class="form-select" name="plan_id" required>
                            <option value="">Select a plan</option>
                            <?php mysqli_data_seek($workout_plans, 0); while ($plan = mysqli_fetch_assoc($workout_plans)): ?>
                                <option value="<?php echo $plan['id']; ?>">
                                    <?php echo htmlspecialchars($plan['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <button type="submit" name="assign_plan" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus"></i> Assign Plan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Workout Plans</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Difficulty</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php mysqli_data_seek($workout_plans, 0); while ($plan = mysqli_fetch_assoc($workout_plans)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plan['name']); ?></td>
                                <td><?php echo ucfirst($plan['difficulty']); ?></td>
                                <td><?php echo $plan['duration_weeks']; ?> weeks</td>
                                <td>
                                    <a href="?edit=<?php echo $plan['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?delete=<?php echo $plan['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this plan?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Assigned Plans</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Plan</th>
                                <th>Start Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($assignment = mysqli_fetch_assoc($assigned_plans)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['plan_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($assignment['start_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo strtotime($assignment['start_date']) <= time() ? 'success' : 'warning'; 
                                    ?>">
                                        <?php echo strtotime($assignment['start_date']) <= time() ? 'Active' : 'Upcoming'; ?>
                                    </span>
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

<?php require_once "footer.php"; ?> 