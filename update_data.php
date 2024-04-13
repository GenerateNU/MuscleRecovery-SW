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
        // Get usernames and dateTimes from the form submission
        $usernames = $_POST['username'];
        $dateTimes = $_POST['dateTime'];

        // Update data in the database
        $updateSql = "UPDATE data SET userName = ? WHERE dateTime = ?";
        $updateStmt = $mysqli->prepare($updateSql);

        // Bind parameters and execute the update statement for each entry
        foreach ($usernames as $index => $username) {
            $dateTime = $dateTimes[$index]; // Get the corresponding dateTime
            // echo $username;
            // echo $dateTime;
            
            // Bind parameters
            $updateStmt->bind_param("ss", $username, $dateTime);
            // Execute the update statement
            $updateStmt->execute();
        }

        // Close the prepared statement
        $updateStmt->close();

        // Output a success message
        echo "Data updated successfully.";
    }

    // Closing the database connection
    $mysqli->close();
}
?>