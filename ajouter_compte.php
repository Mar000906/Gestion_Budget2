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

// Ajouter un nouveau compte
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_compte = trim($_POST["nom_compte"]);
    $montant = $_POST["montant"] ?? 0;

    if (!$nom_compte) {
        $message = "Le nom du compte est obligatoire.";
    } else {
        $sql = "INSERT INTO compte (nom_compte, montant, personne_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Erreur de préparation : " . $conn->error);
        }
        $stmt->bind_param("sdi", $nom_compte, $montant, $user_id);
        if ($stmt->execute()) {
            $message = "Compte ajouté avec succès.";
        } else {
            $message = "Erreur lors de l'ajout : " . $stmt->error;
        }
    }
}

// Récupérer tous les comptes de l'utilisateur
$sql_comptes = "SELECT id, nom_compte, montant FROM compte WHERE personne_id = ?";
$stmt_comptes = $conn->prepare($sql_comptes);
$stmt_comptes->bind_param("i", $user_id);
$stmt_comptes->execute();
$result_comptes = $stmt_comptes->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un compte - Gestion de budget</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f7f7f7; }
        form, .compte-list { background: white; padding: 20px; max-width: 500px; border-radius: 6px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 20px; background: #2980b9; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1c5980; }
        .message { margin-top: 15px; color: red; }
        a { color: #2980b9; text-decoration: none; margin-left: 10px; }
        .compte-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee; }
        .compte-item:last-child { border-bottom: none; }
        .transfer-btn { background: #27ae60; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .transfer-btn:hover { background: #1e8449; }
    </style>
</head>
<body>
    <h2>Ajouter un compte</h2>
    <?php if ($message) echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; ?>

    <form method="POST" action="ajouter_compte.php">
        <label for="nom_compte">Nom du compte :</label>
        <input type="text" id="nom_compte" name="nom_compte" required />

        <label for="montant">Montant initial :</label>
        <input type="number" step="0.01" id="montant" name="montant" value="0" min="0" />

        <button type="submit">Ajouter le compte</button>
    </form>

   

    <p><a href="dashboard.php">Retour au dashboard</a></p>
</body>
</html>




