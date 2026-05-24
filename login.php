<?php
require_once "includes/db.php";
require_once "includes/auth.php";

// Zaten giriş yapmışsa yönlendir
if (getCurrentUser()) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: index.php");
    } else {
        header("Location: resident/index.php");
    }
    exit;
}

$error = "";
$debug = ""; // Debug mesajları için

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username !== "" && $password !== "") {
        // Debug: Kullanıcıyı kontrol et
        $checkStmt = $conn->prepare("SELECT user_id, username, role, password FROM users WHERE username = ?");
        $checkStmt->execute([$username]);
        $userCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($userCheck) {
            $verifyResult = password_verify($password, $userCheck['password']);
            if ($verifyResult) {
                if (loginUser($username, $password)) {
                    if ($_SESSION['role'] === 'admin') {
                        header("Location: index.php");
                    } else {
                        header("Location: resident/index.php");
                    }
                    exit;
                } else {
                    $error = "Oturum başlatılamadı.";
                }
            } else {
                $error = "Şifre doğrulanamadı.";
            }
        } else {
            $error = "Kullanıcı bulunamadı.";
        }
    } else {
        $error = "Lütfen tüm alanları doldurun.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | Site Yönetimi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1d4ed8, #38bdf8);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255,255,255,0.96);
            padding: 48px;
            border-radius: 24px;
            box-shadow: 0 24px 48px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header i {
            font-size: 48px;
            color: #1d4ed8;
            margin-bottom: 16px;
        }
        .login-header h1 {
            font-size: 26px;
            color: #0f172a;
        }
        .login-header p {
            color: #64748b;
            margin-top: 6px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px 14px 14px 42px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
        }
        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
        }
        .btn-login {
            width: 100%;
            background: #111827;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: #1f2937;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        .footer-note {
            text-align: center;
            margin-top: 24px;
            color: #64748b;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <i class="fa-solid fa-building"></i>
        <h1>Site Yönetimi</h1>
        <p>Giriş yaparak panele erişin</p>
    </div>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Kullanıcı Adı</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Kullanıcı adınız" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label>Şifre</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Şifreniz" required>
            </div>
        </div>

        <button type="submit" class="btn-login">Giriş Yap</button>
    </form>

    <div class="footer-note">
        Varsayılan Yönetici: <strong>admin</strong> / <strong>admin123</strong>
    </div>
</div>

</body>
</html>
