<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Himalaya College Booth System</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar .nav-item.active {
            background-color: #087ef3;
            color: #fff;
        }

        .nav-item a {
            color: inherit;
            text-decoration: none;
            display: block;
            padding: 10px;
        }

        .nav-item .dropdown {
            display: none;
            list-style: none;
            padding: 0;
        }

        .nav-item:hover .dropdown {
            display: block;
            background-color: #f1f1f1;
        }

        .nav-item .dropdown li {
            padding: 5px 20px;
        }

        .nav-item .dropdown li a {
            color: #333;
            text-decoration: none;
        }

        .nav-item .dropdown li a:hover {
            text-decoration: underline;
            background-color: #087ef3;
        }

        .logout a {
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <a href="../pages/about.php" class="booth-link">
                <img src="../assets/images/hdc_logo.png" class="no-hover" alt="Himalaya College Logo">
                <h2>Booth System</h2>
            </a>
        </div>
        <ul class="nav">
            <li class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><a href="../pages/dashboard.php"><i class="fas fa-home"></i> Home</a></li>
            <li class="nav-item <?php echo ($current_page == 'events.php') ? 'active' : ''; ?>"><a href="../pages/events.php"><i class="fas fa-calendar"></i> Events</a></li>
            <li class="nav-item <?php echo ($current_page == 'nominee.php') ? 'active' : ''; ?>"><a href="../pages/nominee.php"><i class="fas fa-user"></i> About Nominee</a></li>
            <li class="nav-item <?php echo ($current_page == 'voting.php') ? 'active' : ''; ?>"><a href="../pages/voting.php"><i class="fas fa-vote-yea"></i> Voting</a></li>
            <li class="nav-item <?php echo ($current_page == 'voters.php') ? 'active' : ''; ?>"><a href="../pages/voters.php"><i class="fas fa-users"></i> Voters</a></li>
            <li class="nav-item <?php echo ($current_page == 'result.php') ? 'active' : ''; ?>"><a href="../pages/result.php"><i class="fas fa-chart-line"></i> Result</a></li>
            
            <?php if ($_SESSION['role'] == 'Admin' || (isset($_SESSION['database_empty']) && $_SESSION['database_empty'])): ?>
                <li class="nav-item <?php echo ($current_page == 'register.php') ? 'active' : ''; ?>"><a href="../pages/register.php"><i class="fas fa-edit"></i> Register</a></li>
                <li class="nav-item <?php echo ($current_page == 'requests_display.php') ? 'active' : ''; ?>">
                    <a href="../pages/requests_display.php"><i class="fas fa-tasks"></i> Requests</a>
                </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] == 'Student'): ?>
                <li class="nav-item <?php echo ($current_page == 'support.php') ? 'active' : ''; ?>"><a href="../pages/support.php"><i class="fas fa-life-ring"></i> Support</a></li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
                <ul class="dropdown">
                    <li class="<?php echo ($current_page == 'change_password.php') ? 'active' : ''; ?>"><a href="../pages/change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                </ul>
            </li>
        </ul>
        <button class="logout"><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></button>
    </div>
</body>
</html>
