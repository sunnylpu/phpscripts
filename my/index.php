<?php
// Start the session
session_start();

// Initialize database connection (you'll need to set this up)
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

// Handle form submission for adding new events
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["eventAction"]) && $_POST["eventAction"] == "add") {
    $title = $conn->real_escape_string($_POST["eventTitle"]);
    $start = $conn->real_escape_string($_POST["eventStart"]);
    $end = $conn->real_escape_string($_POST["eventEnd"]);
    $color = $conn->real_escape_string($_POST["eventColor"]);
    $description = $conn->real_escape_string($_POST["eventDescription"]);
    
    $sql = "INSERT INTO events (title, start_date, end_date, color, description) 
            VALUES ('$title', '$start', '$end', '$color', '$description')";
    
    if ($conn->query($sql) !== TRUE) {
        $error_message = "Error: " . $sql . "<br>" . $conn->error;
    } else {
        $success_message = "Event added successfully!";
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle event deletion
if (isset($_GET["delete"])) {
    $event_id = intval($_GET["delete"]);
    $sql = "DELETE FROM events WHERE id = $event_id";
    
    if ($conn->query($sql) !== TRUE) {
        $error_message = "Error deleting event: " . $conn->error;
    } else {
        $success_message = "Event deleted successfully!";
        // Redirect to prevent URL parameters from persisting
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all events
$events = [];
$result = $conn->query("SELECT * FROM events ORDER BY start_date");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Convert events to JSON for FullCalendar
$calendar_events = [];
foreach ($events as $event) {
    $calendar_events[] = [
        'id' => $event['id'],
        'title' => $event['title'],
        'start' => $event['start_date'],
        'end' => $event['end_date'],
        'backgroundColor' => $event['color'],
        'borderColor' => $event['color'],
        'description' => $event['description']
    ];
}
$calendar_events_json = json_encode($calendar_events);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Calendar</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com" defer></script>
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-10">
            <h1 class="text-4xl font-bold text-center text-indigo-700">Event Calendar</h1>
            <p class="text-center text-gray-600 mt-2">Plan your schedule with our interactive calendar</p>
        </header>

        <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Calendar Section -->
            <div class="lg:col-span-3 bg-white rounded-lg shadow-md p-6">
                <div id="calendar" class="fc fc-media-screen fc-direction-ltr"></div>
            </div>

            <!-- Event Form Section -->
            <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-indigo-700 mb-4">Add New Event</h2>
                <form id="eventForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="space-y-4">
                    <input type="hidden" name="eventAction" value="add">
                    
                    <div>
                        <label for="eventTitle" class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                        <input type="text" id="eventTitle" name="eventTitle" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="eventStart" class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                        <input type="datetime-local" id="eventStart" name="eventStart" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="eventEnd" class="block text-sm font-medium text-gray-700 mb-1">End Date & Time</label>
                        <input type="datetime-local" id="eventEnd" name="eventEnd" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="eventColor" class="block text-sm font-medium text-gray-700 mb-1">Event Color</label>
                        <select id="eventColor" name="eventColor" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="#4F46E5">Indigo</option>
                            <option value="#10B981">Green</option>
                            <option value="#EF4444">Red</option>
                            <option value="#F59E0B">Yellow</option>
                            <option value="#3B82F6">Blue</option>
                            <option value="#8B5CF6">Purple</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="eventDescription" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="eventDescription" name="eventDescription" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300">
                            Add Event
                        </button>
                        <button type="reset" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-300">
                            Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Event List Section -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-indigo-700 mb-4">Your Events</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No events added yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="event-color-indicator" style="background-color: <?php echo $event['color']; ?>"></span>
                                            <span><?php echo htmlspecialchars($event['title']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('D, M j, Y g:i A', strtotime($event['start_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('D, M j, Y g:i A', strtotime($event['end_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($event['description']) ?: '-'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                                        <a href="?delete=<?php echo $event['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    
    <!-- Calendar Initialization -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the calendar with PHP-generated event data
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            events: <?php echo $calendar_events_json; ?>,
            // When a date is clicked, open the form with that date pre-filled
            dateClick: function(info) {
                document.getElementById('eventStart').value = formatDateForInput(info.dateStr);
                const endDate = new Date(info.dateStr);
                endDate.setHours(endDate.getHours() + 1);
                document.getElementById('eventEnd').value = formatDateForInput(formatDate(endDate));
            },
            // When an existing event is clicked, navigate to edit page
            eventClick: function(info) {
                window.location.href = 'edit_event.php?id=' + info.event.id;
            },
            // Event drag and drop handled by AJAX (to be implemented)
            eventDrop: function(info) {
                updateEventDates(info.event.id, info.event.start, info.event.end);
            },
            // Event resize handled by AJAX (to be implemented)
            eventResize: function(info) {
                updateEventDates(info.event.id, info.event.start, info.event.end);
            }
        });
        
        calendar.render();
        
        // Helper function to format date for input fields
        function formatDateForInput(dateStr) {
            const date = new Date(dateStr);
            
            // Format: YYYY-MM-DDTHH:MM
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }
        
        // Helper function to format date
        function formatDate(date) {
            return date.toISOString();
        }
        
        // Function to update event dates via AJAX
        function updateEventDates(eventId, start, end) {
            // Use fetch API to send AJAX request to update_event_dates.php
            fetch('update_event_dates.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + eventId + 
                      '&start=' + start.toISOString() + 
                      '&end=' + (end ? end.toISOString() : '')
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to update event: ' + data.message);
                    // Revert the calendar event
                    calendar.refetchEvents();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the event');
                // Revert the calendar event
                calendar.refetchEvents();
            });
        }
    });
    </script>
</body>
</html>