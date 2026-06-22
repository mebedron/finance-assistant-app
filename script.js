let expenseChartInstance = null; // tutaj trzymamy aktualny wykres, żeby później można było go zniszczyć przed ponownym rysowaniem

// główna funkcja - pobiera transakcje z serwera i aktualizuje całą stronę
function loadTransactions() {
    fetch('get.php') // wysyłamy zapytanie GET do get.php
    .then(response => response.json()) // zamieniamy odpowiedź serwera (tekst JSON) na normalny obiekt JS
    .then(data => {
        document.getElementById('totalBalance').innerText = data.balance.toFixed(2); // toFixed(2) - zaokrąglenie do 2 miejsc, dla pieniędzy

        const tbody = document.getElementById('transactionList');
        tbody.innerHTML = ''; // czyścimy tabelę przed ponownym wypełnieniem
        const filterValue = document.getElementById('filterType').value;

        let expensesData = {}; // tutaj zbieramy sumy wydatków po kategoriach - dla wykresu

        if(data.transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Brak operacji. Dodaj swoją pierwszą transakcję.</td></tr>';
        }

        data.transactions.forEach(t => {
            // zbieramy dane do wykresu ze WSZYSTKICH wydatków, filtr na to nie wpływa
            if (t.type === 'expense') {
                expensesData[t.category] = (expensesData[t.category] || 0) + parseFloat(t.amount);
            }

            // jeśli wybrany jest filtr i typ transakcji się nie zgadza - pomijamy ten wiersz w tabeli
            if (filterValue !== 'all' && t.type !== filterValue) return;

            const isIncome = t.type === 'income';
            
            // przygotowujemy badge (znaczek) dla typu transakcji
            const typeBadge = isIncome 
                ? '<span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="bi bi-arrow-up-circle-fill me-1"></i> Dochód</span>' 
                : '<span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill"><i class="bi bi-arrow-down-circle-fill me-1"></i> Wydatek</span>';
            
            const amountStyle = isIncome ? 'text-success' : 'custom-brand-text'; 
            const sign = isIncome ? '+' : '-';

            // składamy jeden wiersz tabeli jako normalny string html
            const row = `<tr>
                <td class="text-muted py-3">${t.date}</td>
                <td class="py-3">${typeBadge}</td>
                <td class="fw-medium py-3"><i class="bi bi-tag text-muted me-2"></i>${t.category}</td>
                <td class="fw-bold fs-6 py-3 ${amountStyle}">${sign}${t.amount} zł</td>
                <td class="py-3 text-end">
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteTransaction(${t.id})" title="Usuń">
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            </tr>`;
            
            tbody.innerHTML += row; // dodajemy wiersz do tabeli
        });

        updateChart(expensesData); // aktualizujemy wykres z nowymi danymi
    })
    .catch(error => console.error('Błąd:', error));
}

// rysuje/przerysowuje wykres wydatków po kategoriach
function updateChart(data) {
    const ctx = document.getElementById('expenseChart').getContext('2d');
    
    // jeśli wykres był już wcześniej narysowany - usuwamy go
    // bez tego Chart.js będzie rysował wykresy jeden na drugim przy każdej aktualizacji
    if (expenseChartInstance) {
        expenseChartInstance.destroy();
    }

    const categories = Object.keys(data); // nazwy kategorii
    const amounts = Object.values(data); // sumy po kategoriach

    expenseChartInstance = new Chart(ctx, {
        type: 'doughnut', // wykres kołowy (pierścieniowy)
        data: {
            labels: categories.length > 0 ? categories : ['Brak danych'], // jeśli danych nie ma - pokazujemy zastępczy tekst
            datasets: [{
                data: amounts.length > 0 ? amounts : [1],
                backgroundColor: amounts.length > 0 ? [
                    '#0d6efd', '#6f42c1', '#d63384', '#fd7e14', '#0dcaf0', '#20c997'
                ] : ['#e9ecef'],
                borderWidth: 0,
                hoverOffset: 5
            }]
        },
        options: { 
            responsive: true,
            cutout: '75%', 
            plugins: {
                legend: { 
                    position: 'bottom', 
                    labels: { 
                        usePointStyle: true, 
                        padding: 20,
                        color: '#212529' 
                    } 
                }
            }
        }
    });
}

// usuwanie transakcji po id
function deleteTransaction(id) {
    if(confirm('Czy na pewno chcesz usunąć?')) { // wbudowane okno potwierdzenia przeglądarki
        let formData = new FormData(); // FormData potrzebny żeby wysłać dane tak jak normalny formularz html
        formData.append('id', id);

        fetch('delete.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') loadTransactions(); // przeładowujemy listę po usunięciu
            else alert('Błąd: ' + data.message);
        });
    }
}

// uruchamiamy ładowanie transakcji od razu jak strona się załaduje
document.addEventListener('DOMContentLoaded', loadTransactions);

// obsługa wysłania formularza dodawania transakcji
document.getElementById('financeForm').addEventListener('submit', function(e) {
    e.preventDefault(); // blokujemy domyślne wysłanie formularza (przeładowałoby stronę)

    let submitBtn = this.querySelector('button[type="submit"]');
    let originalContent = submitBtn.innerHTML;
    submitBtn.disabled = true; // blokujemy przycisk, żeby user nie kliknął dwa razy
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Zapisywanie...';

    let formData = new FormData();
    formData.append('type', document.getElementById('type').value);
    formData.append('amount', document.getElementById('amount').value);
    formData.append('category', document.getElementById('category').value);
    formData.append('date', document.getElementById('date').value);

    fetch('add.php', { method: 'POST', body: formData })
    .then(response => response.json()) 
    .then(data => {
        if(data.status === 'success') {
            document.getElementById('financeForm').reset(); // czyścimy formularz po sukcesie
            loadTransactions(); // aktualizujemy listę i saldo
        } else alert('Błąd: ' + data.message);
    })
    .catch(error => console.error('Błąd:', error))
    .finally(() => {
        // wykonuje się zawsze - odblokowujemy przycisk z powrotem
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
    });
});
