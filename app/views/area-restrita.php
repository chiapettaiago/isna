<?php
$user = $currentUser ?? auth_user();
global $config, $base_path;

$systemUpdateToken = AuthService::userIsAdmin() ? AuthService::generateCsrfToken('system_update') : '';
$systemUpdatesDisabled = !empty($config['disable_update_check']);
$systemUpdatesApiUrl = rtrim((string)($base_path ?? ''), '/') . '/api/system-updates';
$systemLastUpdateLabel = '';

if (AuthService::userIsAdmin()) {
    require_once dirname(__DIR__) . '/services/SystemUpdateService.php';

    try {
        $systemUpdateInfo = (new SystemUpdateService(dirname(__DIR__, 2)))->lastUpdateInfo();
        $systemLastUpdateLabel = isset($systemUpdateInfo['lastUpdatedLabel']) ? (string)$systemUpdateInfo['lastUpdatedLabel'] : '';
    } catch (Throwable $exception) {
        $systemLastUpdateLabel = '';
    }
}

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
$logReadError = null; // Os dados são consultados do MySQL via /api/access-stats

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
        <h1 class="display-6 fw-semibold mb-2">Dashboard</h1>
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

    <?php if (AuthService::userIsAdmin()): ?>
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3" id="systemUpdateCard" data-updates-url="<?php echo htmlspecialchars($systemUpdatesApiUrl, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="flex-grow-1">
            <h2 class="h5 fw-semibold mb-1">
              <i class="bi bi-cloud-arrow-down-fill me-2 text-info"></i>Atualização do sistema
            </h2>
            <p class="text-muted mb-2" id="systemUpdateStatus">
              Verificando automaticamente se existe uma nova versão no GitHub.
            </p>
            <div class="small text-muted mb-2<?php echo $systemLastUpdateLabel !== '' ? '' : ' d-none'; ?>" id="systemUpdateLastUpdated">
              <?php if ($systemLastUpdateLabel !== ''): ?>
                Última atualização: <?php echo htmlspecialchars($systemLastUpdateLabel, ENT_QUOTES, 'UTF-8'); ?>
              <?php endif; ?>
            </div>
            <div class="small text-muted mb-2 d-none" id="systemUpdateVersions"></div>
            <div class="d-none" id="systemUpdateChangesWrap">
              <p class="fw-semibold mb-1">Incluído nesta versão:</p>
              <ul class="mb-0 ps-3" id="systemUpdateChanges"></ul>
            </div>
          </div>
          <?php if ($systemUpdatesDisabled): ?>
            <button class="btn btn-outline-secondary" type="button" disabled>
              <i class="bi bi-slash-circle me-1"></i> Atualizações desativadas
            </button>
          <?php else: ?>
            <button class="btn btn-info text-white" type="button" id="systemUpdateInstallButton" data-bs-toggle="modal" data-bs-target="#systemUpdateModal" disabled>
              <i class="bi bi-cloud-arrow-down-fill me-1"></i> Instalar atualização
            </button>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

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
              <i class="bi bi-graph-up me-2 text-primary"></i>Relatórios de acesso
            </h2>
            <p class="mb-3">
              Gere relatórios de navegação do site por período e exporte os dados em PDF.
            </p>
            <a class="btn btn-outline-primary w-100" href="<?php echo $site_url; ?>/relatorios-acesso">
              <i class="bi bi-file-earmark-pdf me-1"></i> Gerar relatórios
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

      <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex flex-column">
            <h2 class="h5 fw-semibold">
              <i class="bi bi-layout-text-window-reverse me-2 text-warning"></i>CMS do site
            </h2>
            <p class="mb-4">
              Edite textos, chamadas e imagens das principais seções públicas sem alterar arquivos de código.
            </p>
            <a class="btn btn-outline-warning mt-auto w-100" href="<?php echo $site_url; ?>/gestao-cms">
              <i class="bi bi-pencil-square me-1"></i> Gerenciar conteúdo
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (AuthService::userIsAdmin() && !$systemUpdatesDisabled): ?>
  <div class="modal fade" id="systemUpdateModal" tabindex="-1" aria-labelledby="systemUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h2 class="modal-title h5" id="systemUpdateModalLabel">Instalar atualização</h2>
            <div class="small text-muted">Atualização do sistema via GitHub</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">
            O sistema vai baixar e aplicar a nova versão encontrada no GitHub.
          </p>
          <div class="alert alert-warning mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            A operação pode alterar arquivos do sistema. Continue apenas se não houver edições locais pendentes no servidor.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <form method="post" action="<?php echo $site_url; ?>/sistema-atualizar">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($systemUpdateToken, ENT_QUOTES, 'UTF-8'); ?>">
            <button class="btn btn-info text-white" type="submit">
              <i class="bi bi-cloud-arrow-down-fill me-1"></i> Instalar atualização
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

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

        const res = await fetch('<?php echo $site_url; ?>/api/access-stats?' + params.toString(), { credentials: 'same-origin' });

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

<?php if (AuthService::userIsAdmin() && !$systemUpdatesDisabled): ?>
  <script>
    (function() {
      const card = document.getElementById('systemUpdateCard');
      const statusEl = document.getElementById('systemUpdateStatus');
      const lastUpdatedEl = document.getElementById('systemUpdateLastUpdated');
      const versionsEl = document.getElementById('systemUpdateVersions');
      const changesWrap = document.getElementById('systemUpdateChangesWrap');
      const changesList = document.getElementById('systemUpdateChanges');
      const installButton = document.getElementById('systemUpdateInstallButton');

      if (!card || !statusEl || !installButton) return;

      function setButtonEnabled(enabled) {
        installButton.disabled = !enabled;
        installButton.classList.toggle('btn-info', enabled);
        installButton.classList.toggle('btn-outline-secondary', !enabled);
        installButton.classList.toggle('text-white', enabled);
      }

      function renderChanges(changes) {
        if (!changesWrap || !changesList) return;

        changesList.textContent = '';
        if (!Array.isArray(changes) || changes.length === 0) {
          changesWrap.classList.add('d-none');
          return;
        }

        changes.slice(0, 8).forEach(function(change) {
          const item = document.createElement('li');
          item.textContent = String(change);
          changesList.appendChild(item);
        });

        changesWrap.classList.remove('d-none');
      }

      function renderVersions(data) {
        if (!versionsEl) return;

        const currentVersion = data.currentVersion ? String(data.currentVersion) : '';
        const latestVersion = data.latestVersion ? String(data.latestVersion) : '';

        if (!currentVersion && !latestVersion) {
          versionsEl.classList.add('d-none');
          versionsEl.textContent = '';
          return;
        }

        versionsEl.textContent = currentVersion && latestVersion
          ? `Atual: ${currentVersion} | Disponível: ${latestVersion}`
          : `Disponível: ${latestVersion || currentVersion}`;
        versionsEl.classList.remove('d-none');
      }

      function renderLastUpdated(data) {
        if (!lastUpdatedEl) return;

        const label = data.lastUpdatedLabel ? String(data.lastUpdatedLabel) : '';
        if (!label) {
          lastUpdatedEl.classList.add('d-none');
          lastUpdatedEl.textContent = '';
          return;
        }

        lastUpdatedEl.textContent = `Última atualização: ${label}`;
        lastUpdatedEl.classList.remove('d-none');
      }

      async function checkUpdates() {
        setButtonEnabled(false);
        statusEl.textContent = 'Verificando automaticamente se existe uma nova versão no GitHub.';

        try {
          const response = await fetch(card.dataset.updatesUrl || '', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
          });
          const body = await response.text();
          let data = null;

          try {
            data = JSON.parse(body);
          } catch (parseError) {
            throw new Error(`Resposta invalida do servidor (${response.status || 'sem status'}).`);
          }

          if (!response.ok) {
            throw new Error(data.message || `Erro HTTP ${response.status}.`);
          }

          statusEl.textContent = data.message || 'Verificação de atualizações concluída.';
          renderLastUpdated(data);
          renderVersions(data);
          renderChanges(data.available ? data.changes : []);
          setButtonEnabled(Boolean(data.available && data.canInstall));
        } catch (error) {
          statusEl.textContent = error && error.message
            ? `Não foi possível verificar novas versões automaticamente: ${error.message}`
            : 'Não foi possível verificar novas versões automaticamente.';
          renderChanges([]);
          setButtonEnabled(false);
        }
      }

      window.addEventListener('load', checkUpdates);
    })();
  </script>
<?php endif; ?>
