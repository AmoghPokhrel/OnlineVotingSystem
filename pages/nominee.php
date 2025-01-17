<?php
session_start();
include('../includes/db.php'); 

if (isset($_SESSION['username']) && isset($_SESSION['crn'])) {
    $dataFile = '../uploads/card_data.txt';

    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, '');
    }

    $userYear = isset($_SESSION['year']) ? $_SESSION['year'] : '';
    $userFaculty = isset($_SESSION['faculty']) ? $_SESSION['faculty'] : '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['removeCard'])) {
        $cardIdToRemove = $_POST['cardId'];
        $cards = file($dataFile, FILE_IGNORE_NEW_LINES);
        $updatedCards = array_filter($cards, function($card) use ($cardIdToRemove) {
            $trimmedCard = trim($card);
            $cardParts = explode('|', $trimmedCard);
            $username = $_SESSION['username'];
            $role = $_SESSION['role'];

            return !($cardParts[0] === $cardIdToRemove && ($role === 'Admin' || (isset($cardParts[6]) && $cardParts[6] === $username)));
        });
        $updatedCards = array_filter($updatedCards, 'strlen');
        file_put_contents($dataFile, implode("\n", $updatedCards) . "\n");

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['candidateImage']) && $_FILES['candidateImage']['error'] == 0) {
        $targetDir = "uploads/";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $cardId = uniqid();
        $candidateName = htmlspecialchars($_POST['candidateName']);
        $candidateDescription = htmlspecialchars($_POST['candidateDescription']);
        $crn = $_SESSION['crn'];
        $username = $_SESSION['username'];
        $faculty = $_SESSION['faculty'];
        $year = $_SESSION['year'];

        $targetFile = $targetDir . basename($_FILES["candidateImage"]["name"]);
        if (move_uploaded_file($_FILES["candidateImage"]["tmp_name"], $targetFile)) {
            $image = $targetFile;

            $cardData = "$cardId|$image|$candidateName|$candidateDescription|$crn|$username|$faculty|$year";
            file_put_contents($dataFile, "$cardData\n", FILE_APPEND);

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "<p>Sorry, there was an error uploading your file.</p>";
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cardId'])) {
        $cardId = $_POST['cardId'];
        $candidateName = htmlspecialchars($_POST['candidateName']);
        $candidateDescription = htmlspecialchars($_POST['candidateDescription']);
        $cards = file($dataFile, FILE_IGNORE_NEW_LINES);
        $updatedCards = [];

        foreach ($cards as $card) {
            $cardParts = explode('|', $card);
            if ($cardParts[0] == $cardId) {
                $image = isset($cardParts[1]) ? $cardParts[1] : '';
                $crn = isset($cardParts[4]) ? $cardParts[4] : '';
                $username = isset($cardParts[5]) ? $cardParts[5] : '';
                $faculty = isset($cardParts[6]) ? $cardParts[6] : '';
                $year = isset($cardParts[7]) ? $cardParts[7] : '';
                $updatedCard = "$cardId|$image|$candidateName|$candidateDescription|$crn|$username|$faculty|$year";
                $updatedCards[] = $updatedCard;
            } else {
                $updatedCards[] = $card;
            }
        }

        file_put_contents($dataFile, implode("\n", $updatedCards) . "\n");

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $isAdmin = $_SESSION['role'] === 'Admin';
    $username = $_SESSION['username'];
    $userCRN = $_SESSION['crn'];
    $userFaculty = isset($_SESSION['faculty']) ? $_SESSION['faculty'] : '';
    $userYear = isset($_SESSION['year']) ? $_SESSION['year'] : '';
    $isCandidate = false;

    $query = $conn->prepare("SELECT COUNT(*) FROM candidates WHERE crn = ?");
    $query->bind_param("s", $userCRN);
    $query->execute();
    $query->bind_result($count);
    $query->fetch();
    $query->close();

    if ($count > 0) {
        $isCandidate = true;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Cards</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .form-container {
            display: none;
            margin-top: 40px;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            overflow: hidden;
        }
        .card {
            border: 1px solid #ccc;
            padding: 10px;
            width: 300px;
            height: 400px;
            text-align: center;
            /* box-sizing: border-box; */
            /* display: flex; */
            flex-direction: column;
            justify-content: space-between;
        }
        .card img {
            width: 160px;
            height: 160px; 
            object-fit: cover;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .card-content {
            flex: 1;
        }
        .button-container {
            margin-top: auto;
            display: flex;
            justify-content: space-around;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <?php include('../includes/header.php'); ?>
</div>

<div class="content">
    <?php if ($isAdmin || $isCandidate): ?>
        <button id="addButton">Add</button>
    <?php endif; ?>

    <div class="form-container" id="formContainer">
        <form id="addForm" method="post" enctype="multipart/form-data">
            <label for="candidateImage">Image:</label>
            <input type="file" id="candidateImage" name="candidateImage" accept="image/*"><br>
            <label for="candidateName">Candidate Name:</label>
            <input type="text" id="candidateName" name="candidateName" required><br>
            <label for="candidateDescription">Description:</label>
            <textarea id="candidateDescription" name="candidateDescription" required></textarea><br>
            <input type="hidden" id="cardId" name="cardId">
            <button type="submit">Submit</button>
        </form>
    </div>

    <div class="card-container" id="cardContainer">
        <?php
        if (filesize($dataFile) > 0) { 
            $cards = file($dataFile, FILE_IGNORE_NEW_LINES);
            $cards = array_filter($cards, function($card) use ($isAdmin, $userFaculty, $userYear) {
                $cardParts = explode('|', $card);
                $cardFaculty = isset($cardParts[6]) ? $cardParts[6] : '';
                $cardYear = isset($cardParts[7]) ? $cardParts[7] : '';

                return $isAdmin || ($cardFaculty === $userFaculty && $cardYear === $userYear);
            });
            $cards = array_filter($cards, function($card) {
                return strlen(trim($card)) > 0;
            });
            foreach ($cards as $card) {
                $cardParts = explode('|', $card);
                if (count($cardParts) == 8) {
                    $cardId = htmlspecialchars($cardParts[0]);
                    $image = htmlspecialchars($cardParts[1]);
                    $name = htmlspecialchars($cardParts[2]);
                    $description = htmlspecialchars($cardParts[3]);
                    $crn = htmlspecialchars($cardParts[4]);
                    $username = htmlspecialchars($cardParts[5]);

                    echo "<div class='card'>
                            <img src='$image' alt='Candidate Image'>
                            <div class='card-content'>
                                <h3>$name</h3>
                                <p>$description</p>
                            </div>
                            <div class='button-container'>";
                    if ($isAdmin || $username == $_SESSION['username']) {
                        echo "<form method='post' style='display:inline;'>
                                <input type='hidden' name='cardId' value='$cardId'>
                                <button type='submit' name='removeCard'>Remove</button>
                              </form>";
                    }
                    if ($username == $_SESSION['username'] || $isAdmin) {
                        echo "<button onclick=\"editCard('$cardId', '$image', '$name', '$description')\">Edit</button>";
                    }
                    echo "</div></div>";
                } else {
                    echo "<div class='card'>
                            <p>Error displaying card. Data format is incorrect.</p>
                          </div>";
                }
            }
        } else {
            echo "<p>No cards available.</p>";
        }
        ?>
    </div>
</div>
<?php include('../includes/footer.php'); ?>
<script>
    document.getElementById('addButton').addEventListener('click', function() {
        var formContainer = document.getElementById('formContainer');
        formContainer.style.display = formContainer.style.display === 'none' ? 'block' : 'none';
    });

    function editCard(cardId, image, name, description) {
        var formContainer = document.getElementById('formContainer');
        formContainer.style.display = 'block';

        document.getElementById('candidateImage').value = '';
        document.getElementById('candidateName').value = name;
        document.getElementById('candidateDescription').value = description;
        document.getElementById('cardId').value = cardId;
    }
</script>
</body>
</html>
<?php
} else {
    echo '<script>alert("You need to log in"); window.location.href = "..//index.php";</script>';
}
?>
