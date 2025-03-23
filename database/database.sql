-- Création de la base de données

DROP DATABASE IF EXISTS farm_db;

CREATE DATABASE farm_db;
USE farm_db;

CREATE TABLE Utilisateur (
    idCompte INT PRIMARY KEY AUTO_INCREMENT,
    nomUtilisateur VARCHAR(100) NOT NULL,
    motDePasse VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    typeUtilisateur ENUM('Woofer', 'Responsable') NOT NULL,
    dateCreation DATE NOT NULL,
    etat ENUM('connecte', 'deconnecte') NOT NULL,
    derniereConnexion DATE NOT NULL
);

CREATE TABLE Woofer (
    idCompte INT PRIMARY KEY,
    prenom VARCHAR(30) NOT NULL,
    nom VARCHAR(30) NOT NULL,
    diplomes TEXT NULL,
    etat ENUM('inscrit', 'affecte', 'finMission'),
    dateDebut DATE NOT NULL,
    dateFin DATE NOT NULL,
    photo VARCHAR(255) NOT NULL,
    FOREIGN KEY (idCompte) REFERENCES Utilisateur(idCompte)
);

CREATE TABLE Responsable (
    idCompte INT PRIMARY KEY,
    droitsAdmin BOOLEAN NOT NULL,
    FOREIGN KEY (idCompte) REFERENCES Utilisateur(idCompte)
);

CREATE TABLE TypeProduit (
    noTypeProduit INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(30) NOT NULL,
    description TEXT NULL,
    etat ENUM('disponible', 'supprime') NOT NULL
);

CREATE TABLE Vente (
    idnoVente VARCHAR(30) PRIMARY KEY,
    date DATE NOT NULL,
    prixTTC FLOAT NOT NULL,
    prixHT FLOAT NOT NULL,
    woofer INT NOT NULL,
    etat ENUM('cree', 'enregistree', 'enAttente', 'payee', 'annulee', 'refusee', 'archivee') NOT NULL,
    FOREIGN KEY (woofer) REFERENCES Woofer(idCompte)
);

CREATE TABLE Facture (
    idFacture INT PRIMARY KEY AUTO_INCREMENT,
    refFacture VARCHAR(30) UNIQUE NOT NULL,
    prix FLOAT NOT NULL,
    etat ENUM('archivee', 'generee', 'payee') NOT NULL,
    vente VARCHAR(30),
    FOREIGN KEY (vente) REFERENCES Vente(idnoVente)
);

CREATE TABLE Produit (
    noProduit INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(30) NOT NULL,
    quantite INT NOT NULL,
    photo VARCHAR(255),
    etat ENUM('stocke', 'misEnVente', 'vendu', 'epuise'),
    prixUnitaire FLOAT NOT NULL,
    typeProduit INT NOT NULL,
    FOREIGN KEY (typeProduit) REFERENCES TypeProduit(noTypeProduit)
);

CREATE TABLE Vente_Produit (
    idnoVente VARCHAR(30),
    noProduit INT,
    quantite INT NOT NULL,
    PRIMARY KEY (idnoVente, noProduit),
    FOREIGN KEY (idnoVente) REFERENCES Vente(idnoVente),
    FOREIGN KEY (noProduit) REFERENCES Produit(noProduit)
);

CREATE TABLE Client (
    idClient INT PRIMARY KEY AUTO_INCREMENT,
    numTel VARCHAR(10) NOT NULL,
    email VARCHAR(50) NOT NULL, 
    etat ENUM('inscrit', 'participant', 'desinscrit') NOT NULL
);

CREATE TABLE Atelier (
    noAtelier INT PRIMARY KEY AUTO_INCREMENT,
    theme ENUM('fabricationFromage', 'cultureBiologique', 'decouverteSoinsAnimaux') NOT NULL,
    nbMaxParticipants INT NOT NULL,
    etat ENUM('cree', 'enCours', 'annule', 'termine', 'modifie', 'reporte') NOT NULL,
    prix FLOAT NOT NULL,
    date DATE NOT NULL,
    woofer INT NOT NULL,
    FOREIGN KEY (woofer) REFERENCES Woofer(idCompte)
);

