<?php
session_start(); 

if (isset($_SESSION['username'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Document</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/main.css">
    </head>
    <body>
    <div class="sidebar">
        <?php
        include('../includes/header.php');
        ?>
    </div>
    <div class="content">
        <?php
        include('../includes/db.php');

        // Fetch the logged-in user's faculty and year
        $username = $_SESSION['username'];
        $userQuery = "SELECT faculty, year FROM user WHERE name = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $userData = $userResult->fetch_assoc();
        $userFaculty = $userData['faculty'];
        $userYear = $userData['year'];

        // Fetch the latest request details
        $query = "SELECT * FROM requests ORDER BY id DESC";
        $result = $conn->query($query);

        while ($row = $result->fetch_assoc()) {
            // Check if the user's faculty and year match the requestor's faculty and year
            if ($row['faculty'] === $userFaculty && $row['year'] === $userYear) {
                echo '<div class="card" style="width: 18rem; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px;">';
                echo '<h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>';
                echo '<p class="card-text">CRN: ' . htmlspecialchars($row['crn']) . '</p>';
                echo '<p class="card-text">Faculty: ' . htmlspecialchars($row['faculty']) . '</p>';
                echo '<p class="card-text">Year: ' . htmlspecialchars($row['year']) . '</p>';
                echo '<form method="post" action="../modules/handle_vouch.php">';
                echo '<input type="hidden" name="request_id" value="' . htmlspecialchars($row['id']) . '">';
                echo '<button type="submit" class="btn btn-primary">Vouch</button>';
                echo '</form>';
                echo '</div>';
            }
        }

        $stmt->close();
        $conn->close();
        ?>

    </div>
    <?php   
    include('../includes/footer.php');
    ?>
    </body>
    </html>
    <?php
} else {
    echo '<script>alert("You need to log in"); window.location.href = "../index.php";</script>';
}
?>
