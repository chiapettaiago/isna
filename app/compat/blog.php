<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/BlogModel.php';

function blog_config_path(): string { return BlogModel::configPath(); }
function blog_default_config(): array { return BlogModel::defaultConfig(); }
function blog_load(): array { return BlogModel::load(); }
function blog_sanitize_config(array $config): array { return BlogModel::sanitizeConfig($config); }
function blog_slug(string $text): string { return BlogModel::slug($text); }
function blog_render_config(array $config): string { return BlogModel::renderConfig($config); }
function blog_save(array $config): bool { return BlogModel::save($config); }
