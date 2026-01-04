<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/GalleryModel.php';

function gallery_config_path(): string { return GalleryModel::configPath(); }
function gallery_default_config(): array { return GalleryModel::defaultConfig(); }
function gallery_load(): array { return GalleryModel::load(); }
function gallery_sanitize_config(array $config): array { return GalleryModel::sanitizeConfig($config); }
function gallery_slug(string $text): string { return GalleryModel::slug($text); }
function gallery_render_config(array $config): string { return GalleryModel::renderConfig($config); }
function gallery_save(array $config): bool { return GalleryModel::save($config); }
