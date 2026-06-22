<?php
require 'db.php';
$error = '';

// jeśli użytkownik jest już zalogowany - od razu przenosimy go na stronę główną
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // szukamy użytkownika po nazwie, pobieramy jego id i hash hasła
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // password_verify porównuje wpisane hasło z hashem z bazy
        // (to jest para do password_hash z register.php)
        if (password_verify($password, $user['password'])) {
            // hasło prawidłowe - zapisujemy dane w sesji, teraz user jest "zalogowany"
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit; // exit jest tu konieczny, inaczej kod po header i tak by się wykonał
        } else {
            $error = "Nieprawidłowe hasło.";
        }
    } else {
        $error = "Nie znaleziono użytkownika.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Logowanie</h3>
        
        <?php if($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nazwa użytkownika</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Hasło</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Zaloguj się</button>
        </form>
        <div class="mt-3 text-center">
            Nie masz konta? <a href="register.php">Zarejestruj się</a>
        </div>
    </div>
</body>
</html>
