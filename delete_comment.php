<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment_id'])) {
    $comment_id = intval($_POST['comment_id']);

    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $comment_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Comment deleted successfully.";
    } else {
        $_SESSION['message'] = "Error deleting comment.";
    }

    $stmt->close();
}

$conn->close();

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
