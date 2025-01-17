<?php
session_start();
require '../includes/db.php';

if (isset($_SESSION['username'])) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $action_parts = explode('_', $action);
        $action_type = $action_parts[0];
        $crn = $action_parts[1];
        $semester = isset($_POST['semester']) ? $_POST['semester'] : '';

        if ($action_type === 'remove') {
            $crn = $conn->real_escape_string($crn);
            $delete_query = "DELETE FROM requests WHERE crn = '{$crn}'";
            if ($conn->query($delete_query) === TRUE) {
                echo '<script>alert("Request removed successfully."); window.location.href = "../pages/requests_display.php";</script>';
            } else {
                echo '<script>alert("Error removing request: ' . $conn->error . '"); window.location.href = "../pages/requests_display.php";</script>';
            }
        } else if ($action_type === 'add' && !empty($semester)) {
            $crn = $conn->real_escape_string($crn);
            $semester = $conn->real_escape_string($semester);

            // Check if the request has at least two vouches
            $vouch_count_query = "
                SELECT COUNT(DISTINCT v.username) AS vouch_count 
                FROM vouches v 
                JOIN requests r ON r.id = v.request_id 
                WHERE r.crn = ?";
            $stmt = $conn->prepare($vouch_count_query);
            $stmt->bind_param("s", $crn);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            // Debugging output
            error_log("Vouch Count: " . $row['vouch_count']);

            if ($row['vouch_count'] >= 2) {
                // Insert the candidate into the candidates table
                $insert_query = "INSERT INTO candidates (crn, semester) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("ss", $crn, $semester);
                if ($stmt->execute()) {
                    // Delete the request from the requests table
                    $delete_query = "DELETE FROM requests WHERE crn = ?";
                    $stmt = $conn->prepare($delete_query);
                    $stmt->bind_param("s", $crn);
                    if ($stmt->execute()) {
                        echo '<script>alert("Candidate added successfully."); window.location.href = "../pages/requests_display.php";</script>';
                    } else {
                        echo '<script>alert("Candidate added, but error removing request: ' . $conn->error . '"); window.location.href = "../pages/requests_display.php";</script>';
                    }
                } else {
                    echo '<script>alert("Error adding candidate: ' . $conn->error . '"); window.location.href = "../pages/requests_display.php";</script>';
                }
            } else {
                echo '<script>alert("The request must have at least two vouches before it can be added as a candidate."); window.location.href = "../pages/requests_display.php";</script>';
            }
        } else {
            echo '<script>alert("Semester is required for adding."); window.location.href = "../pages/requests_display.php";</script>';
        }
    } else {
        echo '<script>alert("Invalid request."); window.location.href = "../pages/requests_display.php";</script>';
    }
} else {
    echo '<script>alert("Please log in to access this page."); window.location.href = "../index.php";</script>';
}

$conn->close();
?>
