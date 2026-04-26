<?php
if (!\AuthService::userIsAdmin()) {
  \AuthService::flashMessage('error', 'Apenas administradores podem acessar a gestão da galeria.');
  \AuthService::redirect('area-restrita');
}

$config = \GalleryModel::load();
$sections = $config['sections'];
$mediaGroups = \CmsModel::mediaGroups();
$mediaItems = \CmsModel::mediaItems();

$createSectionToken = \AuthService::generateCsrfToken('gallery_create_section');
$editSectionToken = \AuthService::generateCsrfToken('gallery_edit_section');
$deleteSectionToken = \AuthService::generateCsrfToken('gallery_delete_section');
$addItemToken = \AuthService::generateCsrfToken('gallery_add_item');
$removeItemToken = \AuthService::generateCsrfToken('gallery_remove_item');

$oldCreateTitle = \AuthService::flashPullValue('gallery_create_title', '');
$oldCreateType = \AuthService::flashPullValue('gallery_create_type', 'grid');
$oldCreateBackground = \AuthService::flashPullValue('gallery_create_background', '');
$oldCreateDescription = \AuthService::flashPullValue('gallery_create_description', '');
$oldCreateDirectory = \AuthService::flashPullValue('gallery_create_directory', '');
$oldCreateCaptionPrefix = \AuthService::flashPullValue('gallery_create_caption_prefix', '');

$oldItemSection = \AuthService::flashPullValue('gallery_item_section', '');
$oldItemSrc = \AuthService::flashPullValue('gallery_item_src', '');
$oldItemAlt = \AuthService::flashPullValue('gallery_item_alt', '');
$oldItemCaption = \AuthService::flashPullValue('gallery_item_caption', '');

$backgroundOptions = [
  '' => 'Sem fundo especial',
  'bg-light' => 'Fundo claro (bg-light)',
  'bg-white' => 'Fundo branco (bg-white)',
];

$directoryOptions = [];
foreach ($mediaGroups as $group) {
  $key = isset($group['key']) ? (string)$group['key'] : '';
  if ($key !== '' && strpos($key, 'images/') === 0) {
    $directoryOptions[$key] = $key;
  }
}

$sectionsForView = [];
foreach ($sections as $section) {
  $items = \GalleryModel::sectionItems($section);
  $section['resolved_items'] = $items;
  $section['resolved_count'] = count($items);
  $sectionsForView[] = $section;
}

$placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
?>

<style>
  .gallery-admin-section-list {
    display: grid;
    gap: 1rem;
  }

  .gallery-admin-section {
    display: grid;
    gap: 1rem;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
  }

  .gallery-admin-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: flex-end;
  }

  .gallery-admin-photo-grid {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  }

  .gallery-admin-photo {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 0.5rem;
    overflow: hidden;
    background: #fff;
  }

  .gallery-admin-photo img {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    display: block;
    background: #f1f3f5;
  }

  .gallery-admin-photo figcaption {
    min-height: 2.5rem;
    padding: 0.5rem;
    font-size: 0.78rem;
    color: #6c757d;
    overflow-wrap: anywhere;
  }

  .gallery-admin-media-preview {
    width: 76px;
    height: 76px;
    object-fit: cover;
    border-radius: 0.5rem;
    background: #f1f3f5;
  }

  @media (max-width: 767.98px) {
    .gallery-admin-section {
      grid-template-columns: 1fr;
    }

    .gallery-admin-actions {
      justify-content: stretch;
    }

    .gallery-admin-actions .btn,
    .gallery-admin-actions form {
      width: 100%;
    }
  }
</style>

