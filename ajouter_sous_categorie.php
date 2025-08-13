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

// Récupérer les catégories de l'utilisateur
$sql_categories = "SELECT id, nom_categorie FROM categorie WHERE personne_id = ?";
$stmt_categories = $conn->prepare($sql_categories);
$stmt_categories->bind_param("i", $user_id);
$stmt_categories->execute();
$result_categories = $stmt_categories->get_result();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_sous_categorie = trim($_POST["nom_sous_categorie"]);
    $categorie_id = intval($_POST["categorie_id"]);

    if (!empty($nom_sous_categorie) && $categorie_id > 0) {
        $sql = "INSERT INTO sous_categorie (nom_sous_categorie, categorie_id, personne_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $nom_sous_categorie, $categorie_id, $user_id);

        if ($stmt->execute()) {
            $message = "✅ Sous-catégorie ajoutée avec succès.";
            $message_color = "green";
        } else {
            $message = "❌ Erreur lors de l'ajout : " . $conn->error;
            $message_color = "red";
        }
    } else {
        $message = "⚠️ Veuillez remplir tous les champs.";
        $message_color = "orange";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Ajouter une sous-catégorie - Gestion de budget</title>
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
    input[type="text"], select {
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
    <a href="ajouter_sous_categorie.php" style="background:#2980b9;">➕ Ajouter Sous-catégorie</a>
    <a href="liste_sous_categories.php">📋 Liste Sous-catégories</a>
    <a href="logout.php" class="logout">🚪 Déconnexion</a>
</div>

<div class="content">
    <div class="container">
        <h1>Ajouter une sous-catégorie</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="nom_sous_categorie">Nom de la sous-catégorie :</label>
            <input type="text" id="nom_sous_categorie" name="nom_sous_categorie" required>

            <label for="categorie_id">Catégorie :</label>
            <select id="categorie_id" name="categorie_id" required>
                <option value="">-- Choisissez une catégorie --</option>
                <?php while ($row = $result_categories->fetch_assoc()) : ?>
                    <option value="<?= $row["id"]; ?>"><?= htmlspecialchars($row["nom_categorie"]); ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Ajouter</button>
        </form>

        <a href="dashboard.php" class="btn">⬅ Retour au dashboard</a>
    </div>
</div>
</body>
</html>
