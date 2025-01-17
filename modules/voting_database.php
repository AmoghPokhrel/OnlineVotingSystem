<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $crn = $conn->real_escape_string($_POST['crn']);
        $semester = $conn->real_escape_string($_POST['semester']);

        $query = "SELECT role FROM user WHERE crn = '$crn'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['role'] == 'Admin') {
                $_SESSION['message'] = "Cannot add an Admin as a candidate.";
            } else {
                $query = "SELECT * FROM candidates WHERE crn = '$crn' AND semester = '$semester'";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    $_SESSION['message'] = "Candidate already exists for the selected semester, even if inactive.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO candidates (crn, semester, status) VALUES (?, ?, 'active')");
                    $stmt->bind_param("ss", $crn, $semester);

                    if ($stmt->execute()) {
                        $_SESSION['message'] = "New candidate added successfully";
                    } else {
                        $_SESSION['message'] = "Error adding candidate: " . $conn->error;
                    }
                    $stmt->close();
                }
            }
        } else {
            $_SESSION['message'] = "CRN not found.";
        }
    }

    if (isset($_POST['deactivate'])) {
        $id = $conn->real_escape_string($_POST['id']);
        
        // Debugging output
        error_log("Attempting to deactivate candidate with ID: $id");
    
        // Check if the candidate is currently active
        $checkQuery = "SELECT status FROM candidates WHERE id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
    
        if ($checkResult->num_rows > 0) {
            $row = $checkResult->fetch_assoc();
            if ($row['status'] == 'active') {
                // Update candidate status to 'inactive'
                $updateQuery = "UPDATE candidates SET status = 'inactive' WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("i", $id);
                
                if ($updateStmt->execute()) {
                    if ($updateStmt->affected_rows > 0) {
                        $_SESSION['message'] = "Candidate deactivated successfully.";
                    } else {
                        $_SESSION['message'] = "Candidate was not found or already inactive.";
                    }
                } else {
                    $_SESSION['message'] = "Failed to deactivate candidate: " . $updateStmt->error;
                }
                $updateStmt->close();
            } else {
                $_SESSION['message'] = "Candidate is already inactive.";
            }
        } else {
            $_SESSION['message'] = "Candidate not found.";
        }
        $checkStmt->close();
    }
    
    

    if (isset($_POST['vote'])) {
        $crn = $conn->real_escape_string($_POST['crn']);
        $candidate_id = intval($_POST['candidate']);
        
        // Fetch the user's year and faculty from the session
        $year = $_SESSION['year'];
        $faculty = $_SESSION['faculty'];
        
        // Check voting status for the user's year and faculty
        $stmt = $conn->prepare("SELECT status FROM voting_status WHERE year = ? AND faculty = ?");
        $stmt->bind_param("ss", $year, $faculty);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['status'] == 'active') {
                // Proceed with voting
                $stmt = $conn->prepare("SELECT * FROM votes WHERE crn = ?");
                $stmt->bind_param("s", $crn);
                $stmt->execute();
                $voteResult = $stmt->get_result();
                
                if ($voteResult->num_rows > 0) {
                    $_SESSION['message'] = "You have already voted.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO votes (crn, candidates_id) VALUES (?, ?)");
                    $stmt->bind_param("si", $crn, $candidate_id);
                    
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Vote recorded successfully";
                    } else {
                        $_SESSION['message'] = "Error recording vote: " . $conn->error;
                    }
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = "Voting is inactive for your year's faculty.";
            }
        } else {
            $_SESSION['message'] = "Voting status not found for your year's faculty.";
        }
    }

    if (isset($_POST['change_semester'])) {
        $id = $_POST['id'];
        $new_semester = $_POST['new_semester'];
    
        $query = "UPDATE candidates SET semester = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $new_semester, $id);
    
        if ($stmt->execute()) {
            $_SESSION['message'] = "Semester updated successfully!";
        } else {
            $_SESSION['message'] = "Failed to update semester.";
        }
        $stmt->close();
    }
    

    header("Location: ../pages/voting.php");
    exit();
}

$conn->close();
?>
