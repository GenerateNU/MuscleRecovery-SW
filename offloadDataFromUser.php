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
    // Function to insert data into the database
    function insertDataIntoDatabase($mysqli, $offloadedData, $offloadedDateTime) {
        // Prepare a SQL statement to insert data
        $insertSql = "INSERT INTO data (userName, dateTime, muscleData) VALUES (?, ?, ?)";

        // Using prepared statement to prevent SQL injection
        if ($insertStmt = $mysqli->prepare($insertSql)) {
            // Get data from offloadedData and offloadedDateTime arrays
            $username = 'Placeholder';

            // Binding parameters
            $insertStmt->bind_param("sss", $username, $offloadedDateTime, $offloadedData);

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

    // Check if the offloadedDataArray and offloadedDateTimeArray are received from JavaScript
    if (isset($_POST['offloadedDataArray']) && isset($_POST['offloadedDateTimeArray'])) {
        // Decode the JSON arrays received from JavaScript
        $offloadedDataArray = json_decode($_POST['offloadedDataArray'], true);
        $offloadedDateTimeArray = json_decode($_POST['offloadedDateTimeArray'], true);

        // Loop through each array and insert data into the database
        for ($i = 0; $i < count($offloadedDataArray); $i++) {
            // Decode each JSON string to restore the original data
            $offloadedData = json_decode($offloadedDataArray[$i]);
            $offloadedDateTime = json_decode($offloadedDateTimeArray[$i]);

            // echo $offloadedData;
            // echo $offloadedDateTime;
            insertDataIntoDatabase($mysqli, $offloadedData, $offloadedDateTime);
        }
    } else {
        echo "Data not received from JavaScript.";
    }

    // Closing the database connection
    $mysqli->close();

    // Redirect to admin page
    header("Location: user.php");
    exit; // Make sure to exit after redirecting to prevent further script execution
}
?>
