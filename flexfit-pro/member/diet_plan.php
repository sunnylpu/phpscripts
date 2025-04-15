<?php
require_once "../includes/db_connect.php";
require_once "../includes/session_check.php";

// Ensure only members can access this page
if ($_SESSION['role'] !== 'member') {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

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

// Get all available diet plans
$sql = "SELECT * FROM diet_plans ORDER BY name";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_execute($stmt);
    $available_plans = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

// Decode meal plan JSON if diet plan exists
$meal_plan = [];
if ($diet_plan) {
    $meal_plan = json_decode($diet_plan['meal_plan'], true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Plan - FlexFit Pro</title>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="workout_plan.php">Workout Plan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="diet_plan.php">Diet Plan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="progress.php">Progress</a>
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
        <?php if ($diet_plan): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Current Diet Plan</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><?php echo htmlspecialchars($diet_plan['name']); ?></h5>
                            <p><?php echo htmlspecialchars($diet_plan['description']); ?></p>
                            <p><strong>Daily Calories:</strong> <?php echo $diet_plan['calories']; ?> kcal</p>
                            <p><strong>Macronutrients:</strong></p>
                            <ul>
                                <li>Protein: <?php echo $diet_plan['protein']; ?>g</li>
                                <li>Carbohydrates: <?php echo $diet_plan['carbs']; ?>g</li>
                                <li>Fats: <?php echo $diet_plan['fats']; ?>g</li>
                            </ul>
                            <p><strong>Started:</strong> <?php echo date('M d, Y', strtotime($diet_plan['start_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo min(100, (time() - strtotime($diet_plan['start_date'])) / (7 * 24 * 60 * 60 * 4) * 100); ?>%">
                                    <?php echo round(min(100, (time() - strtotime($diet_plan['start_date'])) / (7 * 24 * 60 * 60 * 4) * 100)); ?>%
                                </div>
                            </div>
                            <p class="text-muted">Plan Progress</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Meal Plan</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($meal_plan)): ?>
                        <?php foreach ($meal_plan as $day => $meals): ?>
                            <div class="mb-4">
                                <h5 class="mb-3"><?php echo ucfirst($day); ?></h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Meal</th>
                                                <th>Food Items</th>
                                                <th>Calories</th>
                                                <th>Protein</th>
                                                <th>Carbs</th>
                                                <th>Fats</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($meals as $meal): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($meal['name']); ?></td>
                                                    <td>
                                                        <ul class="list-unstyled mb-0">
                                                            <?php foreach ($meal['items'] as $item): ?>
                                                                <li><?php echo htmlspecialchars($item); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </td>
                                                    <td><?php echo $meal['calories']; ?> kcal</td>
                                                    <td><?php echo $meal['protein']; ?>g</td>
                                                    <td><?php echo $meal['carbs']; ?>g</td>
                                                    <td><?php echo $meal['fats']; ?>g</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No meal plan details available.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Available Diet Plans</h4>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($available_plans) > 0): ?>
                        <div class="row">
                            <?php while ($plan = mysqli_fetch_assoc($available_plans)): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($plan['name']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars($plan['description']); ?></p>
                                            <p><strong>Daily Calories:</strong> <?php echo $plan['calories']; ?> kcal</p>
                                            <p><strong>Macros:</strong> P: <?php echo $plan['protein']; ?>g C: <?php echo $plan['carbs']; ?>g F: <?php echo $plan['fats']; ?>g</p>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestPlanModal" data-plan-id="<?php echo $plan['id']; ?>">
                                                Request Plan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>No diet plans available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Request Plan Modal -->
    <div class="modal fade" id="requestPlanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Diet Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to request this diet plan? An admin will review your request and assign it to you.</p>
                    <form id="requestPlanForm" action="request_plan.php" method="POST">
                        <input type="hidden" name="plan_id" id="planId">
                        <input type="hidden" name="plan_type" value="diet">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="requestPlanForm" class="btn btn-primary">Request Plan</button>
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
        // Set plan ID in modal when requesting a plan
        document.querySelectorAll('[data-bs-target="#requestPlanModal"]').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('planId').value = this.dataset.planId;
            });
        });
    </script>
</body>
</html> 