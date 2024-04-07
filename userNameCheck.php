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

// Check if the username is provided in the POST request
if(isset($_POST['username'])) {
    $username = $_POST['username'];
    
    // Prepare a SQL statement to check if the username exists in the database
    $sql = "SELECT userName FROM data WHERE userName = ?";
    
    // Using prepared statement to prevent SQL injection
    if ($stmt = $mysqli->prepare($sql)) {
        // Binding parameters
        $stmt->bind_param("s", $username);
        
        // Executing the statement
        if ($stmt->execute()) {
            // Fetching the result
            $result = $stmt->get_result();
            
            // Checking if any rows are returned (username exists)
            if ($result->num_rows > 0) {
                // Redirect to user.php
                if ($username == "admin") {
                    header("Location: admin.html");
                    exit;
                }
                else {
                    header("Location: user.php?username=" . $username);
                    exit;
                }
            } else {
                echo "Username not found. Please try again."; // Username doesn't exist
                
                // Redirect back to the login page
                header("Location: index.html");
                exit;
            }
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        
        // Closing the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $mysqli->error;
    }
} else {
    echo "Username not provided";
}

// Closing the database connection
$mysqli->close();
?>
