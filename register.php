<?php
$host = "localhost";
$user = "root";
$pass = "Mysql"; // Mot de passe MySQL
$dbname = "gestion_budgett";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $telephone = trim($_POST["telephone"]);
    $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);

    // Vérification si email ou téléphone déjà utilisé
    $check_sql = "SELECT * FROM personne WHERE email = ? OR telephone = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $email, $telephone);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "❌ Email ou téléphone déjà utilisé.";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO personne (nom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nom, $email, $telephone, $password);
        if ($stmt->execute()) {
            $message = "✅ Compte créé avec succès. <a href='login.php'>Se connecter</a>";
            $messageType = "success";
        } else {
            $message = "❌ Erreur : " . $stmt->error;
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #659adfff, #6ea7e9ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #6798f3ff;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 4px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background: #5082ceff;
            color: white;
            border: none;
            padding: 12px;
            margin-top: 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background: #01004eff;
        }
        .message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            color: #2aac55ff;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        p {
            text-align: center;
            margin-top: 15px;
        }
        a {
            color: #2E7D32;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
       
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Créer un compte</h2>
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post">
            <label>Nom :</label>
            <input type="text" name="nom" required>

            <label>Email :</label>
            <input type="email" name="email" required>

            <label>Téléphone :</label>
            <input type="text" name="telephone" required>

            <label>Mot de passe :</label>
            <input type="password" name="password" required>

            <button type="submit">Créer le compte</button>
        </form>
        <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>