CREATE TABLE Participe (
    idAtelier INT NOT NULL,
    idClient INT NOT NULL,
    PRIMARY KEY (idAtelier, idClient),
    FOREIGN KEY (idAtelier) REFERENCES Atelier(noAtelier),
    FOREIGN KEY (idClient) REFERENCES Client(idClient)
);

CREATE TABLE Activite (
    noActivite INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(30) NOT NULL,
    description TEXT NULL, 
    etat ENUM('prevue', 'enCours', 'annulee', 'terminee') NOT NULL
);

CREATE TABLE EstAffecte (
    noActivite INT NOT NULL,
    idCompteWoofer INT NOT NULL,
    PRIMARY KEY (noActivite, idCompteWoofer),
    FOREIGN KEY (noActivite) REFERENCES Activite(noActivite),
    FOREIGN KEY (idCompteWoofer) REFERENCES Woofer(idCompte)
);

CREATE TABLE Competence (
    noCompetence INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL
);

CREATE TABLE Possede (
    noCompetence INT NOT NULL,
    idCompteWoofer INT NOT NULL,
    etat ENUM('acquise', 'nonAcquise') NOT NULL,
    PRIMARY KEY (noCompetence, idCompteWoofer),
    FOREIGN KEY (noCompetence) REFERENCES Competence(noCompetence),
    FOREIGN KEY (idCompteWoofer) REFERENCES Woofer(idCompte)
);


-- Remplissage de la base de données avec des données fictives

INSERT INTO Utilisateur (nomUtilisateur, motDePasse, email, typeUtilisateur, dateCreation, etat, derniereConnexion) VALUES
('jdupont', 'password123', 'jdupont@email.com', 'Woofer', '2025-01-01', 'deconnecte', '2025-01-01'),
('msmith', 'securepass', 'msmith@email.com', 'Responsable', '2025-01-01', 'deconnecte', '2025-01-01'),
('awalker', 'qwerty123', 'awalker@email.com', 'Woofer', '2025-01-01', 'deconnecte', '2025-01-01'),
('bthomson', 'password456', 'bthomson@email.com', 'Woofer', '2025-01-01', 'deconnecte', '2025-01-01'),
('cjohnson', 'mypassword123', 'cjohnson@email.com', 'Responsable', '2025-01-01', 'deconnecte', '2025-01-01'),
('dperez', 'strongpass567', 'dperez@email.com', 'Woofer', '2025-01-01', 'deconnecte', '2025-01-01');

INSERT INTO Woofer (idCompte, nom, prenom, diplomes, etat, dateDebut, dateFin, photo) VALUES
(1, 'Dupont', 'Jean', 'Bac+2 en agriculture', 'inscrit', '2022-02-01', '2026-02-01', 'photo1.jpg'),
(3, 'Walker', 'Alan', 'Certificat en élevage', 'inscrit', '2023-05-01', '2026-05-01', 'photo3.jpg'),
(4, 'Thomson', 'Bety', 'BTS en agroalimentaire', 'finMission', '2022-06-01', '2026-06-01', 'photo4.jpg'),
(6, 'Perez', 'Dom', 'Licence en élevage équin', 'finMission', '2021-08-10', '2026-08-10', 'photo5.jpg');

INSERT INTO Responsable (idCompte, droitsAdmin) VALUES
(2, TRUE),
(6, FALSE);

INSERT INTO TypeProduit (nom, description, etat) VALUES
('Lait', 'Lait frais de la ferme', 'disponible'),
('Oeufs', 'Oeufs frais des poules de la ferme', 'disponible'),
('Légumes', 'Légumes frais cultivés dans la ferme', 'disponible'),
('Fromage', 'Fromage bio produit sur place', 'disponible');

