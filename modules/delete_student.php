<?php
session_start();
include '../includes/db.php';

if (isset($_POST['crn'])) {
    $crn = $_POST['crn'];

    $sql = "DELETE FROM user WHERE crn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $crn);

    try {
        $stmt->execute();
        echo "<script>alert('Record deleted successfully'); window.location.href = '../pages/voters.php';</script>";
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) {
            echo "<script>alert('Cannot delete this record because it is referenced in other tables. Please delete the related records first.'); window.location.href = '../pages/voters.php';</script>";
        } else {
            echo "<script>alert('Error deleting record: " . addslashes($e->getMessage()) . "'); window.location.href = '../pages/voters.php';</script>";
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request'); window.location.href = '../pages/voters.php';</script>";
}
?>
