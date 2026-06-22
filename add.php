<?php
session_start();
require 'db.php';

// bez logowania nic nie dodajemy
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Brak autoryzacji."]);
    exit;
}

// sprawdzamy czy wszystkie 4 pola przyszły z formularza
if (isset($_POST['type'], $_POST['amount'], $_POST['category'], $_POST['date'])) {
    
    // user_id bierzemy z sesji, a NIE z formularza - dzięki temu user nie może
    // podrobić zapytania i dodać transakcji w imieniu kogoś innego
    $user_id = $_SESSION['user_id'];
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, category, date) VALUES (?, ?, ?, ?, ?)");
    // typy parametrów po kolei: i-int, s-string, d-double(liczba z przecinkiem, dla pieniędzy), s-string, s-string
    $stmt->bind_param("isdss", $user_id, $type, $amount, $category, $date);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    $stmt->close();
}
$conn->close();
?>
