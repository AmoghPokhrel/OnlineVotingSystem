<?php
session_start();

$contentFile = '../includes/content.txt';

$content = file_exists($contentFile) ? file_get_contents($contentFile) : "Default content";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    $newContent = htmlspecialchars($_POST['content']);
    
    file_put_contents($contentFile, $newContent);
    
    $content = $newContent;

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if (isset($_SESSION['username'])) {
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                width: 100%;
                background-color: #f4f4f4;
            }
            .content {
                flex-grow: 1;
                display: flex;
                justify-content: center;
                align-items: center;
                background-color: #fff; 
                height: 100vh;
                width: 100%; 
                padding: 20px;
            }

            .card {
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                width: 900px; 
                padding: 30px;
                box-sizing: border-box;
                text-align: center;
            }

            .card p {
                color: #666;
                margin-bottom: 20px;
            }

            .admin-button {
                display: <?php echo $isAdmin ? 'block' : 'none'; ?>;
                padding: 10px 20px;
                background-color: #007bff;
                color: #fff;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                margin-bottom: 20px;
                transition: background-color 0.3s ease;
            }

            .admin-button:hover {
                background-color: #0056b3;
            }

            .edit-form {
                display: none;
                margin-top: 20px;
            }

            .edit-form textarea {
                width: 100%;
                height: 350px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                margin-bottom: 10px;
            }

            .edit-form button {
                padding: 10px 20px;
                background-color: #28a745;
                color: #fff;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s ease;
                margin-right: 10px;
            }

            .edit-form button:hover {
                background-color: #218838;
            }

            .edit-form button[type="button"] {
                background-color: #dc3545;
            }

            .edit-form button[type="button"]:hover {
                background-color: #c82333;
            }
        </style>
        <script>
            function toggleEditForm() {
                var contentDiv = document.getElementById('contentDiv');
                var editForm = document.getElementById('editForm');
                var isEditing = editForm.style.display === 'block';
                
                if (isEditing) {
                    editForm.style.display = 'none';
                    contentDiv.style.display = 'block';
                } else {
                    editForm.style.display = 'block';
                    contentDiv.style.display = 'none';
                }
            }
        </script>
    </head>
    <body>
        <div class="sidebar">
            <?php
            include('../includes/header.php');
            ?>
        </div>

        <div class="content">
            <div class="card">
                <div id="contentDiv">
                    <p><?php echo htmlspecialchars($content); ?></p>
                    <?php if ($isAdmin): ?>
                        <button class="admin-button" onclick="toggleEditForm()">Edit Content</button>
                    <?php endif; ?>
                </div>
                <div id="editForm" class="edit-form">
                    <form method="post">
                        <textarea name="content"><?php echo htmlspecialchars($content); ?></textarea>
                        <button type="submit">Save Changes</button>
                        <button type="button" onclick="toggleEditForm()">Cancel</button>
                    </form>
                </div>
            </div>
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
