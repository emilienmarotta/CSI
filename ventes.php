<?php
require_once("config/database.php"); // Connexion à la BDD

session_start(); // Permet d'utiliser les variables de session

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { // On vérifie si l'utilisateur est connecté
    header("Location: login.php"); // Si non, on le redirige vers le Login
    exit();
}    

// Ajouter une vente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouterVente'])) {
   
    // mysqli_real_escape_string() permet d'éviter les injections SQL 
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $woofer = mysqli_real_escape_string($conn, $_POST['woofer']);
    $prixHT = floatval($_POST['prixHT']);
    $prixTTC = floatval($_POST['prixTTC']);

    // On génère un numéro de vente et de facture uniques 
    $idVente = uniqid("V");
    $idFacture = "F" . substr($idVente, 1);

    // Requête pour insérer la nouvelle vente dans la BDD
    $sqlVente = "INSERT INTO Vente (idnoVente, date, woofer, prixTTC, prixHT, etat) 
                 VALUES ('$idVente', '$date', '$woofer', '$prixTTC', '$prixHT', 'cree')";
    
    if (mysqli_query($conn, $sqlVente)) {
        // On insère la vente dans la BDD et on met à jour le stock
        if (!empty($_POST['produits']) && !empty($_POST['quantites'])) {
            $ventePossible = true;
        
            foreach ($_POST['produits'] as $index => $noProduit) { // Pour chaque produits de la vente
                $quantite = intval($_POST['quantites'][$index]);
        
                // On vérifie la quantité en stock avant d'insérer
                $resultStock = mysqli_query($conn, "SELECT quantite FROM Produit WHERE noProduit = '$noProduit'");
                $produit = mysqli_fetch_assoc($resultStock);
        
                if ($quantite > $produit['quantite']) {
                    echo "<p style='color:red;'>Erreur : Stock insuffisant pour le produit {$noProduit}.</p>";
                    $ventePossible = false;
                    break;
                }
            }
        
            if ($ventePossible) {
                foreach ($_POST['produits'] as $index => $noProduit) { // Pour chaque produits de la vente
                    $quantite = intval($_POST['quantites'][$index]);
        
                    // Requête d'insertion des produits de la vente
                    $sqlInsertProduit = "INSERT INTO Vente_Produit (idnoVente, noProduit, quantite) 
                                         VALUES ('$idVente', '$noProduit', '$quantite')";
                    mysqli_query($conn, $sqlInsertProduit);
        
                    // On met le stock à jour
                    $sqlUpdateStock = "UPDATE Produit SET quantite = quantite - $quantite 
                                       WHERE noProduit = '$noProduit'";
                    mysqli_query($conn, $sqlUpdateStock);
                }
        
                // On insère la facture 
                $sqlFacture = "INSERT INTO Facture (refFacture, prix, vente, etat) 
                               VALUES ('$idFacture', '$prixHT', '$idVente', 'generee')";
                mysqli_query($conn, $sqlFacture);
        
                header("Location: " . $_SERVER['PHP_SELF']); // Permet d'éviter d'avoir à réactualiser la page pour voir apparaître les modifications
                exit();
            }
        }
    } else {
        echo "<p style='color:red;'>Erreur lors de l'ajout : " . mysqli_error($conn) . "</p>";
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
    <?php include_once("menu.php") ?> <!-- On importe le menu -->
    <h2>Gestion des ventes</h2>
    <p>Accès <?php echo $_SESSION["typeUtilisateur"]?></p>

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
            <option value="prixHT" <?= (isset($_GET['sort']) && $_GET['sort'] == 'prixHT') ? 'selected' : '' ?>>Prix HT</option>
            <option value="prixTTC" <?= (isset($_GET['sort']) && $_GET['sort'] == 'prixTTC') ? 'selected' : '' ?>>Prix TTC</option>
        </select>
        </div>
        <button type="submit">Appliquer les filtres de recherche</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Numéro</th>
                <th>Date</th>
                <th>Woofer</th>
                <th>Prix HT</th>
                <th>Prix TTC</th>
                <th>Facture</th>
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
                        <td>{$vente['idnoVente']}</td>
                        <td>{$vente['date']}</td>
                        <td>{$vente['nomUtilisateur']}</td>
                        <td>{$vente['prixHT']} €</td>
                        <td>{$vente['prixTTC']} €</td>
                        <td>{$vente['refFacture']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>

    <h3>Enregistrer une nouvelle vente :</h3>
    <form id="form-nouvelle-vente" method="POST" action="">
        <label for="date">Date :</label>
        <input type="date" name="date" required>

        <label for="woofer">Woofer :</label>
        <select name="woofer" required>
            <option value="">-- Sélectionner un woofer --</option>
            <?php
            // Requête pour récupérer tous les woofers
            $sqlWoofer = "SELECT u.idCompte, u.nomUtilisateur FROM Woofer w JOIN Utilisateur u ON w.idCompte = u.idCompte";
            $resultWoofer = mysqli_query($conn, $sqlWoofer);
            while ($woofer = mysqli_fetch_assoc($resultWoofer)) { 
                echo "<option value='{$woofer['idCompte']}'>{$woofer['nomUtilisateur']}</option>";
            }
            ?>
        </select>

        <label for="produits">Produits :</label>
        <div id="conteneur-produits">
            <div class="selection-produit">
                <select name="produits[]" class="produit" required>
                    <option value="">-- Sélectionner un produit --</option>
                    <?php
                    // Requête pour récupérer tous les produits dont la quantité en stock est non nulle
                    $sqlProduit = "SELECT noProduit, nom, prixUnitaire, quantite FROM Produit WHERE quantite != 0";
                    $resultProduit = mysqli_query($conn, $sqlProduit);
                    while ($produit = mysqli_fetch_assoc($resultProduit)) {
                        echo "<option value='{$produit['noProduit']}' data-prix='{$produit['prixUnitaire']}' data-stock='{$produit['quantite']}'>
                                {$produit['nom']} (Stock: {$produit['quantite']}) - {$produit['prixUnitaire']} €
                              </option>";
                    }
                    ?>
                </select>
                <input type="number" name="quantites[]" class="quantite" min="1" required>
                <button type="button" class="supprimer-produit">Supprimer</button>
            </div>
        </div>
        <button type="button" id="ajouter-produit">Ajouter un produit</button>

        <label for="prixHT">Prix HT :</label>
        <input type="text" name="prixHT" id="prixHT" readonly required>

        <label for="prixTTC">Prix TTC :</label>
        <input type="text" name="prixTTC" id="prixTTC" readonly required>

        <button type="submit" name="ajouterVente">Ajouter Vente</button>
    </form>

    <script> 
        document.addEventListener("DOMContentLoaded", function () {
            
            const conteneurProduits = document.getElementById("conteneur-produits");
            const ajouterProduitBouton = document.getElementById("ajouter-produit");
            const prixHTSaisi = document.getElementById("prixHT");
            const prixTTC = document.getElementById("prixTTC");

            function updatePrix() {
                let totalHT = 0;
                document.querySelectorAll(".selection-produit").forEach(row => { // Pour chaque produit
                    const produitSelectionne = row.querySelector(".produit");
                    const quantiteSaisie = row.querySelector(".quantite");
                    const prixUnitaire = parseFloat(produitSelectionne.options[produitSelectionne.selectedIndex]?.dataset.prix) || 0;
                    const quantite = parseInt(quantiteSaisie.value) || 0;
                    totalHT += prixUnitaire * quantite;
                });
                // .toFixed(2) permet de convertir le prix en string à deux décimales
                prixHTSaisi.value = totalHT.toFixed(2);
                prixTTC.value = (totalHT * 1.2).toFixed(2);
            }

            // On met les prix à jour lorsque des modifications sont apportées à la vente
            conteneurProduits.addEventListener("input", updatePrix);

            ajouterProduitBouton.addEventListener("click", function () {
                const clone = conteneurProduits.firstElementChild.cloneNode(true);
                clone.querySelector(".produit").value = "";
                clone.querySelector(".quantite").value = "";
                conteneurProduits.appendChild(clone);
            });

            conteneurProduits.addEventListener("click", function (event) {
                if (event.target.classList.contains("supprimer-produit")) {
                    if (document.querySelectorAll(".selection-produit").length > 1) {
                        event.target.parentElement.remove();
                        updatePrix();
                    } else {
                        alert("Vous devez avoir au moins un produit.");
                    }
                }
            });

            function verifStock(event) {
                const quantiteSaisie = event.target;
                const produitSelectionne = quantiteSaisie.closest(".selection-produit").querySelector(".produit");
                const stockDisponible = parseInt(produitSelectionne.options[produitSelectionne.selectedIndex]?.dataset.stock) || 0;
                const quantiteDemandee = parseInt(quantiteSaisie.value) || 0;

                if (quantiteDemandee > stockDisponible) {
                    alert("Quantité insuffisante en stock !");
                    quantiteSaisie.value = stockDisponible; // On ajuste automatiquement à la quantité en stock
                }
            }

            document.getElementById("conteneur-produits").addEventListener("input", function (event) {
                if (event.target.classList.contains("quantite")) {
                    verifStock(event);
                }
            });
        });
    </script>
</body>
</html>
