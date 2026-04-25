<?php
if (!auth_user_is_admin()) {
    auth_flash_message('error', 'Apenas administradores podem gerenciar o conteúdo do site.');
    auth_redirect('area-restrita');
}

$pages = CmsModel::registeredBlocks();
$pageOptions = CmsModel::pageOptions();
$customSections = CmsModel::customSections();
$mediaItems = CmsModel::mediaItems();
$mediaGroups = CmsModel::mediaGroups();
$token = AuthService::generateCsrfToken('cms_save_blocks');
$addSectionToken = AuthService::generateCsrfToken('cms_add_section');
$deleteSectionToken = AuthService::generateCsrfToken('cms_delete_section');
$uploadMediaToken = AuthService::generateCsrfToken('cms_upload_media');
$oldSectionPage = AuthService::flashPullValue('cms_section_page_slug', 'home');
$oldSectionTitle = AuthService::flashPullValue('cms_section_title', '');
$oldSectionContent = AuthService::flashPullValue('cms_section_content', '');
?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
      <div>
        <h1 class="display-6 fw-semibold mb-2">CMS do Site</h1>
        <p class="lead mb-0">Edite textos, imagens e chamadas das principais áreas sem alterar código.</p>
      </div>
      <a class="btn btn-outline-secondary mt-3 mt-lg-0" href="<?php echo $site_url; ?>/" target="_blank" rel="noreferrer">
        <i class="bi bi-box-arrow-up-right me-1"></i> Ver site
      </a>
    </div>

    <form method="post" action="<?php echo $site_url; ?>/gestao-cms">
      <input type="hidden" name="action" value="save_blocks">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

      <div class="accordion" id="cmsAccordion">
        <?php $pageIndex = 0; ?>
        <?php foreach ($pages as $pageSlug => $page): ?>
          <?php if (empty($page['blocks'])) continue; ?>
          <?php
            $collapseId = 'cms-page-' . preg_replace('/[^a-z0-9_-]/i', '-', (string)$pageSlug);
            $isOpen = $pageIndex === 0;
          ?>
          <div class="accordion-item border-0 shadow-sm mb-3">
            <h2 class="accordion-header">
              <button class="accordion-button<?php echo $isOpen ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="<?php echo $isOpen ? 'true' : 'false'; ?>" aria-controls="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8'); ?>
              </button>
            </h2>
            <div id="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>" class="accordion-collapse collapse<?php echo $isOpen ? ' show' : ''; ?>" data-bs-parent="#cmsAccordion">
              <div class="accordion-body">
                <div class="row g-3">
                  <?php foreach ($page['blocks'] as $key => $block): ?>
                    <?php
                      $fieldName = 'blocks[' . $pageSlug . '][' . $key . ']';
                      $fieldId = 'cms-' . preg_replace('/[^a-z0-9_-]/i', '-', $pageSlug . '-' . $key);
                      $type = $block['type'] ?? 'text';
                      $value = (string)($block['value'] ?? '');
                    ?>
                    <div class="col-12<?php echo in_array($type, ['textarea', 'html', 'image'], true) ? '' : ' col-lg-6'; ?>">
                      <label class="form-label fw-semibold" for="<?php echo htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($block['label'], ENT_QUOTES, 'UTF-8'); ?>
                      </label>
                      <?php if ($type === 'image'): ?>
                        <?php
                          $currentImage = CmsModel::isMediaPathAllowed($value) ? $value : (string)($block['default'] ?? '/images/imagem.jpg');
                          $currentImageUrl = $site_url . '/' . ltrim($currentImage, '/');
                        ?>
                        <input type="hidden" id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8'); ?>" data-cms-media-value>
                        <div class="cms-media-field" data-cms-media-field>
                          <button class="cms-media-current" type="button" data-bs-toggle="modal" data-bs-target="#cmsMediaModal" data-cms-media-trigger data-cms-media-field-id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8'); ?>"<?php echo empty($mediaItems) ? ' disabled' : ''; ?>>
                            <img src="<?php echo htmlspecialchars($currentImageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem selecionada para <?php echo htmlspecialchars($block['label'], ENT_QUOTES, 'UTF-8'); ?>" data-cms-media-preview>
                            <span data-cms-media-current-label><?php echo htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8'); ?></span>
                            <i class="bi bi-pencil-square ms-auto" aria-hidden="true"></i>
                          </button>
                          <?php if (empty($mediaItems)): ?>
                            <p class="text-muted small mb-0 mt-2">Nenhuma imagem disponível. Envie uma imagem na biblioteca acima.</p>
                          <?php else: ?>
                            <div class="form-text">Clique na imagem para escolher outra na biblioteca.</div>
                          <?php endif; ?>
                        </div>
                      <?php elseif (in_array($type, ['textarea', 'html'], true)): ?>
                        <textarea class="form-control" id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>" rows="<?php echo $type === 'html' ? 8 : 4; ?>"><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></textarea>
                      <?php else: ?>
                        <input class="form-control" id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>" type="<?php echo $type === 'url' ? 'url' : 'text'; ?>" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
          <?php $pageIndex++; ?>
        <?php endforeach; ?>
      </div>

      <div class="position-sticky bottom-0 bg-light border-top py-3 mt-4">
        <button class="btn btn-primary btn-lg" type="submit">
          <i class="bi bi-save me-1"></i> Salvar conteúdo
        </button>
      </div>
    </form>

    <div class="row g-4 mt-4">
      <div class="col-12 col-xl-5">
        <div class="bg-white border rounded-3 shadow-sm p-4">
          <h2 class="h4 fw-semibold mb-3">Adicionar seção</h2>
          <form method="post" action="<?php echo $site_url; ?>/gestao-cms">
            <input type="hidden" name="action" value="add_section">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($addSectionToken, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold" for="cms-section-page">Página</label>
              <select class="form-select" id="cms-section-page" name="page_slug" required>
                <?php foreach ($pageOptions as $slug => $label): ?>
                  <option value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $oldSectionPage === $slug ? ' selected' : ''; ?>>
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="cms-section-title">Título da seção</label>
              <input class="form-control" id="cms-section-title" name="section_title" type="text" value="<?php echo htmlspecialchars((string)$oldSectionTitle, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="cms-section-content">Texto</label>
              <textarea class="form-control" id="cms-section-content" name="section_content" rows="8"><?php echo htmlspecialchars((string)$oldSectionContent, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <button class="btn btn-success" type="submit">
              <i class="bi bi-plus-lg me-1"></i> Adicionar ao site
            </button>
          </form>
        </div>
      </div>

      <div class="col-12 col-xl-7">
        <div class="bg-white border rounded-3 shadow-sm p-4">
          <h2 class="h4 fw-semibold mb-3">Seções adicionadas</h2>
          <?php if (empty($customSections)): ?>
            <p class="text-muted mb-0">Nenhuma seção extra foi adicionada ainda.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Página</th>
                    <th>Seção</th>
                    <th class="text-end">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($customSections as $section): ?>
                    <?php
                      $sectionPage = isset($section['page_slug']) ? (string)$section['page_slug'] : '';
                      $sectionTitle = isset($section['title']) ? (string)$section['title'] : '';
                      $sectionContent = (string)($section['content'] ?? '');
                      $sectionSummary = function_exists('mb_strimwidth') ? mb_strimwidth($sectionContent, 0, 90, '...') : substr($sectionContent, 0, 90);
                      $pageLabel = $pageOptions[$sectionPage] ?? $sectionPage;
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($pageLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <div class="fw-semibold"><?php echo htmlspecialchars($sectionTitle !== '' ? $sectionTitle : 'Seção sem título', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="small text-muted"><?php echo htmlspecialchars($sectionSummary, ENT_QUOTES, 'UTF-8'); ?></div>
                      </td>
                      <td class="text-end">
                        <form method="post" action="<?php echo $site_url; ?>/gestao-cms" onsubmit="return confirm('Remover esta seção do site?');">
                          <input type="hidden" name="action" value="delete_section">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($deleteSectionToken, ENT_QUOTES, 'UTF-8'); ?>">
                          <input type="hidden" name="section_id" value="<?php echo (int)($section['id'] ?? 0); ?>">
                          <button class="btn btn-outline-danger btn-sm" type="submit">
                            <i class="bi bi-trash me-1"></i> Remover
                          </button>
                        </form>
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
  </div>
</section>

<div class="modal fade" id="cmsMediaModal" tabindex="-1" aria-labelledby="cmsMediaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h2 class="modal-title h5" id="cmsMediaModalLabel">Selecionar imagem</h2>
          <p class="text-muted small mb-0">Imagens organizadas pelas pastas dentro de /images.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="cms-media-upload border rounded-3 p-3 mb-4 bg-light">
          <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
              <h3 class="h6 fw-semibold mb-1">Enviar nova imagem</h3>
              <p class="text-muted small mb-0">A imagem enviada ficará disponível na pasta CMS desta biblioteca.</p>
            </div>
            <form class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center" method="post" action="<?php echo $site_url; ?>/gestao-cms" enctype="multipart/form-data">
              <input type="hidden" name="action" value="upload_media">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($uploadMediaToken, ENT_QUOTES, 'UTF-8'); ?>">
              <input class="form-control" type="file" name="cms_media" accept="image/jpeg,image/png,image/webp,image/gif" required>
              <button class="btn btn-success flex-shrink-0" type="submit">
                <i class="bi bi-upload me-1"></i> Enviar imagem
              </button>
            </form>
          </div>
        </div>
        <?php if (empty($mediaGroups)): ?>
          <p class="text-muted mb-0">Nenhuma imagem disponível. Envie uma imagem acima.</p>
        <?php else: ?>
          <div class="accordion cms-media-modal-accordion" id="cmsMediaGroups">
            <?php foreach ($mediaGroups as $groupIndex => $group): ?>
              <?php
                $groupId = 'cms-media-group-' . $groupIndex;
                $items = isset($group['items']) && is_array($group['items']) ? $group['items'] : [];
              ?>
              <div class="accordion-item">
                <h3 class="accordion-header">
                  <button class="accordion-button<?php echo $groupIndex === 0 ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo htmlspecialchars($groupId, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="<?php echo $groupIndex === 0 ? 'true' : 'false'; ?>" aria-controls="<?php echo htmlspecialchars($groupId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)($group['label'] ?? 'Imagens'), ENT_QUOTES, 'UTF-8'); ?>
                    <span class="badge text-bg-light ms-2"><?php echo count($items); ?></span>
                  </button>
                </h3>
                <div id="<?php echo htmlspecialchars($groupId, ENT_QUOTES, 'UTF-8'); ?>" class="accordion-collapse collapse<?php echo $groupIndex === 0 ? ' show' : ''; ?>" data-bs-parent="#cmsMediaGroups">
                  <div class="accordion-body">
                    <div class="cms-media-grid">
                      <?php foreach ($items as $media): ?>
                        <?php
                          $mediaPath = (string)($media['path'] ?? '');
                          if ($mediaPath === '') continue;
                          $mediaUrl = $site_url . '/' . ltrim($mediaPath, '/');
                        ?>
                        <button class="cms-media-option" type="button" data-cms-media-select data-cms-media-path="<?php echo htmlspecialchars($mediaPath, ENT_QUOTES, 'UTF-8'); ?>" data-cms-media-url="<?php echo htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8'); ?>">
                          <img src="<?php echo htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)($media['label'] ?? 'Imagem CMS'), ENT_QUOTES, 'UTF-8'); ?>">
                          <span><?php echo htmlspecialchars((string)($media['label'] ?? basename($mediaPath)), ENT_QUOTES, 'UTF-8'); ?></span>
                        </button>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
var cmsActiveMediaField = null;
var cmsMediaModal = document.getElementById('cmsMediaModal');

document.querySelectorAll('[data-cms-media-trigger]').forEach(function (trigger) {
  trigger.addEventListener('click', function () {
    var fieldId = trigger.getAttribute('data-cms-media-field-id');
    var input = fieldId ? document.getElementById(fieldId) : null;
    cmsActiveMediaField = input ? {
      input: input,
      wrapper: trigger.closest('[data-cms-media-field]'),
      trigger: trigger
    } : null;
  });
});

if (cmsMediaModal) {
  cmsMediaModal.addEventListener('shown.bs.modal', function () {
    var currentValue = cmsActiveMediaField && cmsActiveMediaField.input ? cmsActiveMediaField.input.value : '';
    document.querySelectorAll('[data-cms-media-select]').forEach(function (button) {
      button.classList.toggle('active', button.getAttribute('data-cms-media-path') === currentValue);
    });
  });
}

document.querySelectorAll('[data-cms-media-select]').forEach(function (button) {
  button.addEventListener('click', function () {
    if (!cmsActiveMediaField || !cmsActiveMediaField.input || !cmsActiveMediaField.wrapper) return;

    var mediaPath = button.getAttribute('data-cms-media-path') || '';
    var mediaUrl = button.getAttribute('data-cms-media-url') || '';
    var preview = cmsActiveMediaField.wrapper.querySelector('[data-cms-media-preview]');
    var label = cmsActiveMediaField.wrapper.querySelector('[data-cms-media-current-label]');

    cmsActiveMediaField.input.value = mediaPath;
    if (preview && mediaUrl) preview.src = mediaUrl;
    if (label) label.textContent = mediaPath;

    document.querySelectorAll('[data-cms-media-select]').forEach(function (option) {
      option.classList.toggle('active', option === button);
    });

    var modal = window.bootstrap ? bootstrap.Modal.getInstance(cmsMediaModal) : null;
    if (modal) modal.hide();
  });
});
</script>