INSERT INTO Produit (nom, quantite, photo, etat, prixUnitaire, typeProduit) VALUES
('Lait cru', 50, 'lait_cru.jpg', 'misEnVente', 1.5, 1),
('Fromage de chèvre', 10, 'fromage_chevre.jpg', 'misEnVente', 5.0, 4),
('Oeufs de poule', 400, 'oeufs.jpg', 'misEnVente', 0.3, 2),
('Fromage de vache', 25, 'fromage_vache.jpg', 'misEnVente', 4.5, 4),
('Carottes', 500, 'carottes.jpg', 'misEnVente', 0.2, 3),
('Pommes de terre', 700, 'pommes_de_terre.jpg', 'misEnVente', 0.2, 3),
('Fromage de brebis', 15, 'fromage_brebis.jpg', 'misEnVente', 6.0, 4);

INSERT INTO Vente (idnoVente, date, prixTTC, prixHT, woofer, etat) VALUES
('V67dd6953c5a71', '2025-03-01', 30.0, 25.0, 1, 'payee'),
('V67dd6a8ac99d9', '2025-03-05', 54.0, 45.0, 4, 'payee'),
('V67d9d512aa78c', '2025-03-10', 120.0, 100.0, 6, 'payee'),
('V67dd6982120a2', '2025-02-8', 120.0, 100.0, 4, 'payee'),
('V67dd6712c6197', '2024-03-7', 51.0, 42.5, 4, 'payee');

INSERT INTO Vente_Produit (idnoVente, noProduit, quantite) VALUES
('V67dd6953c5a71', 1, 10),
('V67dd6953c5a71', 2, 2),
('V67d9d512aa78c', 3, 1),
('V67dd6712c6197', 6, 20),
('V67dd6712c6197', 3, 6);

INSERT INTO Facture (refFacture, prix, etat, vente) VALUES
('F67dd6953c5a71', 30.0, 'payee', 'V67dd6953c5a71'),
('F67dd6a8ac99d9', 54.0, 'payee', 'V67dd6a8ac99d9'),
('F67d9d512aa78c', 120.0, 'archivee', 'V67d9d512aa78c'),
('F67dd6982120a2', 120.0, 'archivee', 'V67dd6982120a2'),
('F67dd6712c6197', 51.0, 'payee', 'V67dd6712c6197');

INSERT INTO Client (numTel, etat, email) VALUES
('0612345678', 'desinscrit', 'client1@mail.com'),
('0612945678', 'inscrit', 'client2@mail.com'),
('0623445677', 'inscrit', 'client3@mail.fr');

INSERT INTO Atelier (theme, nbMaxParticipants, etat, prix, date, woofer) VALUES
('fabricationFromage', 10, 'cree', 20.0, '2025-04-15', 1),
('cultureBiologique', 12, 'cree', 25.0, '2025-04-20', 4),
('cultureBiologique', 2, 'cree', 50.0, '2025-04-21', 4),
('fabricationFromage', 5, 'cree', 10.0, '2025-05-21', 6),
('fabricationFromage', 5, 'cree', 10.0, '2025-06-21', 6),
('decouverteSoinsAnimaux', 8, 'annule', 30.0, '2025-04-25', 6);

INSERT INTO Participe (idAtelier, idClient) VALUES
(1, 1),
(2, 2),
(2, 3);

INSERT INTO Activite (nom, description, etat) VALUES
('Traite des vaches', 'Apprentissage de la traite manuelle', 'prevue'),
('Soin des animaux', 'Apprentissage des soins à apporter aux animaux de la ferme', 'prevue'),
('Récolte des légumes', 'Initiation à la récolte des légumes en culture biologique', 'prevue');

INSERT INTO EstAffecte (noActivite, idCompteWoofer) VALUES
(1, 1),
(2, 4),
(3, 6);

INSERT INTO Competence (nom) VALUES
('Soins aux animaux'),
('Gestion des cultures biologiques'),
('Soins aux chevaux');

INSERT INTO Possede (noCompetence, idCompteWoofer, etat) VALUES
(1, 1, 'acquise'),
(2, 4, 'acquise'),
(3, 6, 'acquise');