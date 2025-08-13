
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

$sql_user = "SELECT nom FROM personne WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$row_user = $result_user->fetch_assoc();
$user_name = $row_user["nom"] ?? "Utilisateur";

$sql_income = "SELECT IFNULL(SUM(montant), 0) AS total_income FROM ressource WHERE personne_id = ?";
$stmt_income = $conn->prepare($sql_income);
$stmt_income->bind_param("i", $user_id);
$stmt_income->execute();
$result_income = $stmt_income->get_result();
$row_income = $result_income->fetch_assoc();
$total_income = $row_income["total_income"];

$sql_outcome = "SELECT IFNULL(SUM(montant), 0) AS total_outcome FROM depense WHERE personne_id = ?";
$stmt_outcome = $conn->prepare($sql_outcome);
$stmt_outcome->bind_param("i", $user_id);
$stmt_outcome->execute();
$result_outcome = $stmt_outcome->get_result();
$row_outcome = $result_outcome->fetch_assoc();
$total_outcome = $row_outcome["total_outcome"];

$solde = $total_income - $total_outcome;

$income = number_format($total_income, 2, ',', ' ');
$outcome = number_format($total_outcome, 2, ',', ' ');
$solde_formatted = number_format($solde, 2, ',', ' ');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Dashboard - Gestion de budget</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

  * {
    box-sizing: border-box;
  }
  body, html {
    margin: 0; padding: 0;
    height: 100%;
    font-family: 'Montserrat', Arial, sans-serif;
    background: linear-gradient(135deg, #e9f0ff, #f7f9fc);
    color: #333;
  }

  /* Bouton menu hamburger */
  .menu-button {
    position: fixed;
    top: 20px;
    left: 20px;
    width: 48px;
    height: 42px;
    cursor: pointer;
    z-index: 1100;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    background: #ffffffdd;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    transition: background-color 0.3s ease;
  }
  .menu-button:hover {
    background: #cde0ff;
  }
  .menu-button div {
    width: 32px;
    height: 5px;
    background: #1976d2;
    border-radius: 3px;
    margin-left: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.15);
  }

  /* Menu latéral */
  .menu {
    position: fixed;
    top: 0;
    left: -260px;
    width: 260px;
    height: 100vh;
    background: #fff;
    color: #1a73e8;
    display: flex;
    flex-direction: column;
    transition: left 0.35s ease;
    z-index: 1000;
    padding-top: 80px;
    box-shadow: 3px 0 20px rgb(0 0 0 / 0.1);
    font-weight: 700;
    letter-spacing: 0.05em;
  }
  .menu.open {
    left: 0;
  }
  .menu a {
    padding: 18px 30px;
    text-decoration: none;
    color: #1a73e8;
    border-bottom: 1px solid #e8f0fe;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background-color 0.3s ease, color 0.3s ease;
    user-select: none;
  }
  .menu a:hover {
    background: #d0f0d0; /* vert clair */
    color: #145214;
    border-left: 4px solid #4caf50;
    padding-left: 26px;
  }
  .menu a::before {
    content: "●";
    font-size: 0.8rem;
    color: #64b5f6;
  }

  /* Bouton déconnexion */
  .logout-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #910200ff;
    color: white;
    padding: 12px 22px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    box-shadow: 0 6px 14px rgb(229 57 53 / 0.6);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    z-index: 1200;
    user-select: none;
  }
  .logout-btn:hover {
    background: #b71c1c;
    box-shadow: 0 8px 20px rgb(183 28 28 / 0.9);
  }

  /* Contenu principal */
  .main {
    margin-left: 0;
    padding: 80px 40px 40px;
    text-align: center;
    transition: margin-left 0.35s ease;
    min-height: 100vh;
  }
  .main.shifted {
    margin-left: 260px;
  }

  .solde-text {
    font-size: 2rem;
    font-weight: 700;
    color: #222;
    margin-bottom: 28px;
    letter-spacing: 0.05em;
    text-shadow: 0 0 5px rgba(0,0,0,0.05);
  }

  /* Cercle principal */
  .circle {
    width: 700px;
    max-width: 95vw;
    height: 360px;
    margin: 0 auto 40px auto;
    border-radius: 50%;
    background: linear-gradient(145deg, #002d55ff, #001e46ff);
    box-shadow:
      8px 8px 16px rgba(0,0,0,0.1),
      -8px -8px 16px rgba(255,255,255,0.8);
    display: flex;
    overflow: hidden;
    border: 4px solid #a79797ff;
    font-size: 2.1rem;
    line-height: 1.3;
    user-select: none;
  }
  @media (prefers-color-scheme: dark) {
    .circle {
      background: #001536ff;
      border-color: #002442ff;
      color: #ddddddff;
      box-shadow:
        8px 8px 16px rgba(0,0,0,0.8),
        -8px -8px 16px rgba(100,181,246,0.6);
    }
  }
  @media (prefers-color-scheme: light) {
    .circle {
      background: #d0e4ff;
      border-color: #001a3bff;
      color: #001029ff;
    }
  }

  .circle .half {
    width: 50%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border-right: 10px solid rgba(255,255,255,0.5);
    padding: 50px;
    box-sizing: border-box;
  }
  .circle .half:last-child {
    border-right: none;
  }
  .circle .label {
    font-weight: 800;
    font-size: 2.2rem;
    margin-bottom: 14px;
    white-space: nowrap;
    letter-spacing: 0.1em;
  }
  .circle .value {
    font-size: 4rem;
    font-weight: 900;
    white-space: nowrap;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.12);
  }
  .outcome .value {
    color: #e74c3c;
    text-shadow: 1px 1px 5px #b71c1c88;
  }
  .income .value {
    color: #27ae60;
    text-shadow: 1px 1px 5px #14521488;
  }

  /* Boutons + et - */
  .buttons {
    margin-bottom: 40px;
  }
  .btn {
    display: inline-block;
    padding: 14px 36px;
    margin: 0 18px;
    font-size: 2.8rem;
    font-weight: 900;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    color: white;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    transition: background-color 0.35s ease, box-shadow 0.35s ease;
    user-select: none;
  }
  .btn.outcome {
    background-color: #e74c3c;
    filter: drop-shadow(0 0 4px #b71c1c);
  }
  .btn.outcome:hover {
    background-color: #b12b1c;
    box-shadow: 0 8px 25px #b71c1ccc;
  }
  .btn.income {
    background-color: #27ae60;
    filter: drop-shadow(0 0 5px #145214);
  }
  .btn.income:hover {
    background-color: #1e8449;
    box-shadow: 0 8px 25px #145214cc;
  }

  /* Responsive */
  @media(max-width: 768px) {
    .circle {
      height: 280px;
      font-size: 1.6rem;
    }
    .circle .label {
      font-size: 1.6rem;
    }
    .circle .value {
      font-size: 2.8rem;
    }
    .buttons {
      margin-bottom: 25px;
    }
    .btn {
      font-size: 2rem;
      padding: 12px 26px;
      margin: 0 12px;
    }
    .menu-button {
      width: 40px;
      height: 38px;
    }
    .menu-button div {
      width: 24px;
      height: 4px;
      margin-left: 5px;
    }
    .menu {
      width: 200px;
      padding-top: 70px;
    }
    .main.shifted {
      margin-left: 200px;
    }
  }
</style>
<script>
  function toggleMenu() {
    const menu = document.getElementById("menu");
    const main = document.getElementById("mainContent");
    menu.classList.toggle("open");
    main.classList.toggle("shifted");
  }
</script>
</head>
<body>

<!-- Bouton menu -->
<div class="menu-button" onclick="toggleMenu()" title="Menu" aria-label="Toggle menu">
  <div></div>
  <div></div>
  <div></div>
</div>

<!-- Bouton déconnexion -->
<a href="logout.php" class="logout-btn">Déconnexion</a>

<!-- Menu latéral -->
<nav class="menu" id="menu" aria-label="Menu de navigation">
  <a href="liste_comptes.php">Compte</a>
  <a href="liste_sources.php">Source</a>
  <a href="liste_ressources.php">Ressource</a>
  <a href="liste_categories.php">Catégorie</a>
  <a href="liste_sous_categories.php">Sous-catégorie</a>
  <a href="liste_depenses.php">Dépense</a>
  <a href="rapport.php">Rapport</a>


</nav>

<!-- Contenu principal -->
<div class="main" id="mainContent" role="main">
  <div class="solde-text" aria-live="polite" aria-atomic="true">
    Votre solde est : <?php echo $solde_formatted; ?> DH
  </div>

  <div class="circle" role="img" aria-label="Visualisation des revenus et dépenses">
    <div class="half income">
      <div class="label">Income</div>
      <div class="value"><?php echo $income; ?></div>
    </div>
    <div class="half outcome">
      <div class="label">Outcome</div>
      <div class="value"><?php echo $outcome; ?></div>
    </div>
  </div>

  <div class="buttons">
    <button class="btn income" onclick="location.href='ajouter_ressource.php'" aria-label="Ajouter une ressource">+</button>
    <button class="btn outcome" onclick="location.href='ajouter_depense.php'" aria-label="Ajouter une dépense">−</button>
  </div>
</div>

</body>
</html>





