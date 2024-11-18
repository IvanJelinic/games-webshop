<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;
$user_role = $_SESSION['role'] ?? '';

$purchase_success = false;
$error_message = '';

if ($game_id <= 0) {
    $error_message = "Invalid game ID.";
} else {
    $sql = "SELECT * FROM games WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $game_result = $stmt->get_result();

    if ($game_result->num_rows == 0) {
        $error_message = "Game not found.";
    } else {
        $game = $game_result->fetch_assoc();

        $discounted_price = $game['price'] - ($game['price'] * ($game['discount'] / 100));

        $sql = "SELECT * FROM purchases WHERE user_id = ? AND game_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $game_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "You have already purchased this game.";
        }

        $stmt->close();
    }

    if (empty($error_message) && $_SERVER["REQUEST_METHOD"] == "POST") {
        $sql = "INSERT INTO purchases (user_id, game_id, purchase_date, price_paid) VALUES (?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iid", $user_id, $game_id, $discounted_price);

        if ($stmt->execute()) {
            $purchase_success = true;
        } else {
            $error_message = "Error: Could not complete the purchase.";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Purchase - Game Webshop</title>
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

    <div class="purchase-container">
        <h2>Confirm Purchase</h2>
        <div class="game-info">
            <img src="<?php echo htmlspecialchars($game['image_path']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="game-image">
            <div class="game-details">
                <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                <p><?php echo htmlspecialchars($game['description']); ?></p>
                <p>Original Price: $<?php echo number_format($game['price'], 2); ?></p>
                <p>Discounted Price: $<?php echo number_format($discounted_price, 2); ?></p>
            </div>
        </div>
        
        <form method="POST" action="buy.php?game_id=<?php echo $game_id; ?>">
            <button type="submit">Confirm Purchase</button>
            <a href="index.php" class="cancel-button">Cancel</a>
        </form>
    </div>

    <?php if ($purchase_success): ?>
        <div id="successModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <p>Purchase successful! You can now access this game in 'My Games'.</p>
                <button class="modal-button" onclick="redirectToDashboard()">Go to Dashboard</button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="error-message" style="color: red; text-align: center; margin-top: 20px;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <script>
        function openModal() {
            var modal = document.getElementById("successModal");
            if (modal) {
                modal.style.display = "block";
            }
        }

        function closeModal() {
            var modal = document.getElementById("successModal");
            if (modal) {
                modal.style.display = "none";
            }
        }

        function redirectToDashboard() {
            window.location.href = "index.php";
        }

        <?php if ($purchase_success): ?>
            window.onload = function() {
                openModal();
            }
        <?php endif; ?>

        window.onclick = function(event) {
            var modal = document.getElementById("successModal");
            if (modal && event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
