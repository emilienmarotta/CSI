<?php
    require_once("config/database.php"); // Connexion à la BDD

    session_start(); // Permet d'utiliser les variables de session
    
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { // On vérifie si l'utilisateur est connecté
        header("Location: login.php"); // Si non, on le redirige vers le Login
        exit();
    }    
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Ajouter un produit
        if (isset($_POST['ajouter_produit'])) {
            // mysqli_real_escape_string() permet d'éviter les injections SQL 
            $nomProduit = mysqli_real_escape_string($conn, $_POST['nom_produit']);
            $quantite = (int)$_POST['quantite_produit'];
            $prixUnitaire = (float)$_POST['prix_unitaire'];
            $nomType = mysqli_real_escape_string($conn, $_POST['type_produit']);

            // On vérifie si le type de produit existe déjà
            $sqlVerifType = "SELECT noTypeProduit FROM TypeProduit WHERE nom = ?";
            $stmt = mysqli_prepare($conn, $sqlVerifType);
            mysqli_stmt_bind_param($stmt, 's', $nomType);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                mysqli_stmt_bind_result($stmt, $idTypeProduit);
                mysqli_stmt_fetch($stmt);
            } else {
                // On insère un nouveau type de produit si le type du produit n'existe pas encore
                $sqlInsererType = "INSERT INTO TypeProduit (nom, etat) VALUES (?, 'disponible')";
                $stmtInsererType = mysqli_prepare($conn, $sqlInsererType);
                mysqli_stmt_bind_param($stmtInsererType, 's', $nomType);
                mysqli_stmt_execute($stmtInsererType);
                $idTypeProduit = mysqli_insert_id($conn);
            }

            // On insère le produit
            $sqlInsererProduit = "INSERT INTO Produit (nom, quantite, etat, prixUnitaire, typeProduit) VALUES (?, ?, 'stocke', ?, ?)";
            $stmtProduit = mysqli_prepare($conn, $sqlInsererProduit);
            mysqli_stmt_bind_param($stmtProduit, 'sidi', $nomProduit, $quantite, $prixUnitaire, $idTypeProduit);
            mysqli_stmt_execute($stmtProduit);
        }

        // Mettre à jour la quantité en stock d'un produit
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
                preg_match('/_(\d+)$/', $action, $str); // Régex pour récupérer l'action
                $idProduit = $str[1];

                // Vérifier l'action
                if (strpos($action, 'ajouter_') !== false) { // Si l'action contient le mot ajouter_
                    $quantite = (int)$_POST['quantite'];
                    if ($quantite > 0) {
                        // On augmente la quantité en stock
                        $sqlUpdateQuantite = "UPDATE Produit SET quantite = quantite + ? WHERE noProduit = ?";
                        $stmtUpdate = mysqli_prepare($conn, $sqlUpdateQuantite);
                        mysqli_stmt_bind_param($stmtUpdate, 'ii', $quantite, $idProduit);
                        mysqli_stmt_execute($stmtUpdate);
                    }
                } elseif (strpos($action, 'enlever_') !== false) { // Si l'action contient le mot enlever_
                    $quantite = (int)$_POST['quantite'];
                    if ($quantite > 0) {
                        // On diminue la quantité en stock
                        $sqlUpdateQuantite = "UPDATE Produit SET quantite = GREATEST(0, quantite - ?) WHERE noProduit = ?";
                        $stmtUpdate = mysqli_prepare($conn, $sqlUpdateQuantite);
                        mysqli_stmt_bind_param($stmtUpdate, 'ii', $quantite, $idProduit);
                        mysqli_stmt_execute($stmtUpdate);
                    }
                }
            }
        }

    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des stocks</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <?php include_once("menu.php") ?> <!-- On importe le menu -->
    <h2>Gestion des stocks</h2>
    <p>Accès <?php echo $_SESSION["typeUtilisateur"]?></p>
    <section>
    <?php
        // On récupère les types de produits et on les affiche par ordre alphabétique
        $sql = "SELECT noTypeProduit, nom FROM TypeProduit ORDER BY nom";
        $result = mysqli_query($conn, $sql);
        while ($typeProduit = $result->fetch_assoc()) { // Pour chaque ligne de la réponse à la requête SQL
            echo "<h3>{$typeProduit['nom']}</h3>";
            $typeID = $typeProduit['noTypeProduit'];
            // On récupère tous les produits de ce type de produits
            $sqlProduit = "SELECT noProduit, nom, quantite FROM Produit WHERE typeProduit = $typeID";
            $resultProduit = mysqli_query($conn, $sqlProduit);
            echo "<ul>";
            
            // On affiche ces produits
            while ($produit = $resultProduit->fetch_assoc()) { // Pour chaque ligne de la réponse à la requête SQL
                if ($produit['quantite'] == 0) {
                    echo "<li>{$produit['nom']} : <span style='color: red'>Stock épuisé</span></li>";
                } else {
                    echo "<li>{$produit['nom']} : {$produit['quantite']}</li>";
                }
                ?>
                <form method="POST" action="">
                    <button type="submit" name="action" value="enlever_<?= $produit['noProduit'] ?>">Enlever</button>
                    <input type="number" name="quantite" value="0" min="0">
                    <button type="submit" name="action" value="ajouter_<?= $produit['noProduit'] ?>">Ajouter</button>
                </form>
                <?php
            }
            echo "</ul>";
        }
    ?>
    </section>
    <section>
        <h3 class="titre">Ajouter un nouveau produit</h3>
        
        <form method="POST" action="">
            <label for="nom_produit">Nom du produit :</label>
            <input type="text" name="nom_produit" id="nom_produit" required>
            <br>
            
            <label for="quantite_produit">Quantité :</label>
            <input type="number" name="quantite_produit" id="quantite_produit" required min="0">
            <br>
            
            <label for="prix_unitaire">Prix unitaire :</label>
            <input type="number" name="prix_unitaire" id="prix_unitaire" required min="0" step="0.01">
            <br>
            
            <label for="type_produit">Type de produit :</label>
            <input type="text" name="type_produit" id="type_produit" required>
            <br>
            
            <button type="submit" name="ajouter_produit">Ajouter produit</button>
        </form>
    </section>
</body>
</html>
    