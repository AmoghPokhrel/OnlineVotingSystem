<?php
session_start();

if (isset($_SESSION['username'])) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booth System</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <script>
        function showForm(role) {
            document.getElementById('role-selection').style.display = 'none';
            if (role === 'Admin') {
                document.getElementById('admin-form').style.display = 'block';
            } else if (role === 'Student') {
                document.getElementById('student-form').style.display = 'block';
            }
        }

        function validateForm(form) {
            const crn = form.crn.value;
            if (!/^\d{6}$/.test(crn)) {
                alert("CRN must be exactly 6 digits.");
                return false;
            }

            const phone = form.number.value;
            if (!/^\d{10}$/.test(phone)) {
                alert("Phone number must be exactly 10 digits.");
                return false;
            }

            const password = form.password.value;
            if (password.length < 8) {
                alert("Password must be at least 8 characters long.");
                return false;
            }

            if (!/(?=.*[0-9])(?=.*[a-zA-Z])/.test(password)) {
                alert("Password must contain at least one letter and one number.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <?php include('../includes/header.php'); ?>
    </div>
    <div class="content">
        <div class="hh">
            <h2>Registration</h2>
        </div>
        <div id="role-selection">
            <form>
                Select Role:
                <input type="radio" name="role" value="Admin" onclick="showForm('Admin')"> Admin
                <input type="radio" name="role" value="Student" onclick="showForm('Student')"> Student
            </form>
        </div>

        <div id="admin-form" style="display:none;">
            <form action="../modules/insertion.php" method="POST" onsubmit="return validateForm(this);">
                <input type="hidden" name="role" value="Admin">
                ID Number:
                <input type="number" name="crn" placeholder="Enter the ID Number" required pattern="\d{6}"><br><br>
                Name:
                <input type="text" name="username" placeholder="Enter the name" required><br><br>
                Email:
                <input type="email" name="email" placeholder="Enter the email" required><br><br>
                Address:
                <input type="text" name="address" placeholder="Enter the address" required><br><br>
                Phone:
                <input type="number" name="number" placeholder="Enter the phone number" required pattern="\d{10}"><br><br>
                Gender:
                <select name="gender" required>
                    <option></option>
                    <option>Male</option> 
                    <option>Female</option>
                    <option>Others</option>
                </select>
                <br><br>
                Password:
                <input type="password" name="password" placeholder="Enter password" required minlength="8"><br><br>
                <button type="submit" id="registerb">Register</button><br><br>
            </form>
        </div>

        <div id="student-form" style="display:none;">
            <form action="../modules/insertion.php" method="POST" onsubmit="return validateForm(this);">
                <input type="hidden" name="role" value="Student">
                CRN Number:
                <input type="number" name="crn" placeholder="Enter the CRN Number" required pattern="\d{6}"><br><br>
                Name:
                <input type="text" name="username" placeholder="Enter the name" required><br><br>
                Email:
                <input type="email" name="email" placeholder="Enter the email" required><br><br>
                Choose your faculty:
                <select name="faculty" required>
                    <option>BSc.Csit</option>
                    <option>BCA</option> 
                    <option>BIM</option>
                    <option>BHM</option>
                    <option>BBS</option>
                </select>
                <br><br>
                Year:
                <input type="text" name="year" placeholder="Enter the Batch Year" required><br><br>
                Address:
                <input type="text" name="address" placeholder="Enter the address" required><br><br>
                Phone:
                <input type="number" name="number" placeholder="Enter the phone number" required pattern="\d{10}"><br><br>
                Gender:
                <select name="gender" required>
                    <option></option>
                    <option>Male</option> 
                    <option>Female</option>
                    <option>Others</option>
                </select>
                <br><br>
                Password:
                <input type="password" name="password" placeholder="Enter password" required minlength="8"><br><br>
                <button type="submit" id="registerb">Register</button><br><br>
            </form>
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
