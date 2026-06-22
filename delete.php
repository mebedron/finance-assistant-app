<?php
session_start();
require 'db.php';

if (isset($_POST['id']) && isset($_SESSION['user_id'])) {
    $id = $_POST['id'];
    $user_id = $_SESSION['user_id'];

    // WAŻNE: usuwamy tylko jeśli id się zgadza I transakcja należy do tego usera
    // bez "AND user_id = ?" każdy mógłby usunąć cudzą transakcję, po prostu zgadując id
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }

    $stmt->close();
}
$conn->close();
?>
