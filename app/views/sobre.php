<?php
$recentChanges = [];
$projectRoot = realpath(__DIR__ . '/../../');
$jsonFile = $projectRoot . '/data/last_changes.json';

if (is_readable($jsonFile)) {
  $content = file_get_contents($jsonFile);
  $decoded = json_decode($content, true);
  if (is_array($decoded)) {
    $recentChanges = $decoded;
  }
} else {
  $gitRoot = $projectRoot;
  if ($gitRoot && is_dir($gitRoot . '/.git') && function_exists('shell_exec')) {
    $cmd = 'git -C ' . escapeshellarg($gitRoot) . ' log -n 8 --pretty=format:"%h|%an|%ad|%s" --date=short';
    $out = @shell_exec($cmd);
    if (is_string($out) && trim($out) !== '') {
      $lines = explode("\n", trim($out));
      foreach ($lines as $ln) {
        $parts = explode('|', $ln, 4);
        if (count($parts) < 4) continue;
        $recentChanges[] = [
          'hash' => $parts[0],
          'author' => $parts[1],
          'date' => $parts[2],
          'message' => $parts[3],
        ];
      }
    }
  }
}

$featureCards = [
  ['icon' => 'bi-phone', 'title' => 'Experiência responsiva', 'text' => 'Interface adaptada para celular, tablet e desktop usando Bootstrap 5.'],
  ['icon' => 'bi-images', 'title' => 'Galeria institucional', 'text' => 'Projetos organizados com fotos, vídeos, modal de visualização e carregamento otimizado.'],
  ['icon' => 'bi-cash-coin', 'title' => 'Doações', 'text' => 'Fluxos de contribuição com opções bancárias, PayPal e doação internacional.'],
  ['icon' => 'bi-shield-check', 'title' => 'Transparência', 'text' => 'Documentos e certificados centralizados para consulta pública.'],
  ['icon' => 'bi-window-sidebar', 'title' => 'ISNAPress', 'text' => 'Área logada para administrar conteúdo, blog, galeria, relatórios e usuários.'],
  ['icon' => 'bi-graph-up-arrow', 'title' => 'Relatórios', 'text' => 'Painéis internos de acesso com filtros por período e exportação em PDF.'],
];

$versionTimeline = [
  [
    'version' => '2.1',
    'date' => 'Junho de 2026',
    'status' => 'Atual',
    'icon' => 'bi-shield-lock',
    'href' => url('/sobre/versao-2-1'),
    'items' => [
      'Criptografia aplicada aos links dos documentos, reforçando a proteção das URLs de acesso aos arquivos.',
      'Novo layout da tela de login, com apresentação mais clara para acesso administrativo.',
      'Área logada redesenhada para melhorar a navegação e a rotina de gestão interna.',
    ],
  ],
  [
    'version' => '2.0',
    'date' => 'Abril de 2026',
    'status' => 'Entrega',
    'icon' => 'bi-rocket-takeoff',
    'items' => [
      'Relatórios internos de acesso com filtros por período, páginas mais acessadas e exportação em PDF.',
      'Dashboard e relatórios desconsideram telas administrativas e IPs internos configurados.',
      'Gestão completa da galeria com edição de seções e fotos carregadas em modal.',
      'Área logada reorganizada como ISNAPress, com navegação administrativa aprimorada.',
    ],
  ],
  [
    'version' => '1.4',
    'date' => 'Agosto de 2025',
    'status' => 'Entrega',
    'icon' => 'bi-play-circle',
    'items' => ['Vídeos interativos na página de doação com player e controles aprimorados.'],
  ],
  [
    'version' => '1.3',
    'date' => 'Julho de 2025',
    'status' => 'Entrega',
    'icon' => 'bi-bank',
    'items' => ['Opção de Doações Bancárias adicionada na página de Doação.'],
  ],
  [
    'version' => '1.2',
    'date' => 'Junho de 2025',
    'status' => 'Entrega',
    'icon' => 'bi-arrow-up-circle',
    'items' => [
      'Sistema aprimorado de exibição de PDFs com visualização integrada.',
      'Botão do Instagram adicionado ao rodapé.',
    ],
  ],
  [
    'version' => '1.1',
    'date' => 'Março de 2025',
    'status' => 'Base',
    'icon' => 'bi-clock-history',
    'items' => [
      'Modal de imagens com abertura e fechamento corrigidos.',
      'Roteamento avançado para URLs amigáveis e navegação dinâmica.',
      'Melhorias de segurança e otimizações de desempenho.',
    ],
  ],
];
?>

