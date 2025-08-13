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

// Récupérer le compte source depuis l'URL
$source_id = isset($_GET['source_id']) ? (int)$_GET['source_id'] : 0;

// Vérifier si le compte source existe et appartient à l'utilisateur
$stmt_check = $conn->prepare("SELECT nom_compte, montant FROM compte WHERE id = ? AND personne_id = ?");
$stmt_check->bind_param("ii", $source_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$source_compte = $result_check->fetch_assoc();

if (!$source_compte) {
    die("Compte source non spécifié ou inexistant.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cible_id = $_POST["compte_cible"];
    $montant_transfer = $_POST["montant"];

    // Vérification de validité
    if ($montant_transfer <= 0) {
        $message = "Le montant doit être supérieur à 0.";
    } elseif ($montant_transfer > $source_compte['montant']) {
        $message = "Le montant dépasse le solde du compte source.";
    } else {
        // Débit du compte source
        $stmt1 = $conn->prepare("UPDATE compte SET montant = montant - ? WHERE id = ?");
        $stmt1->bind_param("di", $montant_transfer, $source_id);
        $stmt1->execute();

        // Crédit du compte cible
        $stmt2 = $conn->prepare("UPDATE compte SET montant = montant + ? WHERE id = ?");
        $stmt2->bind_param("di", $montant_transfer, $cible_id);
        $stmt2->execute();

        $message = "Transfert effectué avec succès.";
        // Mettre à jour le montant du compte source pour affichage
        $source_compte['montant'] -= $montant_transfer;
    }
}

// Récupérer les comptes cibles (exclure le compte source)
$stmt_cibles = $conn->prepare("SELECT id, nom_compte FROM compte WHERE personne_id = ? AND id != ?");
$stmt_cibles->bind_param("ii", $user_id, $source_id);
$stmt_cibles->execute();
$result_cibles = $stmt_cibles->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Transférer un compte - Gestion de budget</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f7f7f7; }
.container { background: white; padding: 20px; max-width: 400px; margin: 50px auto; border-radius: 6px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
label { display: block; margin-top: 15px; font-weight: bold; }
input, select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
button { margin-top: 20px; background: #2980b9; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #1f6690; }
.message { margin-top: 15px; color: green; font-weight: bold; }
.top-right { position: fixed; top: 15px; right: 20px; }
.top-right a { color: #2980b9; font-weight: bold; text-decoration: none; }
</style>
</head>
<body>

<div class="top-right">
    <a href="liste_comptes.php">← Retour à la liste des comptes</a>
</div>

<div class="container">
    <h2>Transférer depuis : <?= htmlspecialchars($source_compte['nom_compte']) ?></h2>
    <p>Solde actuel : <?= number_format($source_compte['montant'], 2, ',', ' ') ?> DH</p>

    <?php if ($message) echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; ?>

    <?php if ($result_cibles->num_rows > 0): ?>
    <form method="POST" action="">
        <label for="compte_cible">Compte cible :</label>
        <select id="compte_cible" name="compte_cible" required>
            <option value="">-- Sélectionnez un compte --</option>
            <?php while ($row = $result_cibles->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nom_compte']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="montant">Montant à transférer :</label>
        <input type="number" step="0.01" id="montant" name="montant" required min="0" max="<?= $source_compte['montant'] ?>">

        <button type="submit">Transférer</button>
    </form>
    <?php else: ?>
        <p>Aucun autre compte disponible pour transfert.</p>
    <?php endif; ?>
</div>

</body>
</html>
