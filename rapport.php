<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php'; // doit définir $conn (mysqli)
if (!isset($conn) || !$conn) {
    die("Connexion DB introuvable. Vérifie db.php.");
}

$user_id = (int)$_SESSION['user_id'];
$year = isset($_REQUEST['year']) ? (int)$_REQUEST['year'] : (int)date('Y');
if ($year < 2000 || $year > (int)date('Y') + 5) $year = (int)date('Y');

// noms des mois en français
$months = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];

// initialisation des tableaux (index 1..12)
$incomeByMonth = array_fill(1, 12, 0.0);
$outcomeByMonth = array_fill(1, 12, 0.0);

// ----- récupérer incomes groupés par mois -----
$sql_inc = "SELECT MONTH(`date`) AS m, IFNULL(SUM(montant),0) AS s
            FROM ressource
            WHERE personne_id = ? AND YEAR(`date`) = ?
            GROUP BY MONTH(`date`)";
$stmt = $conn->prepare($sql_inc);
if (!$stmt) { die("Erreur préparation income : " . $conn->error); }
$stmt->bind_param("ii", $user_id, $year);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $m = (int)$r['m'];
    $incomeByMonth[$m] = (float)$r['s'];
}
$stmt->close();

// ----- récupérer outcomes groupés par mois -----
$sql_out = "SELECT MONTH(`date`) AS m, IFNULL(SUM(montant),0) AS s
            FROM depense
            WHERE personne_id = ? AND YEAR(`date`) = ?
            GROUP BY MONTH(`date`)";
$stmt = $conn->prepare($sql_out);
if (!$stmt) { die("Erreur préparation outcome : " . $conn->error); }
$stmt->bind_param("ii", $user_id, $year);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $m = (int)$r['m'];
    $outcomeByMonth[$m] = (float)$r['s'];
}
$stmt->close();

// totaux annuels
$totalIncomeYear  = array_sum($incomeByMonth);
$totalOutcomeYear = array_sum($outcomeByMonth);
$balanceYear = $totalIncomeYear - $totalOutcomeYear;

// Export Excel si demandé
if (isset($_POST['export_excel'])) {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=rapport_{$year}.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "Mois\tIncome (MAD)\tOutcome (MAD)\n";
    for ($i = 1; $i <= 12; $i++) {
        echo $months[$i-1] . "\t" . number_format($incomeByMonth[$i], 2, ',', '') . "\t" . number_format($outcomeByMonth[$i], 2, ',', '') . "\n";
    }

    echo "\nTotal Income\t" . number_format($totalIncomeYear, 2, ',', '') . "\n";
    echo "Total Outcome\t" . number_format($totalOutcomeYear, 2, ',', '') . "\n";
    echo "Solde\t" . number_format($balanceYear, 2, ',', '') . "\n";

    exit();
}

// pour affichage
$displayIncome  = number_format($totalIncomeYear, 2, ',', ' ');
$displayOutcome = number_format($totalOutcomeYear, 2, ',', ' ');
$displayBalance = number_format($balanceYear, 2, ',', ' ');

