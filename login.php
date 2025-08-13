
<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "Mysql";  // mot de passe MySQL
$dbname = "gestion_budgett";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = trim($_POST["user"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT * FROM personne WHERE (nom = ? OR email = ? OR telephone = ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    $stmt->bind_param("sss", $user_input, $user_input, $user_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["mot_de_passe"])) {
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["user_name"] = $row["nom"];
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Mot de passe incorrect.";
        }
    } else {
        $message = "Utilisateur non trouvé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Connexion - Gestion de budget</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

  * {
    box-sizing: border-box;
  }

  body, html {
    height: 100%;
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #46498dff 0%, #000569ff 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    color: #333;
  }

  .login-container {
    background: white;
    padding: 40px 35px;
    border-radius: 12px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    width: 360px;
    text-align: center;
    animation: fadeInScale 0.6s ease forwards;
  }

  @keyframes fadeInScale {
    0% {opacity: 0; transform: scale(0.8);}
    100% {opacity: 1; transform: scale(1);}
  }

  h2 {
    margin-bottom: 30px;
    font-weight: 600;
    font-size: 2rem;
    color: #0d1a4d;
    letter-spacing: 1px;
  }

  label {
    display: block;
    text-align: left;
    font-weight: 600;
    margin-bottom: 8px;
    color: #555f7a;
    font-size: 0.9rem;
  }

  input[type="text"],
  input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 25px;
    border: 2px solid #d0d7e6;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    outline-offset: 2px;
  }
  input[type="text"]:focus,
  input[type="password"]:focus {
    border-color: #4f6bed;
    box-shadow: 0 0 8px rgba(79,107,237,0.5);
  }

  button {
    width: 100%;
    background: #4f6bed;
    color: white;
    padding: 14px 0;
    font-size: 1.1rem;
    font-weight: 700;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 0 6px 15px rgba(79,107,237,0.6);
    transition: background 0.3s ease;
  }
  button:hover {
    background: #3a54b8;
    box-shadow: 0 8px 20px rgba(58,84,184,0.8);
  }

  .message {
    margin-bottom: 20px;
    color: #e74c3c;
    font-weight: 600;
  }

  .create-account {
    margin-top: 25px;
    font-size: 0.9rem;
    color: #555f7a;
  }
  .create-account a {
    color: #001881ff;
    font-weight: 600;
    text-decoration: none;
  }
  .create-account a:hover {
    text-decoration: underline;
  }

  /* Responsive */
  @media (max-width: 400px) {
    .login-container {
      width: 90vw;
      padding: 30px 20px;
    }
  }
</style>
</head>
<body>
  <div class="login-container">
    <h2>Connexion</h2>
    <?php if ($message): ?>
      <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php" novalidate>
      <label for="user">Nom, Email ou Téléphone :</label>
      <input type="text" id="user" name="user" required autocomplete="username" />

      <label for="password">Mot de passe :</label>
      <input type="password" id="password" name="password" required autocomplete="current-password" />

      <button type="submit">Se connecter</button>
    </form>
    <div class="create-account">
      Pas encore de compte ? <a href="register.php">Créer un compte</a>
    </div>
  </div>
</body>
</html>








