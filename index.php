<?php
session_start();
// jeśli user nie jest zalogowany - od razu wyrzucamy go na stronę logowania
// (bez tego sprawdzenia każdy mógłby otworzyć stronę główną bezpośrednim linkiem)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDV FinanceApp</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom mb-5 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold custom-brand-text" href="#">
                <i class="bi bi-wallet2 text-primary me-2"></i> CDV FinanceApp
            </a>
            <div class="d-flex align-items-center">
                <!-- htmlspecialchars - ochrona przed XSS, żeby username nie wykonał się jako kod HTML/JS -->
                <span class="me-4 text-muted d-none d-md-inline">Witaj, <b class="custom-brand-text"><?= htmlspecialchars($_SESSION['username']) ?></b></span>
                <a href="logout.php" class="btn btn-light rounded-pill border px-4">
                    <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Wyloguj</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <!-- formularz dodawania nowej transakcji, obsługa w script.js -->
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2 text-primary"></i>Nowa operacja</h5>
                    <form id="financeForm">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Typ transakcji</label>
                            <select class="form-select" id="type" required>
                                <option value="income">💰 Dochód</option>
                                <option value="expense">🛒 Wydatek</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Kwota (PLN)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-cash"></i></span>
                                <input type="number" step="0.01" class="form-control border-start-0" id="amount" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Kategoria</label>
                            <input type="text" class="form-control" id="category" placeholder="Np. Jedzenie, Czynsz..." required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-semibold">Data</label>
                            <input type="date" class="form-control" id="date" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-save me-2"></i> Zapisz operację</button>
                    </form>
                </div>

                <!-- tutaj Chart.js rysuje wykres wydatków po kategoriach -->
                <div class="card p-4">
                    <h5 class="fw-bold text-center mb-4"><i class="bi bi-pie-chart me-2 text-primary"></i>Struktura wydatków</h5>
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- saldo aktualizuje się przez JS po pobraniu danych z get.php -->
                <div class="card balance-card p-4 mb-4 d-flex flex-row justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Dostępne środki</h6>
                        <h1 class="fw-bold m-0"><span id="totalBalance">0.00</span> PLN</h1>
                    </div>
                    <div class="fs-1 opacity-50">
                        <i class="bi bi-credit-card-fill"></i>
                    </div>
                </div>
                
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0"><i class="bi bi-card-list me-2 text-primary"></i>Historia operacji</h5>
                        <!-- filtr zmienia widok listy, obsługa w loadTransactions() w script.js -->
                        <select id="filterType" class="form-select w-auto cursor-pointer" style="min-width: 140px;" onchange="loadTransactions()">
                            <option value="all">Wszystkie</option>
                            <option value="income">Zyski</option>
                            <option value="expense">Wydatki</option>
                        </select>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless">
                            <thead class="text-muted small border-bottom">
                                <tr>
                                    <th class="py-3">DATA</th>
                                    <th class="py-3">TYP</th>
                                    <th class="py-3">KATEGORIA</th>
                                    <th class="py-3">KWOTA</th>
                                    <th class="py-3 text-end">AKCJA</th>
                                </tr>
                            </thead>
                            <!-- ta tabela jest wypełniana przez JS, w html jest na początku pusta -->
                            <tbody id="transactionList">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
