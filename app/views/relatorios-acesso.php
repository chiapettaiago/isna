<?php
$today = new DateTimeImmutable('today');
$defaultStart = $today->modify('-29 days');

$fromParam = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$toParam = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$fromDate = DateTimeImmutable::createFromFormat('Y-m-d', $fromParam) ?: $defaultStart;
$toDate = DateTimeImmutable::createFromFormat('Y-m-d', $toParam) ?: $today;

$fromDate = $fromDate->setTime(0, 0);
$toDate = $toDate->setTime(0, 0);

if ($fromDate > $toDate) {
    $temp = $fromDate;
    $fromDate = $toDate;
    $toDate = $temp;
}

$report = AccessLogger::report($fromDate->format('Y-m-d'), $toDate->format('Y-m-d'), 80);
$totals = $report['totals'];
$dailyCounts = $report['daily'];
$topPaths = $report['top_paths'];
$recent = $report['recent'];

$period = new DatePeriod($fromDate, new DateInterval('P1D'), $toDate->modify('+1 day'));
$dailyRows = [];
$maxDaily = 0;

foreach ($period as $day) {
    $key = $day->format('Y-m-d');
    $count = (int)($dailyCounts[$key] ?? 0);
    $dailyRows[] = [
        'label' => $day->format('d/m/Y'),
        'count' => $count,
    ];
    $maxDaily = max($maxDaily, $count);
}

$pdfUrl = $site_url . '/relatorios-acesso/pdf?from=' . rawurlencode($fromDate->format('Y-m-d')) . '&to=' . rawurlencode($toDate->format('Y-m-d'));
?>

<style>
  .access-report-bar {
    height: 0.55rem;
    min-width: 0.25rem;
  }

  .access-report-path {
    max-width: 36rem;
    overflow-wrap: anywhere;
  }

  .access-report-user-agent {
    max-width: 30rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
</style>

<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4">
      <div>
        <h1 class="display-6 fw-semibold mb-2">Relatórios de Acesso</h1>
        <p class="lead mb-0">Gere relatórios internos de navegação do site e exporte o período em PDF.</p>
      </div>
      <a class="btn btn-outline-secondary mt-3 mt-lg-0" href="<?php echo $site_url; ?>/area-restrita">
        <i class="bi bi-arrow-left me-1"></i> Voltar ao dashboard
      </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <form class="row g-3 align-items-end" method="get" action="<?php echo $site_url; ?>/relatorios-acesso">
          <div class="col-sm-6 col-lg-3">
            <label class="form-label" for="from">De</label>
            <input class="form-control" type="date" id="from" name="from" value="<?php echo htmlspecialchars($fromDate->format('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="col-sm-6 col-lg-3">
            <label class="form-label" for="to">Até</label>
            <input class="form-control" type="date" id="to" name="to" value="<?php echo htmlspecialchars($toDate->format('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="col-lg-auto">
            <button class="btn btn-primary" type="submit">
              <i class="bi bi-funnel me-1"></i> Gerar relatório
            </button>
          </div>
          <div class="col-lg-auto">
            <a class="btn btn-danger" href="<?php echo htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8'); ?>">
              <i class="bi bi-file-earmark-pdf me-1"></i> Exportar PDF
            </a>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <p class="text-muted mb-1">Total de acessos</p>
            <p class="display-6 fw-semibold mb-0"><?php echo number_format((int)$totals['accesses'], 0, ',', '.'); ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <p class="text-muted mb-1">IPs únicos</p>
            <p class="display-6 fw-semibold mb-0"><?php echo number_format((int)$totals['unique_ips'], 0, ',', '.'); ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <p class="text-muted mb-1">Páginas acessadas</p>
            <p class="display-6 fw-semibold mb-0"><?php echo number_format((int)$totals['unique_paths'], 0, ',', '.'); ?></p>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 fw-semibold mb-3">Acessos por dia</h2>
            <?php if (empty($dailyRows)): ?>
              <p class="text-muted mb-0">Sem dados no período.</p>
            <?php else: ?>
              <div class="d-grid gap-3">
                <?php foreach ($dailyRows as $row): ?>
                  <?php $width = $maxDaily > 0 ? max(4, (int)round(((int)$row['count'] / $maxDaily) * 100)) : 0; ?>
                  <div>
                    <div class="d-flex justify-content-between small mb-1">
                      <span><?php echo htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <strong><?php echo (int)$row['count']; ?></strong>
                    </div>
                    <div class="progress access-report-bar" role="progressbar" aria-valuenow="<?php echo (int)$row['count']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $maxDaily; ?>">
                      <div class="progress-bar" style="width: <?php echo $width; ?>%"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 fw-semibold mb-3">Páginas mais acessadas</h2>
            <?php if (empty($topPaths)): ?>
              <p class="text-muted mb-0">Sem páginas acessadas no período.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                  <thead>
                    <tr>
                      <th scope="col">Página</th>
                      <th scope="col" class="text-end">Acessos</th>
                      <th scope="col">Último acesso</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($topPaths as $row): ?>
                      <tr>
                        <td class="access-report-path"><code><?php echo htmlspecialchars((string)$row['path'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                        <td class="text-end fw-semibold"><?php echo number_format((int)$row['accesses'], 0, ',', '.'); ?></td>
                        <td><?php echo !empty($row['last_ts']) ? date('d/m/Y H:i', (int)$row['last_ts']) : '—'; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
      <div class="card-body">
        <h2 class="h5 fw-semibold mb-3">Acessos recentes</h2>
        <?php if (empty($recent)): ?>
          <p class="text-muted mb-0">Sem registros no período.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th scope="col">Data</th>
                  <th scope="col">Método</th>
                  <th scope="col">Página</th>
                  <th scope="col">IP</th>
                  <th scope="col">Navegador</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent as $row): ?>
                  <tr>
                    <td><?php echo !empty($row['ts']) ? date('d/m/Y H:i', (int)$row['ts']) : '—'; ?></td>
                    <td><span class="badge text-bg-light"><?php echo htmlspecialchars((string)$row['method'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td class="access-report-path"><code><?php echo htmlspecialchars((string)$row['path'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                    <td><?php echo htmlspecialchars((string)$row['ip'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="text-muted access-report-user-agent" title="<?php echo htmlspecialchars((string)$row['user_agent'], ENT_QUOTES, 'UTF-8'); ?>">
                      <?php echo htmlspecialchars((string)$row['user_agent'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