<main class="sobre-page">
  <section class="sobre-hero" style="--sobre-hero-image: url('<?php echo htmlspecialchars(cms_attr('sobre', 'hero.image', '/images/imagem.jpg'), ENT_QUOTES, 'UTF-8'); ?>');">
    <div class="container">
      <div class="sobre-hero-grid">
        <div class="sobre-hero-copy">
          <span class="sobre-eyebrow">ISNAPress 2.1</span>
          <h1>Sobre o sistema</h1>
          <p>
            Portal institucional e área administrativa criados para manter o conteúdo do Instituto Social Novo Amanhecer organizado, acessível e fácil de atualizar.
          </p>
          <div class="sobre-hero-actions">
            <a class="btn btn-primary" href="<?php echo $site_url; ?>/area-restrita">
              <i class="bi bi-speedometer2 me-1"></i> Abrir painel
            </a>
            <a class="btn btn-outline-secondary" href="<?php echo $site_url; ?>/">
              <i class="bi bi-house-door me-1"></i> Ver site
            </a>
          </div>
        </div>
        <div class="sobre-hero-panel" aria-label="Resumo do sistema">
          <div>
            <span class="sobre-panel-label">Versão</span>
            <strong>2.1</strong>
          </div>
          <div>
            <span class="sobre-panel-label">Lançamento</span>
            <strong>Junho de 2026</strong>
          </div>
          <div>
            <span class="sobre-panel-label">Foco</span>
            <strong>Segurança, login e área logada</strong>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sobre-section sobre-section-muted">
    <div class="container">
      <div class="sobre-section-header">
        <span class="sobre-eyebrow">Capacidades</span>
        <h2>Recursos principais</h2>
        <p>Uma visão rápida das áreas que sustentam a operação pública e administrativa do portal.</p>
      </div>

      <div class="sobre-feature-grid">
        <?php foreach ($featureCards as $feature): ?>
          <article class="sobre-feature-card">
            <div class="sobre-feature-icon">
              <i class="bi <?php echo htmlspecialchars($feature['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
            </div>
            <h3><?php echo htmlspecialchars($feature['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><?php echo htmlspecialchars($feature['text'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="sobre-section">
    <div class="container">
      <div class="sobre-split">
        <div class="sobre-section-header sobre-section-header-sticky">
          <span class="sobre-eyebrow">Evolução</span>
          <h2>Histórico de versões</h2>
          <p>Principais entregas feitas para ampliar a autonomia de gestão, melhorar a navegação e reforçar a transparência.</p>
        </div>

        <div class="sobre-version-list">
          <?php foreach ($versionTimeline as $version): ?>
            <?php
              $versionHref = isset($version['href']) ? (string) $version['href'] : '';
              $versionContentTag = $versionHref !== '' ? 'a' : 'div';
            ?>
            <article class="sobre-version-item">
              <div class="sobre-version-marker">
                <i class="bi <?php echo htmlspecialchars($version['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
              </div>
              <<?php echo $versionContentTag; ?> class="sobre-version-content<?php echo $versionHref !== '' ? ' sobre-version-content-link' : ''; ?>"<?php if ($versionHref !== ''): ?> href="<?php echo htmlspecialchars($versionHref, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Abrir relatório completo da versão <?php echo htmlspecialchars($version['version'], ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
                <div class="sobre-version-heading">
                  <div>
                    <span class="sobre-version-kicker"><?php echo htmlspecialchars($version['date'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3>Versão <?php echo htmlspecialchars($version['version'], ENT_QUOTES, 'UTF-8'); ?></h3>
                  </div>
                  <span class="sobre-version-badge"><?php echo htmlspecialchars($version['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <ul>
                  <?php foreach ($version['items'] as $item): ?>
                    <li><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                  <?php endforeach; ?>
                </ul>
              </<?php echo $versionContentTag; ?>>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="sobre-section sobre-section-muted">
    <div class="container">
      <div class="sobre-section-header">
        <span class="sobre-eyebrow">Repositório</span>
        <h2>Últimas modificações</h2>
        <p>Registro recente de mudanças do projeto, exibido a partir do arquivo de changelog gerado.</p>
      </div>

      <?php if (!empty($recentChanges)): ?>
        <div class="sobre-change-list">
          <?php foreach ($recentChanges as $change): ?>
            <article class="sobre-change-item">
              <div>
                <h3><?php echo htmlspecialchars((string)($change['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars((string)($change['author'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars((string)($change['date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
              <code><?php echo htmlspecialchars((string)($change['hash'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></code>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="sobre-empty-state">
          <i class="bi bi-info-circle" aria-hidden="true"></i>
          <span>Histórico de modificações não disponível.</span>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>
