<?php

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "usbw";
$database = "test";

// Establishing connection to the database
$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get usernames from the form submission
        $usernames = $_POST['username'];

        // Update data in the database
        $updateSql = "UPDATE data SET userName = ? WHERE dateTime = ?";
        $updateStmt = $mysqli->prepare($updateSql);

        foreach ($usernames as $index => $username) {
            $updateStmt->bind_param("s", $username);
            $updateStmt->execute();
        }

        $updateStmt->close();
        echo "Data updated successfully.";
    }

    // Closing the database connection
    $mysqli->close();
}
?>
