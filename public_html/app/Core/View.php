<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Plain-PHP templating. A page template is rendered into a layout via the
 * $content slot. Templates live in app/Views/pages, layouts in app/Views/layouts,
 * shared fragments in app/Views/partials.
 */
final class View
{
    public static function render(string $template, array $data = [], string $layout = 'main'): string
    {
        $content = self::renderFile(VIEW_PATH . '/pages/' . $template . '.php', $data);

        if ($layout === '' || $layout === 'none') {
            return $content;
        }

        return self::renderFile(
            VIEW_PATH . '/layouts/' . $layout . '.php',
            array_merge($data, ['content' => $content])
        );
    }

    /** Render a shared partial (used from inside layouts/pages). */
    public static function partial(string $name, array $data = []): string
    {
        return self::renderFile(VIEW_PATH . '/partials/' . $name . '.php', $data);
    }

    private static function renderFile(string $path, array $data): string
    {
        if (!is_file($path)) {
            throw new \RuntimeException("View not found: $path");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
