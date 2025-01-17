<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Overlay</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: #357abd;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            z-index: 1000;
            color: White;
        }
        .popup.active {
            display: block;
        }
        .overlay {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 500;
        }
        .overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include('../pages/dashboard.php'); ?>

    <div class="overlay"></div>
    <div class="popup">
        <h2>Upcoming Events</h2>
        <p>Join us in upcoming event! Don't miss the opportunity.</p>
        <button onclick="learnMore()">Learn More</button>
        <button onclick="closePopup()">Close</button>
    </div>

    <script>
        window.onload = function() {
            document.querySelector('.popup').classList.add('active');
            document.querySelector('.overlay').classList.add('active');
        };

        function closePopup() {
            document.querySelector('.popup').classList.remove('active');
            document.querySelector('.overlay').classList.remove('active');
            window.location.href = "../pages/dashboard.php";
        }

        function learnMore() {
            window.location.href = "../pages/events.php";
        }
    </script>
</body>
</html>
