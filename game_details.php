<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (!isset($_GET['game_name'])) {
    die("Game name not provided!");
}

$game_name = htmlspecialchars($_GET['game_name']);
$redirect = $_GET['game_name'];

$api_key = '77823c9d7a3e48b29ecaf05aea05c730';
$search_url = "https://api.rawg.io/api/games?key=$api_key&search=" . urlencode($game_name);

$response = file_get_contents($search_url);
if ($response === false) {
    die("Error fetching game details!");
}

$search_results = json_decode($response, true);

if (empty($search_results['results'])) {
    die("No games found with the name: " . htmlspecialchars($game_name));
}

$game_data = $search_results['results'][0];

$game_id = $game_data['id'];
$details_url = "https://api.rawg.io/api/games/$game_id?key=$api_key";
$details_response = file_get_contents($details_url);
if ($details_response === false) {
    die("Error fetching detailed game information!");
}

$game_details = json_decode($details_response, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (game_name, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $game_name, $user_id, $comment);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: game_details.php?game_name=$redirect");
            exit();
        } else {
            echo "<script>alert('Failed to add comment.');</script>";
        }

        $stmt->close();
    }
}

$comment_query = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.name 
                                 FROM comments c 
                                 JOIN users u ON c.user_id = u.user_id 
                                 WHERE c.game_name = ? 
                                 ORDER BY c.created_at DESC");
$comment_query->bind_param("s", $game_name);
$comment_query->execute();
$comments_result = $comment_query->get_result();

$stmt = $conn->prepare("SELECT * FROM games WHERE title = ?");
$stmt->bind_param("s", $redirect);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();
$stmt->close();

if ($game) {
    $game_buy_id = $game['game_id'];
} else {
    echo "<h2>Game not found.</h2>";
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game_details['name']); ?> - Game Details</title>
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

    <div class="game-details-container">
        <div class="game-header">
            <h1><?php echo htmlspecialchars($game_details['name']); ?></h1>
            <img src="<?php echo htmlspecialchars($game_details['background_image']); ?>" alt="<?php echo htmlspecialchars($game_details['name']); ?>">
            <div class="game-details-info">
                <?php echo $game_details['description']; ?>
                <div class="game-metadata">
                    <h3>Details:</h3>
                    <p><strong>Released:</strong> <?php echo htmlspecialchars($game_details['released']); ?></p>
                    <p><strong>Rating:</strong> <?php echo htmlspecialchars($game_details['rating']); ?> / 5</p>
                    <p><strong>Genres:</strong> 
                        <?php 
                        $genres = array_map(fn($genre) => $genre['name'], $game_details['genres']);
                        echo htmlspecialchars(implode(", ", $genres)); 
                        ?>
                    </p>
                </div>
                <button class="buy-button" onclick="window.location.href='buy.php?game_id=<?php echo $game_buy_id; ?>'">Buy Now</button>
            </div>
        </div>
    </div>
    <div class="comment-section">
        <h2>Comments</h2>
        <form method="POST" class="comment-form">
            <textarea name="comment" rows="4" placeholder="Leave a comment..." required></textarea>
            <button type="submit">Post Comment</button>
        </form>
        <div class="comments-list">
            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                <div class="comment-card">
                    <p class="comment-user"><?php echo htmlspecialchars($comment['name']); ?>:</p>
                    <p class="comment-text"><?php echo htmlspecialchars($comment['comment']); ?></p>
                    <p class="comment-date"><?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?></p>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <form method="POST" action="delete_comment.php" style="display: inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <button type="submit" class="delete-button" style="background-color:#ff4d4d;">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
