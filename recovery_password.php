<?php
include 'config.php';

$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $ps = $_POST['password'] ?? '';

    // Validate email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $query = "UPDATE users SET password='$ps' WHERE email='$email'";

        if (mysqli_query($connect, $query)) {
            echo "<script>
                    alert('Password updated successfully!');
                    window.location='index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Update password failed.');
                    window.location='index.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Invalid email format.');
                window.location='index.php';
              </script>";
    }
} else {
    if ($email) {
        $papar = mysqli_query($connect, "SELECT * FROM users WHERE email='$email'");

        if ($res = mysqli_fetch_array($papar)) {
            $email = $res['email'];
            $ps = $res['password'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }

        form {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            width: 350px;
            transition: transform 0.3s;
        }

        form:hover {
            transform: translateY(-5px);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #495057;
            font-size: 1.8rem;
        }

        label {
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ced4da;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #28a745;
            outline: none;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.2);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #262160;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.2s;
        }

        button:hover {
            background-color: #3a3a9e;
            transform: translateY(-2px);
        }

        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <form method="post" action="">
        <h2>Reset Password</h2>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="text" name="password" required>
        </div>
        <button type="submit" name="update">Update</button>
    </form>
</body>

</html>