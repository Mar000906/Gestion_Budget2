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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $montant = $_POST["montant"];
    $date = $_POST["date"];
    $compte_id = $_POST["compte"];
    $source_id = $_POST["source"];

    $sql_insert = "INSERT INTO ressource (montant, date, compte_id, source_id, personne_id) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("dsiii", $montant, $date, $compte_id, $source_id, $user_id);
    if ($stmt_insert->execute()) {
        $message = "Ressource ajoutée avec succès.";
    } else {
        $message = "Erreur : " . $stmt_insert->error;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Ajouter une ressource</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #000000ff, #ffffff);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        flex-direction: column;
    }
    /* Bouton Retour en haut à droite */
    .top-right {
        position: absolute;
        top: 15px;
        right: 20px;
    }
    .top-right a {
        color: #1a74acff;
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
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 400px;
        animation: fadeIn 0.6s ease-in-out;
        position: relative;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #2980b9;
    }
    label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        color: #333;
    }
    input, select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        box-sizing: border-box;
    }
    .select-container {
        display: flex;
        align-items: center;
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
    .add-button:hover {
        background-color: #219150;
    }
    .message {
        text-align: center;
        color: green;
        margin-bottom: 15px;
        font-weight: bold;
    }
    button[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #2980b9;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }
   
    button[type="submit"]:hover {
        background-color: #1f6690;
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

<!-- Retour au Dashboard en haut -->
<div class="top-right">
    <a href="dashboard.php">Retour au Dashboard</a>
</div>

<div class="container">
    <h2></h2>

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
    



        <button type="submit">Ajouter</button>
    </form>
</div>
</body>
</html>