<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4">
      <div>
        <h1 class="display-6 fw-semibold mb-2">Gestão da Galeria</h1>
        <p class="lead mb-0">Gerencie seções, pastas automáticas e fotos dos projetos institucionais.</p>
      </div>
      <a class="btn btn-outline-secondary mt-3 mt-lg-0" href="<?php echo $site_url; ?>/galeria" target="_blank" rel="noreferrer">
        <i class="bi bi-box-arrow-up-right me-1"></i> Ver galeria pública
      </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <h2 class="h5 fw-semibold">Cadastrar nova seção</h2>
        <form method="post" action="<?php echo $site_url; ?>/gestao-galeria" class="mt-3" autocomplete="off" data-gallery-section-form>
          <input type="hidden" name="action" value="create_section">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($createSectionToken, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="row g-3">
            <div class="col-lg-5">
              <label class="form-label" for="section_title">Título da seção</label>
              <input class="form-control" type="text" id="section_title" name="title" value="<?php echo htmlspecialchars($oldCreateTitle, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="section_type">Tipo</label>
              <select class="form-select" id="section_type" name="type" data-gallery-section-type>
                <option value="grid"<?php echo $oldCreateType !== 'directory' ? ' selected' : ''; ?>>Manual (fotos escolhidas)</option>
                <option value="directory"<?php echo $oldCreateType === 'directory' ? ' selected' : ''; ?>>Automática (pasta)</option>
              </select>
            </div>
            <div class="col-lg-4">
              <label class="form-label" for="section_background">Estilo de fundo</label>
              <select class="form-select" id="section_background" name="background">
                <?php foreach ($backgroundOptions as $value => $label): ?>
                  <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $value === $oldCreateBackground ? ' selected' : ''; ?>>
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" for="section_description">Descrição (opcional)</label>
              <textarea class="form-control" id="section_description" name="description" rows="2"><?php echo htmlspecialchars($oldCreateDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="col-lg-6" data-gallery-directory-fields>
              <label class="form-label" for="section_directory">Pasta dentro de /images</label>
              <input class="form-control" type="text" id="section_directory" name="directory" list="gallery-directory-options" value="<?php echo htmlspecialchars($oldCreateDirectory, ENT_QUOTES, 'UTF-8'); ?>" placeholder="images/nome-da-pasta">
            </div>
            <div class="col-lg-6" data-gallery-directory-fields>
              <label class="form-label" for="section_caption_prefix">Prefixo das legendas</label>
              <input class="form-control" type="text" id="section_caption_prefix" name="caption_prefix" value="<?php echo htmlspecialchars($oldCreateCaptionPrefix, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Usa o título se ficar vazio">
            </div>
          </div>

          <button class="btn btn-primary mt-3" type="submit">
            <i class="bi bi-layer-plus me-1"></i> Criar seção
          </button>
        </form>
      </div>
    </div>

    <datalist id="gallery-directory-options">
      <?php foreach ($directoryOptions as $directory): ?>
        <option value="<?php echo htmlspecialchars($directory, ENT_QUOTES, 'UTF-8'); ?>"></option>
      <?php endforeach; ?>
    </datalist>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
          <div>
            <h2 class="h5 fw-semibold mb-1">Seções cadastradas</h2>
            <p class="text-muted mb-0">Abra uma seção para editar dados e carregar as fotos em modal.</p>
          </div>
        </div>

        <div class="gallery-admin-section-list">
          <?php foreach ($sectionsForView as $section): ?>
            <?php
              $sectionId = (string)$section['id'];
              $modalId = 'gallery-admin-section-' . $sectionId;
              $typeLabel = $section['type'] === 'directory' ? 'Automática (pasta)' : 'Manual (grid)';
              $typeClass = $section['type'] === 'directory' ? 'text-bg-info' : 'text-bg-success';
              $note = $section['type'] === 'directory'
                ? 'Pasta: ' . (string)($section['directory'] ?? '')
                : 'Fotos escolhidas pela biblioteca do CMS.';
            ?>
            <div class="gallery-admin-section border rounded-3 p-3 bg-white">
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                  <h3 class="h6 fw-semibold mb-0"><?php echo htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                  <span class="badge <?php echo $typeClass; ?>"><?php echo htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="badge text-bg-light"><?php echo (int)$section['resolved_count']; ?> fotos</span>
                </div>
                <div class="small text-muted">
                  <code><?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?></code>
                  <span class="ms-2"><?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
              </div>
              <div class="gallery-admin-actions">
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>">
                  <i class="bi bi-images me-1"></i> Gerenciar
                </button>
                <form method="post" action="<?php echo $site_url; ?>/gestao-galeria" onsubmit="return confirm('Excluir esta seção da galeria?');">
                  <input type="hidden" name="action" value="delete_section">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($deleteSectionToken, ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">
                  <button class="btn btn-outline-danger btn-sm" type="submit">
                    <i class="bi bi-trash me-1"></i> Excluir
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php foreach ($sectionsForView as $section): ?>
  <?php
    $sectionId = (string)$section['id'];
    $modalId = 'gallery-admin-section-' . $sectionId;
    $items = isset($section['resolved_items']) && is_array($section['resolved_items']) ? $section['resolved_items'] : [];
    $isGrid = isset($section['type']) && $section['type'] === 'grid';
    $sectionOldSrc = $oldItemSection === $sectionId ? $oldItemSrc : '';
    $sectionOldAlt = $oldItemSection === $sectionId ? $oldItemAlt : '';
    $sectionOldCaption = $oldItemSection === $sectionId ? $oldItemCaption : '';
    $previewUrl = $sectionOldSrc !== '' ? $site_url . '/' . ltrim($sectionOldSrc, '/') : '';
    $srcFieldId = 'gallery-src-' . $sectionId;
    $previewId = 'gallery-preview-' . $sectionId;
    $labelId = 'gallery-src-label-' . $sectionId;
  ?>
  <div class="modal fade" id="<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" aria-hidden="true" data-gallery-admin-modal>
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h2 class="modal-title h5"><?php echo htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="small text-muted"><?php echo $isGrid ? 'Seção manual' : 'Seção automática por pasta'; ?> · <?php echo count($items); ?> fotos</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>-photos" role="tab">
                <i class="bi bi-images me-1"></i> Fotos
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>-settings" role="tab">
                <i class="bi bi-sliders me-1"></i> Dados da seção
              </button>
            </li>
          </ul>

          <div class="tab-content pt-4">
            <div class="tab-pane fade show active" id="<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>-photos" role="tabpanel">
              <?php if ($isGrid): ?>
                <div class="border rounded-3 p-3 mb-4 bg-light">
                  <h3 class="h6 fw-semibold">Adicionar foto</h3>
                  <?php if (empty($mediaItems)): ?>
                    <div class="alert alert-warning mb-0" role="alert">
                      Envie imagens na biblioteca do CMS antes de adicionar fotos à galeria.
                    </div>
                  <?php else: ?>
                    <form method="post" action="<?php echo $site_url; ?>/gestao-galeria" autocomplete="off">
                      <input type="hidden" name="action" value="add_item">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($addItemToken, ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" id="<?php echo htmlspecialchars($srcFieldId, ENT_QUOTES, 'UTF-8'); ?>" name="src" value="<?php echo htmlspecialchars($sectionOldSrc, ENT_QUOTES, 'UTF-8'); ?>" required>

                      <div class="row g-3 align-items-end">
                        <div class="col-lg-5">
                          <label class="form-label">Imagem</label>
                          <div class="d-flex align-items-center gap-3">
                            <img id="<?php echo htmlspecialchars($previewId, ENT_QUOTES, 'UTF-8'); ?>" class="gallery-admin-media-preview" src="<?php echo htmlspecialchars($previewUrl !== '' ? $previewUrl : $placeholder, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                            <div>
                              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#galleryMediaPickerModal" data-gallery-media-trigger data-gallery-media-field-id="<?php echo htmlspecialchars($srcFieldId, ENT_QUOTES, 'UTF-8'); ?>" data-gallery-media-preview-id="<?php echo htmlspecialchars($previewId, ENT_QUOTES, 'UTF-8'); ?>" data-gallery-media-label-id="<?php echo htmlspecialchars($labelId, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="bi bi-folder2-open me-1"></i> Escolher imagem
                              </button>
                              <div id="<?php echo htmlspecialchars($labelId, ENT_QUOTES, 'UTF-8'); ?>" class="small text-muted mt-1"><?php echo htmlspecialchars($sectionOldSrc !== '' ? $sectionOldSrc : 'Nenhuma imagem selecionada', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-3">
                          <label class="form-label" for="alt-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">Texto alternativo</label>
                          <input class="form-control" type="text" id="alt-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>" name="alt" value="<?php echo htmlspecialchars($sectionOldAlt, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-lg-3">
                          <label class="form-label" for="caption-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">Legenda</label>
                          <input class="form-control" type="text" id="caption-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>" name="caption" value="<?php echo htmlspecialchars($sectionOldCaption, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-lg-1">
                          <button class="btn btn-success w-100" type="submit">
                            <i class="bi bi-plus-circle-fill"></i>
                          </button>
                        </div>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div class="alert alert-info" role="alert">
                  As fotos desta seção são carregadas automaticamente da pasta <code><?php echo htmlspecialchars((string)($section['directory'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></code>.
                </div>
              <?php endif; ?>

              <?php if (empty($items)): ?>
                <p class="text-muted mb-0">Nenhuma foto encontrada nesta seção.</p>
              <?php else: ?>
                <div class="gallery-admin-photo-grid">
                  <?php foreach ($items as $itemIndex => $item): ?>
                    <?php
                      $src = isset($item['src']) ? (string)$item['src'] : '';
                      if ($src === '') continue;
                      $srcUrl = $site_url . '/' . ltrim($src, '/');
                      $caption = isset($item['caption']) && $item['caption'] !== '' ? (string)$item['caption'] : basename($src);
                    ?>
                    <figure class="gallery-admin-photo m-0">
                      <img src="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" data-admin-gallery-src="<?php echo htmlspecialchars($srcUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)($item['alt'] ?? $caption), ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                      <figcaption><?php echo htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?></figcaption>
                      <?php if ($isGrid): ?>
                        <form method="post" action="<?php echo $site_url; ?>/gestao-galeria" class="p-2 pt-0" onsubmit="return confirm('Remover esta foto da seção?');">
                          <input type="hidden" name="action" value="remove_item">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($removeItemToken, ENT_QUOTES, 'UTF-8'); ?>">
                          <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">
                          <input type="hidden" name="item_index" value="<?php echo (int)$itemIndex; ?>">
                          <button class="btn btn-outline-danger btn-sm w-100" type="submit">
                            <i class="bi bi-trash me-1"></i> Remover
                          </button>
                        </form>
                      <?php endif; ?>
                    </figure>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>-settings" role="tabpanel">
              <form method="post" action="<?php echo $site_url; ?>/gestao-galeria" autocomplete="off">
                <input type="hidden" name="action" value="edit_section">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($editSectionToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="row g-3">
                  <div class="col-lg-6">
                    <label class="form-label" for="title-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">Título</label>
                    <input class="form-control" type="text" id="title-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>" name="title" value="<?php echo htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label" for="background-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">Estilo de fundo</label>
                    <select class="form-select" id="background-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>" name="background">
                      <?php foreach ($backgroundOptions as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"<?php echo ((string)($section['background'] ?? '') === $value) ? ' selected' : ''; ?>>
                          <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="description-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">Descrição</label>
                    <textarea class="form-control" id="description-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>" name="description" rows="3"><?php echo htmlspecialchars((string)($section['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                  </div>
                  <?php if (!$isGrid): ?>
                    <div class="col-lg-6">
                      <label class="form-label" for="directory-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">Pasta dentro de /images</label>
                      <input class="form-control" type="text" id="directory-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>" name="directory" list="gallery-directory-options" value="<?php echo htmlspecialchars((string)($section['directory'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-lg-6">
                      <label class="form-label" for="caption-prefix-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>">Prefixo das legendas</label>
                      <input class="form-control" type="text" id="caption-prefix-<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>" name="caption_prefix" value="<?php echo htmlspecialchars((string)($section['caption_prefix'] ?? $section['title']), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                  <?php endif; ?>
                </div>

                <button class="btn btn-primary mt-3" type="submit">
                  <i class="bi bi-check2-circle me-1"></i> Salvar seção
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<div class="modal fade" id="galleryMediaPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5">Escolher imagem</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <?php if (empty($mediaGroups)): ?>
          <p class="text-muted mb-0">Nenhuma imagem disponível na biblioteca do CMS.</p>
        <?php else: ?>
          <div class="accordion" id="galleryMediaGroups">
            <?php foreach ($mediaGroups as $groupIndex => $group): ?>
              <?php
                $groupId = 'gallery-media-group-' . $groupIndex;
                $items = isset($group['items']) && is_array($group['items']) ? $group['items'] : [];
              ?>
              <div class="accordion-item">
                <h3 class="accordion-header">
                  <button class="accordion-button<?php echo $groupIndex === 0 ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo htmlspecialchars($groupId, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="<?php echo $groupIndex === 0 ? 'true' : 'false'; ?>" aria-controls="<?php echo htmlspecialchars($groupId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)($group['label'] ?? 'Imagens'), ENT_QUOTES, 'UTF-8'); ?>
                    <span class="badge text-bg-light ms-2"><?php echo count($items); ?></span>
                  </button>
                </h3>
                <div id="<?php echo htmlspecialchars($groupId, ENT_QUOTES, 'UTF-8'); ?>" class="accordion-collapse collapse<?php echo $groupIndex === 0 ? ' show' : ''; ?>" data-bs-parent="#galleryMediaGroups">
                  <div class="accordion-body">
                    <div class="cms-media-grid">
                      <?php foreach ($items as $media): ?>
                        <?php
                          $mediaPath = (string)($media['path'] ?? '');
                          if ($mediaPath === '') continue;
                          $mediaUrl = $site_url . '/' . ltrim($mediaPath, '/');
                        ?>
                        <button class="cms-media-option" type="button" data-gallery-media-select data-gallery-media-path="<?php echo htmlspecialchars($mediaPath, ENT_QUOTES, 'UTF-8'); ?>" data-gallery-media-url="<?php echo htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8'); ?>">
                          <img src="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" data-admin-gallery-src="<?php echo htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)($media['label'] ?? 'Imagem'), ENT_QUOTES, 'UTF-8'); ?>">
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
(function () {
  var placeholder = <?php echo json_encode($placeholder); ?>;
  var activeMediaField = null;
  var mediaModal = document.getElementById('galleryMediaPickerModal');

  function hydrateImages(scope) {
    if (!scope) return;
    scope.querySelectorAll('img[data-admin-gallery-src]').forEach(function (img) {
      if (!img.getAttribute('src') || img.getAttribute('src') === placeholder) {
        img.setAttribute('src', img.getAttribute('data-admin-gallery-src'));
      }
    });
  }

  function updateCreateDirectoryFields(form) {
    var type = form.querySelector('[data-gallery-section-type]');
    var fields = form.querySelectorAll('[data-gallery-directory-fields]');
    var isDirectory = type && type.value === 'directory';
    fields.forEach(function (field) {
      field.classList.toggle('d-none', !isDirectory);
      field.querySelectorAll('input').forEach(function (input) {
        input.required = isDirectory && input.name === 'directory';
      });
    });
  }

  document.querySelectorAll('[data-gallery-section-form]').forEach(function (form) {
    updateCreateDirectoryFields(form);
    var type = form.querySelector('[data-gallery-section-type]');
    if (type) {
      type.addEventListener('change', function () {
        updateCreateDirectoryFields(form);
      });
    }
  });

  document.querySelectorAll('[data-gallery-admin-modal]').forEach(function (modal) {
    modal.addEventListener('shown.bs.modal', function () {
      hydrateImages(modal);
    });
  });

  document.querySelectorAll('[data-gallery-media-trigger]').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      var input = document.getElementById(trigger.getAttribute('data-gallery-media-field-id') || '');
      var preview = document.getElementById(trigger.getAttribute('data-gallery-media-preview-id') || '');
      var label = document.getElementById(trigger.getAttribute('data-gallery-media-label-id') || '');

      activeMediaField = input ? {
        input: input,
        preview: preview,
        label: label
      } : null;
    });
  });

  if (mediaModal) {
    mediaModal.addEventListener('shown.bs.modal', function () {
      hydrateImages(mediaModal);
      var currentValue = activeMediaField && activeMediaField.input ? activeMediaField.input.value : '';
      document.querySelectorAll('[data-gallery-media-select]').forEach(function (button) {
        button.classList.toggle('active', button.getAttribute('data-gallery-media-path') === currentValue);
      });
    });
  }

  document.querySelectorAll('[data-gallery-media-select]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!activeMediaField || !activeMediaField.input) return;

      var mediaPath = button.getAttribute('data-gallery-media-path') || '';
      var mediaUrl = button.getAttribute('data-gallery-media-url') || '';

      activeMediaField.input.value = mediaPath;
      if (activeMediaField.preview && mediaUrl) {
        activeMediaField.preview.src = mediaUrl;
      }
      if (activeMediaField.label) {
        activeMediaField.label.textContent = mediaPath;
      }

      document.querySelectorAll('[data-gallery-media-select]').forEach(function (option) {
        option.classList.toggle('active', option === button);
      });

      var modal = window.bootstrap && mediaModal ? bootstrap.Modal.getInstance(mediaModal) : null;
      if (modal) modal.hide();
    });
  });
})();
</script>
