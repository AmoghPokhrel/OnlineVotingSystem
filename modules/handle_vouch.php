<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id']);
    $vouching_user = $_SESSION['username']; // Assuming the username is stored in the session

    // Check if the vouching user is the same as the requestor
    $requestorQuery = "SELECT name AS vname FROM requests WHERE id = ?";
    $stmt = $conn->prepare($requestorQuery);
    if (!$stmt) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        die('Get result failed: ' . htmlspecialchars($stmt->error));
    }
    $row = $result->fetch_assoc();
    $requestor = $row['vname'];

    if ($vouching_user === $requestor) {
        echo '<script>alert("You cannot vouch for your own request."); window.location.href = "../pages/support.php";</script>';
    } else {
        // Check if the user has already vouched for this request
        $checkVouchQuery = "SELECT COUNT(*) AS vouch_count FROM vouches WHERE request_id = ? AND username = ?";
        $stmt = $conn->prepare($checkVouchQuery);
        if (!$stmt) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("is", $request_id, $vouching_user);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            die('Get result failed: ' . htmlspecialchars($stmt->error));
        }
        $row = $result->fetch_assoc();

        if ($row['vouch_count'] > 0) {
            echo '<script>alert("You have already vouched for this request."); window.location.href = "../pages/support.php";</script>';
        } else {
            // Insert the vouch into the vouch tracking table
            $vouchQuery = "INSERT INTO vouches (request_id, username) VALUES (?, ?)";
            $stmt = $conn->prepare($vouchQuery);
            if (!$stmt) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("is", $request_id, $vouching_user);
            $stmt->execute();

            // Count the number of unique vouches for this request
            $countQuery = "SELECT COUNT(DISTINCT username) AS vouch_count FROM vouches WHERE request_id = ?";
            $stmt = $conn->prepare($countQuery);
            if (!$stmt) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                die('Get result failed: ' . htmlspecialchars($stmt->error));
            }
            $row = $result->fetch_assoc();

            if ($row['vouch_count'] >= 2) {
                // Set the request as invisible instead of deleting it
                $updateQuery = "UPDATE requests SET is_visible = 0 WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                if (!$stmt) {
                    die('Prepare failed: ' . htmlspecialchars($conn->error));
                }
                $stmt->bind_param("i", $request_id);
                if (!$stmt->execute()) {
                    die('Execute failed: ' . htmlspecialchars($stmt->error));
                }
                echo 'Debug: Request with ID ' . $request_id . ' set to invisible.';
            }

            echo '<script>alert("Vouch recorded successfully."); window.location.href = "../pages/support.php";</script>';
        }
    }
} else {
    echo '<script>alert("Invalid request."); window.location.href = "../pages/support.php";</script>';
}

$conn->close();
?>
