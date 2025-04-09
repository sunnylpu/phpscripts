<?php
$servername = "localhost"; // Change if needed
$username = "root"; // Default for XAMPP
$password = ""; // Default for XAMPP
$dbname = "StudentDB";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the Database
$conn->select_db($dbname);

// Create Table
$sql = "CREATE TABLE IF NOT EXISTS Students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    age INT CHECK (age BETWEEN 18 AND 30),
    gender ENUM('Male', 'Female', 'Other'),
    course VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'Students' created successfully or already exists.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Insert 40 Students (Only if table is empty)
$sql_check = "SELECT COUNT(*) AS count FROM Students";
$result = $conn->query($sql_check);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    $sql_insert = "INSERT INTO Students (name, age, gender, course, email) VALUES
    ('Aarav Sharma', 20, 'Male', 'Computer Science', 'aarav.sharma@example.com'),
    ('Ananya Singh', 19, 'Female', 'Electronics', 'ananya.singh@example.com'),
    ('Rohan Verma', 21, 'Male', 'Mechanical', 'rohan.verma@example.com'),
    ('Priya Mehta', 22, 'Female', 'Civil Engineering', 'priya.mehta@example.com'),
    ('Karan Malhotra', 23, 'Male', 'Information Technology', 'karan.malhotra@example.com'),
    ('Simran Kaur', 20, 'Female', 'Biotechnology', 'simran.kaur@example.com'),
    ('Rahul Patel', 24, 'Male', 'Computer Science', 'rahul.patel@example.com'),
    ('Pooja Yadav', 19, 'Female', 'Electronics', 'pooja.yadav@example.com'),
    ('Amit Das', 22, 'Male', 'Electrical Engineering', 'amit.das@example.com'),
    ('Sneha Roy', 21, 'Female', 'Mechanical', 'sneha.roy@example.com'),
    ('Vikram Joshi', 25, 'Male', 'Civil Engineering', 'vikram.joshi@example.com'),
    ('Neha Tiwari', 20, 'Female', 'Information Technology', 'neha.tiwari@example.com'),
    ('Manoj Kumar', 23, 'Male', 'Biotechnology', 'manoj.kumar@example.com'),
    ('Sanya Arora', 19, 'Female', 'Computer Science', 'sanya.arora@example.com'),
    ('Arjun Kapoor', 21, 'Male', 'Electronics', 'arjun.kapoor@example.com'),
    ('Meera Sen', 22, 'Female', 'Mechanical', 'meera.sen@example.com'),
    ('Ravi Gupta', 24, 'Male', 'Civil Engineering', 'ravi.gupta@example.com'),
    ('Alisha Khan', 20, 'Female', 'Information Technology', 'alisha.khan@example.com'),
    ('Sachin Bansal', 23, 'Male', 'Biotechnology', 'sachin.bansal@example.com'),
    ('Komal Desai', 19, 'Female', 'Computer Science', 'komal.desai@example.com')";

    if ($conn->query($sql_insert) === TRUE) {
        echo "Sample student records inserted successfully.<br>";
    } else {
        echo "Error inserting students: " . $conn->error . "<br>";
    }
} else {
    echo "Students already exist in the database.<br>";
}

// Fetch Students from the Database
$sql_fetch = "SELECT * FROM Students";
$result = $conn->query($sql_fetch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Database</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        table { width: 80%; margin: auto; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; text-align: center; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Student Database</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Course</th>
            <th>Email</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['student_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['age']}</td>
                    <td>{$row['gender']}</td>
                    <td>{$row['course']}</td>
                    <td>{$row['email']}</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No students found.</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php
$conn->close();
?>