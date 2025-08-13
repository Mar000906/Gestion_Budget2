<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT r.id, r.montant, r.date, s.nom_source
    FROM ressource r
    LEFT JOIN source s ON r.source_id = s.id
    WHERE r.personne_id = ?
    ORDER BY r.date DESC
";

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
<title>Liste des ressources</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f7f7f7;
        margin: 20px;
        color: #333;
    }
    h1 {
        color: #2980b9;
        text-align: center;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        max-width: 800px;
        margin: auto;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
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
    .top-right {
        position: fixed;
        top: 15px;
        right: 20px;
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
</style>
</head>
<body>

<div class="top-right">
    <a href="dashboard.php">Retour au Dashboard</a>
</div>

<h1>Liste des ressources</h1>

<?php if ($result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>Montant (€)</th>
            <th>Date</th>
            <th>Source</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= number_format($row['montant'], 2, ',', ' ') ?></td>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['date']))) ?></td>
            <td><?= htmlspecialchars($row['nom_source'] ?? 'N/A') ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <p class="no-data">Aucune ressource trouvée.</p>
<?php endif; ?>

</body>
</html>
