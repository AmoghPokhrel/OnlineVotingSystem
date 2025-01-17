<?php
session_start();
include('../includes/db.php');

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $crn = '';
    $studentYear = '';
    $studentFaculty = '';
    $votingActive = false;

    $query = "SELECT crn, year, faculty, permission_status FROM user WHERE name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $crn = $row['crn'];
        $studentYear = $row['year'];
        $studentFaculty = $row['faculty'];
        $permissionStatus = $row['permission_status'];
    }

    // Check voting status for the user's year and faculty
    if ($_SESSION['role'] != "Admin") {
        $query = "SELECT status FROM voting_status WHERE year = ? AND faculty = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $studentYear, $studentFaculty);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $votingActive = ($row['status'] == 'active');
        }
    }

    // Handle vote request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request'])) {
        $requested_crn = $conn->real_escape_string($_POST['crn']);
        
        $query = "SELECT crn, name, faculty, year FROM user WHERE crn = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $requested_crn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $requested_name = htmlspecialchars($row['name']);
            $requested_faculty = htmlspecialchars($row['faculty']);
            $requested_year = htmlspecialchars($row['year']);
        } else {
            echo '<script>alert("User not found.");</script>';
            $requested_name = $requested_faculty = $requested_year = '';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/main.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/voting.css">
    <script>
        function checkAdmin(event) {
            var role = "<?php echo $_SESSION['role']; ?>";
            if (role === "Admin") {
                alert("You cannot vote as an Admin.");
                logAdminVoteAttempt();
                event.preventDefault();
            }
        }

        function logAdminVoteAttempt() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../modules/log_admin_vote_attempt.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("crn=<?php echo $crn; ?>&candidate=" + document.querySelector("select[name='candidate']").value);
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <?php include('../includes/header.php'); ?>
    </div>

    <div class="content">
        <h1>Voting System</h1>

        <?php
        if (isset($_SESSION['message'])) {
            echo "<script>alert('" . $_SESSION['message'] . "');</script>";
            unset($_SESSION['message']);
        }
        ?>
        <div class="flex-container">
            <div class="container flex-item">
                <h2>Vote for your favorite candidate</h2>
                <h3>Choose Wisely</h3>
                <?php
                if ($_SESSION['role'] == "Admin" || $votingActive) {
                    $query = $_SESSION['role'] == "Admin" 
                        ? "SELECT c.id, u.name, u.year, u.faculty FROM candidates c JOIN user u ON c.crn = u.crn WHERE c.status = 'active'"
                        : "SELECT c.id, u.name, u.year, u.faculty FROM candidates c JOIN user u ON c.crn = u.crn WHERE c.status = 'active' AND u.year = '$studentYear' AND u.faculty = '$studentFaculty'";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0 || $_SESSION['role'] == "Admin") {
                        if ($_SESSION['role'] == "Admin" || $permissionStatus === 'Grant') {
                            ?>
                            <form action="../modules/voting_database.php" method="post" onsubmit="checkAdmin(event);">
                                <input type="hidden" name="crn" value="<?php echo htmlspecialchars($crn); ?>">
                                <select name="candidate" required>
                                    <?php
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}'>{$row['name']} - {$row['year']} - {$row['faculty']}</option>";
                                    }
                                    ?>
                                </select><br>
                                <button type="submit" name="vote">&nbsp;&nbsp;Vote&nbsp;&nbsp;</button>
                            </form>
                            <?php
                        } else {
                            echo "<p>You do not have permission to vote.</p>";
                        }
                    } else {
                        echo "<p>No candidates available for voting.</p>";
                    }
                } else {
                    echo "<p>Voting is inactive for your year and faculty.</p>";
                }
                ?>
            </div>

            <?php if ($_SESSION['role'] == "Admin") { ?>
            <div class="admin flex-item">
                <h2>Admin Panel</h2>
                <form action="../modules/voting_database.php" method="post">
                    <input type="text" name="crn" placeholder="Add CRN of Candidate" required>
                    <select name="semester" required>
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
                    <button type="submit" name="add">Add</button>
                </form>

                <form action="../modules/voting_database.php" method="post">
                    <select name="id" required>
                        <?php
                            // Fetch candidates details for the dropdown
                            $query = "SELECT c.id, u.name, u.year, u.faculty, c.semester FROM candidates c JOIN user u ON c.crn = u.crn WHERE c.status = 'active'";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                $candidateDetails = htmlspecialchars($row['name'] . ' - ' . $row['year'] . ' - ' . $row['faculty'] . ' - ' . $row['semester']);
                                echo "<option value='{$row['id']}'>{$candidateDetails}</option>";
                            }
                        ?>
                    </select><br>
                    <select name="new_semester" required>
                        <option value="">Change Semester</option>
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
                    <button type="submit" name="change_semester">Change Semester</button>
                </form>


                <form action="../modules/voting_database.php" method="post">
                    <select name="id" required>
                        <option value="">Select Candidate to Deactivate</option>
                        <?php
                        // Fetch candidates details for the dropdown
                        $query = "SELECT c.id, u.name, u.year, u.faculty, c.semester FROM candidates c JOIN user u ON c.crn = u.crn WHERE c.status = 'active'";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            $candidateDetails = htmlspecialchars($row['name'] . ' - ' . $row['year'] . ' - ' . $row['faculty'] . ' - ' . $row['semester']);
                            echo "<option value='{$row['id']}'>{$candidateDetails}</option>";
                        }
                        ?>
                    </select><br>
                    <button type="submit" name="deactivate">Deactivate</button>
                </form>


                <form action="../modules/voting_control.php" method="post">
                    <select name="faculty" required>
                        <option value="">Select Faculty</option>
                        <option value="BSc.CSIT">BSc.CSIT</option>
                        <option value="BCA">BCA</option>
                        <option value="BIM">BIM</option>
                        <option value="BHM">BHM</option>
                        <option value="BBS">BBS</option>
                    </select>
                    <select name="year" required>
                        <option value="">Select Year</option>
                        <?php
                        $query = "SELECT DISTINCT year FROM years";
                        $yearResult = $conn->query($query);
                        while ($yearRow = $yearResult->fetch_assoc()) {
                            echo "<option value='{$yearRow['year']}'>{$yearRow['year']}</option>";
                        }
                        ?>
                    </select><br>
                    <button type="submit" name="end_voting">End Voting</button>
                    <button type="submit" name="start_voting">Start Voting</button>
                </form>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</body>
</html>
<?php
} else {
    echo '<script>alert("You need to Log In"); window.location.href = "..//index.php";</script>';
}
?>
