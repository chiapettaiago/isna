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
}