// convertir séries en arrays indexés 0..11 pour JS
$incomeSeries = [];
$outcomeSeries = [];
for ($i = 1; $i <= 12; $i++) {
    $incomeSeries[] = round($incomeByMonth[$i], 2);
    $outcomeSeries[] = round($outcomeByMonth[$i], 2);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Rapport - Chart</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#f6f8fb;
    --card:#ffffff;
    --accent:#ff6b99; /* rose clair */
    --accent-2:#ff2e5c; /* rose foncé */
    --muted:#6b7280;
  }
  *{box-sizing:border-box}
  body {
    margin:0;
    font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    background: var(--bg);
    color:#1f2937;
    padding:18px;
  }
  header{
    display:flex;
    justify-content:space-between;
    align-items:end;
    gap:20px;
    margin-bottom:18px;
  }
  h1{ margin:0; font-size:1.6rem; font-weight:700; color:#0f172a; }
  .controls { display:flex; gap:10px; align-items:center; }
  select, button {
    padding:8px 12px;
    border-radius:8px;
    border:1px solid #e6e9ee;
    background: #fff;
    font-weight:600;
  }
  button.primary {
    background: linear-gradient(90deg,var(--accent),var(--accent-2));
    color:white;
    border:none;
    cursor:pointer;
  }

  .summary {
    display:flex;
    gap:12px;
    margin-bottom:16px;
    flex-wrap:wrap;
  }
  .card {
    background:var(--card);
    border-radius:12px;
    padding:14px 18px;
    box-shadow: 0 8px 20px rgba(17,24,39,0.06);
    min-width:180px;
    flex:1;
  }
  .card .label{ font-size:0.9rem; color:var(--muted); font-weight:600; }
  .card .value{ font-size:1.4rem; font-weight:800; margin-top:6px; }

  .chart-wrap {
    background:var(--card);
    border-radius:12px;
    padding:18px;
    box-shadow: 0 8px 20px rgba(9, 13, 22, 0.06);
    margin-bottom:18px;
  }
  .chart-title{ font-weight:700; margin:0 0 8px 0; color:#0f172a; }
  canvas{ width:100% !important; max-height:520px; }

  .legend { display:flex; gap:12px; margin-top:10px; align-items:center; }
  .legend .item { display:flex; gap:8px; align-items:center; font-weight:600; color:var(--muted); }
  .swatch { width:14px; height:14px; border-radius:3px; display:inline-block; }

  .tables { display:flex; gap:16px; margin-top:18px; flex-wrap:wrap; }
  table { border-collapse:collapse; width:100%; min-width:360px; background:var(--card); border-radius:8px; overflow:hidden; box-shadow:0 8px 20px rgba(17,24,39,0.04); }
  th, td { padding:10px 12px; text-align:left; border-bottom:1px solid #f1f5f9; font-size:0.95rem; }
  th { background:#fbfdff; font-weight:700; color:#374151; }
  caption { font-weight:700; padding:10px; text-align:left; color:#111827; }
  @media (max-width:900px){
    .summary,.tables{ flex-direction:column; }
  }
</style>
</head>
<body>

<header>
  <div>
    <h1>Chart — Revenus & Dépenses (<?= htmlspecialchars($year) ?>)</h1>
    <div style="color:var(--muted); margin-top:6px; font-size:0.95rem;">Graphique mensuel : chaque mois contient 2 barres (Income / Outcome)</div>
  </div>

  <div class="controls">
    <form method="GET" id="yearForm" style="display:flex;gap:8px;align-items:center;">
      <label for="year" style="font-weight:700;color:var(--muted)">Année</label>
      <select id="year" name="year" onchange="document.getElementById('yearForm').submit();">
        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--):
            $sel = ($y === $year) ? 'selected' : '';
        ?>
            <option value="<?php echo $y;?>" <?php echo $sel;?>><?php echo $y;?></option>
        <?php endfor; ?>
      </select>
    </form>

    <form method="POST" style="margin-left:10px;">
      <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">
      <button type="submit" name="export_excel" class="primary">Exporter Excel</button>
    </form>
  </div>
</header>

<section class="summary">
  <div class="card">
    <div class="label">Total Income (année)</div>
    <div class="value" style="color:#4CAF50"><?php echo $displayIncome; ?> MAD</div>
  </div>
  <div class="card">
    <div class="label">Total Outcome (année)</div>
    <div class="value" style="color:#E53935"><?php echo $displayOutcome; ?> MAD</div>
  </div>
  <div class="card">
    <div class="label">Solde (année)</div>
    <div class="value" style="color:#0a84ff"><?php echo $displayBalance; ?> MAD</div>
  </div>
</section>

<section class="chart-wrap">
  <h3 class="chart-title">Graphique mensuel (Income vs Outcome)</h3>
  <canvas id="barChart"></canvas>
  <div class="legend">
    <div class="item"><span class="swatch" style="background:#4CAF50"></span> Income</div>
    <div class="item"><span class="swatch" style="background:#E53935"></span> Outcome</div>
  </div>
</section>

<!-- Optionnel : tables détaillées montrant les montants par mois -->
<section class="tables">
  <table aria-describedby="income-table">
    <caption>Income par mois (MAD)</caption>
    <thead><tr><th>Mois</th><th>Montant</th></tr></thead>
    <tbody>
      <?php for($i=0;$i<12;$i++): ?>
        <tr>
          <td><?php echo $months[$i]; ?></td>
          <td><?php echo number_format($incomeSeries[$i],2,',',' '); ?> MAD</td>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <table aria-describedby="outcome-table">
    <caption>Outcome par mois (MAD)</caption>
    <thead><tr><th>Mois</th><th>Montant</th></tr></thead>
    <tbody>
      <?php for($i=0;$i<12;$i++): ?>
        <tr>
          <td><?php echo $months[$i]; ?></td>
          <td><?php echo number_format($outcomeSeries[$i],2,',',' '); ?> MAD</td>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  const months = <?php echo json_encode($months, JSON_HEX_TAG); ?>;
  const incomeData = <?php echo json_encode($incomeSeries, JSON_NUMERIC_CHECK); ?>;
  const outcomeData = <?php echo json_encode($outcomeSeries, JSON_NUMERIC_CHECK); ?>;
  const totalIncome = <?php echo json_encode((float)$totalIncomeYear, JSON_NUMERIC_CHECK); ?>;
  const totalOutcome = <?php echo json_encode((float)$totalOutcomeYear, JSON_NUMERIC_CHECK); ?>;

  const ctx = document.getElementById('barChart').getContext('2d');

  const config = {
    type: 'bar',
    data: {
      labels: months,
      datasets: [
        {
          label: 'Income',
          data: incomeData,
          backgroundColor: 'rgba(27, 218, 59, 0.7)', 
          borderColor: 'rgba(107, 255, 127, 1)',
          borderRadius: 6,
          barThickness: 'flex'
        },
        {
          label: 'Outcome',
          data: outcomeData,
          backgroundColor: 'rgba(223, 15, 15, 0.85)', 
          borderColor: 'rgba(199, 15, 15, 1)',
          borderRadius: 6,
          barThickness: 'flex'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top' },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.dataset.label || '';
              const value = context.parsed.y ?? context.parsed; // Chart.js v4
              const formatted = value.toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2});
              // pourcentage par rapport au total annuel du même type
              let percent = 0;
              if (label === 'Income') {
                percent = totalIncome ? (value / totalIncome * 100) : 0;
              } else {
                percent = totalOutcome ? (value / totalOutcome * 100) : 0;
              }
              return label + ': ' + formatted + ' MAD (' + percent.toFixed(1) + '%)';
            }
          }
        }
      },
      scales: {
        x: {
          stacked: false,
          title: { display:false }
        },
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return value.toLocaleString('fr-FR');
            }
          }
        }
      }
    }
  };

  // Resize container to make chart visible (set canvas parent height)
  (function setChartHeight(){
    const canvas = document.getElementById('barChart');
    const parent = canvas.parentElement;
    parent.style.minHeight = '420px';
  })();

  new Chart(ctx, config);
</script>
</body>
</html>











