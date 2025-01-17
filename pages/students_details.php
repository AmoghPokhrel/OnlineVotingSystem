<?php
session_start();
include('../includes/db.php');

if (isset($_SESSION['username']) && isset($_GET['crn'])) {
    $crn = $_GET['crn'];

    $sql = "SELECT * FROM user WHERE crn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $crn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "No details found";
        exit;
    }
    $stmt->close();
    $conn->close();
} else {
    echo '<script>alert("You need to log in or provide a valid CRN"); window.location.href = "../index.php";</script>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/main.css">
</head>
<body>
    <div class="sidebar">
        <?php include('../includes/header.php'); ?>
    </div>
    <div class="content">
        <h1>User Details</h1>
        <form action="../modules/update_student.php" method="POST">
            <input type="hidden" name="crn" value="<?php echo htmlspecialchars($user['crn']); ?>">
            
            <label for="name"><strong>Name:</strong></label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br><br>

            <label for="email"><strong>Email:</strong></label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

            <label for="role"><strong>Role:</strong></label>
            <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" required><br><br>

            <label for="faculty"><strong>Faculty:</strong></label>
            <input type="text" id="faculty" name="faculty" value="<?php echo htmlspecialchars($user['Faculty']); ?>" required><br><br>

            <label for="year"><strong>Year:</strong></label>
            <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($user['Year']); ?>" required><br><br>

            <label for="address"><strong>Address:</strong></label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required><br><br>

            <label for="phone"><strong>Phone:</strong></label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required><br><br>

            <label for="gender"><strong>Gender:</strong></label>
            <input type="text" id="gender" name="gender" value="<?php echo htmlspecialchars($user['gender']); ?>" required><br><br>

            <button type="submit">Update</button>
        </form>
    </div>
    <?php include('../includes/footer.php'); ?>
</body>
</html>
