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
$sql = "SELECT sc.id, sc.nom_sous_categorie, c.nom_categorie
        FROM sous_categorie sc
        JOIN categorie c ON sc.categorie_id = c.id
        WHERE sc.personne_id = ? ORDER BY c.nom_categorie, sc.nom_sous_categorie";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erreur de préparation SQL : " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Liste des sous-catégories - Gestion de budget</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f7f7f7;
        margin: 20px;
        color: #333;
    }
    .top-right {
        position: fixed;
        top: 15px;
        right: 20px;
        z-index: 1000;
    }
    .top-right a {
        color: #2980b9;
        font-weight: bold;
        text-decoration: none;
        font-size: 1rem;
        position: relative;
        padding-left: 20px;
    }
    .top-right a::before {
        content: "←";
        position: absolute;
        left: 0;
        top: 0;
        color: #2980b9;
        font-size: 1.2rem;
    }
    .top-right a::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: -3px;
        height: 2px;
        background-color: #2980b9;
        transform: scaleX(0);
        transform-origin: center;
        transition: transform 0.3s ease;
    }
    .top-right a:hover::after {
        transform: scaleX(1);
    }

    .container {
        max-width: 700px;
        margin: 70px auto 0;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        animation: fadeIn 0.6s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    h2 {
        margin-bottom: 20px;
        color: #2980b9;
        text-align: center;
    }
    .btn-add {
        display: inline-block;
        margin-bottom: 15px;
        background-color: #27ae60;
        color: white;
        padding: 8px 12px;
        text-decoration: none;
        border-radius: 6px;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    .btn-add:hover {
        background-color: #219150;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    th {
        background-color: #2980b9;
        color: white;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
    .no-data {
        text-align: center;
        padding: 20px;
        color: #666;
    }
</style>
</head>
<body>

<div class="top-right">
    <a href="dashboard.php">Retour au Dashboard</a>
</div>

<div class="container">
    <h2>Liste des sous-catégories</h2>
    <a class="btn-add" href="ajouter_sous_categorie.php">+ Ajouter une sous-catégorie</a>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom sous-catégorie</th>
                <th>Catégorie</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row["id"]) ?></td>
                <td><?= htmlspecialchars($row["nom_sous_categorie"]) ?></td>
                <td><?= htmlspecialchars($row["nom_categorie"]) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="no-data">Aucune sous-catégorie trouvée.</p>
    <?php endif; ?>
</div>

</body>
</html>
