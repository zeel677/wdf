<?php
session_start();

// Handle logout before sending any HTML
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    $_SESSION = [];
    session_destroy();

    // Remove the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    header("Location: login.php");
    exit;
}

// Check whether the user is logged in
if (!isset($_SESSION["user_email"])) {
    header("Location: login.php");
    exit;
}

// Get logged-in user's details
$name = $_SESSION["user_name"] ?? "User";
$email = $_SESSION["user_email"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home Page</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 30px;
        }

        .box {
            max-width: 600px;
            margin: 70px auto;
            padding: 35px;
            text-align: center;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #1e293b;
        }

        p {
            color: #475569;
            line-height: 1.6;
        }

        button {
            padding: 12px 25px;
            margin-top: 15px;
            color: white;
            background: #dc2626;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #b91c1c;
        }
    </style>
</head>

<body>

    <div class="box">

        <h1>
            Welcome,
            <?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>!
        </h1>

        <p>You have successfully logged in.</p>

        <p>
            <strong>Email:</strong>
            <?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>
        </p>

        <form action="index.php" method="POST">
            <button type="submit" name="logout" value="1">
                Logout
            </button>
        </form>

    </div>

</body>
</html>
