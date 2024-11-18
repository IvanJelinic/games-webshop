<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GameHive</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login"> 
    <div>
        <div class="login-container">
            <h2>Login to Your Account</h2>
            <form method="POST" action="authenticate.php">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Login</button>
                <a href="register.php" class="register-link">Don't have an account? Register here</a>
            </form>
        </div>
    </div>
</body>
</html>
