<?php
require_once "header.php";
require_once "../includes/db_connect.php";

$message = '';
$error = '';

// Handle trainer deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $trainer_id = $_GET['delete'];
    $sql = "DELETE FROM trainers WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $trainer_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Trainer deleted successfully.";
        } else {
            $error = "Error deleting trainer.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle trainer addition/editing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trainer_id = $_POST['trainer_id'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    $specialization = $_POST['specialization'];
    $experience_years = $_POST['experience_years'];
    $certification = $_POST['certification'];

    if ($trainer_id) {
        // Update existing trainer
        $sql = "UPDATE trainers SET specialization = ?, experience_years = ?, certification = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sisi", $specialization, $experience_years, $certification, $trainer_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Trainer updated successfully.";
            } else {
                $error = "Error updating trainer.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Add new trainer
        $sql = "INSERT INTO trainers (user_id, specialization, experience_years, certification) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "isis", $user_id, $specialization, $experience_years, $certification);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Trainer added successfully.";
            } else {
                $error = "Error adding trainer.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get all trainers with their user details
$sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.phone 
        FROM trainers t 
        JOIN users u ON t.user_id = u.id 
        ORDER BY t.experience_years DESC";
$trainers = mysqli_query($conn, $sql);

// Get all users who are not yet trainers
$sql = "SELECT id, first_name, last_name, email 
        FROM users 
        WHERE role = 'trainer' 
        AND id NOT IN (SELECT user_id FROM trainers)";
$non_trainers = mysqli_query($conn, $sql);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Trainers</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTrainerModal">
                    <i class="bi bi-person-plus"></i> Add New Trainer
                </button>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Specialization</th>
                                <th>Experience</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($trainer = mysqli_fetch_assoc($trainers)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($trainer['email']); ?></td>
                                <td><?php echo htmlspecialchars($trainer['phone']); ?></td>
                                <td><?php echo htmlspecialchars($trainer['specialization']); ?></td>
                                <td><?php echo $trainer['experience_years']; ?> years</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editTrainerModal<?php echo $trainer['id']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?delete=<?php echo $trainer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this trainer?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Edit Trainer Modal -->
                            <div class="modal fade" id="editTrainerModal<?php echo $trainer['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Trainer</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="hidden" name="trainer_id" value="<?php echo $trainer['id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?>" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Specialization</label>
                                                    <input type="text" class="form-control" name="specialization" value="<?php echo htmlspecialchars($trainer['specialization']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Experience (Years)</label>
                                                    <input type="number" class="form-control" name="experience_years" value="<?php echo $trainer['experience_years']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Certification</label>
                                                    <textarea class="form-control" name="certification"><?php echo htmlspecialchars($trainer['certification']); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Trainer Modal -->
<div class="modal fade" id="addTrainerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Trainer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select User</label>
                        <select class="form-select" name="user_id" required>
                            <option value="">Select a user</option>
                            <?php while ($user = mysqli_fetch_assoc($non_trainers)): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Specialization</label>
                        <input type="text" class="form-control" name="specialization" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" class="form-control" name="experience_years" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Certification</label>
                        <textarea class="form-control" name="certification"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Trainer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?> 