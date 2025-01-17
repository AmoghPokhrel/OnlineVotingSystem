<?php
session_start();
include('../includes/db.php');

if (isset($_SESSION['username']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_SESSION['username'];
    $user_query = "SELECT role FROM user WHERE name = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_role = $user_data['role'];
        
        if ($user_role != 'Student') {
            // Sanitize and prepare user input
            $crn = intval($_POST['crn']);
            $name = $_POST['name'];
            $email = $_POST['email'];
            $role = $_POST['role'];
            $faculty = $_POST['faculty'];
            $year = $_POST['year'];
            $address = $_POST['address'];
            $phone = $_POST['phone'];
            $gender = $_POST['gender'];

            // Update user details
            $update_query = "UPDATE user SET name = ?, email = ?, role = ?, faculty = ?, year = ?, address = ?, phone = ?, gender = ? WHERE crn = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssssssi", $name, $email, $role, $faculty, $year, $address, $phone, $gender, $crn);

            if ($stmt->execute()) {
                echo '<script>alert("User details updated successfully."); window.location.href = "../pages/voters.php";</script>';
            } else {
                echo '<script>alert("Error updating record: ' . $stmt->error . '"); window.location.href = "../pages/voters.php";</script>';
            }
            $stmt->close();
        } else {
            echo '<script>alert("Students are not allowed to update details."); window.location.href = "../pages/voters.php";</script>';
        }
    } else {
        echo '<script>alert("User data not found."); window.location.href = "../pages/voters.php";</script>';
    }
    $conn->close();
} else {
    echo '<script>alert("Invalid request."); window.location.href = "index.php";</script>';
}
?>
