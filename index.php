<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'];

$sql = "SELECT * FROM games ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Webshop</title>
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

    <div class="game-container">
        <?php while($game = $result->fetch_assoc()): 
            
            $original_price = $game['price'];
            $discount = $game['discount'];
            $discounted_price = $original_price;

            if ($discount > 0) {
                $discounted_price = $original_price - ($original_price * ($discount / 100));
            }
        ?>
            <div class="game-card">
                <a href="game_details.php?game_name=<?php echo urlencode($game['title']); ?>" class="game-link">
                    <img src="<?php echo $game['image_path']; ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="game-image-dashboard">
                    <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                    <p><?php echo htmlspecialchars($game['description']); ?></p>
                    <br>

                    <?php if ($discount > 0): ?>
                        <p class="original-price">$<?php echo number_format($original_price, 2); ?></p>
                        <p class="discounted-price">$<?php echo number_format($discounted_price, 2); ?></p>
                        <p class="discount-label">-<?php echo $discount; ?>% Off</p>
                    <?php else: ?>
                        <p class="price">$<?php echo number_format($original_price, 2); ?></p>
                    <?php endif; ?>
                </a>
                <button onclick="location.href='buy.php?game_id=<?php echo $game['game_id'];?>'">Buy Now</button>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
