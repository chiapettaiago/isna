<?php
$cmsPageSlug = isset($cms_page_slug) ? (string)$cms_page_slug : '';
$cmsPage = $cmsPageSlug !== '' ? CmsModel::customPageBySlug($cmsPageSlug, true) : null;

if (!is_array($cmsPage)) {
  http_response_code(404);
  echo '<div class="container py-5 text-center">';
  echo '<h1 class="display-1">404</h1>';
  echo '<h2>Página Não Encontrada</h2>';
  echo '</div>';
  return;
}

$cmsPageTitle = (string)($cmsPage['title'] ?? 'Página');
$cmsPageContent = (string)($cmsPage['content'] ?? '');
?>

<section class="dynamic-page-hero py-5 bg-light">
  <div class="container">
    <h1 class="display-5 fw-semibold mb-0"><?php echo htmlspecialchars($cmsPageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
  </div>
</section>

<?php if ($cmsPageContent !== ''): ?>
  <section class="dynamic-page-content py-5">
    <div class="container">
      <article class="dynamic-page-article">
        <?php echo nl2br(htmlspecialchars($cmsPageContent, ENT_QUOTES, 'UTF-8')); ?>
      </article>
    </div>
  </section>
<?php endif; ?>

<?php
$dynamicSections = CmsModel::customSections($cmsPageSlug, true);
if (!empty($dynamicSections)):
?>
  <section class="cms-extra-sections py-5">
    <div class="container">
      <?php foreach ($dynamicSections as $section): ?>
        <?php
          $sectionTitle = isset($section['title']) ? (string)$section['title'] : '';
          $sectionContent = isset($section['content']) ? (string)$section['content'] : '';
          if ($sectionTitle === '' && $sectionContent === '') continue;
        ?>
        <article class="cms-extra-section mb-5">
          <?php if ($sectionTitle !== ''): ?>
            <h2 class="cms-extra-section-title h3 fw-bold mb-3"><?php echo htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
          <?php endif; ?>
          <?php if ($sectionContent !== ''): ?>
            <div class="cms-extra-section-content"><?php echo nl2br(htmlspecialchars($sectionContent, ENT_QUOTES, 'UTF-8')); ?></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>
