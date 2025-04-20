<?php
require_once '../auth/auth.php';
require_once '../config/database.php';

// Initialiser l'objet Auth
$auth = new Auth();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php?error=' . urlencode('Vous devez être administrateur pour accéder à cette page'));
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$currentUser = $auth->getCurrentUser();

// Définir le titre de la page
$pageTitle = "Tableau de Bord Administrateur";

// Inclure l'en-tête

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin - Gestion de Bibliothèque</title>
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
        form {
            display: grid;
            gap: 10px;
        }
        input[type="text"], input[type="email"], input[type="password"], select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color var(--transition-speed);
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, select:focus, textarea:focus {
            border-color: var(--primary-color);
            outline: none;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar" id="sidebar">
            <h2>Menu Admin</h2>
            <ul>
                <li><a href="#" onclick="showSection('dashboard')"><i class="fas fa-tachometer-alt"></i> <span>Tableau de Bord</span></a></li>
                <li><a href="#" onclick="showSection('books')"><i class="fas fa-book"></i> <span>Gestion des Livres</span></a></li>
                <li><a href="#" onclick="showSection('members')"><i class="fas fa-users"></i> <span>Gestion des Adhérents</span></a></li>
                <li><a href="#" onclick="showSection('loans')"><i class="fas fa-exchange-alt"></i> <span>Gestion des Emprunts</span></a></li>
                <li><a href="../login.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span>Se Déconnecter</span></a></li>
            </ul>
        </div>
        <div class="main-content">
            <div id="dashboard" class="section">
                <h1>Bienvenue <?php echo htmlspecialchars($currentUser['nom']); ?> dans votre Espace Admin!</h1>
                <div class="stats">
                    <div class="stat-card">
                        <h3>Total des Livres</h3>
                        <p id="totalBooks">0</p>
                    </div>
                    <div class="stat-card">
                        <h3>Total des Adhérents</h3>
                        <p id="totalMembers">0</p>
                    </div>
                    <div class="stat-card">
                        <h3>Emprunts en cours</h3>
                        <p id="activeLoans">0</p>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="statsChart"></canvas>
                </div>
            </div>
            <div id="books" class="section" style="display:none;">
                <h1>Gestion des Livres</h1>
                <div class="card">
                    <h2>Ajouter un Livre</h2>
                    <form id="addBookForm">
                        <input type="text" id="bookTitle" placeholder="Titre" required>
                        <input type="text" id="bookAuthor" placeholder="Auteur" required>
                        <textarea id="bookDescription" placeholder="Description" rows="3"></textarea>
                        <input type="text" id="bookImageUrl" placeholder="URL de l'image">
                        <select id="bookCategory" required>
                            <option value="">Sélectionner une catégorie</option>
                            <?php
                            // Récupérer les catégories depuis la base de données
                            $db = new PDO("mysql:host=localhost;dbname=bibliotheque", "root", "");
                            $stmt = $db->query("SELECT * FROM categories ORDER BY nom");
                            while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $category['id'] . '">' . htmlspecialchars($category['nom']) . '</option>';
                            }
                            ?>
                        </select>
                        <input type="number" id="bookQuantity" placeholder="Quantité disponible" min="0" required>
                        <button type="submit" class="btn">Ajouter</button>
                    </form>
                </div>
                <div class="card">
                    <h2>Liste des Livres</h2>
                    <div class="search-bar">
                        <input type="text" id="searchBooks" placeholder="Rechercher un livre...">
                    </div>
                    <table id="booksList">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Catégorie</th>
                                <th>Disponibilité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="pagination" id="booksPagination"></div>
                </div>
            </div>
            <div id="members" class="section" style="display:none;">
                <h1>Gestion des Adhérents</h1>
                <div class="card">
                    <h2>Ajouter un Adhérent</h2>
                    <form id="addMemberForm">
                        <input type="text" id="memberName" placeholder="Nom complet" required>
                        <input type="email" id="memberEmail" placeholder="Email" required>
                        <input type="password" id="memberPassword" placeholder="Mot de passe" required>
                        <button type="submit" class="btn">Ajouter</button>
                    </form>
                </div>
                <div class="card">
                    <h2>Liste des Adhérents</h2>
                    <div class="search-bar">
                        <input type="text" id="searchMembers" placeholder="Rechercher un adhérent...">
                    </div>
                    <table id="membersList">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="pagination" id="membersPagination"></div>
                </div>
            </div>
            <div id="loans" class="section" style="display:none;">
                <h1>Gestion des Emprunts</h1>
                <div class="card">
                    <h2>Nouvel Emprunt</h2>
                    <form id="newLoanForm">
                        <select id="loanBook" required>
                            <option value="">Sélectionner un livre</option>
                            <?php
                            // Récupérer les livres disponibles depuis la base de données
                            $stmt = $db->query("SELECT * FROM livres WHERE quantite_disponible > 0 ORDER BY titre");
                            while ($book = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $book['id'] . '">' . htmlspecialchars($book['titre']) . ' (' . $book['quantite_disponible'] . ' disponibles)</option>';
                            }
                            ?>
                        </select>
                        <select id="loanMember" required>
                            <option value="">Sélectionner un adhérent</option>
                            <?php
                            // Récupérer les adhérents depuis la base de données
                            $stmt = $db->query("SELECT * FROM utilisateurs WHERE role = 'utilisateur' ORDER BY nom");
                            while ($member = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $member['id'] . '">' . htmlspecialchars($member['nom']) . ' (' . $member['email'] . ')</option>';
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn">Enregistrer l'emprunt</button>
                    </form>
                </div>
                <div class="card">
                    <h2>Emprunts en cours</h2>
                    <div class="search-bar">
                        <input type="text" id="searchLoans" placeholder="Rechercher un emprunt...">
                    </div>
                    <table id="loansList">
                        <thead>
                            <tr>
                                <th>Livre</th>
                                <th>Adhérent</th>
                                <th>Date d'emprunt</th>
                                <th>Date de retour</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="pagination" id="loansPagination"></div>
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

        // Récupérer les données depuis le serveur
        let books = [];
        let members = [];
        let loans = [];

        // Charger les données au chargement de la page
        function loadData() {
            // Charger les livres
            fetch('../catalogue.php?format=json')
                .then(response => response.json())
                .then(data => {
                    books = data;
                    updateDashboard();
                    renderBooks();
                })
                .catch(error => console.error('Erreur lors du chargement des livres:', error));

            // Charger les adhérents
            fetch('get_users.php?format=json')
                .then(response => response.json())
                .then(data => {
                    members = data;
                    updateDashboard();
                    renderMembers();
                })
                .catch(error => console.error('Erreur lors du chargement des adhérents:', error));

            // Charger les emprunts
            fetch('../emprunter.php?format=json&admin=1')
                .then(response => response.json())
                .then(data => {
                    loans = data;
                    updateDashboard();
                    renderLoans();
                })
                .catch(error => console.error('Erreur lors du chargement des emprunts:', error));
        }

        // Mettre à jour le tableau de bord
        function updateDashboard() {
            document.getElementById('totalBooks').textContent = books.length;
            document.getElementById('totalMembers').textContent = members.length;
            document.getElementById('activeLoans').textContent = loans.length;
            updateStatsChart();
        }

        // Mettre à jour le graphique des statistiques
        function updateStatsChart() {
            const ctx = document.getElementById('statsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Livres', 'Adhérents', 'Emprunts'],
                    datasets: [{
                        label: 'Statistiques',
                        data: [books.length, members.length, loans.length],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)'
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

        // Gestion des livres
        function addBook(event) {
            event.preventDefault();
            const title = document.getElementById('bookTitle').value;
            const author = document.getElementById('bookAuthor').value;
            const description = document.getElementById('bookDescription').value;
            const imageUrl = document.getElementById('bookImageUrl').value;
            const categoryId = document.getElementById('bookCategory').value;
            const quantity = document.getElementById('bookQuantity').value;

            fetch('add_book.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `titre=${encodeURIComponent(title)}&auteur=${encodeURIComponent(author)}&description=${encodeURIComponent(description)}&image_url=${encodeURIComponent(imageUrl)}&categorie_id=${encodeURIComponent(categoryId)}&quantite_disponible=${encodeURIComponent(quantity)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Livre ajouté avec succès.');
                    event.target.reset();
                    loadData(); // Recharger les données
                } else {
                    showNotification('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'ajout du livre:', error);
                showNotification('Erreur lors de l\'ajout du livre.');
            });
        }

        function renderBooks(page = 1, searchTerm = '') {
            const itemsPerPage = 5;
            const filteredBooks = books.filter(book => 
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
                row.insertCell(2).textContent = book.categorie_nom || 'Non catégorisé';
                row.insertCell(3).textContent = book.quantite_disponible > 0 ? 'Disponible' : 'Indisponible';
                const actionsCell = row.insertCell(4);
                actionsCell.innerHTML = `
                    <button onclick="editBook(${book.id})" class="btn"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteBook(${book.id})" class="btn"><i class="fas fa-trash"></i></button>
                `;
            });

            renderPagination('booksPagination', totalPages, page, (newPage) => renderBooks(newPage, searchTerm));
        }

        function editBook(bookId) {
            const book = books.find(b => b.id === bookId);
            if (book) {
                document.getElementById('bookTitle').value = book.titre;
                document.getElementById('bookAuthor').value = book.auteur;
                document.getElementById('bookDescription').value = book.description || '';
                document.getElementById('bookImageUrl').value = book.image_url || '';
                document.getElementById('bookCategory').value = book.categorie_id || '';
                document.getElementById('bookQuantity').value = book.quantite_disponible;
                
                // Changer le bouton du formulaire
                const form = document.getElementById('addBookForm');
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.textContent = 'Mettre à jour';
                
                // Ajouter un champ caché pour l'ID du livre
                let hiddenInput = form.querySelector('input[name="book_id"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'book_id';
                    form.appendChild(hiddenInput);
                }
                hiddenInput.value = bookId;
                
                showNotification('Modifiez les détails du livre et soumettez pour mettre à jour.');
            }
        }

        function deleteBook(bookId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce livre ?')) {
                fetch('delete_book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${bookId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Livre supprimé avec succès.');
                        loadData(); // Recharger les données
                    } else {
                        showNotification('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la suppression du livre:', error);
                    showNotification('Erreur lors de la suppression du livre.');
                });
            }
        }

        // Gestion des adhérents
        function addMember(event) {
            event.preventDefault();
            const name = document.getElementById('memberName').value;
            const email = document.getElementById('memberEmail').value;
            const password = document.getElementById('memberPassword').value;

            fetch('add_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `nom=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&mot_de_passe=${encodeURIComponent(password)}&role=utilisateur`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Adhérent ajouté avec succès.');
                    event.target.reset();
                    loadData(); // Recharger les données
                } else {
                    showNotification('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'ajout de l\'adhérent:', error);
                showNotification('Erreur lors de l\'ajout de l\'adhérent.');
            });
        }

        function renderMembers(page = 1, searchTerm = '') {
            const itemsPerPage = 5;
            const filteredMembers = members.filter(member => 
                member.nom.toLowerCase().includes(searchTerm.toLowerCase()) || 
                member.email.toLowerCase().includes(searchTerm.toLowerCase())
            );
            const totalPages = Math.ceil(filteredMembers.length / itemsPerPage);
            const startIndex = (page - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const membersToDisplay = filteredMembers.slice(startIndex, endIndex);

            const tbody = document.querySelector('#membersList tbody');
            tbody.innerHTML = '';
            membersToDisplay.forEach(member => {
                const row = tbody.insertRow();
                row.insertCell(0).textContent = member.nom;
                row.insertCell(1).textContent = member.email;
                row.insertCell(2).textContent = member.role === 'admin' ? 'Administrateur' : 'Adhérent';
                const actionsCell = row.insertCell(3);
                actionsCell.innerHTML = `
                    <button onclick="editMember(${member.id})" class="btn"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteMember(${member.id})" class="btn"><i class="fas fa-trash"></i></button>
                `;
            });

            renderPagination('membersPagination', totalPages, page, (newPage) => renderMembers(newPage, searchTerm));
        }

        function editMember(memberId) {
            const member = members.find(m => m.id === memberId);
            if (member) {
                document.getElementById('memberName').value = member.nom;
                document.getElementById('memberEmail').value = member.email;
                
                // Changer le bouton du formulaire
                const form = document.getElementById('addMemberForm');
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.textContent = 'Mettre à jour';
                
                // Ajouter un champ caché pour l'ID de l'adhérent
                let hiddenInput = form.querySelector('input[name="member_id"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'member_id';
                    form.appendChild(hiddenInput);
                }
                hiddenInput.value = memberId;
                
                showNotification('Modifiez les détails de l\'adhérent et soumettez pour mettre à jour.');
            }
        }

        function deleteMember(memberId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?')) {
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${memberId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Adhérent supprimé avec succès.');
                        loadData(); // Recharger les données
                    } else {
                        showNotification('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la suppression de l\'adhérent:', error);
                    showNotification('Erreur lors de la suppression de l\'adhérent.');
                });
            }
        }

        // Gestion des emprunts
        function addLoan(event) {
            event.preventDefault();
            const bookId = document.getElementById('loanBook').value;
            const memberId = document.getElementById('loanMember').value;

            fetch('../emprunter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `livre_id=${bookId}&utilisateur_id=${memberId}&admin=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Emprunt enregistré avec succès.');
                    event.target.reset();
                    loadData(); // Recharger les données
                } else {
                    showNotification('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'enregistrement de l\'emprunt:', error);
                showNotification('Erreur lors de l\'enregistrement de l\'emprunt.');
            });
        }

        function renderLoans(page = 1, searchTerm = '') {
            const itemsPerPage = 5;
            const filteredLoans = loans.filter(loan => 
                loan.titre.toLowerCase().includes(searchTerm.toLowerCase()) || 
                loan.nom.toLowerCase().includes(searchTerm.toLowerCase())
            );
            const totalPages = Math.ceil(filteredLoans.length / itemsPerPage);
            const startIndex = (page - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const loansToDisplay = filteredLoans.slice(startIndex, endIndex);

            const tbody = document.querySelector('#loansList tbody');
            tbody.innerHTML = '';
            loansToDisplay.forEach(loan => {
                const row = tbody.insertRow();
                row.insertCell(0).textContent = loan.titre;
                row.insertCell(1).textContent = loan.nom;
                row.insertCell(2).textContent = formatDate(loan.date_emprunt);
                row.insertCell(3).textContent = formatDate(loan.date_retour_prevue);
                const actionsCell = row.insertCell(4);
                actionsCell.innerHTML = `
                    <button onclick="returnBook(${loan.id})" class="btn"><i class="fas fa-undo"></i> Retourner</button>
                `;
            });

            renderPagination('loansPagination', totalPages, page, (newPage) => renderLoans(newPage, searchTerm));
        }

        function returnBook(loanId) {
            if (confirm('Confirmer le retour de ce livre ?')) {
                fetch('../retourner.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${loanId}&admin=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Livre retourné avec succès.');
                        loadData(); // Recharger les données
                    } else {
                        showNotification('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du retour du livre:', error);
                    showNotification('Erreur lors du retour du livre.');
                });
            }
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

        // Écouteurs d'événements pour les formulaires
        document.getElementById('addBookForm').addEventListener('submit', addBook);
        document.getElementById('addMemberForm').addEventListener('submit', addMember);
        document.getElementById('newLoanForm').addEventListener('submit', addLoan);

        // Écouteurs d'événements pour la recherche
        document.getElementById('searchBooks').addEventListener('input', debounce((e) => renderBooks(1, e.target.value), 300));
        document.getElementById('searchMembers').addEventListener('input', debounce((e) => renderMembers(1, e.target.value), 300));
        document.getElementById('searchLoans').addEventListener('input', debounce((e) => renderLoans(1, e.target.value), 300));

        // Charger les données au chargement de la page
        document.addEventListener('DOMContentLoaded', loadData);
    </script>
</body>
</html>

