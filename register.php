<?php
require 'db.php'; // podłączamy bazę (tam też startuje sesja)
$error = '';
$success = '';

// jeśli formularz został wysłany (metoda POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); // trim usuwa spacje z początku i końca
    $password = $_POST['password'];

    // sprawdzamy czy pola nie są puste
    if (!empty($username) && !empty($password)) {
        
        // hashujemy hasło - NIGDY nie trzymamy hasła w bazie jako normalny tekst
        // PASSWORD_DEFAULT sam wybiera bezpieczny algorytm (obecnie to bcrypt)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // zapytanie przygotowane z "?" zamiast wartości - ochrona przed SQL injection
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password); // "ss" = dwa stringi (string, string)
        
        if ($stmt->execute()) {
            $success = "Rejestracja zakończona sukcesem! <a href='login.php'>Zaloguj się</a>";
        } else {
            // jeśli taki username już jest w bazie (UNIQUE), zapytanie się nie wykona - łapiemy to tutaj
            $error = "Taki użytkownik już istnieje.";
        }
        $stmt->close();
    } else {
        $error = "Wypełnij wszystkie pola.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Rejestracja</h3>
        
        <?php if($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
        <?php if($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nazwa użytkownika</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Hasło</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Zarejestruj się</button>
        </form>
        <div class="mt-3 text-center">
            Masz już konto? <a href="login.php">Zaloguj się</a>
        </div>
    </div>
</body>
</html>
