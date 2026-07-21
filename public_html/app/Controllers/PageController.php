<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

final class PageController extends Controller
{
    public function about(Request $request): void
    {
        $this->view('about', [
            'page'        => 'about',
            'title'       => 'About — iTrend Solution',
            'description' => 'A global product-technology company with 9+ years of expertise, 60+ specialists, and operations across 7+ countries.',
        ]);
    }

    public function careers(Request $request): void
    {
        $this->view('careers', [
            'page'        => 'careers',
            'title'       => 'Careers — iTrend Solution',
            'description' => 'Build the future of commerce technology with iTrend. Explore open roles across engineering, product, design and growth.',
            'jobs'        => self::jobs(),
        ]);
    }

    public function contact(Request $request): void
    {
        $this->view('contact', [
            'page'        => 'contact',
            'title'       => 'Contact — iTrend Solution',
            'description' => 'Talk to iTrend Solution about products, partnerships, demos and global opportunities.',
        ]);
    }

    public function privacy(Request $request): void
    {
        $this->view('legal', [
            'page'        => 'legal',
            'title'       => 'Privacy Policy — iTrend Solution',
            'description' => 'How iTrend Solution collects, uses, and protects your information.',
            'heading'     => 'Privacy Policy',
            'updated'     => 'June 2026',
            'doc'         => 'privacy',
        ]);
    }

    public function terms(Request $request): void
    {
        $this->view('legal', [
            'page'        => 'legal',
            'title'       => 'Terms of Service — iTrend Solution',
            'description' => 'The terms that govern your use of the iTrend Solution website.',
            'heading'     => 'Terms of Service',
            'updated'     => 'June 2026',
            'doc'         => 'terms',
        ]);
    }

    public function health(Request $request): void
    {
        $storageWritable = is_writable(STORAGE_PATH . '/cache') && is_writable(STORAGE_PATH . '/logs');
        $secretsFound    = defined('SECRETS_FILE') && SECRETS_FILE !== '';

        $checks = [
            'php'     => PHP_VERSION,
            'mail'    => \App\Core\Mailer::enabled() ? 'configured' : 'not-configured',
            'storage' => $storageWritable ? 'writable' : 'not-writable',
            'secrets' => $secretsFound ? 'found' : 'none',
        ];
        // "ok" only when the things that would actually break delivery are in order.
        $ok = $storageWritable && \App\Core\Mailer::enabled();

        $this->json([
            'status' => $ok ? 'ok' : 'check-config',
            'checks' => $checks,
            'time'   => date('c'),
        ]);
    }

    public function notFound(Request $request): void
    {
        $this->error(404, 'Page not found', "The page you're looking for doesn't exist or has moved.");
    }

    public function serverError(Request $request, ?string $detail = null): void
    {
        $this->error(500, 'Something went wrong', 'An unexpected error occurred on our side. Please try again shortly.', $detail);
    }

    /** Generic branded error page (used by router + Apache ErrorDocument). */
    public function error(int $code, string $heading, string $message, ?string $detail = null): void
    {
        if (!headers_sent()) {
            http_response_code($code);
        }
        $this->view('error', [
            'page'        => 'error',
            'title'       => $code . ' — ' . $heading,
            'description' => $heading,
            'code'        => $code,
            'heading'     => $heading,
            'message'     => $message,
            'detail'      => $detail,
        ]);
    }

    /** @return list<array<string,string>> */
    private static function jobs(): array
    {
        return [
            ['title' => 'Full Stack Developer', 'dept' => 'IT & Software', 'location' => 'Chennai / Remote', 'type' => 'Full-time', 'exp' => '2+ yrs'],
            ['title' => 'Data Analyst', 'dept' => 'IT & Software', 'location' => 'Chennai', 'type' => 'Full-time', 'exp' => '1-3 yrs'],
            ['title' => 'Cataloguing Executive', 'dept' => 'Cataloguing', 'location' => 'Chennai', 'type' => 'Full-time', 'exp' => '1-3 yrs'],
            ['title' => 'Graphic Designer', 'dept' => 'Graphic Design', 'location' => 'Chennai', 'type' => 'Full-time', 'exp' => '2+ yrs'],
            ['title' => 'Senior PPC / Performance Marketing Specialist', 'dept' => 'Digital Marketing', 'location' => 'Chennai', 'type' => 'Full-time', 'exp' => '3+ yrs'],
            ['title' => 'Supply Chain & Logistics Analyst', 'dept' => 'SCM & Logistics', 'location' => 'Chennai', 'type' => 'Full-time', 'exp' => '2+ yrs'],
            ['title' => 'Accounts & Finance Executive', 'dept' => 'Accounts', 'location' => 'Chennai', 'type' => 'Full-time', 'exp' => '1-3 yrs'],
        ];
    }
}
