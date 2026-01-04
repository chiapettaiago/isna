<?php
$user = $currentUser ?? auth_user();

$logFile = dirname(__DIR__) . '/logs/access_log';
$today = new DateTimeImmutable('today');
$defaultStart = $today->modify('-29 days');

$fromParam = isset($_GET['from']) ? trim((string) $_GET['from']) : '';
$toParam = isset($_GET['to']) ? trim((string) $_GET['to']) : '';

$fromDate = DateTimeImmutable::createFromFormat('Y-m-d', $fromParam) ?: $defaultStart;
$toDate = DateTimeImmutable::createFromFormat('Y-m-d', $toParam) ?: $today;

$fromDate = $fromDate->setTime(0, 0);
$toDate = $toDate->setTime(0, 0);

if ($fromDate > $toDate) {
    $temp = $fromDate;
    $fromDate = $toDate;
    $toDate = $temp;
}

$dailyCounts = [];
$logReadError = null; // Não dependemos mais do arquivo de log; o frontend consulta /api/access-stats

$period = new DatePeriod($fromDate, new DateInterval('P1D'), $toDate->modify('+1 day'));

$chartLabels = [];
$chartValues = [];
$chartDays = [];

foreach ($period as $day) {
    $key = $day->format('Y-m-d');
    $chartLabels[] = $day->format('d/m');
    $chartValues[] = (int) ($dailyCounts[$key] ?? 0);
    $chartDays[] = $day;
}

$totalAccesses = array_sum($chartValues);
$chartPointCount = count($chartValues);
$dailyAverage = $chartPointCount > 0 ? $totalAccesses / $chartPointCount : 0;

$peakDayLabel = null;
$peakDayValue = 0;

if (!empty($chartValues)) {
    $maxValue = max($chartValues);
    if ($maxValue > 0) {
        $index = array_search($maxValue, $chartValues, true);
        if ($index !== false && isset($chartDays[$index])) {
            $peakDayLabel = $chartDays[$index]->format('d/m');
            $peakDayValue = $maxValue;
        }
    }
}

