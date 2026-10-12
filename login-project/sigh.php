<?php
session_start();

$csvFile = __DIR__ . "/users.csv";
$error = "";

if (!file_exists($csvFile)) {
    $file = fopen($csvFile, "w");
    fputcsv($file, ["name", "email", "password_hash"]);
    fclose($file);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid name and email.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $file = fopen($csvFile, "c+");

        if (!$file) {
            $error = "Could not open users.csv.";
        } else {
            flock($file, LOCK_EX);
            rewind($file);

            $users = [];
            while (($row = fgetcsv($file)) !== false) {
                $users[] = $row;
            }

            if (empty($users)) {
                $users[] = ["name", "email", "password_hash"];
            }

            $exists = false;
            foreach (array_slice($users, 1) as $row) {
                if (isset($row[1]) && strtolower($row[1]) === $email) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $error = "This email is already registered. Please log in.";
            } else {
                fseek($file, 0, SEEK_END);
                fputcsv($file, [
                    $name,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT)
                ]);
                fflush($file);
            }

            flock($file, LOCK_UN);
            fclose($file);

            if (!$exists && $error === "") {
                header("Location: login.php?registered=1");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account</title>
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
    </style>
</head>
<body>
<div class="box">
    <h2>Create Account</h2>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="sigh.php">
        <label>Full Name</label>
        <input type="text" name="name" required maxlength="100">

        <label>Email</label>
        <input type="email" name="email" required maxlength="254">

        <label>Password (minimum 8 characters)</label>
        <input type="password" name="password"
               required minlength="8" autocomplete="new-password">

        <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account?
        <a href="login.php">Login</a>
    </p>
</div>
</body>
</html>
