<?php
require_once "header.php";
require_once "../includes/db_connect.php";

$message = '';
$error = '';

// Handle plan deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $plan_id = $_GET['delete'];
    $sql = "DELETE FROM membership_plans WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $plan_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Plan deleted successfully.";
        } else {
            $error = "Error deleting plan.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle plan addition/editing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plan_id = $_POST['plan_id'] ?? null;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration_months = $_POST['duration_months'];
    $features = $_POST['features'];

    if ($plan_id) {
        // Update existing plan
        $sql = "UPDATE membership_plans SET name = ?, description = ?, price = ?, duration_months = ?, features = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssdisi", $name, $description, $price, $duration_months, $features, $plan_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Plan updated successfully.";
            } else {
                $error = "Error updating plan.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Add new plan
        $sql = "INSERT INTO membership_plans (name, description, price, duration_months, features) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssdis", $name, $description, $price, $duration_months, $features);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Plan added successfully.";
            } else {
                $error = "Error adding plan.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get all membership plans
$sql = "SELECT * FROM membership_plans ORDER BY price ASC";
$plans = mysqli_query($conn, $sql);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Membership Plans</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                    <i class="bi bi-plus-circle"></i> Add New Plan
                </button>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="row">
                    <?php while ($plan = mysqli_fetch_assoc($plans)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($plan['name']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    $<?php echo number_format($plan['price'], 2); ?> / 
                                    <?php echo $plan['duration_months']; ?> months
                                </h6>
                                <p class="card-text"><?php echo htmlspecialchars($plan['description']); ?></p>
                                <ul class="list-unstyled">
                                    <?php 
                                    $features = json_decode($plan['features'], true);
                                    foreach ($features as $feature): 
                                    ?>
                                    <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo htmlspecialchars($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="card-footer bg-transparent">
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editPlanModal<?php echo $plan['id']; ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <a href="?delete=<?php echo $plan['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this plan?')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Plan Modal -->
                    <div class="modal fade" id="editPlanModal<?php echo $plan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Plan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Plan Name</label>
                                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($plan['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" required><?php echo htmlspecialchars($plan['description']); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Price ($)</label>
                                            <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $plan['price']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Duration (Months)</label>
                                            <input type="number" class="form-control" name="duration_months" value="<?php echo $plan['duration_months']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Features (One per line)</label>
                                            <textarea class="form-control" name="features" rows="5" required><?php 
                                                $features = json_decode($plan['features'], true);
                                                echo implode("\n", $features);
                                            ?></textarea>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (Months)</label>
                        <input type="number" class="form-control" name="duration_months" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Features (One per line)</label>
                        <textarea class="form-control" name="features" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?> 