<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Controller.php';

class CmsController extends Controller
{
    public function handlePost(): void
    {
        if (!AuthService::check()) {
            AuthService::redirect('login');
        }

        if (!AuthService::userIsAdmin()) {
            AuthService::flashMessage('error', 'Apenas administradores podem gerenciar o conteúdo do site.');
            AuthService::redirect('area-restrita');
        }

        $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';

        if ($action === 'save_blocks') {
            $this->saveBlocks();
            return;
        }

        if ($action === 'add_section') {
            $this->addSection();
            return;
        }

        if ($action === 'delete_section') {
            $this->deleteSection();
            return;
        }

        if ($action === 'upload_media') {
            $this->uploadMedia();
            return;
        }

        AuthService::flashMessage('error', 'Ação do CMS desconhecida.');
        AuthService::redirect('gestao-cms');
    }

    private function saveBlocks(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!AuthService::validateCsrfToken('cms_save_blocks', $token)) {
            AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
            AuthService::redirect('gestao-cms');
        }

        $registered = CmsModel::registeredBlocks();
        $postedBlocks = isset($_POST['blocks']) && is_array($_POST['blocks']) ? $_POST['blocks'] : [];
        $updatedBy = AuthService::userUsername();
        $saved = 0;

        foreach ($registered as $pageSlug => $page) {
            $blocks = isset($page['blocks']) && is_array($page['blocks']) ? $page['blocks'] : [];
            foreach ($blocks as $key => $block) {
                $value = $postedBlocks[$pageSlug][$key] ?? $block['default'] ?? '';
                if (is_array($value)) {
                    continue;
                }

                $type = isset($block['type']) ? (string)$block['type'] : 'text';
                $label = isset($block['label']) ? (string)$block['label'] : (string)$key;

                if ($type === 'image') {
                    $value = trim((string)$value);
                    if (!CmsModel::isMediaPathAllowed($value)) {
                        $fallback = isset($block['default']) ? trim((string)$block['default']) : '';
                        $value = CmsModel::isMediaPathAllowed($fallback) ? $fallback : '';
                    }
                }

                if (CmsModel::saveBlock((string)$pageSlug, (string)$key, $type, $label, trim((string)$value), $updatedBy)) {
                    $saved++;
                }
            }
        }

        AuthService::flashMessage('success', $saved . ' bloco' . ($saved === 1 ? '' : 's') . ' de conteúdo salvo' . ($saved === 1 ? '' : 's') . ' no CMS.');
        AuthService::redirect('gestao-cms');
    }

    private function addSection(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!AuthService::validateCsrfToken('cms_add_section', $token)) {
            AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
            AuthService::redirect('gestao-cms');
        }

        $pageSlug = isset($_POST['page_slug']) ? trim((string)$_POST['page_slug']) : '';
        $title = isset($_POST['section_title']) ? trim((string)$_POST['section_title']) : '';
        $content = isset($_POST['section_content']) ? trim((string)$_POST['section_content']) : '';
        $pageOptions = CmsModel::pageOptions();

        AuthService::flashValue('cms_section_page_slug', $pageSlug);
        AuthService::flashValue('cms_section_title', $title);
        AuthService::flashValue('cms_section_content', $content);

        if ($pageSlug === '' || !array_key_exists($pageSlug, $pageOptions)) {
            AuthService::flashMessage('error', 'Escolha uma página válida para a nova seção.');
            AuthService::redirect('gestao-cms');
        }

        if ($title === '' && $content === '') {
            AuthService::flashMessage('error', 'Informe pelo menos um título ou texto para a nova seção.');
            AuthService::redirect('gestao-cms');
        }

        if (!CmsModel::addCustomSection($pageSlug, $title, $content, AuthService::userUsername())) {
            AuthService::flashMessage('error', 'Não foi possível adicionar a seção no CMS.');
            AuthService::redirect('gestao-cms');
        }

        AuthService::flashMessage('success', 'Seção adicionada ao site com sucesso.');
        AuthService::redirect('gestao-cms');
    }

    private function deleteSection(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!AuthService::validateCsrfToken('cms_delete_section', $token)) {
            AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
            AuthService::redirect('gestao-cms');
        }

        $id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
        if ($id <= 0) {
            AuthService::flashMessage('error', 'Seção inválida.');
            AuthService::redirect('gestao-cms');
        }

        if (!CmsModel::deleteCustomSection($id)) {
            AuthService::flashMessage('error', 'Não foi possível remover a seção.');
            AuthService::redirect('gestao-cms');
        }

        AuthService::flashMessage('success', 'Seção removida do site.');
        AuthService::redirect('gestao-cms');
    }

    private function uploadMedia(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!AuthService::validateCsrfToken('cms_upload_media', $token)) {
            AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
            AuthService::redirect('gestao-cms');
        }

        if (!isset($_FILES['cms_media']) || !is_array($_FILES['cms_media'])) {
            AuthService::flashMessage('error', 'Selecione uma imagem para enviar.');
            AuthService::redirect('gestao-cms');
        }

        $file = $_FILES['cms_media'];
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            AuthService::flashMessage('error', 'Não foi possível enviar a imagem. Tente novamente.');
            AuthService::redirect('gestao-cms');
        }

        $tmpName = isset($file['tmp_name']) ? (string)$file['tmp_name'] : '';
        $originalName = isset($file['name']) ? (string)$file['name'] : 'imagem';
        $size = isset($file['size']) ? (int)$file['size'] : 0;

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            AuthService::flashMessage('error', 'Upload inválido.');
            AuthService::redirect('gestao-cms');
        }

        if ($size <= 0 || $size > 8 * 1024 * 1024) {
            AuthService::flashMessage('error', 'Envie uma imagem de até 8 MB.');
            AuthService::redirect('gestao-cms');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName) ?: '';
        $extensionByMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($extensionByMime[$mime])) {
            AuthService::flashMessage('error', 'Formato inválido. Use JPG, PNG, WebP ou GIF.');
            AuthService::redirect('gestao-cms');
        }

        if (!CmsModel::ensureMediaDirectory()) {
            AuthService::flashMessage('error', 'Não foi possível preparar a pasta de uploads do CMS.');
            AuthService::redirect('gestao-cms');
        }

        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        if (function_exists('iconv')) {
            $baseName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $baseName) ?: $baseName;
        }
        $baseName = strtolower($baseName);
        $baseName = preg_replace('/[^a-z0-9]+/', '-', $baseName) ?: 'imagem';
        $baseName = trim($baseName, '-');
        $extension = $extensionByMime[$mime];
        $filename = date('Ymd-His') . '-' . substr(bin2hex(random_bytes(6)), 0, 12) . '-' . $baseName . '.' . $extension;
        $destination = rtrim(CmsModel::mediaDirectory(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            AuthService::flashMessage('error', 'Não foi possível salvar a imagem enviada.');
            AuthService::redirect('gestao-cms');
        }

        @chmod($destination, 0664);
        AuthService::flashMessage('success', 'Imagem enviada para a biblioteca do CMS.');
        AuthService::redirect('gestao-cms');
    }
}
