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
    // Prepare a SQL statement to select data with userName = 'Placeholder'
    $selectSql = "SELECT dateTime, muscleData FROM data WHERE userName IN ('Placeholder', '')";
    
    // Execute the query
    $result = $mysqli->query($selectSql);

    if ($result) {
        // Fetch associative array
        while ($row = $result->fetch_assoc()) {
            $dateTime = $row["dateTime"];
            $muscleData = $row["muscleData"];
            // Output hidden input fields for dateTime
            echo "<input type='hidden' name='dateTime[]' value='$dateTime'>";
            // Output the label and input field for username
            echo "<label>$dateTime</label><input type='text' name='username[]'><br>";
        }
        // Free result set
        $result->free();
    } else {
        echo "Error executing query: " . $mysqli->error;
    }

    // Closing the database connection
    $mysqli->close();
}
?>