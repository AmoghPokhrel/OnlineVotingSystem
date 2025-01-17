<?php
session_start();

if (isset($_SESSION['username'])) {
    include('../includes/db.php');

    $username = $_SESSION['username'];
    $user_query = "SELECT role FROM user WHERE name = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_role = $user_data['role'];
    } else {
        echo '<script>alert("User data not found."); window.location.href = "index.php";</script>';
        exit();
    }

    $requests_query = "SELECT * FROM requests ORDER BY request_date DESC";
    $requests_result = $conn->query($requests_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests Display</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/main.css">
    <style>
        /* Existing styles */
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            padding-left: 40px;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
        }
        .card-container {
            display: flex;
            align-items: center;
            margin: 10px;
            flex: 1 1 300px;
        }
        .card {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1;
        }
        .card h2 {
            margin-top: 0;
            font-size: 20px;
            color: #333;
        }
        .card p {
            margin: 5px 0;
            color: #555;
        }
        .content {
            padding: 20px;
            margin-left: 240px; 
            background-color: #f1f1f1;
        }
        .card button {
            margin: 5px 0;
            padding: 8px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .card .btn-add {
            background-color: #4CAF50;
            color: white;
        }
        .card .btn-remove {
            background-color: #f44336;
            color: white;
        }
        .semester-input {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php include('../includes/header.php'); ?>
    </div>

    <div class="content">
        <div class="container">
            <?php 
            if ($requests_result->num_rows > 0) {
                while($row = $requests_result->fetch_assoc()) {
                    $crn = htmlspecialchars($row['crn']);
                    $name = htmlspecialchars($row['name']);
                    $faculty = htmlspecialchars($row['faculty']);
                    $year = htmlspecialchars($row['year']);
                    $request_date = htmlspecialchars($row['request_date']);
            ?>
            <div class="card-container">
                <div class="button-container">
                    <form action="../modules/handle_request.php" method="POST">
                        <select name="semester" class="semester-input" required>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="3rd Semester">3rd Semester</option>
                            <option value="4th Semester">4th Semester</option>
                            <option value="5th Semester">5th Semester</option>
                            <option value="6th Semester">6th Semester</option>
                            <option value="7th Semester">7th Semester</option>
                            <option value="8th Semester">8th Semester</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select><br> 
                        <button type="submit" name="action" value="add_<?php echo $crn; ?>" class="btn-add">Add</button>
                        <button type="submit" name="action" value="remove_<?php echo $crn; ?>" class="btn-remove">Remove</button>
                    </form>
                </div>
                <!-- Information Card -->
                <div class="card">
                    <h2><?php echo $name; ?></h2>
                    <p><strong>CRN:</strong> <?php echo $crn; ?></p>
                    <p><strong>Faculty:</strong> <?php echo $faculty; ?></p>
                    <p><strong>Year:</strong> <?php echo $year; ?></p>
                    <p><strong>Requested On:</strong> <?php echo $request_date; ?></p>
                </div>
            </div>
            <?php
                }
            } else {
                echo '<p>No requests found.</p>';
            }
            ?>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</body>
</html>

<?php
} else {
    echo '<script>alert("Please log in to access this page."); window.location.href = "..//index.php";</script>';
}
?>
