<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';

$sql = "
    SELECT g.game_id, g.title, g.description, g.image_path, p.purchase_date, p.price_paid
    FROM purchases p
    JOIN games g ON p.game_id = g.game_id
    WHERE p.user_id = ?
    ORDER BY p.purchase_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$purchased_games = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $purchased_games[] = $row;
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Games - Game Webshop</title>
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
            
            <?php if (in_array($user_role, ['developer', 'admin'])): ?>
                <li><a href="add_game.php">Add New Game</a></li>
                <li><a href="manage_games.php">Dev Tools</a></li>
            <?php endif; ?>

            <li><a href="logout.php">Logout</a></li>
        </ul>
        <p>Welcome, <?php echo $_SESSION['name'] ?></p>
    </nav>

    <div class="my-games-container">
        <h2>My Purchased Games</h2>
        <?php if (count($purchased_games) > 0): ?>
            <?php foreach ($purchased_games as $game): ?>
                <div class="game-card-purchased">
                    <img src="<?php echo htmlspecialchars($game['image_path']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                    <div class="game-info-purchased">
                        <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                        <p><?php echo htmlspecialchars($game['description']); ?></p>
                        <p class="purchase-date">Purchased on: <?php echo date("F j, Y", strtotime($game['purchase_date'])); ?></p>
                        <p class="price-paid">Price Paid: $<?php echo number_format($game['price_paid'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You haven't purchased any games yet. <a href="browse.php">Browse Games</a> to find your next adventure!</p>
        <?php endif; ?>
    </div>
</body>
</html>
