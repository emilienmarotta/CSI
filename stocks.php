<?php
    require_once("config/database.php"); // On importe la BDD

    session_start(); // Permet d'utiliser les variables de session
    
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { // On vérifie si l'utilisateur est connecté
        header("Location: login.php");
        exit();
    }    
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // Supprimer un produit
        // if (isset($_POST['action']) && isset($_POST['confirm_delete'])) {
        //     $action = $_POST['action'];
            
        //     preg_match('/_(\d+)$/', $action, $str);
        //     $idProduit = $str[1];
            
        //     if (strpos($action, 'supprimer_') !== false) {
        //         // Demander confirmation pour supprimer
        //         $confirmation = $_POST['confirm_delete'];

        //         if ($confirmation == 'oui') {
        //             // Vérifier si ce produit est référencé dans d'autres tables avant de le supprimer
        //             $sqlCheckReferences = "SELECT COUNT(*) FROM Vente_Produit WHERE noProduit = ? AND (SELECT etat FROM Vente WHERE idnoVente = Vente_Produit.idnoVente) NOT IN ('payee', 'archivee')";
        //             $stmtCheck = mysqli_prepare($conn, $sqlCheckReferences);
        //             mysqli_stmt_bind_param($stmtCheck, 'i', $idProduit);
        //             mysqli_stmt_execute($stmtCheck);
        //             mysqli_stmt_bind_result($stmtCheck, $countReferences);
        //             mysqli_stmt_fetch($stmtCheck);

        //             if ($countReferences == 0) {
        //                 // Supprimer le produit
        //                 $sqlDeleteProduit = "DELETE FROM Produit WHERE noProduit = ?";
        //                 $stmt = mysqli_prepare($conn, $sqlDeleteProduit);
        //                 mysqli_stmt_bind_param($stmt, 'i', $idProduit);
        //                 mysqli_stmt_execute($stmt);

        //                 // Vérifier s'il reste des produits pour ce type de produit
        //                 $sqlCheckProducts = "SELECT COUNT(*) FROM Produit WHERE typeProduit IN (SELECT typeProduit FROM Produit WHERE noProduit = ?)";
        //                 $stmtCheck = mysqli_prepare($conn, $sqlCheckProducts);
        //                 mysqli_stmt_bind_param($stmtCheck, 'i', $idProduit);
        //                 mysqli_stmt_execute($stmtCheck);
        //                 mysqli_stmt_bind_result($stmtCheck, $countProducts);
        //                 mysqli_stmt_fetch($stmtCheck);

        //                 // Si aucun produit n'est associé à ce type, supprimer le type de produit
        //                 if ($countProducts == 0) {
        //                     $sqlDeleteType = "DELETE FROM TypeProduit WHERE noTypeProduit = (SELECT typeProduit FROM Produit WHERE noProduit = ?)";
        //                     $stmtDeleteType = mysqli_prepare($conn, $sqlDeleteType);
        //                     mysqli_stmt_bind_param($stmtDeleteType, 'i', $idProduit);
        //                     mysqli_stmt_execute($stmtDeleteType);
        //                 }
        //             } else {
        //                 echo "Le produit ne peut pas être supprimé car il est utilisé dans des ventes en cours ou déjà enregistrées.";
        //             }
        //         }
        //     }
        // }


        // Ajouter un produit
        if (isset($_POST['ajouter_produit'])) {
            $nomProduit = mysqli_real_escape_string($conn, $_POST['nom_produit']);
            $quantite = (int)$_POST['quantite_produit'];
            $prixUnitaire = (float)$_POST['prix_unitaire'];
            $nomType = mysqli_real_escape_string($conn, $_POST['type_produit']);

            // Vérifier si le type de produit existe déjà
            $sqlCheckType = "SELECT noTypeProduit FROM TypeProduit WHERE nom = ?";
            $stmt = mysqli_prepare($conn, $sqlCheckType);
            mysqli_stmt_bind_param($stmt, 's', $nomType);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                mysqli_stmt_bind_result($stmt, $idTypeProduit);
                mysqli_stmt_fetch($stmt);
            } else {
                // Insérer un nouveau type de produit
                $sqlInsertType = "INSERT INTO TypeProduit (nom, etat) VALUES (?, 'disponible')";
                $stmtInsertType = mysqli_prepare($conn, $sqlInsertType);
                mysqli_stmt_bind_param($stmtInsertType, 's', $nomType);
                mysqli_stmt_execute($stmtInsertType);
                $idTypeProduit = mysqli_insert_id($conn);
            }

            // Insérer le nouveau produit
            $sqlInsertProduit = "INSERT INTO Produit (nom, quantite, etat, prixUnitaire, typeProduit) VALUES (?, ?, 'stocke', ?, ?)";
            $stmtProduit = mysqli_prepare($conn, $sqlInsertProduit);
            mysqli_stmt_bind_param($stmtProduit, 'sidi', $nomProduit, $quantite, $prixUnitaire, $idTypeProduit);
            mysqli_stmt_execute($stmtProduit);
        }

        // Mettre à jour la quantité d'un produit
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
                preg_match('/_(\d+)$/', $action, $str);
                $idProduit = $str[1];

                // Vérifier l'action
                if (strpos($action, 'ajouter_') !== false) {
                    $quantite = (int)$_POST['quantite'];
                    if ($quantite > 0) {
                        // Mettre à jour la quantité en l'ajoutant
                        $sqlUpdateQuantite = "UPDATE Produit SET quantite = quantite + ? WHERE noProduit = ?";
                        $stmtUpdate = mysqli_prepare($conn, $sqlUpdateQuantite);
                        mysqli_stmt_bind_param($stmtUpdate, 'ii', $quantite, $idProduit);
                        mysqli_stmt_execute($stmtUpdate);
                    }
                } elseif (strpos($action, 'enlever_') !== false) {
                    $quantite = (int)$_POST['quantite'];
                    if ($quantite > 0) {
                        // Mettre à jour la quantité en la diminuant
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
    <?php include_once("menu.php") ?>
    <h2>Gestion des stocks</h2>
    <p>Accès <?php echo $_SESSION["typeUtilisateur"]?></p>
    <div>
    <?php
        $sql = "SELECT noTypeProduit, nom FROM TypeProduit ORDER BY nom";
        $result = mysqli_query($conn, $sql);
        while ($typeProduit = $result->fetch_assoc()) {
            echo "<h3>{$typeProduit['nom']}</h3>";
            $typeID = $typeProduit['noTypeProduit'];
            $sqlProduit = "SELECT noProduit, nom, quantite FROM Produit WHERE typeProduit = $typeID";
            $resultProduit = mysqli_query($conn, $sqlProduit);
            echo "<ul>";

            while ($produit = $resultProduit->fetch_assoc()) {
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
                    <button type="submit" name="action" value="supprimer_<?= $produit['noProduit'] ?>" onclick="return confirmDelete()">Supprimer</button>
                </form>
                <?php
            }
            echo "</ul>";
        }
    ?>
    </div>
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

    <script>
        function confirmDelete() {
            return confirm("Êtes-vous sûr de vouloir supprimer ce produit ? Cela peut entraîner la suppression du type de produit si aucun produit n'est associé.");
        }
    </script>
</body>
</html>
