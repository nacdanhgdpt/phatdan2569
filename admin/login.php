<!-- filepath: f:\wampp\www\phatdan2569\login.php -->
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $usersFile = __DIR__ . '/../users.csv'; // Ensure the correct path to users.csv
    if (!file_exists($usersFile)) {
        $error = 'User data file not found. Please contact the administrator.';
    } else {
        // Load users from CSV
        $users = array_map('str_getcsv', file($usersFile));
        foreach ($users as $user) {
            [$storedUsername, $storedPasswordHash] = $user;

            // Check credentials
            if ($username === $storedUsername && password_verify($password, $storedPasswordHash)) {
                $_SESSION['logged_in'] = true;
                header('Location: index.php');
                exit;
            }
        }

        $error = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="common-style.css">
    <link rel="stylesheet" href="login-style.css">
</head>
<body>
    <form method="POST" action="login.php">
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>