<?php
require_once "../includes/db_connect.php";
require_once "../includes/session_check.php";

// Ensure only members can access this page
if ($_SESSION['role'] !== 'member') {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zip_code = $_POST['zip_code'];
        $emergency_contact_name = $_POST['emergency_contact_name'];
        $emergency_contact_phone = $_POST['emergency_contact_phone'];
        $emergency_contact_relation = $_POST['emergency_contact_relation'];
        $medical_conditions = $_POST['medical_conditions'];
        $allergies = $_POST['allergies'];
        $fitness_goals = $_POST['fitness_goals'];

        // Update user information
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                city = ?, 
                state = ?, 
                zip_code = ? 
                WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssi", 
                $first_name, $last_name, $email, $phone, 
                $address, $city, $state, $zip_code, $member_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Update member information
        $sql = "UPDATE members SET 
                emergency_contact_name = ?, 
                emergency_contact_phone = ?, 
                emergency_contact_relation = ?, 
                medical_conditions = ?, 
                allergies = ?, 
                fitness_goals = ? 
                WHERE user_id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssi", 
                $emergency_contact_name, $emergency_contact_phone, 
                $emergency_contact_relation, $medical_conditions, 
                $allergies, $fitness_goals, $member_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    }
}

// Get member information
$sql = "SELECT u.*, m.*, mp.name as plan_name, mp.price, mp.duration, mp.start_date, mp.end_date 
        FROM users u 
        JOIN members m ON u.id = m.user_id 
        LEFT JOIN member_plans mp ON m.id = mp.member_id 
        WHERE u.id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $member = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - FlexFit Pro</title>
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
                        <a class="nav-link" href="diet_plan.php">Diet Plan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="progress.php">Progress</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php">Attendance</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Profile Picture</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="../assets/images/default-avatar.png" alt="Profile Picture" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <button class="btn btn-primary btn-sm">Change Picture</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Membership Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Plan:</strong> <?php echo htmlspecialchars($member['plan_name'] ?? 'No active plan'); ?></p>
                        <p><strong>Price:</strong> $<?php echo number_format($member['price'] ?? 0, 2); ?>/month</p>
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($member['duration'] ?? 'N/A'); ?></p>
                        <p><strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($member['start_date'] ?? 'now')); ?></p>
                        <p><strong>End Date:</strong> <?php echo date('M d, Y', strtotime($member['end_date'] ?? 'now')); ?></p>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: <?php 
                                echo isset($member['start_date']) && isset($member['end_date']) 
                                    ? round((time() - strtotime($member['start_date'])) / (strtotime($member['end_date']) - strtotime($member['start_date'])) * 100) 
                                    : 0; 
                            ?>%"></div>
                        </div>
                        <button class="btn btn-primary w-100">Upgrade Plan</button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <h6 class="mb-3">Personal Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>" required>
                                </div>
                            </div>

                            <h6 class="mb-3">Contact Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($member['address']); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($member['city']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($member['state']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" name="zip_code" value="<?php echo htmlspecialchars($member['zip_code']); ?>">
                                </div>
                            </div>

                            <h6 class="mb-3">Emergency Contact</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="emergency_contact_name" value="<?php echo htmlspecialchars($member['emergency_contact_name']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="emergency_contact_phone" value="<?php echo htmlspecialchars($member['emergency_contact_phone']); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Relationship</label>
                                    <input type="text" class="form-control" name="emergency_contact_relation" value="<?php echo htmlspecialchars($member['emergency_contact_relation']); ?>">
                                </div>
                            </div>

                            <h6 class="mb-3">Health Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Medical Conditions</label>
                                    <textarea class="form-control" name="medical_conditions" rows="2"><?php echo htmlspecialchars($member['medical_conditions']); ?></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Allergies</label>
                                    <textarea class="form-control" name="allergies" rows="2"><?php echo htmlspecialchars($member['allergies']); ?></textarea>
                                </div>
                            </div>

                            <h6 class="mb-3">Fitness Goals</h6>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <textarea class="form-control" name="fitness_goals" rows="3"><?php echo htmlspecialchars($member['fitness_goals']); ?></textarea>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
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
</body>
</html> 