?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row justify-content-between align-items-center mb-4">
      <div class="col-lg-8">
        <h1 class="display-6 fw-semibold mb-2">Área Restrita</h1>
        <p class="lead mb-0">
          Bem-vindo<?php if ($user): ?>, <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>.
          Utilize os atalhos abaixo para administrar o conteúdo institucional.
        </p>
      </div>
      <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a class="btn btn-outline-secondary me-2" href="<?php echo $site_url; ?>/logout">
          <i class="bi bi-box-arrow-right me-1"></i> Encerrar sessão
        </a>
        <a class="btn btn-primary" href="<?php echo $site_url; ?>/">
          <i class="bi bi-house-door-fill me-1"></i> Voltar ao site
        </a>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end mb-4">
          <div>
            <h2 class="h5 fw-semibold mb-1">Painel de Acessos</h2>
            <p class="text-muted mb-0">Visualize os acessos às páginas do site agregados por dia.</p>
          </div>
          <form class="row g-2 align-items-end mt-3 mt-lg-0" method="get" action="<?php echo $site_url; ?>/area-restrita">
            <div class="col-auto">
              <label class="form-label" for="from" hidden>De</label>
              <input class="form-control" type="date" id="from" name="from" value="<?php echo htmlspecialchars($fromDate->format('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-auto">
              <label class="form-label" for="to" hidden>Até</label>
              <input class="form-control" type="date" id="to" name="to" value="<?php echo htmlspecialchars($toDate->format('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-auto">
              <button class="btn btn-primary" type="submit">
                <i class="bi bi-funnel me-1"></i> Filtrar
              </button>
            </div>
          </form>
        </div>

        <?php if ($logReadError): ?>
          <div class="alert alert-warning mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($logReadError, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php else: ?>
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <div class="bg-light border rounded p-3 h-100">
                <p class="text-muted mb-1">Total no período</p>
                <p id="totalAccesses" class="display-6 fw-semibold mb-0"><?php echo number_format($totalAccesses, 0, ',', '.'); ?></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="bg-light border rounded p-3 h-100">
                <p class="text-muted mb-1">Média diária</p>
                <p id="dailyAverage" class="display-6 fw-semibold mb-0"><?php echo number_format($dailyAverage, 1, ',', '.'); ?></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="bg-light border rounded p-3 h-100">
                <p class="text-muted mb-1">Dia com mais acessos</p>
                <p id="peakDayLabel" class="display-6 fw-semibold mb-1"><?php echo $peakDayLabel ? $peakDayLabel : '—'; ?></p>
                <small id="peakDaySmall" class="text-muted"><?php echo $peakDayLabel ? $peakDayValue . ' acessos' : 'Sem dados no intervalo'; ?></small>
              </div>
            </div>
          </div>

          <div style="position: relative; min-height: 320px;">
            <canvas id="accessChart" class="w-100 h-100"></canvas>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 fw-semibold">
              <i class="bi bi-files-alt me-2 text-primary"></i>Documentos &amp; Transparência
            </h2>
            <p class="mb-3">
              Organize relatórios, atas e outros materiais disponíveis na seção de transparência.
            </p>
            <a class="btn btn-outline-primary w-100" href="<?php echo $site_url; ?>/transparencia">
              <i class="bi bi-eye-fill me-1"></i> Visualizar página pública
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 fw-semibold">
              <i class="bi bi-images me-2 text-success"></i>Galeria de projetos
            </h2>
            <p class="mb-3">
              Revise fotos, legendas e vídeos para manter a vitrine dos projetos sempre atualizada.
            </p>
            <a class="btn btn-outline-success w-100" href="<?php echo $site_url; ?>/galeria">
              <i class="bi bi-image-fill me-1"></i> Ver galeria pública
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex flex-column">
            <h2 class="h5 fw-semibold">
              <i class="bi bi-info-circle-fill me-2 text-secondary"></i>Sobre o projeto
            </h2>
            <p class="mb-4">
              Conheça o propósito do site e as diretrizes de conteúdo acessando a página institucional interna.
            </p>
            <a class="btn btn-outline-secondary mt-auto w-100" href="<?php echo $site_url; ?>/sobre">
              <i class="bi bi-file-earmark-text me-1"></i> Abrir página /sobre
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex flex-column">
            <h2 class="h5 fw-semibold">
              <i class="bi bi-journal-text me-2 text-danger"></i>Blog institucional
            </h2>
            <p class="mb-4">
              Compartilhe notícias, campanhas e relatos inspiradores através de artigos publicados no site.
            </p>
            <a class="btn btn-outline-danger mt-auto w-100" href="<?php echo $site_url; ?>/gestao-blog">
              <i class="bi bi-pencil-square me-1"></i> Gerenciar blog
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!$logReadError): ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script>
    (function() {
      const ctx = document.getElementById('accessChart');
      if (!ctx) return;

      const fromInput = document.getElementById('from');
      const toInput = document.getElementById('to');

      async function fetchData() {
        const params = new URLSearchParams();
        if (fromInput && fromInput.value) params.set('from', fromInput.value);
        if (toInput && toInput.value) params.set('to', toInput.value);

        // Tenta o endpoint autenticado primeiro
        let res = await fetch('<?php echo $site_url; ?>/api/access-stats?' + params.toString(), { credentials: 'same-origin' });
        if (res.status === 401) {
          // fallback para endpoint de debug (sem autenticação)
          console.warn('api/access-stats retornou 401, tentando /api/access-stats-debug');
          try {
            res = await fetch('<?php echo $site_url; ?>/api/access-stats-debug?' + params.toString());
          } catch (ex) {
            console.error('Falha ao obter dados de debug', ex);
            return null;
          }
        }

        if (!res.ok) {
          console.error('Falha ao obter dados de acessos', res.status);
          return null;
        }
        return res.json();
      }

      function renderChart(labels, values) {
        const total = values.reduce((acc, v) => acc + v, 0);
        return new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [{
              label: 'Acessos por dia',
              data: values,
              borderColor: 'rgba(13,110,253,1)',
              backgroundColor: 'rgba(13,110,253,0.15)',
              pointBackgroundColor: '#ffffff',
              pointBorderColor: 'rgba(13,110,253,1)',
              pointHoverRadius: 5,
              pointRadius: 4,
              tension: 0.3,
              fill: true,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label(context) {
                    const value = context.parsed.y || 0;
                    const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return `${value} acessos (${percent}% do período)`;
                  }
                }
              }
            },
            scales: {
              y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Acessos' } },
              x: { title: { display: true, text: 'Dia' } }
            }
          }
        });
      }

      let chartInstance = null;

      async function loadAndRender() {
        const data = await fetchData();
        if (!data) return;
        const labels = data.labels || [];
        const values = data.values || [];

        // Atualiza os cards numericos
        const totalEl = document.getElementById('totalAccesses');
        const avgEl = document.getElementById('dailyAverage');
        const peakLabelEl = document.getElementById('peakDayLabel');
        const peakSmallEl = document.getElementById('peakDaySmall');

        const total = (data.total || 0);
        const average = (typeof data.average === 'number') ? data.average : 0;
        const peakLabel = data.peakLabel || null;
        const peakValue = data.peakValue || 0;

        if (totalEl) totalEl.textContent = total.toLocaleString();
        if (avgEl) avgEl.textContent = Number(average).toFixed(1).replace('.', ',');
        if (peakLabelEl) peakLabelEl.textContent = peakLabel ? String(peakLabel) : '—';
        if (peakSmallEl) peakSmallEl.textContent = peakLabel ? (String(peakValue) + ' acessos') : 'Sem dados no intervalo';

        if (chartInstance) chartInstance.destroy();
        chartInstance = renderChart(labels, values);
      }

      // Load on page ready
      window.addEventListener('load', loadAndRender);

      // Re-fetch when filter form submitted
      const filterForm = document.querySelector('form[action="<?php echo $site_url; ?>/area-restrita"]');
      if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
          // allow server reload as well; fetch will update quickly
          setTimeout(loadAndRender, 100);
        });
      }
    })();
  </script>
<?php endif; ?>
