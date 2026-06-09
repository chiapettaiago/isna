<?php
require_once __DIR__ . '/../services/VersionReportData.php';

$reportData = VersionReportData::version21();
$summaryCards = $reportData['summaryCards'];
$changeSections = $reportData['changeSections'];
$deliveryRows = $reportData['deliveryRows'];
$qualityChecks = $reportData['qualityChecks'];
$pdfUrl = $site_url . '/sobre/versao-2-1/pdf';
?>

<main class="release-page">
  <section class="release-section release-intro-section">
    <div class="container">
      <div class="release-intro-grid">
        <div>
          <a class="release-back-link" href="<?php echo $site_url; ?>/sobre">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Voltar para o histórico
          </a>
          <span class="sobre-eyebrow"><?php echo htmlspecialchars($reportData['label'], ENT_QUOTES, 'UTF-8'); ?></span>
          <h1><?php echo htmlspecialchars($reportData['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
          <p><?php echo htmlspecialchars($reportData['description'], ENT_QUOTES, 'UTF-8'); ?></p>
          <div class="release-intro-actions">
            <a class="btn btn-danger" href="<?php echo htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8'); ?>">
              <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>
              Exportar PDF
            </a>
          </div>
        </div>

        <aside class="release-version-card" aria-label="Resumo da versão">
          <div>
            <span>Versão</span>
            <strong>2.1</strong>
          </div>
          <div>
            <span>Lançamento</span>
            <strong><?php echo htmlspecialchars($reportData['period'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div>
            <span>Status</span>
            <strong><?php echo htmlspecialchars($reportData['status'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="release-section release-section-muted">
    <div class="container">
      <div class="release-summary-grid">
        <?php foreach ($summaryCards as $card): ?>
          <article class="release-summary-card">
            <div class="release-icon">
              <i class="bi <?php echo htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
            </div>
            <span><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></span>
            <h2><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?php echo htmlspecialchars($card['text'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="release-section">
    <div class="container">
      <div class="release-section-header">
        <span class="sobre-eyebrow">Mudanças realizadas</span>
        <h2>Escopo detalhado da versão 2.1</h2>
        <p>As entregas abaixo documentam o que mudou, por que a mudança existe e qual efeito prático ela traz para o portal.</p>
      </div>

      <div class="release-detail-list">
        <?php foreach ($changeSections as $section): ?>
          <article class="release-detail-card">
            <div class="release-detail-heading">
              <div class="release-icon">
                <i class="bi <?php echo htmlspecialchars($section['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
              </div>
              <div>
                <h3><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars($section['intro'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>

            <ul class="release-check-list">
              <?php foreach ($section['items'] as $item): ?>
                <li>
                  <i class="bi bi-check2-circle" aria-hidden="true"></i>
                  <span><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>

            <div class="release-result">
              <span>Resultado</span>
              <p><?php echo htmlspecialchars($section['result'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="release-section release-section-muted">
    <div class="container">
      <div class="release-section-header">
        <span class="sobre-eyebrow">Comparativo</span>
        <h2>Antes e depois</h2>
        <p>Resumo objetivo das melhorias perceptíveis na operação e na navegação.</p>
      </div>

      <div class="release-table-wrap">
        <table class="release-table">
          <thead>
            <tr>
              <th>Área</th>
              <th>Antes</th>
              <th>Depois da 2.1</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($deliveryRows as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['area'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['before'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['after'], ENT_QUOTES, 'UTF-8'); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="release-section">
    <div class="container">
      <div class="release-final-grid">
        <div class="release-section-header">
          <span class="sobre-eyebrow">Conferência</span>
          <h2>Critérios de qualidade</h2>
          <p>Itens usados como referência para considerar a versão documentada e pronta para uso interno.</p>
        </div>

        <div class="release-quality-card">
          <ul class="release-check-list">
            <?php foreach ($qualityChecks as $check): ?>
              <li>
                <i class="bi bi-patch-check" aria-hidden="true"></i>
                <span><?php echo htmlspecialchars($check, ENT_QUOTES, 'UTF-8'); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <a class="btn btn-primary" href="<?php echo $site_url; ?>/sobre">
            <i class="bi bi-clock-history me-1" aria-hidden="true"></i>
            Ver linha do tempo
          </a>
        </div>
      </div>
    </div>
  </section>
</main>
