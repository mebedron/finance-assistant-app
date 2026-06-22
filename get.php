<?php
session_start();
require 'db.php';

// jeśli user nie jest zalogowany - oddajemy puste dane i wychodzimy
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["balance" => 0, "transactions" => []]);
    exit;
}

$user_id = $_SESSION['user_id'];

// ważne: bierzemy tylko transakcje TEGO usera (WHERE user_id = ?)
// sortujemy najpierw po dacie (najnowsze na górze), potem po id jeśli daty się powtarzają
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC, id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
$totalBalance = 0;

// przechodzimy po wszystkich wierszach wyniku i od razu liczymy saldo
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
    if ($row['type'] === 'income') {
        $totalBalance += $row['amount']; // dochód - dodajemy
    } else {
        $totalBalance -= $row['amount']; // wydatek - odejmujemy
    }
}

// odsyłamy wszystko jednym JSON-em, żeby JS na froncie mógł to odczytać
echo json_encode([
    "balance" => $totalBalance,
    "transactions" => $transactions
]);

$stmt->close();
$conn->close();
?>
