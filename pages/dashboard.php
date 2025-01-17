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
        <p>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?>, to our CR Voting system.</p>
        <img src="../assets/images/himalaya-min.png" alt="Himalaya Image">
    </div>
    <?php   
    include('../includes/footer.php');
    ?>
    </body>
    </html>
    <?php
} else {
    echo '<script>alert("You need to log in"); window.location.href = "..//index.php";</script>';
}
?>
