<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['permission_status']) && isset($_POST['crn'])) {
        $permission_status = $_POST['permission_status'];
        $crn = intval($_POST['crn']);

        // Validate permission status
        if ($permission_status === 'Grant' || $permission_status === 'Revoke') {
            // Prepare and execute the update query
            $stmt = $conn->prepare("UPDATE user SET permission_status = ? WHERE crn = ?");
            $stmt->bind_param('si', $permission_status, $crn);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Permission status updated successfully.";
            } else {
                $_SESSION['message'] = "Error updating permission status: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Invalid permission status.";
        }
    } else {
        $_SESSION['message'] = "Required data not provided.";
    }

    $conn->close();
    header('Location: ../pages/voters.php');
    exit();
} else {
    echo "Invalid request method.";
}
?>
