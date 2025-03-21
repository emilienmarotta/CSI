<?php
    require_once("config/database.php"); // On importe la BDD

    session_start(); // Permet d'utiliser les variables de session
    
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { // On vérifie si l'utilisateur est connecté
        header("Location: login.php");
        exit();
    }    
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouterVente'])) {
    // On récupère les données du formulaire
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $woofer = mysqli_real_escape_string($conn, $_POST['woofer']);
    $prixHT = mysqli_real_escape_string($conn, $_POST['prixHT']);

    // On génère un identifiant unique pour la vente et pour la facture 
    $idVente = uniqid("V");
    $idFacture = "F" . substr($idVente, 1);

    // On calcule le prix TTC avec une TVA normale à 20%
    $prixTTC = $prixHT * 1.2;

    // Requête d'insertion des données
    $sqlInsert = "INSERT INTO Vente (idnoVente, date, woofer, prixTTC, prixHT) 
                  VALUES ('$idVente', '$date', '$woofer', '$prixTTC', '$prixHT'); 
                  
                  INSERT INTO Facture (refFacture, prix, vente) 
                  VALUES ('$idFacture', '$prixHT', '$idVente')";

    if (mysqli_multi_query($conn, $sqlInsert)) { 
        while (mysqli_next_result($conn)) {;}

        // Permet de garder l'affichage de l'historique après ajout d'une nouvelle vente
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<p style='color:red;'>Erreur lors de l'ajout : ".mysqli_error($conn)."</p>";
    }
        
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des ventes</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <?php include_once("menu.php") ?> 
    <h2>Gestion des ventes</h2>
    <p>Accès <?php echo $_SESSION["typeUtilisateur"]?></p>
    <div>
    <h3>Historique des ventes</h3>

    <!-- Recherche avancée et tri -->
    <form class="rechercheAvancee" method="GET" action="">
        <div>
        <label for="search">Rechercher un numéro de vente ou un woofer : </label>
        <input type="text" name="search" id="search" placeholder="" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
        </div>
        <br>
        <div>
        <label for="sort">Trier par : </label>
        <select name="sort" id="sort">
            <option value="date" <?= (isset($_GET['sort']) && $_GET['sort'] == 'date') ? 'selected' : '' ?>>Date</option>
            <option value="prixTTC" <?= (isset($_GET['sort']) && $_GET['sort'] == 'prixTTC') ? 'selected' : '' ?>>Prix Global</option>
            <option value="prixHT" <?= (isset($_GET['sort']) && $_GET['sort'] == 'prixHT') ? 'selected' : '' ?>>Prix Total</option>
        </select>
        </div>
        <button type="submit">Appliquer les filtres de recherche</button>
    </form>

    <!-- Historique des ventes -->
    <table>
        <thead>
            <tr>
                <th scope="col">Numéro</th>
                <th scope="col">Date</th>
                <th scope="col">Woofer</th>
                <th scope="col">prixTTC</th>
                <th scope="col">prixHT</th>
                <th scope="col">Facture</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // On récupère les paramètres de recherche et de tri
            $search = isset($_GET['search']) ?mysqli_real_escape_string($conn, $_GET['search']) : '';
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date'; // Les valeurs sont triées par date par défaut
            
            // On injecte les paramètres dans la requête SQL
            $sql = "SELECT v.idnoVente, v.date, u.nomUtilisateur, v.prixTTC, v.prixHT, f.refFacture 
                    FROM Vente v
                    JOIN Woofer w ON v.woofer = w.idCompte
                    JOIN utilisateur u ON w.idCompte = u.idCompte
                    JOIN Facture f ON v.idnoVente = f.vente
                    WHERE v.idnoVente LIKE '%$search%' OR u.nomUtilisateur LIKE '%$search%' 
                    ORDER BY $sort DESC"; 

            $result = mysqli_query($conn, $sql);
            
            // Affichage 
            while ($vente = $result->fetch_assoc()) {
                echo "<tr>
                        <th scope='row'>{$vente['idnoVente']}</th>
                        <td>{$vente['date']}</td>
                        <td>{$vente['nomUtilisateur']}</td>
                        <td>{$vente['prixTTC']}</td>
                        <td>{$vente['prixHT']}</td>
                        <td>{$vente['refFacture']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>     
    </div>
    <div>
    <h3>Enregistrer une nouvelle vente :</h3>
    <form method="POST" action="">
        <label for="date">Date :</label>
        <input type="date" name="date" id="date" required>
        <br>

        <label for="woofer">Woofer :</label>
        <select name="woofer" id="woofer" required>
            <option value="">-- Sélectionner un woofer --</option>
            <?php
            // On récupère la liste des woofers
            $sqlWoofer = "SELECT u.idCompte, u.nomUtilisateur AS nomUtilisateur FROM Woofer w JOIN Utilisateur u ON w.idCompte = u.idCompte";
            $resultWoofer = mysqli_query($conn, $sqlWoofer);
            while ($woofer = mysqli_fetch_assoc($resultWoofer)) {
                echo "<option value='{$woofer['idCompte']}'>{$woofer['nomUtilisateur']}</option>";
            }
            ?>
        </select>
        <br>

        <label for="prixHT">Prix HT :</label>
        <input type="number" name="prixHT" id="prixHT" step="0.01" min=0 required>
        <br>

        <br>
        <button type="submit" name="ajouterVente">Ajouter Vente</button>
    </form>
    </div>
</body>
</html>