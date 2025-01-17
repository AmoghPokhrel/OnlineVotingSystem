<!DOCTYPE html>
<html lang="en">
<head>
    <title>HDC Booth System</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/login.css">
</head>
<body>
    <div class="login">
        <div class="loginn">
            <form action="modules/validate_login.php" method="POST">
                <h2>Booth System Login</h2>
                Username:
                <input type="text" id="username" name="username" >
                Password:
                <input type="password" id="password" name="password" >
                Role:
                <select id="role" name="role" >
                    <option value=""></option>
                    <option value="Admin">Admin</option>
                    <option value="Student">Student</option>
                </select>
                <button type="submit">Log In</button>
                <h4><a href="pages/forgot_password.php">Forgot Password?</a></h4>
            </form>
        </div>
        <div class="logo-div">
            <img src="assets/images/hdc_logo.png" alt="Himalaya College Logo">
        </div>
    </div>
</body>
</html>
