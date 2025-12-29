<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ apache_vhost_name }} - Projet Ansible TI331</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }
        .error {
            color: #721c24;
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }
        .info {
            color: #0c5460;
            background: #d1ecf1;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #bee5eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .config-list {
            list-style: none;
            padding: 0;
        }
        .config-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .config-list li:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌐 {{ apache_vhost_name }}</h1>
        <p class="info">Projet TI331 - Automatisation avec Ansible</p>
    </div>

    <?php
    // Configuration de la connexion à la base de données
    $host = '{{ db_host }}';
    $dbname = '{{ mariadb_database }}';
    $user = '{{ mariadb_user }}';
    $password = '{{ mariadb_password }}';
    $port = {{ mariadb_port }};

    // Affichage des informations de configuration
    echo '<div class="container">';
    echo '<h2>📋 Configuration de connexion</h2>';
    echo '<ul class="config-list">';
    echo '<li><span class="label">Serveur DB :</span> ' . htmlspecialchars($host) . '</li>';
    echo '<li><span class="label">Port :</span> ' . $port . '</li>';
    echo '<li><span class="label">Base de données :</span> ' . htmlspecialchars($dbname) . '</li>';
    echo '<li><span class="label">Utilisateur :</span> ' . htmlspecialchars($user) . '</li>';
    echo '</ul>';
    echo '</div>';

    // Tentative de connexion
    echo '<div class="container">';
    echo '<h2>🔌 État de la connexion</h2>';

    try {
        // Création de la connexion avec timeout
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($host, $user, $password, $dbname, $port);
        $conn->set_charset("utf8mb4");
        
        echo '<p class="success">✅ Connexion à la base de données réussie !</p>';
        
        echo '<ul class="config-list">';
        echo '<li><span class="label">Version MySQL/MariaDB :</span> ' . $conn->server_info . '</li>';
        echo '<li><span class="label">Charset :</span> ' . $conn->character_set_name() . '</li>';
        echo '</ul>';
        echo '</div>';

        // Vérification et affichage des tables
        echo '<div class="container">';
        echo '<h2>📊 Données de la base</h2>';
        
        // Vérifier si la table produits existe
        $result = $conn->query("SHOW TABLES LIKE 'produits'");
        
        if ($result->num_rows > 0) {
            // Récupérer les produits
            $produits = $conn->query("SELECT * FROM produits ORDER BY id");
            
            if ($produits->num_rows > 0) {
                echo '<h3>🛒 Liste des produits</h3>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Nom</th><th>Description</th><th>Prix</th><th>Date ajout</th></tr>';
                
                while ($row = $produits->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['nom']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['description']) . '</td>';
                    echo '<td>' . number_format($row['prix'], 2, ',', ' ') . ' €</td>';
                    echo '<td>' . htmlspecialchars($row['date_ajout']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="info">ℹ️ La table "produits" existe mais est vide.</p>';
            }
        } else {
            echo '<p class="info">ℹ️ La table "produits" n\'existe pas encore.</p>';
        }
        
        // Vérifier si la table system_info existe
        $result = $conn->query("SHOW TABLES LIKE 'system_info'");
        
        if ($result->num_rows > 0) {
            $infos = $conn->query("SELECT * FROM system_info ORDER BY cle");
            
            if ($infos->num_rows > 0) {
                echo '<h3>ℹ️ Informations système</h3>';
                echo '<table>';
                echo '<tr><th>Clé</th><th>Valeur</th></tr>';
                
                while ($row = $infos->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['cle']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['valeur']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
        
        // Afficher toutes les tables disponibles
        echo '<h3>📁 Tables dans la base de données</h3>';
        $tables = $conn->query("SHOW TABLES");
        
        if ($tables->num_rows > 0) {
            echo '<ul class="config-list">';
            while ($table = $tables->fetch_array()) {
                echo '<li>' . htmlspecialchars($table[0]) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="info">ℹ️ Aucune table dans la base de données.</p>';
        }
        
        $conn->close();
        
    } catch (mysqli_sql_exception $e) {
        echo '<p class="error">❌ Erreur de connexion MySQL : ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p class="info">💡 <strong>Vérifiez que :</strong></p>';
        echo '<ul>';
        echo '<li>Le conteneur MariaDB est en cours d\'exécution sur le serveur ' . htmlspecialchars($host) . '</li>';
        echo '<li>Le port ' . $port . ' est accessible depuis ce serveur</li>';
        echo '<li>Le firewall autorise les connexions sur le port ' . $port . '</li>';
        echo '<li>Les identifiants de connexion sont corrects</li>';
        echo '</ul>';
    } catch (Exception $e) {
        echo '<p class="error">❌ Exception : ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';
    ?>

    <div class="container">
        <h2>🖥️ Informations serveur Web</h2>
        <ul class="config-list">
            <li><span class="label">Serveur :</span> <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'); ?></li>
            <li><span class="label">PHP Version :</span> <?php echo phpversion(); ?></li>
            <li><span class="label">Hostname :</span> <?php echo htmlspecialchars(gethostname()); ?></li>
            <li><span class="label">Date/Heure :</span> <?php echo date('d/m/Y H:i:s'); ?></li>
        </ul>
    </div>

    <footer style="text-align: center; color: #666; margin-top: 20px;">
        <p>Projet Ansible TI331 - Automatisation d'infrastructure</p>
    </footer>
</body>
</html>
