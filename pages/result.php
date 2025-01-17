<?php
session_start();

if (isset($_SESSION['username'])) {
    include('../includes/db.php');

    $role = $_SESSION['role'];
    $username = $_SESSION['username'];

    if ($role === 'Student') {
        if (!isset($_SESSION['year']) || !isset($_SESSION['faculty'])) {
            echo "Error: Student year and faculty not set in session.<br>";
            echo "Session data: ";
            print_r($_SESSION);
            exit();
        }
    }

    $sql = "SELECT user.name, user.year, user.faculty, COUNT(votes.candidates_id) AS vote_count 
            FROM candidates 
            LEFT JOIN votes ON candidates.id = votes.candidates_id
            LEFT JOIN user ON candidates.crn = user.crn
            WHERE candidates.status = 'active'";

    if ($role === 'Student') {
        if (isset($_SESSION['year']) && isset($_SESSION['faculty'])) {
            $student_year = $_SESSION['year'];
            $student_faculty = $_SESSION['faculty'];

            $sql .= " AND user.year = '$student_year' AND user.faculty = '$student_faculty'";
        } else {
            echo "Error: Student year and faculty not set in session.";
            exit();
        }
    }

    $sql .= " GROUP BY user.name, user.year, user.faculty
              ORDER BY user.year, user.faculty, user.name";

    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $data = [];
    $tie = false;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['year']][$row['faculty']][] = [
                'name' => $row['name'],
                'vote_count' => $row['vote_count']
            ];
        }
    }

    $conn->close();

    // File to store the winner's name
    $winnerFile = "../includes/winners.json";  
    $winnersData = [];

    // Load winners data from the file if it exists
    if (file_exists($winnerFile)) {
        $winnersData = json_decode(file_get_contents($winnerFile), true);
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Voting Results</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/main.css">
        <style>
            body {
                margin: 0;
                overflow-y: scroll;
                min-height: 100vh;
                font-family: Arial, sans-serif;
                background-color: #f2f2f2;
            }
            .content {
                margin-left: 250px; 
                padding: 20px;
                min-height: 100vh;
                width: calc(100% - 330px); 
            }
            .card-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                justify-content: center;
            }
            .card {
                background-color: #f9f9f9;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 10px;
                padding: 20px;
                width: calc(50% - 20px); 
                margin-bottom: 20px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .bar-container {
                width: 100%;
                margin-top: 20px;
                margin-bottom: 30px;
            }
            .bar {
                width: 70%;
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }
            .bar-label {
                width: 15%;
                text-align: right;
                padding-right: 10px;
                font-weight: bold;
            }
            .bar-value {
                width: 80%;
                background-color: #4CAF50;
                text-align: right;
                padding-right: 10px;
                color: white;
            }
            .no-results {
                text-align: center;
                font-size: 18px;
                color: #ff0000;
                padding: 20px;
            }
            .footer {
                text-align: center;
                margin-top: auto;
            }
            .publish-button {
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .publish-button:hover {
                background-color: #45a049;
            }
            .tie-message {
                color: #ff0000;
                font-weight: bold;
                text-align: center;
                margin-top: 20px;
            }
            .flip-button {
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #008CBA;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
            }
            .flip-button:hover {
                background-color: #007bb5;
            }
            .winner-form {
                margin-top: 20px;
                text-align: center;
            }
            .winner-form input[type="text"] {
                padding: 10px;
                border-radius: 5px;
                border: 1px solid #ccc;
                width: 80%;
                margin-bottom: 10px;
            }
            .winner-form input[type="submit"] {
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .winner-form input[type="submit"]:hover {
                background-color: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="sidebar">
            <?php include('../includes/header.php'); ?>
        </div>

        <div class="content">
            <?php if (empty($data)) { ?>
                <div class="no-results">
                    <p>No results available to display.</p>
                </div>
            <?php } else { ?>
                <div class="card-container">
                    <?php
                    foreach ($data as $year => $faculties) {
                        foreach ($faculties as $faculty => $candidates) {
                            $max_votes = 0;
                            $winner = '';
                            $tie_candidates = [];

                            // Check if there's a winner stored in the file
                            if (isset($winnersData[$year][$faculty])) {
                                $winner = $winnersData[$year][$faculty];
                            } else {
                                foreach ($candidates as $candidate) {
                                    if ($candidate['vote_count'] > $max_votes) {
                                        $max_votes = $candidate['vote_count'];
                                        $winner = $candidate['name'];
                                        $tie_candidates = [$candidate['name']];
                                    } elseif ($candidate['vote_count'] == $max_votes) {
                                        $tie_candidates[] = $candidate['name'];
                                    }
                                }
                            }

                            if (count($tie_candidates) > 1) {
                                $tie = true;
                                echo '<div class="card">';
                                echo "<h3>Year: " . htmlspecialchars($year) . " - Faculty: " . htmlspecialchars($faculty) . "</h3>";
                                echo '<div class="bar-container">';
                                foreach ($candidates as $candidate) {
                                    echo '<div class="bar">';
                                    echo '<div class="bar-label">' . htmlspecialchars($candidate['name']) . '</div>';
                                    echo '<div class="bar-value" style="width: ' . ($candidate['vote_count'] * 10) . '%;">' . $candidate['vote_count'] . '</div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                                echo '<h3 class="tie-message">Tie between ' . implode(' and ', $tie_candidates) . '</h3>';

                                if ($role === 'Admin') {
                                    echo '<div class="winner-form">';
                                    echo '<a class="flip-button" href="https://justflipacoin.com/#flip-a-coin" target="_blank">Flip a Coin</a><br><br>';
                                    echo '<form method="POST" action="">';
                                    echo '<label for="winner_name">Enter Winner Name:</label><br>';
                                    echo '<input type="text" id="winner_name" name="winner_name" required><br>';
                                    echo '<input type="submit" value="Submit">';
                                    echo '</form>';

                                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['winner_name'])) {
                                        $winner = htmlspecialchars($_POST['winner_name']);
                                        // Store the winner's name in the file
                                        $winnersData[$year][$faculty] = $winner;
                                        file_put_contents($winnerFile, json_encode($winnersData));
                                    }
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="card">';
                                echo "<h3>Year: " . htmlspecialchars($year) . " - Faculty: " . htmlspecialchars($faculty) . "</h3>";
                                echo '<div class="bar-container">';
                                foreach ($candidates as $candidate) {
                                    echo '<div class="bar">';
                                    echo '<div class="bar-label">' . htmlspecialchars($candidate['name']) . '</div>';
                                    echo '<div class="bar-value" style="width: ' . ($candidate['vote_count'] * 10) . '%;">' . $candidate['vote_count'] . '</div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                                echo '<h3>' . htmlspecialchars($winner) . ' is the winner</h3>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
        <?php include('../includes/footer.php'); ?>
    </body>
    </html>
    <?php
} else {
    echo '<script>alert("You need to Log In"); window.location.href = "..//index.php";</script>';
}
?>
