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
}

// Function to insert data into the database
function insertDataIntoDatabase($mysqli, $offloadedData, $offloadedDateTime) {

    // Prepare a SQL statement to insert data
    $insertSql = "INSERT INTO data (userName, dateTime, muscleData) VALUES (?, ?, ?)";

    // Using prepared statement to prevent SQL injection
    if ($insertStmt = $mysqli->prepare($insertSql)) {
        // Placeholder username
        $username = "Placeholder";

        // Get data from offloadedData and offloadedDateTime arrays
        $muscleData = $offloadedData[$i];
        $dateTime = $offloadedDateTime[$i];

        // Binding parameters
        $insertStmt->bind_param("sss", $username, $dateTime, $muscleData);

        // Executing the statement
        if ($insertStmt->execute()) {
            echo "Data inserted successfully.";
        } else {
            echo "Error inserting data: " . $insertStmt->error;
        }

        // Closing the statement
        $insertStmt->close();
    } else {
        echo "Error preparing insert statement: " . $mysqli->error;
    }
}

// Check if the offloadedData and offloadedDateTime are received from JavaScript
if (isset($_POST['offloadedData']) && isset($_POST['offloadedDateTime'])) {
    // Decode the JSON data received from JavaScript
    $offloadedData = json_decode($_POST['offloadedData'], true);
    $offloadedDateTime = json_decode($_POST['offloadedDateTime'], true);

    // Insert data into the database
    insertDataIntoDatabase($mysqli, $offloadedData, $offloadedDateTime);
} else {
    echo "Data not received from JavaScript.";
}

// Closing the database connection
$mysqli->close();
?>
