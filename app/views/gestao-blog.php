<?php
if (!auth_user_is_admin()) {
    auth_flash_message('error', 'Apenas administradores podem gerenciar o blog.');
    auth_redirect('area-restrita');
}

$blogConfig = blog_load();
$posts = $blogConfig['posts'];

$createPostToken = auth_generate_csrf_token('blog_create_post');

$oldTitle = auth_flash_pull_value('blog_post_title', '');
$oldSummary = auth_flash_pull_value('blog_post_summary', '');
$oldContent = auth_flash_pull_value('blog_post_content', '');
$oldAuthor = auth_flash_pull_value('blog_post_author', '');
?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4">
      <div>
        <h1 class="display-6 fw-semibold mb-2">Gestão do Blog</h1>
        <p class="lead mb-0">Divulgue novidades e histórias do instituto publicando artigos no blog.</p>
      </div>
      <a class="btn btn-outline-secondary mt-3 mt-lg-0" href="<?php echo $site_url; ?>/" target="_blank" rel="noreferrer">
        <i class="bi bi-box-arrow-up-right me-1"></i> Ver página inicial
      </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <h2 class="h5 fw-semibold">Publicar novo post</h2>
        <p class="text-muted">Preencha os campos abaixo e clique em publicar para adicionar o conteúdo ao blog.</p>
        <form method="post" action="<?php echo $site_url; ?>/gestao-blog" autocomplete="off">
          <input type="hidden" name="action" value="create_post">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($createPostToken, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="mb-3">
            <label class="form-label" for="post_title">Título</label>
            <input
              class="form-control"
              type="text"
              id="post_title"
              name="title"
              value="<?php echo htmlspecialchars($oldTitle, ENT_QUOTES, 'UTF-8'); ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label class="form-label" for="post_author">Autor (opcional)</label>
            <input
              class="form-control"
              type="text"
              id="post_author"
              name="author"
              value="<?php echo htmlspecialchars($oldAuthor, ENT_QUOTES, 'UTF-8'); ?>"
              placeholder="Nome de quem assina a publicação"
            >
          </div>

          <div class="mb-3">
            <label class="form-label" for="post_summary">Resumo (opcional)</label>
            <textarea
              class="form-control"
              id="post_summary"
              name="summary"
              rows="3"
              maxlength="600"
              placeholder="Breve descrição que aparecerá na página inicial"
            ><?php echo htmlspecialchars($oldSummary, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="form-text">Limite sugerido: até 600 caracteres.</div>
          </div>

          <div class="mb-4">
            <label class="form-label" for="post_content">Conteúdo</label>
            <textarea
              class="form-control"
              id="post_content"
              name="content"
              rows="10"
              required
              placeholder="Digite o texto completo do artigo"
            ><?php echo htmlspecialchars($oldContent, ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <button class="btn btn-primary" type="submit">
            <i class="bi bi-journal-plus me-1"></i> Publicar post
          </button>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h5 fw-semibold mb-3">Posts publicados</h2>
        <?php if (empty($posts)): ?>
          <p class="text-muted mb-0">Nenhum post cadastrado até o momento. Publique o primeiro usando o formulário acima.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th scope="col">Título</th>
                  <th scope="col">Autor</th>
                  <th scope="col">Data</th>
                  <th scope="col">Resumo</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($posts as $post): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($post['published_at'])); ?></td>
                    <td><?php echo htmlspecialchars($post['summary'], ENT_QUOTES, 'UTF-8'); ?></td>
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
