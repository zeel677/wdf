<?php
session_start();

if (isset($_SESSION["user_email"])) {
    header("Location: index.php");
    exit;
}

$csvFile = __DIR__ . "/users.csv";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $userFound = false;

    if (file_exists($csvFile) && ($file = fopen($csvFile, "r"))) {
        flock($file, LOCK_SH);
        fgetcsv($file); // Skip the CSV header

        while (($row = fgetcsv($file)) !== false) {
            if (
                isset($row[0], $row[1], $row[2]) &&
                strtolower($row[1]) === $email &&
                password_verify($password, $row[2])
            ) {
                $userFound = true;
                $name = $row[0];
                break;
            }
        }

        flock($file, LOCK_UN);
        fclose($file);
    }

    if ($userFound) {
        session_regenerate_id(true);
        $_SESSION["user_email"] = $email;
        $_SESSION["user_name"] = $name;

        header("Location: index.php");
        exit;
    } else {
        $error = "Incorrect email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 30px;
        }
        .box {
            max-width: 380px;
            margin: 50px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0001;
        }
        input, button {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin: 8px 0 14px;
        }
        button {
            background: #2563eb;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        .error { color: #c62828; }
        .success { color: #15803d; }
    </style>
</head>
<body>
<div class="box">
    <h2>Login</h2>

    <?php if (isset($_GET["registered"])): ?>
        <p class="success">Account created! Please log in.</p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Email</label>
        <input type="email" name="email" required autocomplete="email">

        <label>Password</label>
        <input type="password" name="password" required
               autocomplete="current-password">

        <button type="submit">Login</button>
    </form>

    <p>New user? <a href="sigh.php">Create an account</a></p>
</div>
</body>
</html>
