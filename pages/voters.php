<?php
session_start();

if (isset($_SESSION['username'])) {
    include('../includes/db.php');
    
    $username = $_SESSION['username'];
    $user_query = "SELECT crn, role, Year, Faculty FROM user WHERE name = ?";
    $stmt = $conn->prepare($user_query);
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_crn = htmlspecialchars($user_data['crn']);
        $user_role = htmlspecialchars($user_data['role']);
        $user_year = htmlspecialchars($user_data['Year']);
        $user_faculty = htmlspecialchars($user_data['Faculty']);
    } else {
        echo '<script>alert("User data not found."); window.location.href = "../pages/dashboard.php";</script>';
        exit();
    }
    
    $selected_year = $user_role === 'Admin' ? ($_GET['year'] ?? '') : $user_year;
    $selected_faculty = $user_role === 'Admin' ? ($_GET['faculty'] ?? '') : $user_faculty;

    $year_filter = $selected_year ? "AND user.Year = '{$conn->real_escape_string($selected_year)}'" : '';
    $faculty_filter = $selected_faculty ? "AND user.Faculty = '{$conn->real_escape_string($selected_faculty)}'" : '';

    $years_query = "SELECT year FROM years ORDER BY year DESC";
    $years_result = $conn->query($years_query);
    $years = [];
    while ($row = $years_result->fetch_assoc()) {
        $years[] = htmlspecialchars($row['year']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_year'])) {
        $new_year = htmlspecialchars(trim($_POST['new_year']));
        
        if ($new_year && !in_array($new_year, $years)) {
            $insert_query = "INSERT INTO years (year) VALUES (?)";
            $stmt = $conn->prepare($insert_query);
            
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param('s', $new_year);
            if ($stmt->execute()) {
                $years[] = $new_year;
                echo '<script>alert("Year added successfully."); window.location.reload();</script>';
            } else {
                echo '<script>alert("Error adding year: ' . $conn->error . '");</script>';
            }
            $stmt->close();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/main.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
    <script>
        function updateFilters() {
            const year = document.querySelector('select[name="year"]').value;
            const faculty = document.querySelector('input[name="faculty"]:checked')?.value || '';
            window.location.href = `?year=${year}&faculty=${faculty}`;
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <?php include('../includes/header.php'); ?>
    </div>

    <div class="content">
        <div class="container">
            <?php if ($user_role === 'Admin') { ?>
            <div class="dropdown">
                <form action="" method="post">
                    <select name="year" onchange="updateFilters()">
                        <option value="">Select a year</option>
                        <?php foreach ($years as $year) { ?>
                            <option value="<?php echo htmlspecialchars($year); ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>><?php echo htmlspecialchars($year); ?></option>
                        <?php } ?>
                    </select>
                    <input type="text" name="new_year" placeholder="Add a year">
                    <button type="submit">Add Year</button>
                </form>
            </div>
            <div class="buttons">
                <?php
                $faculties = ['BIM', 'BCA', 'BSc.Csit', 'BHM', 'BBS'];
                foreach ($faculties as $faculty) {
                    echo "<input type='radio' name='faculty' value='$faculty' id='$faculty' onclick='updateFilters()' " . ($selected_faculty == $faculty ? 'checked' : '') . ">
                          <label for='$faculty'>$faculty</label>";
                }
                ?>
            </div>
            <?php } ?>
            <h1>Student List</h1>
            <table>
                <thead>
                    <tr>
                        <th>CRN</th>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Status</th>
                        <th>About</th>
                        <?php if ($user_role === 'Admin') { ?>
                        <th>Permission</th>
                        <th>Remove</th>
                        <?php } ?>
                        <?php if ($user_role === 'Student') { ?>
                        <th>Request</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT user.crn, user.name, user.phone, IF(votes.id IS NULL, 'No', 'Yes') AS voting_status, user.permission_status
                            FROM user
                            LEFT JOIN votes ON user.crn = votes.crn
                            WHERE user.role='Student' {$year_filter} {$faculty_filter}";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $crn = htmlspecialchars($row["crn"]);
                            $name = htmlspecialchars($row["name"]);
                            $phone = htmlspecialchars($row["phone"]);
                            $voting_status = htmlspecialchars($row["voting_status"]);
                            $permission_status = htmlspecialchars($row["permission_status"]);

                            echo "<tr>
                                    <td>{$crn}</td>
                                    <td>{$name}</td>
                                    <td>{$phone}</td>
                                    <td>
                                        <input type='radio' name='status_{$crn}' value='Yes' " . ($voting_status == 'Yes' ? 'checked' : '') . "> Yes
                                        <input type='radio' name='status_{$crn}' value='No' " . ($voting_status == 'No' ? 'checked' : '') . "> No
                                    </td>
                                    <td>
                                        <form action='../pages/students_details.php' method='get'>
                                            <input type='hidden' name='crn' value='{$crn}'>
                                            <button type='submit'>Detail</button>
                                        </form>
                                    </td>";
                                    if ($user_role === 'Admin') {
                                        echo "<td>
                                            <form action='../modules/update_permission.php' method='post'>
                                                <input type='radio' name='permission_status' value='Grant' " . ($permission_status == 'Grant' ? 'checked' : '') . "> Grant
                                                <input type='radio' name='permission_status' value='Revoke' " . ($permission_status == 'Revoke' ? 'checked' : '') . "> Revoke
                                                <input type='hidden' name='crn' value='{$crn}'>
                                                <button type='submit'>Update Permission</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action='../modules/delete_student.php' method='post'>
                                                <input type='hidden' name='crn' value='{$crn}'>
                                                <button type='submit'>Delete</button>
                                            </form>
                                        </td>";
                                    }
                                    if ($user_role === 'Student' && $user_crn == $crn) {
                                        echo "<td>
                                            <form action='../modules/requests.php' method='post'>
                                                <input type='hidden' name='crn' value='{$crn}'>
                                                <input type='hidden' name='name' value='{$name}'>
                                                <input type='hidden' name='faculty' value='{$user_faculty}'>
                                                <input type='hidden' name='year' value='{$user_year}'>
                                                <button type='submit'>Request</button>
                                            </form>
                                        </td>";
                                    }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='" . ($user_role === 'Admin' ? '8' : '7') . "'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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
