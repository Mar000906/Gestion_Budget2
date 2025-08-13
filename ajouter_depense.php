<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION["user_id"];

// Récupérer les comptes
$sql_comptes = "SELECT id, nom_compte FROM compte WHERE personne_id = ?";
$stmt_comptes = $conn->prepare($sql_comptes);
$stmt_comptes->bind_param("i", $user_id);
$stmt_comptes->execute();
$result_comptes = $stmt_comptes->get_result();

// Récupérer les sources
$sql_sources = "SELECT id, nom_source FROM source WHERE personne_id = ?";
$stmt_sources = $conn->prepare($sql_sources);
$stmt_sources->bind_param("i", $user_id);
$stmt_sources->execute();
$result_sources = $stmt_sources->get_result();

// Récupérer les catégories
$sql_cats = "SELECT id, nom_categorie FROM categorie WHERE personne_id = ?";
$stmt_cats = $conn->prepare($sql_cats);
$stmt_cats->bind_param("i", $user_id);
$stmt_cats->execute();
$result_cats = $stmt_cats->get_result();

// Récupérer les sous-catégories
$sql_subcats = "SELECT id, nom_sous_categorie FROM sous_categorie WHERE personne_id = ?";
$stmt_subcats = $conn->prepare($sql_subcats);
$stmt_subcats->bind_param("i", $user_id);
$stmt_subcats->execute();
$result_subcats = $stmt_subcats->get_result();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $montant = $_POST["montant"];
    $date = $_POST["date"];
    $compte_id = $_POST["compte"];
    $source_id = $_POST["source"];
    $sous_categorie_id = $_POST["sous_categorie"];

    $sql_insert = "INSERT INTO depense (montant, date, compte_id, source_id, sous_categorie_id, personne_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        die("Erreur dans la préparation de la requête SQL : " . $conn->error);
    }

    $stmt_insert->bind_param("dsiiii", $montant, $date, $compte_id, $source_id, $sous_categorie_id, $user_id);

    if ($stmt_insert->execute()) {
        $message = "Dépense ajoutée avec succès.";
    } else {
        $message = "Erreur : " . $stmt_insert->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter une dépense</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background: #040607ff;
        padding: 20px;
        margin: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
      }
      .top-bar {
        text-align: right;
        margin-bottom: 20px;
      }
      .top-bar a {
        color: #2980b9;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        transition: color 0.3s ease;
      }
      .top-bar a:hover {
        color: #1f5a86;
        text-decoration: underline;
      }
      form {
        background: white;
        padding: 20px 30px;
        border-radius: 8px;
        max-width: 450px;
        margin: auto;
        box-shadow: 0 10px 25px rgba(138, 79, 79, 0.1);
        animation: fadeInUp 0.8s ease forwards, pulse 1.5s ease 0.3s;
      }
      label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        color: #1e0a77ff;
      }
      input, select {
        width: calc(100% - 40px);
        padding: 8px 15px;
        margin-bottom: 15px;
        border: 1.5px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
      }
      input:focus, select:focus {
        border-color: #000000ff;
        outline: none;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.4);
      }
      .select-container {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
      }
      .select-container select {
        flex-grow: 1;
      }
      .add-button {
        margin-left: 8px;
        background-color: #27ae60;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 14px;
        cursor: pointer;
        font-weight: bold;
        font-size: 1.3rem;
        text-decoration: none;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 8px rgba(39, 174, 96, 0.4);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
      }
      .add-button:hover {
        background-color: #219150;
        box-shadow: 0 6px 12px rgba(33, 145, 80, 0.7);
      }
      button[type="submit"] {
        width: 100%;
        padding: 14px;
        background-color: #070b0eff;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 1.1rem;
        cursor: pointer;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(41, 128, 185, 0.5);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
      }
      button[type="submit"]:hover {
        background-color: #1f5a86;
        box-shadow: 0 7px 20px rgba(31, 90, 134, 0.7);
      }
      .message {
        text-align: center;
        color: green;
        margin-bottom: 15px;
        font-weight: bold;
        font-size: 1.1rem;
      }

      /* Animations */
      @keyframes fadeInUp {
        0% {
          opacity: 0;
          transform: translateY(20px);
        }
        100% {
          opacity: 1;
          transform: translateY(0);
        }
      }
      @keyframes pulse {
        0%, 100% {
          transform: scale(1);
          box-shadow: 0 0 0 rgba(39, 174, 96, 0.7);
        }
        50% {
          transform: scale(1.05);
          box-shadow: 0 0 15px rgba(39, 174, 96, 0.7);
        }
      }
    </style>
</head>
<body>

<div class="top-bar">
    <a href="dashboard.php" title="Retour au dashboard">← Retour au dashboard</a>
</div>

<?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post" action="">
    <label for="date">Date :</label>
    <input type="date" id="date" name="date" required>

    <label for="montant">Montant :</label>
    <input type="number" step="0.01" id="montant" name="montant" required>

    <label for="compte">Compte :</label>
    <div class="select-container">
        <select id="compte" name="compte" required>
            <option value="">-- Sélectionnez un compte --</option>
            <?php while ($compte = $result_comptes->fetch_assoc()): ?>
                <option value="<?= $compte['id'] ?>"><?= htmlspecialchars($compte['nom_compte']) ?></option>
            <?php endwhile; ?>
        </select>
        <a href="ajouter_compte.php" class="add-button" title="Ajouter un compte">+</a>
    </div>

    <label for="source">Source :</label>
    <div class="select-container">
        <select id="source" name="source" required>
            <option value="">-- Sélectionnez une source --</option>
            <?php while ($source = $result_sources->fetch_assoc()): ?>
                <option value="<?= $source['id'] ?>"><?= htmlspecialchars($source['nom_source']) ?></option>
            <?php endwhile; ?>
        </select>
        <a href="ajouter_source.php" class="add-button" title="Ajouter une source">+</a>
    </div>

    <label for="categorie">Catégorie :</label>
    <div class="select-container">
        <select id="categorie" name="categorie" required>
            <option value="">-- Sélectionnez une catégorie --</option>
            <?php while ($cat = $result_cats->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom_categorie']) ?></option>
            <?php endwhile; ?>
        </select>
        <a href="ajouter_categorie.php" class="add-button" title="Ajouter une catégorie">+</a>
    </div>

    <label for="sous_categorie">Sous-catégorie :</label>
    <div class="select-container">
        <select id="sous_categorie" name="sous_categorie" required>
            <option value="">-- Sélectionnez une sous-catégorie --</option>
            <?php while ($subcat = $result_subcats->fetch_assoc()): ?>
                <option value="<?= $subcat['id'] ?>"><?= htmlspecialchars($subcat['nom_sous_categorie']) ?></option>
            <?php endwhile; ?>
        </select>
        <a href="ajouter_sous_categorie.php" class="add-button" title="Ajouter une sous-catégorie">+</a>
    </div>

    <button type="submit">Ajouter</button>
</form>

</body>
</html>















