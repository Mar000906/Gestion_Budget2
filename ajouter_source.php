<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "Mysql";
$dbname = "gestion_budgett";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_source = trim($_POST["nom_source"]);

    if (!$nom_source) {
        $message = "Le nom de la source est obligatoire.";
    } else {
        $sql = "INSERT INTO source (nom_source, personne_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Erreur de préparation : " . $conn->error);
        }
        $stmt->bind_param("si", $nom_source, $user_id);
        if ($stmt->execute()) {
            $message = "Source ajoutée avec succès.";
        } else {
            $message = "Erreur lors de l'ajout : " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter une source - Gestion de budget</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f7f7f7; }
        form { background: white; padding: 20px; max-width: 400px; border-radius: 6px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 20px; background: #2980b9; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1c5980; }
        .message { margin-top: 15px; color: red; }
        a { color: #2980b9; text-decoration: none; }
    </style>
</head>
<body>
    <h2>Ajouter une source</h2>
    <?php if ($message) echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; ?>
    <form method="POST" action="ajouter_source.php">
        <label for="nom_source">Nom de la source :</label>
        <input type="text" id="nom_source" name="nom_source" required />

        <button type="submit">Ajouter la source</button>
    </form>
    <p><a href="dashboard.php">Retour au dashboard</a></p>
</body>
</html>
