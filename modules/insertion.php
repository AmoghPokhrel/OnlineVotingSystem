<?php
session_start();
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $crn = $_POST['crn'];
    $name = $_POST['username'];
    $email = $_POST['email']; 
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    $faculty = $role === 'Admin' ? '' : (isset($_POST['faculty']) ? $_POST['faculty'] : ''); 
    $year = $role === 'Admin' ? '' : (isset($_POST['year']) ? $_POST['year'] : '');
    $address = $_POST['address'];
    $phone = $_POST['number'];
    $gender = $_POST['gender'];
    $password = $_POST['password']; 

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if the CRN already exists
    $check_sql = "SELECT crn FROM user WHERE crn = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $crn);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // CRN already exists
        echo '<script>alert("The CRN is already in use. Please use a different CRN."); window.location.href = "../pages/register.php";</script>';
    } else {
        // CRN does not exist, so insert a new record
        $sql = "INSERT INTO user (crn, name, email, faculty, year, address, phone, gender, role, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing insert statement: " . $conn->error);
        }

        $stmt->bind_param("isssssssss", $crn, $name, $email, $faculty, $year, $address, $phone, $gender, $role, $hashed_password);

        if ($stmt->execute()) {
            echo '<script>alert("Data inserted successfully"); window.location.href = "../pages/register.php";</script>';
        } else {
            echo '<script>alert("Error inserting data: ' . $stmt->error . '"); window.location.href = "../pages/register.php";</script>';
        }

        $stmt->close();
    }

    $check_stmt->close();
}

$conn->close();
?>
