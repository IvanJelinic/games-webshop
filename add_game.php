<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['developer', 'admin'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_role = $_SESSION['role'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $developer_id = $_SESSION['user_id'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $target_dir = "images/";
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($image_tmp_name, $target_file)) {
            $image_path = $target_file;
        } else {
            echo "Error uploading the image.";
            exit();
        }
    } else {
        $image_path = "images/default_image.jpg";
    }

    $sql = "INSERT INTO games (title, description, price, developer_id, image_path, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdss", $title, $description, $price, $developer_id, $image_path);

    if ($stmt->execute()) {
        echo "Game added successfully!";
    } else {
        echo "Error: Could not add game.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Game</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar">
        <div class="logo">
            <h1>GameHive</h1>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="browse.php">Browse</a></li>
            <li><a href="purchases.php">My Games</a></li>
            
            <?php if ($user_role === 'developer' || $user_role === 'admin'): ?>
                <li><a href="add_game.php">Add New Game</a></li>
                <li><a href="manage_games.php">Dev Tools</a></li>
            <?php endif; ?>

            <li><a href="logout.php">Logout</a></li>
        </ul>
        <p>Welcome, <?php echo $_SESSION['name'] ?></p>
    </nav>

    <div class="add-form">
        <h2>Add New Game</h2>
        <form method="POST" action="add_game.php" enctype="multipart/form-data">
            <label for="title">Game Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="image">Upload Image:</label>
            <input type="file" id="image" name="image" accept="image/*">

            <button type="submit">Add Game</button>
        </form>
    </div>
</body>
</html>
