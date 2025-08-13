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

$message = "";
$message_color = "green";
$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_categorie = trim($_POST["nom_categorie"]);
    if (!empty($nom_categorie)) {
        $sql = "INSERT INTO categorie (nom_categorie, personne_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nom_categorie, $user_id);

        if ($stmt->execute()) {
            $message = "✅ Catégorie ajoutée avec succès.";
            $message_color = "green";
        } else {
            $message = "❌ Erreur lors de l'ajout : " . $conn->error;
            $message_color = "red";
        }
    } else {
        $message = "⚠️ Veuillez saisir un nom de catégorie.";
        $message_color = "orange";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Ajouter une catégorie - Gestion de budget</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background: #f7f7f7;
        color: #333;
    }
    .sidebar {
        position: fixed;
        top: 0; left: 0; bottom: 0;
        width: 200px;
        background-color: #2980b9;
        padding-top: 20px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
    }
    .sidebar a {
        color: white;
        padding: 12px 20px;
        text-decoration: none;
        font-weight: bold;
        border-left: 5px solid transparent;
        transition: background 0.3s, border-color 0.3s;
    }
    .sidebar a:hover {
        background-color: #1f5c87;
    }
    .sidebar a[style*="background:#2980b9;"] {
        background-color: #1f5c87;
        border-left-color: #27ae60;
    }
    .sidebar a.logout {
        color: #e74c3c !important;
        font-weight: bold;
    }
    .content {
        margin-left: 200px;
        padding: 40px 30px;
        max-width: 700px;
    }
    .container {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        animation: fadeIn 0.6s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    h1 {
        color: #2980b9;
        margin-bottom: 20px;
        text-align: center;
    }
    label {
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
    }
    input[type="text"] {
        width: 100%;
        padding: 10px;
        font-size: 1rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        box-sizing: border-box;
        margin-bottom: 20px;
    }
    button[type="submit"] {
        background-color: #27ae60;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 12px 20px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 100%;
    }
    button[type="submit"]:hover {
        background-color: #219150;
    }
    .message {
        text-align: center;
        font-weight: bold;
        margin-bottom: 20px;
        color: <?= $message_color ?>;
    }
    a.btn {
        display: inline-block;
        margin-top: 20px;
        color: #2980b9;
        text-decoration: none;
        font-weight: bold;
    }
    a.btn:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="sidebar">
    <a href="dashboard.php">🏠 Accueil</a>
    <a href="ajouter_categorie.php" style="background:#2980b9;">➕ Ajouter Catégorie</a>
    <a href="liste_categories.php">📋 Liste Catégories</a>
    <a href="logout.php" class="logout">🚪 Déconnexion</a>
</div>

<div class="content">
    <div class="container">
        <h1>Ajouter une catégorie</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="nom_categorie">Nom de la catégorie :</label>
            <input type="text" id="nom_categorie" name="nom_categorie" required>

            <button type="submit">Ajouter</button>
        </form>

        <a href="dashboard.php" class="btn">⬅ Retour au dashboard</a>
    </div>
</div>
</body>
</html>
