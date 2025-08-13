<?php
$host = "localhost";
$user = "root"; // nom d'utilisateur MySQL
$pass = "Mysql"; // ton mot de passe MySQL
$dbname = "gestion_budgett"; // nom de ta base

// Connexion à MySQL
$conn = new mysqli($host, $user, $pass, $dbname);

// Vérifier si la connexion marche
if ($conn->connect_error) {
    die("Erreur de connexion à la base : " . $conn->connect_error);
}
?>
