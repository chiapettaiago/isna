<?php
if (!\AuthService::userIsAdmin()) {
  \AuthService::flashMessage('error', 'Apenas administradores podem acessar a gestão da galeria.');
  \AuthService::redirect('area-restrita');
}

$config = \GalleryModel::load();
$sections = $config['sections'];
$mediaItems = \CmsModel::mediaItems();

$gridSections = array_values(array_filter($sections, static function ($section) {
  return isset($section['type']) && $section['type'] === 'grid';
}));

$createSectionToken = \AuthService::generateCsrfToken('gallery_create_section');
$addItemToken = \AuthService::generateCsrfToken('gallery_add_item');

$oldCreateTitle = \AuthService::flashPullValue('gallery_create_title', '');
$oldCreateBackground = \AuthService::flashPullValue('gallery_create_background', '');
$oldCreateDescription = \AuthService::flashPullValue('gallery_create_description', '');

$oldItemSection = \AuthService::flashPullValue('gallery_item_section', '');
$oldItemSrc = \AuthService::flashPullValue('gallery_item_src', '');
$oldItemAlt = \AuthService::flashPullValue('gallery_item_alt', '');
$oldItemCaption = \AuthService::flashPullValue('gallery_item_caption', '');

$backgroundOptions = [
  '' => 'Sem fundo especial',
  'bg-light' => 'Fundo claro (bg-light)',
  'bg-white' => 'Fundo branco (bg-white)',
];
?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4">
      <div>
        <h1 class="display-6 fw-semibold mb-2">Gestão da Galeria</h1>
        <p class="lead mb-0">Cadastre novas seções e fotos para destacar os projetos institucionais.</p>
      </div>
      <a class="btn btn-outline-secondary mt-3 mt-lg-0" href="<?php echo $site_url; ?>/galeria" target="_blank" rel="noreferrer">
        <i class="bi bi-box-arrow-up-right me-1"></i> Ver galeria pública
      </a>
    </div>

    <div class="row g-4">
      <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 fw-semibold">Cadastrar nova seção</h2>
            <p class="text-muted">As seções criadas aqui aparecerão na página da galeria após receberem itens.</p>
            <form method="post" action="<?php echo $site_url; ?>/gestao-galeria" class="mt-3" autocomplete="off">
              <input type="hidden" name="action" value="create_section">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($createSectionToken, ENT_QUOTES, 'UTF-8'); ?>">

              <div class="mb-3">
                <label class="form-label" for="section_title">Título da seção</label>
                <input
                  class="form-control"
                  type="text"
                  id="section_title"
                  name="title"
                  value="<?php echo htmlspecialchars($oldCreateTitle, ENT_QUOTES, 'UTF-8'); ?>"
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label" for="section_background">Estilo de fundo</label>
                <select class="form-select" id="section_background" name="background">
                  <?php foreach ($backgroundOptions as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $value === $oldCreateBackground ? ' selected' : ''; ?>>
                      <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-4">
                <label class="form-label" for="section_description">Descrição (opcional)</label>
                <textarea class="form-control" id="section_description" name="description" rows="3" placeholder="Explique o que será exibido nesta seção."><?php echo htmlspecialchars($oldCreateDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <div class="form-text">Aceita múltiplas linhas; o texto aparece abaixo do título.</div>
              </div>

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-layer-plus me-1"></i> Criar seção
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 fw-semibold">Adicionar item a uma seção</h2>
            <p class="text-muted">Envie imagens adicionais para qualquer seção criada manualmente.</p>

            <?php if (empty($gridSections)): ?>
              <div class="alert alert-warning" role="alert">
                Cadastre uma seção antes de adicionar itens.
              </div>
            <?php else: ?>
              <form method="post" action="<?php echo $site_url; ?>/gestao-galeria" class="mt-3" autocomplete="off">
                <input type="hidden" name="action" value="add_item">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($addItemToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="mb-3">
                  <label class="form-label" for="item_section">Seção</label>
                  <select class="form-select" id="item_section" name="section_id" required>
                    <option value="" disabled<?php echo $oldItemSection === '' ? ' selected' : ''; ?>>Selecione uma seção</option>
                    <?php foreach ($gridSections as $section): ?>
                      <option value="<?php echo htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo $section['id'] === $oldItemSection ? ' selected' : ''; ?>>
                        <?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo isset($section['items']) ? count($section['items']) : 0; ?> itens)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Imagem</label>
                  <?php if (empty($mediaItems)): ?>
                    <div class="alert alert-warning" role="alert">
                      Envie imagens na biblioteca do CMS antes de adicionar itens à galeria.
                    </div>
                  <?php else: ?>
                    <div class="cms-media-picker" data-cms-media-picker>
                      <div class="cms-media-grid">
                        <?php foreach ($mediaItems as $mediaIndex => $media): ?>
                          <?php
                            $mediaPath = (string)($media['path'] ?? '');
                            if ($mediaPath === '') continue;
                            $mediaUrl = $site_url . '/' . ltrim($mediaPath, '/');
                            $radioId = 'gallery-item-media-' . $mediaIndex;
                            $checked = $oldItemSrc === $mediaPath || ($oldItemSrc === '' && $mediaIndex === 0);
                          ?>
                          <label class="cms-media-option<?php echo $checked ? ' active' : ''; ?>" for="<?php echo htmlspecialchars($radioId, ENT_QUOTES, 'UTF-8'); ?>">
                            <input id="<?php echo htmlspecialchars($radioId, ENT_QUOTES, 'UTF-8'); ?>" type="radio" name="src" value="<?php echo htmlspecialchars($mediaPath, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $checked ? ' checked' : ''; ?> data-cms-media-radio required>
                            <img src="<?php echo htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)($media['label'] ?? 'Imagem'), ENT_QUOTES, 'UTF-8'); ?>">
                            <span><?php echo htmlspecialchars((string)($media['label'] ?? basename($mediaPath)), ENT_QUOTES, 'UTF-8'); ?></span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                  <div class="form-text">Para usar uma imagem nova, envie primeiro em CMS do site &gt; Biblioteca de imagens.</div>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="item_alt">Texto alternativo</label>
                  <input
                    class="form-control"
                    type="text"
                    id="item_alt"
                    name="alt"
                    value="<?php echo htmlspecialchars($oldItemAlt, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Breve descrição da imagem"
                  >
                </div>

                <div class="mb-4">
                  <label class="form-label" for="item_caption">Legenda (opcional)</label>
                  <input
                    class="form-control"
                    type="text"
                    id="item_caption"
                    name="caption"
                    value="<?php echo htmlspecialchars($oldItemCaption, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Título exibido no modal"
                  >
                </div>

                <button class="btn btn-success" type="submit"<?php echo empty($mediaItems) ? ' disabled' : ''; ?>>
                  <i class="bi bi-plus-circle-fill me-1"></i> Adicionar item
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
          <div>
            <h2 class="h5 fw-semibold mb-1">Seções cadastradas</h2>
            <p class="text-muted mb-0">Resumo das seções configuradas e quantidade de itens publicados.</p>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th scope="col">Título</th>
                <th scope="col">Identificador</th>
                <th scope="col">Tipo</th>
                <th scope="col" class="text-center">Itens</th>
                <th scope="col">Nota</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($sections as $section): ?>
                <?php
                  $typeLabel = $section['type'] === 'directory' ? 'Automática (pasta)' : 'Manual (grid)';
                  $itemCount = $section['type'] === 'directory' ? '—' : (isset($section['items']) ? count($section['items']) : 0);
                  $note = $section['type'] === 'directory'
                    ? 'Itens exibidos automaticamente a partir da pasta ' . htmlspecialchars($section['directory'] ?? '', ENT_QUOTES, 'UTF-8')
                    : 'Itens adicionados pela interface.';
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><code><?php echo htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                  <td><?php echo $typeLabel; ?></td>
                  <td class="text-center"><?php echo $itemCount; ?></td>
                  <td><?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('[data-cms-media-picker]').forEach(function (picker) {
  picker.querySelectorAll('[data-cms-media-radio]').forEach(function (radio) {
    radio.addEventListener('change', function () {
      picker.querySelectorAll('.cms-media-option').forEach(function (option) {
        option.classList.toggle('active', option.contains(radio));
      });
    });
  });
});
</script>
