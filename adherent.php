<?php
require_once 'auth/auth.php';
require_once 'config/database.php';

// Initialiser l'objet Auth
$auth = new Auth();

// Vérifier si l'utilisateur est connecté et est un adhérent
if (!$auth->isLoggedIn()) {
    header('Location: login.php?error=' . urlencode('Vous devez être connecté pour accéder à cette page'));
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$currentUser = $auth->getCurrentUser();

// Définir le titre de la page
$pageTitle = "Tableau de Bord Adhérent";


?>

<!-- Styles spécifiques à la page -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    :root {
        --primary-color: #362828;
        --secondary-color: #362828;
        --background-color: #AF9284;
        --text-color: #0F090B;
        --transition-speed: 0.3s;
    }
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background-color: var(--background-color);
        color: var(--text-color);
        transition: background-color var(--transition-speed);
    }
    .container {
        display: flex;
        min-height: 100vh;
    }
    .sidebar {
        width: 250px;
        background-color: var(--secondary-color);
        color: white;
        padding: 20px;
        transition: width var(--transition-speed);
    }
    .sidebar.collapsed {
        width: 60px;
    }
    .sidebar h2 {
        margin-bottom: 20px;
        font-size: 1.5em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar ul {
        list-style-type: none;
        padding: 0;
    }
    .sidebar ul li {
        margin-bottom: 10px;
    }
    .sidebar ul li a {
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 5px;
        transition: background-color var(--transition-speed);
    }
    .sidebar ul li a:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    .sidebar ul li a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    .main-content {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
    }
    h1 {
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 10px;
    }
    .card {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: box-shadow var(--transition-speed);
    }
    .card:hover {
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }
    .btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color var(--transition-speed);
    }
    .btn:hover {
        background-color: #2980b9;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    th {
        background-color: var(--primary-color);
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .search-bar {
        margin-bottom: 20px;
    }
    .search-bar input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    .pagination button {
        margin: 0 5px;
        padding: 5px 10px;
        border: 1px solid #ddd;
        background-color: white;
        cursor: pointer;
    }
    .pagination button.active {
        background-color: var(--primary-color);
        color: white;
    }
    #notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border-radius: 5px;
        display: none;
        z-index: 1000;
    }
    .chart-container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
    }
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    .stat-card {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform var(--transition-speed);
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-card h3 {
        margin: 0;
        color: var(--primary-color);
    }
    .stat-card p {
        font-size: 2em;
        margin: 10px 0;
        font-weight: bold;
    }
</style>

<div class="container">
    <div class="sidebar" id="sidebar">
        <h2>Menu Adhérent</h2>
        <ul>
            <li><a href="#" onclick="showSection('dashboard')"><i class="fas fa-tachometer-alt"></i> <span>Tableau de Bord</span></a></li>
            <li><a href="#" onclick="showSection('borrowed')"><i class="fas fa-book"></i> <span>Livres Empruntés</span></a></li>
            <li><a href="#" onclick="showSection('catalogue')"><i class="fas fa-list"></i> <span>Catalogue</span></a></li>
            <li><a href="#" onclick="showSection('history')"><i class="fas fa-history"></i> <span>Historique</span></a></li>
            <li><a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span>Se Déconnecter</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <div id="dashboard" class="section">
            <h1>Bienvenue <?php echo htmlspecialchars($currentUser['nom']); ?> dans votre espace adhérent!</h1>
            <div class="stats">
                <div class="stat-card">
                    <h3>Livres Empruntés</h3>
                    <p id="borrowedBooks">0</p>
                </div>
                <div class="stat-card">
                    <h3>Jours Restants</h3>
                    <p id="remainingDays">0</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="statsChart"></canvas>
            </div>
        </div>
        <div id="borrowed" class="section" style="display:none;">
            <h1>Vos Livres Empruntés</h1>
            <div class="card">
                <h2>Liste des Emprunts</h2>
                <table id="borrowedList">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Date d'emprunt</th>
                            <th>Date de retour</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div id="catalogue" class="section" style="display:none;">
            <h1>Catalogue de la Bibliothèque</h1>
            <div class="card">
                <h2>Rechercher un Livre</h2>
                <div class="search-bar">
                    <input type="text" id="searchBooks" placeholder="Rechercher un livre...">
                </div>
                <table id="booksList">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Disponibilité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="pagination" id="booksPagination"></div>
            </div>
        </div>
        <div id="history" class="section" style="display:none;">
            <h1>Historique des Emprunts</h1>
            <div class="card">
                <h2>Vos Emprunts Passés</h2>
                <table id="historyList">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Date d'emprunt</th>
                            <th>Date de retour</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="pagination" id="historyPagination"></div>
            </div>
        </div>
    </div>
</div>
<div id="notification"></div>

<script>
    // Fonction pour afficher une section
    function showSection(sectionId) {
        document.querySelectorAll('.section').forEach(section => {
            section.style.display = 'none';
        });
        document.getElementById(sectionId).style.display = 'block';
    }

    // Fonction pour formater une date
    function formatDate(date) {
        return new Date(date).toLocaleDateString('fr-FR');
    }

    // Fonction pour afficher une notification
    function showNotification(message) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    // Récupérer les données des emprunts en cours
    let borrowedBooks = [];
    let catalogueBooks = [];
    let borrowHistory = [];

    // Charger les données depuis le serveur
    function loadData() {
        // Charger les emprunts en cours
        fetch('mes-emprunts.php?format=json')
            .then(response => response.json())
            .then(data => {
                borrowedBooks = data;
                updateDashboard();
                renderBorrowedBooks();
            })
            .catch(error => console.error('Erreur lors du chargement des emprunts:', error));

        // Charger le catalogue
        fetch('catalogue.php?format=json')
            .then(response => response.json())
            .then(data => {
                catalogueBooks = data;
                renderCatalogue();
            })
            .catch(error => console.error('Erreur lors du chargement du catalogue:', error));

        // Charger l'historique des emprunts
        fetch('mes-emprunts.php?format=json&history=1')
            .then(response => response.json())
            .then(data => {
                borrowHistory = data;
                updateDashboard();
                renderHistory();
            })
            .catch(error => console.error('Erreur lors du chargement de l\'historique:', error));
    }

    // Mettre à jour le tableau de bord
    function updateDashboard() {
        document.getElementById('borrowedBooks').textContent = borrowedBooks.length;
        const today = new Date();
        const remainingDays = borrowedBooks.reduce((acc, book) => {
            const returnDate = new Date(book.date_retour_prevue);
            const diff = Math.ceil((returnDate - today) / (1000 * 60 * 60 * 24));
            return acc + (diff > 0 ? diff : 0);
        }, 0);
        document.getElementById('remainingDays').textContent = remainingDays;
        updateStatsChart();
    }

    // Mettre à jour le graphique des statistiques
    function updateStatsChart() {
        const ctx = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Empruntés', 'Historique'],
                datasets: [{
                    label: 'Nombre de Livres',
                    data: [borrowedBooks.length, borrowHistory.length],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Afficher les livres empruntés
    function renderBorrowedBooks() {
        const tbody = document.querySelector('#borrowedList tbody');
        tbody.innerHTML = '';
        borrowedBooks.forEach(book => {
            const row = tbody.insertRow();
            row.insertCell(0).textContent = book.titre;
            row.insertCell(1).textContent = book.auteur;
            row.insertCell(2).textContent = formatDate(book.date_emprunt);
            row.insertCell(3).textContent = formatDate(book.date_retour_prevue);
            const actionsCell = row.insertCell(4);
            actionsCell.innerHTML = `
                <button onclick="extendLoan(${book.id})" class="btn"><i class="fas fa-calendar-plus"></i> Prolonger</button>
            `;
        });
    }

    // Prolonger un emprunt
    function extendLoan(bookId) {
        fetch('emprunter.php?action=extend&id=' + bookId, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Emprunt prolongé de 7 jours.');
                loadData(); // Recharger les données
            } else {
                showNotification('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la prolongation:', error);
            showNotification('Erreur lors de la prolongation de l\'emprunt.');
        });
    }

    // Afficher le catalogue
    function renderCatalogue(page = 1, searchTerm = '') {
        const itemsPerPage = 5;
        const filteredBooks = catalogueBooks.filter(book => 
            book.titre.toLowerCase().includes(searchTerm.toLowerCase()) || 
            book.auteur.toLowerCase().includes(searchTerm.toLowerCase())
        );
        const totalPages = Math.ceil(filteredBooks.length / itemsPerPage);
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const booksToDisplay = filteredBooks.slice(startIndex, endIndex);

        const tbody = document.querySelector('#booksList tbody');
        tbody.innerHTML = '';
        booksToDisplay.forEach(book => {
            const row = tbody.insertRow();
            row.insertCell(0).textContent = book.titre;
            row.insertCell(1).textContent = book.auteur;
            row.insertCell(2).textContent = book.quantite_disponible > 0 ? 'Disponible' : 'Indisponible';
            const actionsCell = row.insertCell(3);
            actionsCell.innerHTML = `
                <button onclick="borrowBook(${book.id})" class="btn" ${book.quantite_disponible <= 0 ? 'disabled' : ''}><i class="fas fa-book"></i> Emprunter</button>
            `;
        });

        renderPagination('booksPagination', totalPages, page, (newPage) => renderCatalogue(newPage, searchTerm));
    }

    // Emprunter un livre
    function borrowBook(bookId) {
        fetch('emprunter.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'livre_id=' + bookId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Livre emprunté avec succès.');
                loadData(); // Recharger les données
            } else {
                showNotification('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'emprunt:', error);
            showNotification('Erreur lors de l\'emprunt du livre.');
        });
    }

    // Afficher l'historique des emprunts
    function renderHistory(page = 1) {
        const itemsPerPage = 5;
        const totalPages = Math.ceil(borrowHistory.length / itemsPerPage);
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const historyToDisplay = borrowHistory.slice(startIndex, endIndex);

        const tbody = document.querySelector('#historyList tbody');
        tbody.innerHTML = '';
        historyToDisplay.forEach(book => {
            const row = tbody.insertRow();
            row.insertCell(0).textContent = book.titre;
            row.insertCell(1).textContent = book.auteur;
            row.insertCell(2).textContent = formatDate(book.date_emprunt);
            row.insertCell(3).textContent = formatDate(book.date_retour_effective);
        });

        renderPagination('historyPagination', totalPages, page, renderHistory);
    }

    // Afficher la pagination
    function renderPagination(containerId, totalPages, currentPage, callback) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.classList.toggle('active', i === currentPage);
            button.addEventListener('click', () => callback(i));
            container.appendChild(button);
        }
    }

    // Fonction debounce pour limiter les appels de fonction
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Réduire/agrandir la barre latérale
    document.querySelector('.sidebar h2').addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });

    // Écouteur d'événement pour la recherche
    document.getElementById('searchBooks').addEventListener('input', debounce((e) => renderCatalogue(1, e.target.value), 300));

    // Charger les données au chargement de la page
    document.addEventListener('DOMContentLoaded', loadData);
</script>

