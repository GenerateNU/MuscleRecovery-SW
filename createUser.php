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

// Function to create ADMIN user with placeholder values
function createAdminUser($mysqli) {
    $adminUsername = "ADMIN";
    $adminDateTime = "2003-12-29 04:20:00.696969";
    $adminMuscleData = "0";

    // Prepare a SQL statement to insert ADMIN user if not exists
    $insertAdminSql = "INSERT INTO data (userName, dateTime, muscleData) VALUES (?, ?, ?)";

    // Using prepared statement to prevent SQL injection
    if ($insertAdminStmt = $mysqli->prepare($insertAdminSql)) {
        // Binding parameters
        $insertAdminStmt->bind_param("sss", $adminUsername, $adminDateTime, $adminMuscleData);

        // Executing the statement
        if ($insertAdminStmt->execute()) {
            echo "ADMIN user created successfully!";
        } else {
            echo "Error creating ADMIN user: " . $insertAdminStmt->error;
        }

        // Closing the statement
        $insertAdminStmt->close();
    } else {
        echo "Error preparing insert statement for ADMIN user: " . $mysqli->error;
    }
}

// Check if the ADMIN user already exists
$checkAdminSql = "SELECT userName FROM data WHERE userName = 'ADMIN'";
$checkAdminResult = $mysqli->query($checkAdminSql);

if ($checkAdminResult->num_rows == 0) {
    // Create ADMIN user if not exists
    createAdminUser($mysqli);
}

// Check if the new username is provided in the POST request
if(isset($_POST['newUsername'])) {
    $newUsername = $_POST['newUsername'];
    
    // Prepare a SQL statement to check if the username already exists in the database
    $checkSql = "SELECT userName FROM data WHERE userName = ?";
    
    // Using prepared statement to prevent SQL injection
    if ($checkStmt = $mysqli->prepare($checkSql)) {
        // Binding parameters
        $checkStmt->bind_param("s", $newUsername);
        
        // Executing the statement
        if ($checkStmt->execute()) {
            // Fetching the result
            $checkResult = $checkStmt->get_result();
            
            // Checking if any rows are returned (username already exists)
            if ($checkResult->num_rows > 0) {
                echo "Username already exists. Please choose a different username.";
            } 
            else {
                // Prepare a SQL statement to insert a new user with empty placeholder values for dateTime and muscleData
                $insertSql = "INSERT INTO data (userName, dateTime, muscleData) VALUES (?, ?, ?)";
                
                // Using prepared statement to prevent SQL injection
                if ($insertStmt = $mysqli->prepare($insertSql)) {

                    $date = '24-4-11 2:27:30';
                    $data = '0,0,0,0,0,0,0,0,0,0';

                    // Binding parameters
                    $insertStmt->bind_param("sss", $newUsername, $date, $data);
                    
                    // Executing the statement
                    if ($insertStmt->execute()) {
                        echo "New user created successfully!";
                        header("Location: user.php?username=" . $newUsername);
                        exit;
                    } else {
                        echo "Error creating new user: " . $insertStmt->error;
                        exit;
                    }
                    
                    // Closing the statement
                    $insertStmt->close();
                } else {
                    echo "Error preparing insert statement: " . $mysqli->error;
                }
                }
        
        } else {
            echo "Error executing check query: " . $checkStmt->error;
        }
        
        // Closing the statement
        $checkStmt->close();
    } else {
        echo "Error preparing check statement: " . $mysqli->error;
    }
} else {
    echo "New username not provided";
}

// Closing the database connection
$mysqli->close();
?>