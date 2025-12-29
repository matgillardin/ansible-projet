<!DOCTYPE html>
<html>
<head>
    <title>{{ apache_vhost_name }}</title>
</head>
<body>
    <h1>Bienvenue sur {{ apache_vhost_name }}</h1>
    
    <?php
    $host = '{{ db_host }}';
    $dbname = '{{ mariadb_database }}';
    $user = '{{ mariadb_user }}';
    $password = '{{ mariadb_password }}';
    $port = {{ mariadb_port }};

    try {
        $conn = new mysqli($host, $user, $password, $dbname, $port);
        
        if ($conn->connect_error) {
            echo "<p style='color: red;'>Erreur de connexion : " . $conn->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>Connexion à la base de données réussie !</p>";
            
            // Afficher quelques infos
            echo "<h2>Informations serveur :</h2>";
            echo "<ul>";
            echo "<li>Serveur DB : " . $host . "</li>";
            echo "<li>Base de données : " . $dbname . "</li>";
            echo "<li>Version MySQL : " . $conn->server_info . "</li>";
            echo "</ul>";
            
            $conn->close();
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Exception : " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>
