<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Controller.php';

class BlogController extends Controller
{
    public function handlePost(): void
    {
        if (!AuthService::check()) {
            AuthService::redirect('login');
        }

        if (!AuthService::userIsAdmin()) {
            AuthService::flashMessage('error', 'Apenas administradores podem gerenciar o blog.');
            AuthService::redirect('area-restrita');
        }

        $action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

        if ($action === 'create_post') {
            $token = $_POST['csrf_token'] ?? '';

            if (!AuthService::validateCsrfToken('blog_create_post', $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('gestao-blog');
            }

            $title = isset($_POST['title']) ? trim((string) $_POST['title']) : '';
            $summary = isset($_POST['summary']) ? trim((string) $_POST['summary']) : '';
            $content = isset($_POST['content']) ? trim((string) $_POST['content']) : '';
            $author = isset($_POST['author']) ? trim((string) $_POST['author']) : '';

            AuthService::flashValue('blog_post_title', $title);
            AuthService::flashValue('blog_post_summary', $summary);
            AuthService::flashValue('blog_post_content', $content);
            AuthService::flashValue('blog_post_author', $author);

            if ($title === '' || $content === '') {
                auth_flash_message('error', 'Informe pelo menos o título e o conteúdo do post.');
                auth_redirect('gestao-blog');
            }

            $config = BlogModel::load();
            $posts = $config['posts'];

            $baseSlug = BlogModel::slug($title);
            $slug = $baseSlug;
            $suffix = 1;

            $existingIds = array_map(static function ($post) { return $post['id']; }, $posts);

            while (in_array($slug, $existingIds, true)) {
                $suffix++;
                $slug = $baseSlug . '-' . $suffix;
            }

            $posts[] = [
                'id' => $slug,
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'author' => $author !== '' ? $author : 'Equipe ISNA',
                'published_at' => date('c'),
            ];

            $savePayload = ['posts' => $posts];

            if (!BlogModel::save($savePayload)) {
                AuthService::flashMessage('error', 'Não foi possível salvar o post. Verifique as permissões do arquivo.');
                AuthService::redirect('gestao-blog');
            }

            AuthService::flashMessage('success', 'Post publicado com sucesso!');
            AuthService::redirect('gestao-blog');
        } else {
            AuthService::flashMessage('error', 'Ação do blog desconhecida.');
            AuthService::redirect('gestao-blog');
        }
    }
}
