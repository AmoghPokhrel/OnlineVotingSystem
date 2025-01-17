<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crn = $_POST['crn'];
    $candidate_id = intval($_POST['candidate']);

    $sql = "SELECT permission_status FROM user WHERE crn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $crn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['permission_status'] === 'Grant') {
            $checkVoteQuery = "SELECT * FROM votes WHERE crn = ?";
            $checkStmt = $conn->prepare($checkVoteQuery);
            $checkStmt->bind_param("i", $crn);
            $checkStmt->execute();
            $voteResult = $checkStmt->get_result();

            if ($voteResult->num_rows > 0) {
                $_SESSION['message'] = "You have already voted.";
            } else {
                $voteQuery = "INSERT INTO votes (crn, candidate_id) VALUES (?, ?)";
                $voteStmt = $conn->prepare($voteQuery);
                $voteStmt->bind_param("ii", $crn, $candidate_id);

                if ($voteStmt->execute()) {
                    $_SESSION['message'] = "Vote recorded successfully.";
                } else {
                    $_SESSION['message'] = "Error recording vote: " . $conn->error;
                }
                $voteStmt->close();
            }
            $checkStmt->close();
        } else {
            $_SESSION['message'] = "You do not have permission to vote.";
        }
    } else {
        $_SESSION['message'] = "User not found.";
    }

    $stmt->close();
    $conn->close();

    header('Location: pages/voting.php');
    exit();
} else {
    echo "Invalid request method.";
}
?>
