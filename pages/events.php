<?php
session_start(); 

if (isset($_SESSION['username'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add'])) {
            if ($_SESSION['role'] == 'Admin') {
                $newEvent = [
                    'title' => $_POST['title'],
                    'description' => $_POST['description']
                ];
                $events = json_decode(file_get_contents('../includes/events.json'), true);
                $events[] = $newEvent;
                file_put_contents('../includes/events.json', json_encode($events));
                header('Location: ../pages/events.php');
                exit();
            } else {
                echo '<script>alert("You do not have permission to add events");</script>';
            }
        } elseif (isset($_POST['remove'])) {
            if ($_SESSION['role'] == 'Admin') {
                $events = json_decode(file_get_contents('../includes/events.json'), true);
                unset($events[$_POST['index']]);
                $events = array_values($events);
                file_put_contents('../includes/events.json', json_encode($events));
                header('Location: ../pages/events.php');
                exit();
            } else {
                echo '<script>alert("You do not have permission to remove events");</script>';
            }
        } elseif (isset($_POST['edit'])) {
            if ($_SESSION['role'] == 'Admin') {
                $events = json_decode(file_get_contents('../includes/events.json'), true);
                $index = $_POST['index'];
                $events[$index]['title'] = $_POST['title'];
                $events[$index]['description'] = $_POST['description'];
                file_put_contents('../includes/events.json', json_encode($events));
                header('Location: ../pages/events.php');
                exit();
            } else {
                echo '<script>alert("You do not have permission to edit events");</script>';
            }
        }
    }

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Events</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/main.css">
    </head>
    <body class="body">
        <div class="sidebar">
            <?php include('../includes/header.php');  ?>
        </div>
        <div class="content">
            <h1>Upcoming Events</h1>
            <div class="card-container">
                <?php 
                    $events = json_decode(file_get_contents('../includes/events.json'), true); 
                    if (!empty($events)) {
                        foreach ($events as $index => $event): ?>
                            <div class="card">
                                <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <div class="button-container">
                                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                                        <button type="button" class="editEventBtn" data-index="<?php echo $index; ?>" data-title="<?php echo htmlspecialchars($event['title']); ?>" data-description="<?php echo htmlspecialchars($event['description']); ?>">Edit</button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                                        <button type="submit" name="remove">Remove</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php 
                        endforeach;
                    } else {
                        echo '<p>No events found.</p>';
                    }
                ?>
            </div>
            <?php if ($_SESSION['role'] == 'Admin'): ?>
            <button class="addEventBtn" id="addEventBtn">Add Event</button>
            <?php endif; ?>
        
            <div id="addEventForm" class="popup-form">
                <form method="post" class="popup-content">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                    <button type="submit" name="add">Add Event</button>
                    <button type="button" id="closePopupBtn">Close</button>
                </form>
            </div>

            <div id="editEventForm" class="popup-form">
                <form method="post" class="popup-content">
                    <input type="hidden" id="editIndex" name="index">
                    <label for="editTitle">Title:</label>
                    <input type="text" id="editTitle" name="title" required>
                    <label for="editDescription">Description:</label>
                    <textarea id="editDescription" name="description" required></textarea>
                    <button type="submit" name="edit">Edit Event</button>
                    <button type="button" id="closeEditPopupBtn">Close</button>
                </form>
            </div>
        </div>
        <div>
            <?php include('../includes/footer.php'); //  ?>
        </div>
        <script src="../assets/js/scripts.js"></script> 
        <script>
            document.querySelectorAll('.editEventBtn').forEach(button => {
                button.addEventListener('click', () => {
                    document.getElementById('editIndex').value = button.getAttribute('data-index');
                    document.getElementById('editTitle').value = button.getAttribute('data-title');
                    document.getElementById('editDescription').value = button.getAttribute('data-description');
                    document.getElementById('editEventForm').style.display = 'block';
                });
            });

            document.getElementById('addEventBtn').addEventListener('click', () => {
                document.getElementById('addEventForm').style.display = 'block';
            });

            document.getElementById('closePopupBtn').addEventListener('click', () => {
                document.getElementById('addEventForm').style.display = 'none';
            });

            document.getElementById('closeEditPopupBtn').addEventListener('click', () => {
                document.getElementById('editEventForm').style.display = 'none';
            });
        </script>
    </body>
    </html>
    <?php
} else {
    echo '<script>alert("You need to log in"); window.location.href = "..//index.php";</script>';
}
?>
