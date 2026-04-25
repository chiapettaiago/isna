<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AuthService.php';

class CmsModel
{
    private static ?array $cache = null;

    public static function configPath(): string
    {
        return __DIR__ . '/../../config/cms.php';
    }

    public static function config(): array
    {
        $path = self::configPath();
        if (!is_readable($path)) {
            return ['pages' => []];
        }

        $config = include $path;
        return is_array($config) ? $config : ['pages' => []];
    }

    public static function tableName(): string
    {
        $cfg = AuthService::dbConfig() ?? [];
        return (string)($cfg['cms_table'] ?? 'cms_blocks');
    }

    public static function sectionsTableName(): string
    {
        $cfg = AuthService::dbConfig() ?? [];
        return (string)($cfg['cms_sections_table'] ?? 'cms_sections');
    }

    public static function ensureTable(): bool
    {
        $pdo = AuthService::getPdo();
        if (!$pdo) return false;

        try {
            $table = AuthService::quoteIdentifier(self::tableName());
            $pdo->exec("
CREATE TABLE IF NOT EXISTS {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_slug VARCHAR(191) NOT NULL,
  block_key VARCHAR(191) NOT NULL,
  block_type VARCHAR(32) NOT NULL DEFAULT 'text',
  label VARCHAR(255) NOT NULL,
  value MEDIUMTEXT NULL,
  updated_by VARCHAR(191) NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_page_block (page_slug, block_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function ensureSectionsTable(): bool
    {
        $pdo = AuthService::getPdo();
        if (!$pdo) return false;

        try {
            $table = AuthService::quoteIdentifier(self::sectionsTableName());
            $pdo->exec("
CREATE TABLE IF NOT EXISTS {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_slug VARCHAR(191) NOT NULL,
  title VARCHAR(255) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  position INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  updated_by VARCHAR(191) NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cms_sections_page_position (page_slug, is_active, position, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function allValues(bool $refresh = false): array
    {
        if (self::$cache !== null && !$refresh) {
            return self::$cache;
        }

        self::$cache = [];
        $pdo = AuthService::getPdo();
        if (!$pdo || !self::ensureTable()) {
            return self::$cache;
        }

        try {
            $table = AuthService::quoteIdentifier(self::tableName());
            $stmt = $pdo->query("SELECT page_slug, block_key, value FROM {$table}");
            foreach ($stmt->fetchAll() as $row) {
                if (!is_array($row)) continue;
                $page = isset($row['page_slug']) ? (string)$row['page_slug'] : '';
                $key = isset($row['block_key']) ? (string)$row['block_key'] : '';
                if ($page === '' || $key === '') continue;
                self::$cache[$page][$key] = isset($row['value']) ? (string)$row['value'] : '';
            }
        } catch (Exception $e) {
            self::$cache = [];
        }

        return self::$cache;
    }

    public static function get(string $page, string $key, string $default = ''): string
    {
        $values = self::allValues();
        if (array_key_exists($page, $values) && array_key_exists($key, $values[$page])) {
            return $values[$page][$key];
        }

        return $default;
    }

    public static function saveBlock(string $page, string $key, string $type, string $label, string $value, ?string $updatedBy = null): bool
    {
        $pdo = AuthService::getPdo();
        if (!$pdo || !self::ensureTable()) return false;

        try {
            $table = AuthService::quoteIdentifier(self::tableName());
            $stmt = $pdo->prepare("
INSERT INTO {$table} (page_slug, block_key, block_type, label, value, updated_by)
VALUES (:page, :block_key, :block_type, :label, :value, :updated_by)
ON DUPLICATE KEY UPDATE
  block_type = VALUES(block_type),
  label = VALUES(label),
  value = VALUES(value),
  updated_by = VALUES(updated_by),
  updated_at = CURRENT_TIMESTAMP
");
            $ok = $stmt->execute([
                ':page' => $page,
                ':block_key' => $key,
                ':block_type' => $type,
                ':label' => $label,
                ':value' => $value,
                ':updated_by' => $updatedBy,
            ]);
            self::allValues(true);
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function registeredBlocks(): array
    {
        $config = self::config();
        $pages = isset($config['pages']) && is_array($config['pages']) ? $config['pages'] : [];
        $values = self::allValues();
        $out = [];

        foreach ($pages as $pageSlug => $pageConfig) {
            if (!is_array($pageConfig)) continue;
            $blocks = isset($pageConfig['blocks']) && is_array($pageConfig['blocks']) ? $pageConfig['blocks'] : [];
            $out[$pageSlug] = [
                'title' => isset($pageConfig['title']) ? (string)$pageConfig['title'] : (string)$pageSlug,
                'blocks' => [],
            ];

            foreach ($blocks as $key => $block) {
                if (!is_array($block)) continue;
                $default = isset($block['default']) ? (string)$block['default'] : '';
                $out[$pageSlug]['blocks'][$key] = [
                    'label' => isset($block['label']) ? (string)$block['label'] : (string)$key,
                    'type' => isset($block['type']) ? (string)$block['type'] : 'text',
                    'default' => $default,
                    'value' => $values[$pageSlug][$key] ?? $default,
                ];
            }
        }

        return $out;
    }

    public static function pageOptions(): array
    {
        $config = self::config();
        $pages = isset($config['pages']) && is_array($config['pages']) ? $config['pages'] : [];
        $out = [];

        foreach ($pages as $pageSlug => $pageConfig) {
            if ($pageSlug === 'global' || !is_array($pageConfig)) continue;
            $out[(string)$pageSlug] = isset($pageConfig['title']) ? (string)$pageConfig['title'] : (string)$pageSlug;
        }

        return $out;
    }

    public static function pageSlugFromView(string $file): string
    {
        $rel = trim(str_replace('\\', '/', $file), '/');
        $base = basename($rel);
        $slug = preg_replace('/\.php$/i', '', $base) ?: $base;
        $map = [
            'home' => 'home',
            'bank-donations' => 'doacoes-bancarias',
        ];

        return $map[$slug] ?? $slug;
    }

    public static function customSections(?string $pageSlug = null, bool $activeOnly = false): array
    {
        $pdo = AuthService::getPdo();
        if (!$pdo || !self::ensureSectionsTable()) return [];

        try {
            $table = AuthService::quoteIdentifier(self::sectionsTableName());
            $where = [];
            $params = [];

            if ($pageSlug !== null && $pageSlug !== '') {
                $where[] = 'page_slug = :page';
                $params[':page'] = $pageSlug;
            }

            if ($activeOnly) {
                $where[] = 'is_active = 1';
            }

            $sql = "SELECT id, page_slug, title, content, position, is_active, updated_by, updated_at, created_at FROM {$table}";
            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY page_slug ASC, position ASC, id ASC';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public static function addCustomSection(string $pageSlug, string $title, string $content, ?string $updatedBy = null): bool
    {
        $pdo = AuthService::getPdo();
        if (!$pdo || !self::ensureSectionsTable()) return false;

        try {
            $table = AuthService::quoteIdentifier(self::sectionsTableName());
            $positionStmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) + 10 FROM {$table} WHERE page_slug = :page");
            $positionStmt->execute([':page' => $pageSlug]);
            $position = (int)$positionStmt->fetchColumn();

            $stmt = $pdo->prepare("
INSERT INTO {$table} (page_slug, title, content, position, is_active, updated_by)
VALUES (:page, :title, :content, :position, 1, :updated_by)
");
            return $stmt->execute([
                ':page' => $pageSlug,
                ':title' => $title,
                ':content' => $content,
                ':position' => $position,
                ':updated_by' => $updatedBy,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function deleteCustomSection(int $id): bool
    {
        $pdo = AuthService::getPdo();
        if (!$pdo || !self::ensureSectionsTable()) return false;

        try {
            $table = AuthService::quoteIdentifier(self::sectionsTableName());
            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = :id LIMIT 1");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }
}
