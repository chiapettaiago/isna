<?php
if (!auth_user_is_admin()) {
    auth_flash_message('error', 'Apenas administradores podem gerenciar o conteúdo do site.');
    auth_redirect('area-restrita');
}

$pages = CmsModel::registeredBlocks();
$pageOptions = CmsModel::pageOptions();
$customSections = CmsModel::customSections();
$token = AuthService::generateCsrfToken('cms_save_blocks');
$addSectionToken = AuthService::generateCsrfToken('cms_add_section');
$deleteSectionToken = AuthService::generateCsrfToken('cms_delete_section');
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
                    <div class="col-12<?php echo in_array($type, ['textarea', 'html'], true) ? '' : ' col-lg-6'; ?>">
                      <label class="form-label fw-semibold" for="<?php echo htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($block['label'], ENT_QUOTES, 'UTF-8'); ?>
                      </label>
                      <?php if (in_array($type, ['textarea', 'html'], true)): ?>
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
