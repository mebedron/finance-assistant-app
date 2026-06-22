<?php
// jeśli sesja jeszcze nie została uruchomiona - uruchamiamy ją
// (to sprawdzenie jest potrzebne, żeby PHP nie wyrzucał błędu, gdy sesja już była wcześniej zaczęta)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// dane do połączenia z bazą danych
$host = 'localhost';
$user = 'root'; 
$password = ''; 
$dbname = 'finance_db';

// tworzymy połączenie przez mysqli
$conn = new mysqli($host, $user, $password, $dbname);

// jeśli połączenie się nie powiodło - zatrzymujemy skrypt i wyświetlamy błąd
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}
?>
