<?php
   require_once("config/database.php"); // Connexion à la BDD

   session_start(); // Permet d'utiliser les variables de session
   
   if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { // On vérifie si l'utilisateur est connecté
       header("Location: login.php"); // Si non, on le redirige vers le Login
       exit();
   }    
?>

<?php
// Ajouter un woofer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouterWoofer'])) {
    // On récupère les données du formulaire
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $nomUtilisateur = mysqli_real_escape_string($conn, $_POST['nomUtilisateur']);
    $motDePasse = mysqli_real_escape_string($conn, $_POST['motDePasse']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $diplomes = mysqli_real_escape_string($conn, $_POST['diplomes']);
    $dateDebut = mysqli_real_escape_string($conn, $_POST['dateDebut']);
    $dateFin = mysqli_real_escape_string($conn, $_POST['dateFin']);
    
    // On récupère la date du jour 
    $dateCreation = $dateDuJour = date("Y-m-d");

    // Requête d'insertion des données pour la table Utilisateur
    $sqlUtilisateur = "INSERT INTO Utilisateur (nomUtilisateur, motDePasse, email, typeUtilisateur, dateCreation, etat, derniereConnexion) 
                    VALUES ('$nomUtilisateur', '$motDePasse', '$email', 'Woofer', '$dateCreation', 'deconnecte', '$dateCreation')";

    mysqli_query($conn, $sqlUtilisateur);
        
    // Récupération de l'id de l'utilisateur pour pouvoir l'utiliser dans la requête de création du woofer
    $sqlGetIdCompte = "SELECT idCompte FROM Utilisateur WHERE nomUtilisateur='$nomUtilisateur'";
    $result = mysqli_query($conn, $sqlGetIdCompte);
    $utilisateur = $result->fetch_assoc();
    $idCompte = $utilisateur['idCompte']; 
    
    // Requête d'insertion des données pour la table Woofer
    $sqlWoofer = "INSERT INTO Woofer (idCompte, nom, prenom, diplomes, etat, dateDebut, dateFin, photo)  
                    VALUES ('$idCompte', '$nom', '$prenom', '$diplomes', 'inscrit', '$dateCreation', '$dateFin', 'aDefinir')";

    mysqli_query($conn, $sqlWoofer);

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des woofers</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <?php include_once("menu.php") ?> <!-- Intégration du menu -->
    <h2>Gestion des woofers</h2>
    <p>Accès <?php echo $_SESSION["typeUtilisateur"]?></p>
    <div>
        <h3>Woofers connectés</h3> 
        <table>
            <thead>
                <tr>
                    <th scope='col'>Nom</th>
                    <th scope='col'>Prénom</th>
                    <th scope='col'>NomUtilisateur</th>
                    <th scope='col'>email</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Requête pour récupérer les woofers connectés
                $sql = "SELECT w.nom, w.prenom, u.nomUtilisateur, u.email FROM Woofer w JOIN Utilisateur u ON w.idCompte = u.idCompte WHERE u.etat = 'connecte'";
                $result = mysqli_query($conn, $sql);
                while($woofer = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$woofer['nom']}</td>
                            <td>{$woofer['prenom']}</td>
                            <td>{$woofer['nomUtilisateur']}</td>
                            <td>{$woofer['email']}</td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div>
    <h3>Liste des Woofers</h3>
    <!-- Formulaire de recherche et tri -->
    <form class="rechercheAvancee" method="GET" action="">
        <div>
        <label for="search">Rechercher un woofer : </label>
        <input type="text" name="search" id="search" placeholder="Nom, prénom, email..." value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
        </div>
        <br>
        <div>
        <label for="sort">Trier par : </label>
        <select name="sort" id="sort">
            <option value="nomUtilisateur" <?= (isset($_GET['sort']) && $_GET['sort'] == 'nomUtilisateur') ? 'selected' : '' ?>>Nom d'utilisateur</option>
            <option value="nom" <?= (isset($_GET['sort']) && $_GET['sort'] == 'nom') ? 'selected' : '' ?>>Nom</option>
            <option value="prenom" <?= (isset($_GET['sort']) && $_GET['sort'] == 'prenom') ? 'selected' : '' ?>>Prénom</option>
            <option value="dateDebut" <?= (isset($_GET['sort']) && $_GET['sort'] == 'dateDebut') ? 'selected' : '' ?>>Date de début</option>
            <option value="etatUtilisateur" <?= (isset($_GET['sort']) && $_GET['sort'] == 'etatUtilisateur') ? 'selected' : '' ?>>État utilisateur</option>
        </select>
        </div>
        <button type="submit">Appliquer les filtres</button>
    </form>

    <!-- Tableau des woofers -->
    <table>
        <thead>
            <tr>
                <th scope="col">ID Compte</th>
                <th scope="col">Nom d'utilisateur</th>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
                <th scope="col">Email</th>
                <th scope="col">Date Début</th>
                <th scope="col">Date Fin</th>
                <th scope="col">État</th>
                <th scope="col">Dernière Connexion</th>
            </tr>
        </thead>
        <tbody>
            <?php

            // Récupération des paramètres de recherche et tri
            $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'nomUtilisateur'; // Trié par défaut par rapport au nom d'utilisateur

            // Requête SQL pour récupérer les woofers avec filtre de recherche
            $sql = "SELECT u.idCompte, u.nomUtilisateur, u.email, u.etat AS etatUtilisateur, u.derniereConnexion,
                        w.nom, w.prenom, w.diplomes, w.etat, w.dateDebut, w.dateFin
                    FROM Utilisateur u
                    JOIN Woofer w ON u.idCompte = w.idCompte
                    WHERE u.nomUtilisateur LIKE '%$search%'
                    OR w.nom LIKE '%$search%'
                    OR w.prenom LIKE '%$search%'
                    OR u.email LIKE '%$search%'
                    OR w.diplomes LIKE '%$search%'
                    ORDER BY $sort ASC"; 

            $result = mysqli_query($conn, $sql);

            // Affichage des woofers
            while ($woofer = $result->fetch_assoc()) {
                echo "<tr>
                        <th scope='row'>{$woofer['idCompte']}</th>
                        <td>{$woofer['nomUtilisateur']}</td>
                        <td>{$woofer['nom']}</td>
                        <td>{$woofer['prenom']}</td>
                        <td>{$woofer['email']}</td>
                        <td>{$woofer['dateDebut']}</td>
                        <td>{$woofer['dateFin']}</td>
                        <td>{$woofer['etatUtilisateur']}</td>
                        <td>{$woofer['derniereConnexion']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
    <div>
    <h3>Ajouter un woofer</h3> <!-- Seuls les responsables ont la possibilité d'ajouter des woofers -->
    <?php
        if ($_SESSION["typeUtilisateur"] !== "Responsable") {
            echo "<p>Fonctionnalité réservée aux responsables.</p>";
        }
        else {
    ?>
    <form method="POST" action="">
        <label for="nom">Nom :</label>
        <input type="text" name="nom" id="nom" required>
        <br>

        <label for="prenom">Prénom :</label>
        <input type="text" name="prenom" id="prenom" required>
        <br>

        <label for="nomUtilisateur">Nom utilisateur :</label>
        <input type="text" name="nomUtilisateur" id="nomUtilisateur" required>
        <br>

        <label for="motDePasse">Mot de passe :</label>
        <input type="text" name="motDePasse" id="motDePasse" required>
        <br>

        <label for="email">Email :</label>
        <input type="email" name="email" id="email" required>
        <br>

        <label for="diplomes">Diplômes :</label>
        <input type="text" name="diplomes" id="diplomes" required>
        <br>

        <label for="dateDebut">Date de début :</label>
        <input type="date" name="dateDebut" id="dateDebut" required>
        <br>

        <label for="dateFin">Date de fin :</label>
        <input type="date" name="dateFin" id="dateFin" required>
        <br>

        <br>
        <button type="submit" name="ajouterWoofer">Ajouter Woofer</button>
    </form>
    <?php
        } 
    ?>
    </div>
</body>
</html>