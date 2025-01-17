<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('../includes/db.php');
    
    $crn = $_POST['crn'];
    $name = $_POST['name'];
    $faculty = $_POST['faculty'];
    $year = $_POST['year'];

    // Check if the candidate already exists
    $check_query = "SELECT * FROM candidates WHERE crn = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $crn);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo '<script>alert("You are already a candidate."); window.location.href = "../pages/voters.php";</script>';
    } else {
        // Insert request into the database
        $insert_query = "INSERT INTO requests (crn, name, faculty, year) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $crn, $name, $faculty, $year);

        if ($stmt->execute()) {
            $_SESSION['request_id'] = $stmt->insert_id; // Store the request ID in the session
            echo '<script>alert("Request submitted successfully."); window.location.href = "../pages/voters.php";</script>';
        } else {
            echo '<script>alert("Error submitting request: ' . $stmt->error . '"); window.history.back();</script>';
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo '<script>alert("Invalid request."); window.location.href = "index.php";</script>';
}
?>
