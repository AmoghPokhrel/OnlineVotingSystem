<?php
session_start();
include('../includes/db.php');

if (isset($_POST['start_voting']) || isset($_POST['end_voting'])) {
    $year = $_POST['year'];
    $faculty = $_POST['faculty'];

    if (isset($_POST['start_voting'])) {
        $status = 'active';
    } elseif (isset($_POST['end_voting'])) {
        $status = 'inactive';
    }
    
    // Check if there is an existing record for the given year and faculty
    $query = "SELECT id FROM voting_status WHERE year = ? AND faculty = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $year, $faculty);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the existing record
        $query = "UPDATE voting_status SET status = ? WHERE year = ? AND faculty = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $status, $year, $faculty);
    } else {
        // Insert a new record
        $query = "INSERT INTO voting_status (status, year, faculty) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $status, $year, $faculty);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Voting has been " . ($status === 'active' ? "started" : "ended") . " for " . htmlspecialchars($year) . "'s" . htmlspecialchars($faculty) . ".";
    } else {
        $_SESSION['message'] = "Error updating voting status: " . $conn->error;
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "No action performed.";
}

$conn->close();
header("Location: ../pages/voting.php");
exit();
?>
