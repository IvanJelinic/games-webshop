<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 100;

$sql = "SELECT * FROM games WHERE (title LIKE ? OR description LIKE ?)";
if (!empty($category)) {
    $sql .= " AND category = ?";
}
$sql .= " AND price BETWEEN ? AND ?";
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($category)) {
    $stmt->bind_param("sssdd", $searchParam, $searchParam, $category, $min_price, $max_price);
} else {
    $stmt->bind_param("ssdd", $searchParam, $searchParam, $min_price, $max_price);
}

$searchParam = "%" . $search . "%";
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Games</title>
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
            
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'developer' || $_SESSION['role'] === 'admin')): ?>
                <li><a href="add_game.php">Add New Game</a></li>
                <li><a href="manage_games.php">Dev Tools</a></li>
            <?php endif; ?>

            <li><a href="logout.php">Logout</a></li>
        </ul>
        <p>Welcome, <?php echo $_SESSION['name'] ?></p>
    </nav>

    <div class="filter-form">
        <h2 style="margin-top:10px">Browse Games</h2>
        <form method="GET" action="browse.php">
            <input type="text" name="search" placeholder="Search by title or description" value="<?php echo htmlspecialchars($search); ?>">
            Min price:
            <input type="number" name="min_price" placeholder="Min Price" value="<?php echo $min_price; ?>" step="0.01">
            Max price:
            <input type="number" name="max_price" placeholder="Max Price" value="<?php echo $max_price; ?>" step="0.01">
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="game-list">
        <div class="games-container-browse">
            <?php while ($game = $result->fetch_assoc()): ?>
                <div class="game-card-horizontal">
                    <img src="<?php echo $game['image_path']; ?>" alt="<?php echo $game['title']; ?>" class="game-image-horizontal">
                    <div class="game-info">
                        <h3><?php echo $game['title']; ?></h3>
                        <p><?php echo $game['description']; ?></p>
                        <p class="price" style="margin-top:5%">
                            <?php
                            if ($game['discount'] > 0) {
                                $discounted_price = $game['price'] * (1 - $game['discount'] / 100);
                                echo "<span class='original-price'>$" . $game['price'] . "</span> $<strong>" . number_format($discounted_price, 2) . "</strong>";
                            } else {
                                echo "$" . number_format($game['price'], 2);
                            }
                            ?>
                        </p>
                        <button class="button-browse" onclick="location.href='buy.php?game_id=<?php echo $game['game_id']; ?>'">Buy Now</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
