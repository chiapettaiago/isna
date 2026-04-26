<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Controller.php';

class GalleryController extends Controller
{
    public function handlePost(): void
    {
        if (!AuthService::check()) {
            AuthService::redirect('login');
        }

        if (!AuthService::userIsAdmin()) {
            AuthService::flashMessage('error', 'Apenas administradores podem alterar a galeria.');
            AuthService::redirect('area-restrita');
        }

        $action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

        if ($action === 'create_section') {
            $token = $_POST['csrf_token'] ?? '';

            if (!AuthService::validateCsrfToken('gallery_create_section', $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('gestao-galeria');
            }

            $title = isset($_POST['title']) ? trim((string) $_POST['title']) : '';
            $type = isset($_POST['type']) ? trim((string) $_POST['type']) : 'grid';
            $background = isset($_POST['background']) ? trim((string) $_POST['background']) : '';
            $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';
            $directory = isset($_POST['directory']) ? trim((string) $_POST['directory']) : '';
            $captionPrefix = isset($_POST['caption_prefix']) ? trim((string) $_POST['caption_prefix']) : '';

            AuthService::flashValue('gallery_create_title', $title);
            AuthService::flashValue('gallery_create_type', $type);
            AuthService::flashValue('gallery_create_background', $background);
            AuthService::flashValue('gallery_create_description', $description);
            AuthService::flashValue('gallery_create_directory', $directory);
            AuthService::flashValue('gallery_create_caption_prefix', $captionPrefix);

            if ($title === '') {
                auth_flash_message('error', 'Informe um título para a nova seção.');
                auth_redirect('gestao-galeria');
            }

            if (!in_array($type, ['grid', 'directory'], true)) {
                $type = 'grid';
            }

            $allowedBackgrounds = ['', 'bg-light', 'bg-white'];
            if (!in_array($background, $allowedBackgrounds, true)) {
                $background = '';
            }

            if ($type === 'directory' && !GalleryModel::isDirectoryPathAllowed($directory)) {
                AuthService::flashMessage('error', 'Informe uma pasta de imagens válida dentro de /images para a seção automática.');
                AuthService::redirect('gestao-galeria');
            }

            $config = GalleryModel::load();
            $existingIds = array_map(static function ($section) { return $section['id']; }, $config['sections']);

            $baseSlug = GalleryModel::slug($title);
            $slug = $baseSlug;
            $suffix = 1;
            while (in_array($slug, $existingIds, true)) {
                $suffix++;
                $slug = $baseSlug . '-' . $suffix;
            }

            $config['sections'][] = [
                'id' => $slug,
                'title' => $title,
                'type' => $type,
                'background' => $background,
                'description' => $description,
            ];

            if ($type === 'directory') {
                $config['sections'][array_key_last($config['sections'])]['directory'] = trim(str_replace('\\', '/', $directory), '/');
                $config['sections'][array_key_last($config['sections'])]['caption_prefix'] = $captionPrefix !== '' ? $captionPrefix : $title;
            } else {
                $config['sections'][array_key_last($config['sections'])]['items'] = [];
            }

            if (!GalleryModel::save($config)) {
                AuthService::flashMessage('error', 'Não foi possível salvar a nova seção. Verifique as permissões do arquivo.');
                AuthService::redirect('gestao-galeria');
            }

            AuthService::flashMessage('success', 'Seção criada com sucesso. Agora adicione itens para populá-la.');
            AuthService::redirect('gestao-galeria');
        } elseif ($action === 'edit_section') {
            $token = $_POST['csrf_token'] ?? '';

            if (!AuthService::validateCsrfToken('gallery_edit_section', $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('gestao-galeria');
            }

            $sectionId = isset($_POST['section_id']) ? trim((string) $_POST['section_id']) : '';
            $title = isset($_POST['title']) ? trim((string) $_POST['title']) : '';
            $background = isset($_POST['background']) ? trim((string) $_POST['background']) : '';
            $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';
            $directory = isset($_POST['directory']) ? trim((string) $_POST['directory']) : '';
            $captionPrefix = isset($_POST['caption_prefix']) ? trim((string) $_POST['caption_prefix']) : '';

            if ($sectionId === '' || $title === '') {
                AuthService::flashMessage('error', 'Informe a seção e o título para salvar.');
                AuthService::redirect('gestao-galeria');
            }

            $allowedBackgrounds = ['', 'bg-light', 'bg-white'];
            if (!in_array($background, $allowedBackgrounds, true)) {
                $background = '';
            }

            $config = GalleryModel::load();
            $found = false;

            foreach ($config['sections'] as &$section) {
                if ($section['id'] !== $sectionId) {
                    continue;
                }

                $found = true;
                $section['title'] = $title;
                $section['background'] = $background;
                $section['description'] = $description;

                if ($section['type'] === 'directory') {
                    if (!GalleryModel::isDirectoryPathAllowed($directory)) {
                        AuthService::flashMessage('error', 'Informe uma pasta de imagens válida dentro de /images.');
                        AuthService::redirect('gestao-galeria');
                    }

                    $section['directory'] = trim(str_replace('\\', '/', $directory), '/');
                    $section['caption_prefix'] = $captionPrefix !== '' ? $captionPrefix : $title;
                }

                break;
            }
            unset($section);

            if (!$found) {
                AuthService::flashMessage('error', 'Seção selecionada não encontrada.');
                AuthService::redirect('gestao-galeria');
            }

            if (!GalleryModel::save($config)) {
                AuthService::flashMessage('error', 'Não foi possível salvar a seção. Verifique as permissões do arquivo.');
                AuthService::redirect('gestao-galeria');
            }

            AuthService::flashMessage('success', 'Seção atualizada com sucesso.');
            AuthService::redirect('gestao-galeria');
        } elseif ($action === 'delete_section') {
            $token = $_POST['csrf_token'] ?? '';

            if (!AuthService::validateCsrfToken('gallery_delete_section', $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('gestao-galeria');
            }

            $sectionId = isset($_POST['section_id']) ? trim((string) $_POST['section_id']) : '';
            if ($sectionId === '') {
                AuthService::flashMessage('error', 'Seção não informada.');
                AuthService::redirect('gestao-galeria');
            }

            $config = GalleryModel::load();
            if (count($config['sections']) <= 1) {
                AuthService::flashMessage('error', 'A galeria precisa manter pelo menos uma seção.');
                AuthService::redirect('gestao-galeria');
            }

            $before = count($config['sections']);
            $config['sections'] = array_values(array_filter($config['sections'], static function ($section) use ($sectionId) {
                return isset($section['id']) && $section['id'] !== $sectionId;
            }));

            if (count($config['sections']) === $before) {
                AuthService::flashMessage('error', 'Seção selecionada não encontrada.');
                AuthService::redirect('gestao-galeria');
            }

            if (!GalleryModel::save($config)) {
                AuthService::flashMessage('error', 'Não foi possível excluir a seção. Verifique as permissões do arquivo.');
                AuthService::redirect('gestao-galeria');
            }

            AuthService::flashMessage('success', 'Seção excluída com sucesso.');
            AuthService::redirect('gestao-galeria');
        } elseif ($action === 'add_item') {
            $token = $_POST['csrf_token'] ?? '';

            if (!AuthService::validateCsrfToken('gallery_add_item', $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('gestao-galeria');
            }

            $sectionId = isset($_POST['section_id']) ? trim((string) $_POST['section_id']) : '';
            $src = isset($_POST['src']) ? trim((string) $_POST['src']) : '';
            $alt = isset($_POST['alt']) ? trim((string) $_POST['alt']) : '';
            $caption = isset($_POST['caption']) ? trim((string) $_POST['caption']) : '';

            AuthService::flashValue('gallery_item_section', $sectionId);
            AuthService::flashValue('gallery_item_src', $src);
            AuthService::flashValue('gallery_item_alt', $alt);
            AuthService::flashValue('gallery_item_caption', $caption);

            if ($sectionId === '') {
                auth_flash_message('error', 'Escolha uma seção para adicionar o item.');
                auth_redirect('gestao-galeria');
            }

            if ($src === '') {
                auth_flash_message('error', 'Escolha uma imagem da biblioteca.');
                auth_redirect('gestao-galeria');
            }

            if (!CmsModel::isMediaPathAllowed($src)) {
                auth_flash_message('error', 'A imagem selecionada não está disponível na biblioteca do CMS.');
                auth_redirect('gestao-galeria');
            }

            $config = GalleryModel::load();
            $found = false;

            foreach ($config['sections'] as &$section) {
                if ($section['id'] !== $sectionId) continue;
                $found = true;
                if ($section['type'] !== 'grid') {
                    auth_flash_message('error', 'Não é possível adicionar itens diretamente em seções automáticas.');
                    auth_redirect('gestao-galeria');
                }
                if (!isset($section['items']) || !is_array($section['items'])) $section['items'] = [];
                $section['items'][] = [
                    'type' => 'image',
                    'src' => $src,
                    'alt' => $alt !== '' ? $alt : $section['title'],
                    'caption' => $caption,
                ];
                break;
            }
            unset($section);

            if (!$found) {
                auth_flash_message('error', 'Seção selecionada não encontrada.');
                auth_redirect('gestao-galeria');
            }

            if (!GalleryModel::save($config)) {
                AuthService::flashMessage('error', 'Não foi possível salvar o item. Verifique as permissões do arquivo.');
                AuthService::redirect('gestao-galeria');
            }

            AuthService::flashMessage('success', 'Item adicionado à galeria com sucesso.');
            AuthService::redirect('gestao-galeria');
        } elseif ($action === 'remove_item') {
            $token = $_POST['csrf_token'] ?? '';

            if (!AuthService::validateCsrfToken('gallery_remove_item', $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('gestao-galeria');
            }

            $sectionId = isset($_POST['section_id']) ? trim((string) $_POST['section_id']) : '';
            $itemIndex = isset($_POST['item_index']) ? (int) $_POST['item_index'] : -1;

            if ($sectionId === '' || $itemIndex < 0) {
                AuthService::flashMessage('error', 'Item não informado.');
                AuthService::redirect('gestao-galeria');
            }

            $config = GalleryModel::load();
            $found = false;

            foreach ($config['sections'] as &$section) {
                if ($section['id'] !== $sectionId) continue;
                $found = true;

                if ($section['type'] !== 'grid') {
                    AuthService::flashMessage('error', 'Fotos de seções automáticas são gerenciadas pela pasta configurada.');
                    AuthService::redirect('gestao-galeria');
                }

                if (!isset($section['items'][$itemIndex])) {
                    AuthService::flashMessage('error', 'Foto selecionada não encontrada.');
                    AuthService::redirect('gestao-galeria');
                }

                array_splice($section['items'], $itemIndex, 1);
                break;
            }
            unset($section);

            if (!$found) {
                AuthService::flashMessage('error', 'Seção selecionada não encontrada.');
                AuthService::redirect('gestao-galeria');
            }

            if (!GalleryModel::save($config)) {
                AuthService::flashMessage('error', 'Não foi possível remover a foto. Verifique as permissões do arquivo.');
                AuthService::redirect('gestao-galeria');
            }

            AuthService::flashMessage('success', 'Foto removida da galeria com sucesso.');
            AuthService::redirect('gestao-galeria');
        } else {
            AuthService::flashMessage('error', 'Ação da galeria desconhecida.');
            AuthService::redirect('gestao-galeria');
        }
    }
}
