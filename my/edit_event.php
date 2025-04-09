<?php
// Start the session
session_start();

// Initialize database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "calendar_events";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$event_id = intval($_GET['id']);

// Handle form submission for updating event
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["eventAction"]) && $_POST["eventAction"] == "update") {
    $title = $conn->real_escape_string($_POST["eventTitle"]);
    $start = $conn->real_escape_string($_POST["eventStart"]);
    $end = $conn->real_escape_string($_POST["eventEnd"]);
    $color = $conn->real_escape_string($_POST["eventColor"]);
    $description = $conn->real_escape_string($_POST["eventDescription"]);
    
    $sql = "UPDATE events SET 
            title = '$title', 
            start_date = '$start', 
            end_date = '$end', 
            color = '$color', 
            description = '$description' 
            WHERE id = $event_id";
    
    if ($conn->query($sql) !== TRUE) {
        $error_message = "Error: " . $sql . "<br>" . $conn->error;
    } else {
        // Redirect back to index page
        header("Location: index.php?updated=true");
        exit();
    }
}

// Fetch event data
$result = $conn->query("SELECT * FROM events WHERE id = $event_id");
if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$event = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Event Calendar</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com" defer></script>
    
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-10">
            <h1 class="text-4xl font-bold text-center text-indigo-700">Edit Event</h1>
            <p class="text-center text-gray-600 mt-2">
                <a href="index.php" class="text-indigo-600 hover:text-indigo-800">← Back to Calendar</a>
            </p>
        </header>

        <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $event_id; ?>" class="space-y-4">
                <input type="hidden" name="eventAction" value="update">
                
                <div>
                    <label for="eventTitle" class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                    <input type="text" id="eventTitle" name="eventTitle" value="<?php echo htmlspecialchars($event['title']); ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label for="eventStart" class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                    <input type="datetime-local" id="eventStart" name="eventStart" 
                           value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_date'])); ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label for="eventEnd" class="block text-sm font-medium text-gray-700 mb-1">End Date & Time</label>
                    <input type="datetime-local" id="eventEnd" name="eventEnd" 
                           value="<?php echo date('Y-m-d\TH:i', strtotime($event['end_date'])); ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label for="eventColor" class="block text-sm font-medium text-gray-700 mb-1">Event Color</label>
                    <select id="eventColor" name="eventColor" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="#4F46E5" <?php echo ($event['color'] == '#4F46E5') ? 'selected' : ''; ?>>Indigo</option>
                        <option value="#10B981" <?php echo ($event['color'] == '#10B981') ? 'selected' : ''; ?>>Green</option>
                        <option value="#EF4444" <?php echo ($event['color'] == '#EF4444') ? 'selected' : ''; ?>>Red</option>
                        <option value="#F59E0B" <?php echo ($event['color'] == '#F59E0B') ? 'selected' : ''; ?>>Yellow</option>
                        <option value="#3B82F6" <?php echo ($event['color'] == '#3B82F6') ? 'selected' : ''; ?>>Blue</option>
                        <option value="#8B5CF6" <?php echo ($event['color'] == '#8B5CF6') ? 'selected' : ''; ?>>Purple</option>
                    </select>
                </div>
                
                <div>
                    <label for="eventDescription" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="eventDescription" name="eventDescription" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300">
                        Update Event
                    </button>
                    <a href="index.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>