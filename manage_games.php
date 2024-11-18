<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['developer', 'admin'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        $game_id = $_POST['game_id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $discount = trim($_POST['discount']);

        if ($role === 'admin') {
            $sql = "UPDATE games SET title=?, description=?, price=?, discount=? WHERE game_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdii", $title, $description, $price, $discount, $game_id);
        } else {
            $sql = "UPDATE games SET title=?, description=?, price=?, discount=? WHERE game_id=? AND developer_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiii", $title, $description, $price, $discount, $game_id, $user_id);
        }

        if ($stmt->execute()) {
            echo "Game updated successfully!";
        } else {
            echo "Error updating game.";
        }
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        $game_id = $_POST['game_id'];

        if ($role === 'admin') {
            $sql = "DELETE FROM games WHERE game_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $game_id);
        } else {
            $sql = "DELETE FROM games WHERE game_id=? AND developer_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $game_id, $user_id);
        }

        if ($stmt->execute()) {
            echo "Game deleted successfully!";
        } else {
            echo "Error deleting game.";
        }
        $stmt->close();
    }
}

if ($role === 'admin') {
    $sql = "SELECT * FROM games";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM games WHERE developer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Games</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .game-item { cursor: pointer; margin: 5px 0; padding: 10px; background: #f4f4f4; border: 1px solid #ddd; }
        .edit-form { display: none; padding: 10px; border-top: 1px solid #ddd; background: #fafafa; }
        .edit-form input, .edit-form textarea { width: 100%; margin: 5px 0; border: 1px solid}
        .toggle-button { cursor: pointer; color: blue; text-decoration: none; }
    </style>
    <script>
        function toggleForm(gameId) {
            var form = document.getElementById("edit-form-" + gameId);
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>
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
            
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'developer' || $_SESSION['role'] === 'admin')): ?>
                <li><a href="add_game.php">Add New Game</a></li>
                <li><a href="manage_games.php">Dev Tools</a></li>
            <?php endif; ?>

            <li><a href="logout.php">Logout</a></li>
        </ul>
        <p>Welcome, <?php echo $_SESSION['name'] ?></p>
    </nav>
    <br>
    <div class="manage-games">
        <h2><?php echo $role === 'admin' ? "All Games in Store" : "Your Games"; ?></h2>
        <br>
        <?php while ($game = $result->fetch_assoc()): ?>
            <div class="game-item">
                <span class="toggle-button" onclick="toggleForm(<?php echo $game['game_id']; ?>)">
                    <?php echo htmlspecialchars($game['title']); ?>
                </span>

                <div class="edit-form" id="edit-form-<?php echo $game['game_id']; ?>">
                    <form method="POST" action="manage_games.php">
                        <input type="hidden" name="game_id" value="<?php echo $game['game_id']; ?>">

                        <label>Title:</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($game['title']); ?>" required>

                        <label>Description:</label>
                        <textarea name="description" rows="3" required><?php echo htmlspecialchars($game['description']); ?></textarea>

                        <label>Price (USD):</label>
                        <input type="number" name="price" value="<?php echo $game['price']; ?>" step="0.01" required>

                        <label>Discount (%):</label>
                        <input type="number" name="discount" value="<?php echo $game['discount']; ?>" step="1" min="0" max="100">

                        <button type="submit" name="update" class="update-btn">Update Game</button>
                        <button type="submit" name="delete" class="delete-btn" onclick="return confirm('Are you sure you want to delete this game?');">Delete Game</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
