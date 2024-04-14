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
    function insertDataIntoDatabase($mysqli, $username, $offloadedData, $offloadedDateTime) {
        // Prepare a SQL statement to insert data
        $insertSql = "INSERT INTO data (userName, dateTime, muscleData) VALUES (?, ?, ?)";

        // Using prepared statement to prevent SQL injection
        if ($insertStmt = $mysqli->prepare($insertSql)) {

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
    if (isset($_POST['offloadedDataArray']) && isset($_POST['offloadedDateTimeArray']) && isset($_POST['currentUser'])) {
        // Decode the JSON arrays received from JavaScript
        $offloadedDataArray = json_decode($_POST['offloadedDataArray'], true);
        $offloadedDateTimeArray = json_decode($_POST['offloadedDateTimeArray'], true);
        $currentUser = $_POST['currentUser'];

        // echo $offloadedDataArray;
        // echo $offloadedDateTimeArray;
        // echo $currentUser;
        // Insert data into the database
        insertDataIntoDatabase($mysqli, $currentUser, $offloadedDataArray, $offloadedDateTimeArray);

        // Redirect back to the user page with the username as a parameter
        header("Location: user.php?username=" . urlencode($currentUser));
        exit; // Make sure to exit after redirecting to prevent further script execution
    } else {
        echo "Data not received from JavaScript.";
    }

    // Closing the database connection
    $mysqli->close();
}
?>
