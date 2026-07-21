<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Validator;

/**
 * Handles all public form posts with a uniform security pipeline:
 * CSRF → honeypot → rate-limit → validation → (upload) → email.
 * This is the public landing site: submissions are emailed to HR only — no database.
 */
final class FormController extends Controller
{
    private const RATE_MAX = 5;          // submissions...
    private const RATE_WINDOW = 600;     // ...per 10 minutes per IP

    private const ALLOWED_UPLOAD_EXT = ['pdf', 'doc', 'docx'];
    private const ALLOWED_UPLOAD_MIME = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    private const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

    public function contact(Request $request): void
    {
        $this->process($request, 'contact', [
            'name'    => 'required|max:160',
            'email'   => 'required|email|max:190',
            'company' => 'max:190',
            'service' => 'max:120',
            'message' => 'max:5000',
        ], static fn (Request $r): array => [
            'name'    => $r->input('name'),
            'email'   => $r->input('email'),
            'company' => $r->input('company'),
            'subject' => $r->input('service'),
            'message' => $r->input('message'),
        ]);
    }

    public function quote(Request $request): void
    {
        $this->process($request, 'quote', [
            'name'    => 'required|max:160',
            'email'   => 'required|email|max:190',
            'service' => 'required|max:120',
            'budget'  => 'max:60',
            'details' => 'max:5000',
        ], static fn (Request $r): array => [
            'name'    => $r->input('name'),
            'email'   => $r->input('email'),
            'subject' => $r->input('service'),
            'budget'  => $r->input('budget'),
            'message' => $r->input('details'),
        ]);
    }

    public function feedback(Request $request): void
    {
        $this->process($request, 'feedback', [
            'name'     => 'required|max:160',
            'email'    => 'email|max:190',
            'role'     => 'max:160',
            'rating'   => 'required|in:1,2,3,4,5',
            'message'  => 'required|max:3000',
        ], static fn (Request $r): array => [
            'name'    => $r->input('name'),
            'email'   => $r->input('email'),
            'subject' => $r->input('role'),
            'message' => $r->input('message'),
            'extra'   => ['rating' => (int) $r->input('rating', '5')],
        ]);
    }

    public function newsletter(Request $request): void
    {
        $this->process($request, 'newsletter', [
            'email' => 'required|email|max:190',
            'name'  => 'max:160',
        ], static fn (Request $r): array => [
            'name'  => $r->input('name'),
            'email' => $r->input('email'),
        ]);
    }

    public function career(Request $request): void
    {
        $this->process($request, 'career', [
            'name'       => 'required|max:160',
            'email'      => 'required|email|max:190',
            'phone'      => 'required|phone',
            'level'      => 'required|in:Fresher,Experienced',
            'role'       => 'required|max:120',
            'experience' => 'max:60',
            'linkedin'   => 'url|max:255',
            'cover'      => 'max:5000',
        ], function (Request $r): array {
            $attachment = $this->handleResumeUpload($r);
            return [
                'name'       => $r->input('name'),
                'email'      => $r->input('email'),
                'phone'      => $r->input('phone'),
                'subject'    => $r->input('role'),
                'message'    => $r->input('cover'),
                'attachment' => $attachment,
                'extra'      => [
                    'level'      => $r->input('level'),
                    'experience' => $r->input('experience'),
                    'linkedin'   => $r->input('linkedin'),
                ],
            ];
        });
    }

    /**
     * @param array<string,string>            $rules
     * @param callable(Request):array<string,mixed> $mapper
     */
    private function process(Request $request, string $type, array $rules, callable $mapper): void
    {
        // 1. CSRF
        if (!Csrf::validate($request->input('_csrf'))) {
            $this->respond($request, false, 'Your session expired. Please refresh and try again.', 419);
            return;
        }

        // 2. Honeypot — bots fill the hidden "website" field. Fake success.
        if ($request->input('website', '') !== '') {
            $this->respond($request, true, 'Thank you!');
            return;
        }

        // 3. Rate limit
        $ip = $request->ip();
        if (RateLimiter::tooManyAttempts($ip, $type, self::RATE_MAX, self::RATE_WINDOW)) {
            $this->respond($request, false, 'Too many requests. Please try again later.', 429);
            return;
        }

        // 4. Validation
        $validator = new Validator($request->all());
        if (!$validator->validate($rules)) {
            $this->respond($request, false, $validator->firstError() ?? 'Please check the form.', 422, $validator->errors());
            return;
        }

        // 5. Map + (optional) upload
        try {
            $payload = $mapper($request);
        } catch (\RuntimeException $e) {
            $this->respond($request, false, $e->getMessage(), 422);
            return;
        }

        $payload['type'] = $type;
        $payload['ip_address'] = $ip;
        $payload['user_agent'] = $request->userAgent();
        RateLimiter::hit($ip, $type);

        // 6. Email the submission to HR (this landing site stores nothing — email only).
        $this->notify($type, $payload);

        // Never keep uploaded files on disk — remove the temp resume regardless of mail state.
        if (!empty($payload['attachment']) && is_file((string) $payload['attachment'])) {
            @unlink((string) $payload['attachment']);
        }

        $this->respond($request, true, $this->successMessage($type));
    }

    private function handleResumeUpload(Request $request): ?string
    {
        $file = $request->file('resume');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new \RuntimeException('Please attach your resume (PDF, DOC or DOCX).');
        }
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed. Please try again.');
        }
        if (($file['size'] ?? 0) > self::MAX_UPLOAD_BYTES) {
            throw new \RuntimeException('Resume must be 5 MB or smaller.');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload.');
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_UPLOAD_EXT, true)) {
            throw new \RuntimeException('Only PDF, DOC or DOCX files are accepted.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';
        // DOC/DOCX sometimes report octet-stream; accept when extension is whitelisted.
        if (!in_array($mime, self::ALLOWED_UPLOAD_MIME, true) && $mime !== 'application/octet-stream' && $mime !== 'application/zip') {
            throw new \RuntimeException('That file type is not allowed.');
        }

        // Stage the resume in the system temp dir only — this site keeps no uploads
        // folder. It is attached to the HR email and deleted immediately after.
        $dest = sys_get_temp_dir() . '/itrend_resume_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Could not process the upload. Please try again.');
        }
        @chmod($dest, 0600);

        return $dest; // absolute temp path
    }

    private function notify(string $type, array $payload): void
    {
        if (!Mailer::enabled()) {
            return;
        }
        $body = $this->hrEmailBody($type, $payload);

        $replyTo = !empty($payload['email']) && filter_var($payload['email'], FILTER_VALIDATE_EMAIL)
            ? (string) $payload['email'] : null;

        // Attach the uploaded resume (if any) to the email, then remove the temp file —
        // this site keeps no database and no permanent uploads.
        $attachment = null;
        $tmpPath = !empty($payload['attachment']) ? (string) $payload['attachment'] : null;
        if ($tmpPath !== null && is_file($tmpPath)) {
            $applicant = preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($payload['name'] ?? 'applicant')) ?: 'applicant';
            $attachment = ['path' => $tmpPath, 'name' => 'resume-' . trim($applicant, '-') . '.' . pathinfo($tmpPath, PATHINFO_EXTENSION)];
        }

        // 1) Notify HR (resume attached). $toOverride = null → goes to MAIL_TO (hr@).
        //    The From display name shows the person who filled the form (their actual
        //    address is the Reply-To), so HR sees who it's from and can reply directly.
        $senderName = trim((string) ($payload['name'] ?? ''));
        $fromLabel  = $senderName !== '' ? ($senderName . ' via iTrend website') : null;
        Mailer::send($this->hrSubject($type, $payload), $body, $replyTo, null, $attachment, $fromLabel);

        // 2) Auto-acknowledge the candidate — sent FROM hr@ TO the address they entered.
        //    Only when a valid email is present; never carries the resume or internal IP.
        if ($replyTo !== null) {
            Mailer::send($this->ackSubject($type), $this->ackBody($type, $payload), null, $replyTo, null);
        }
    }

    /** Subject line for the acknowledgement email sent to the candidate. */
    private function ackSubject(string $type): string
    {
        return match ($type) {
            'career'     => 'We received your application — iTrend Solution',
            'quote'      => 'We received your request — iTrend Solution',
            'newsletter' => "You're subscribed — iTrend Solution",
            'feedback'   => 'Thank you for your feedback — iTrend Solution',
            default      => 'We received your message — iTrend Solution',
        };
    }

    /** Friendly branded acknowledgement body for the sender (no internal data). */
    private function ackBody(string $type, array $payload): string
    {
        $name     = trim((string) ($payload['name'] ?? ''));
        $greeting = $name !== '' ? 'Hi ' . e($name) . ',' : 'Hello,';
        $message  = $this->successMessage($type);

        $eyebrow = match ($type) {
            'career'     => 'Application received',
            'feedback'   => 'Thank you',
            'newsletter' => 'You are subscribed',
            default      => 'Message received',
        };
        $title = match ($type) {
            'career'     => 'Thanks for applying to iTrend',
            'feedback'   => 'Thank you for your feedback',
            'newsletter' => 'Welcome aboard',
            default      => 'Thanks for reaching out',
        };

        $inner =
            '<p style="margin:0 0 16px;color:#374151;font-size:15px;line-height:1.6;">' . $greeting . '</p>'
            . '<p style="margin:0 0 20px;color:#374151;font-size:15px;line-height:1.6;">' . e($message) . '</p>'
            . '<p style="margin:0;color:#374151;font-size:15px;line-height:1.6;">Warm regards,<br>'
            . '<strong style="color:#7c2e85;">iTrend Solution — HR Team</strong><br>'
            . '<a href="mailto:hr@itrendsolution.com" style="color:#7c2e85;text-decoration:none;">hr@itrendsolution.com</a></p>';

        return $this->emailShell($eyebrow, $title, $inner, 'This is an automated confirmation — no need to reply to this email.');
    }

    /** Subject line for the HR notification, tailored per form type. */
    private function hrSubject(string $type, array $payload): string
    {
        $who = trim((string) ($payload['name'] ?? '')) ?: 'Website visitor';
        $role = trim((string) ($payload['subject'] ?? ''));
        return match ($type) {
            'career'     => '[iTrend] Job application — ' . $who . ($role !== '' ? ' (' . $role . ')' : ''),
            'contact'    => '[iTrend] Contact enquiry — ' . $who,
            'quote'      => '[iTrend] Quote request — ' . $who,
            'feedback'   => '[iTrend] Feedback — ' . $who,
            'newsletter' => '[iTrend] Newsletter signup — ' . (trim((string) ($payload['email'] ?? '')) ?: $who),
            default      => '[iTrend] New ' . ucfirst($type) . ' — ' . $who,
        };
    }

    /**
     * Branded HR notification email, with distinct content per form type
     * (Career/Apply Now, Contact, Quote, Feedback). Email-client-safe HTML.
     */
    private function hrEmailBody(string $type, array $payload): string
    {
        $extra = is_array($payload['extra'] ?? null) ? $payload['extra'] : [];
        $name  = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $phone = trim((string) ($payload['phone'] ?? ''));

        if ($type === 'career') {
            $eyebrow = 'New job application';
            $title   = ($name !== '' ? e($name) : 'A candidate') . ' applied'
                . (!empty($payload['subject']) ? ' for <span style="color:#7c2e85;">' . e((string) $payload['subject']) . '</span>' : '');
            $linkedin = trim((string) ($extra['linkedin'] ?? ''));
            $inner = $this->senderCard($name, $email, $phone)
                . $this->replyButton($email, $name, $type)
                . $this->detailRows([
                    ['Role applied for', !empty($payload['subject']) ? e((string) $payload['subject']) : ''],
                    ['Experience level', !empty($extra['level']) ? e((string) $extra['level']) : ''],
                    ['Experience', !empty($extra['experience']) ? e((string) $extra['experience']) : ''],
                    ['LinkedIn', $linkedin !== '' ? '<a href="' . e($linkedin) . '" style="color:#7c2e85;">' . e($linkedin) . '</a>' : ''],
                    ['Résumé', !empty($payload['attachment']) ? '📎 Attached to this email' : 'Not provided'],
                ])
                . $this->messageBlock('Cover note', (string) ($payload['message'] ?? ''));
        } elseif ($type === 'feedback') {
            $eyebrow = 'New feedback';
            $rating  = (int) ($extra['rating'] ?? 0);
            $stars   = $rating > 0 ? str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) : '';
            $title   = 'Feedback from ' . ($name !== '' ? e($name) : 'a visitor');
            $inner = $this->senderCard($name, $email, $phone)
                . $this->replyButton($email, $name, $type)
                . $this->detailRows([
                    ['Rating', $stars !== '' ? '<span style="color:#ee942f;font-size:18px;letter-spacing:2px;">' . $stars . '</span> &nbsp;' . $rating . '/5' : ''],
                    ['Role / Company', !empty($payload['subject']) ? e((string) $payload['subject']) : ''],
                ])
                . $this->messageBlock('Their feedback', (string) ($payload['message'] ?? ''));
        } elseif ($type === 'newsletter') {
            $eyebrow = 'New newsletter signup';
            $title   = ($name !== '' ? e($name) : 'Someone') . ' subscribed';
            $inner   = $this->senderCard($name, $email, $phone);
        } else { // contact / quote
            $eyebrow = $type === 'quote' ? 'New quote request' : 'New contact enquiry';
            $title   = 'New message from ' . ($name !== '' ? e($name) : 'a website visitor');
            $inner = $this->senderCard($name, $email, $phone)
                . $this->replyButton($email, $name, $type)
                . $this->detailRows([
                    ['Company', !empty($payload['company']) ? e((string) $payload['company']) : ''],
                    ['Interested in', !empty($payload['subject']) ? e((string) $payload['subject']) : ''],
                    ['Budget', !empty($payload['budget']) ? e((string) $payload['budget']) : ''],
                ])
                . $this->messageBlock('Message', (string) ($payload['message'] ?? ''));
        }

        $footNote = 'Reply directly to this email to reach ' . ($name !== '' ? e($name) : 'the sender')
            . ($email !== '' ? ' — ' . e($email) : '') . '.'
            . (!empty($payload['ip_address']) ? '<br>Received ' . date('M j, Y · g:i A') . ' · IP ' . e((string) $payload['ip_address']) : '');

        return $this->emailShell($eyebrow, $title, $inner, $footNote);
    }

    /** Branded, email-client-safe HTML shell (tables + inline styles). */
    private function emailShell(string $eyebrow, string $title, string $innerHtml, string $footNote = ''): string
    {
        $foot = $footNote !== ''
            ? '<p style="margin:0 0 8px;color:#9ca3af;font-size:12px;line-height:1.6;">' . $footNote . '</p>'
            : '';

        return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            . '<body style="margin:0;padding:0;background:#f4f2f8;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f2f8;padding:24px 12px;"><tr><td align="center">'
            . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #ece7f2;font-family:Arial,Helvetica,sans-serif;">'
            . '<tr><td style="background:#7c2e85;padding:22px 28px;"><table role="presentation" width="100%"><tr>'
            . '<td style="color:#ffffff;font-size:20px;font-weight:bold;letter-spacing:.3px;">iTrend <span style="color:#f0c98a;">Solution</span></td>'
            . '<td align="right" style="color:#e6d3ea;font-size:10px;letter-spacing:1.5px;text-transform:uppercase;">Think Loud. Exist Loudly.</td>'
            . '</tr></table></td></tr>'
            . '<tr><td style="height:4px;line-height:4px;font-size:0;background:#ee942f;">&nbsp;</td></tr>'
            . '<tr><td style="padding:30px 28px 4px;">'
            . '<p style="margin:0 0 6px;color:#ee942f;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;">' . $eyebrow . '</p>'
            . '<h1 style="margin:0 0 20px;color:#1f2937;font-size:22px;line-height:1.3;font-weight:bold;">' . $title . '</h1>'
            . '</td></tr>'
            . '<tr><td style="padding:0 28px 28px;">' . $innerHtml . '</td></tr>'
            . '<tr><td style="background:#faf8fc;border-top:1px solid #ece7f2;padding:18px 28px;">'
            . $foot
            . '<p style="margin:0;color:#9ca3af;font-size:12px;line-height:1.6;">&copy; ' . date('Y') . ' iTrend Solution · sent from the iTrend website</p>'
            . '</td></tr></table></td></tr></table></body></html>';
    }

    /**
     * One-click "Reply to {name}" button for HR — a mailto link that opens the HR
     * team's mail app with a draft already addressed to the candidate/sender, with
     * a subject and greeting pre-filled, so replying is a single click.
     */
    private function replyButton(string $email, string $name, string $type): string
    {
        if ($email === '') {
            return '';
        }
        $subject  = $type === 'career'
            ? 'Re: Your application to iTrend Solution'
            : 'Re: Your enquiry — iTrend Solution';
        $greeting = $name !== '' ? 'Hi ' . $name . ',' : 'Hello,';
        $href = 'mailto:' . $email
            . '?subject=' . rawurlencode($subject)
            . '&amp;body=' . rawurlencode($greeting . "\r\n\r\n");
        $label = $name !== '' ? 'Reply to ' . e($name) : 'Reply to sender';

        return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;"><tr>'
            . '<td style="border-radius:10px;background:#7c2e85;box-shadow:0 6px 16px rgba(124,46,133,.28);">'
            . '<a href="' . $href . '" style="display:inline-block;padding:13px 26px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:10px;">&#9993;&nbsp;&nbsp;' . $label . '</a>'
            . '</td></tr></table>';
    }

    /** Highlighted sender card (name + email + phone) for the top of HR emails. */
    private function senderCard(string $name, string $email, string $phone = ''): string
    {
        $emailLink = $email !== ''
            ? '<a href="mailto:' . e($email) . '" style="color:#7c2e85;text-decoration:none;font-weight:bold;">' . e($email) . '</a>'
            : '<span style="color:#9ca3af;">no email provided</span>';
        $phoneLine = $phone !== '' ? '<span style="color:#6b7280;"> &nbsp;·&nbsp; ' . e($phone) . '</span>' : '';

        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3eef9;border-radius:10px;margin:0 0 22px;">'
            . '<tr><td style="padding:16px 18px;">'
            . '<p style="margin:0 0 4px;color:#1f2937;font-size:16px;font-weight:bold;">' . ($name !== '' ? e($name) : 'Website visitor') . '</p>'
            . '<p style="margin:0;font-size:14px;">' . $emailLink . $phoneLine . '</p>'
            . '</td></tr></table>';
    }

    /**
     * Label/value detail rows. Each row is [label, value-html]; empty values skipped.
     * @param array<int,array{0:string,1:string}> $rows
     */
    private function detailRows(array $rows): string
    {
        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">';
        foreach ($rows as [$label, $value]) {
            if ($value === '' || $value === null) {
                continue;
            }
            $html .= '<tr>'
                . '<td style="padding:11px 16px 11px 0;color:#6b7280;font-size:13px;font-weight:bold;vertical-align:top;white-space:nowrap;border-bottom:1px solid #f0edf5;">' . e($label) . '</td>'
                . '<td style="padding:11px 0;color:#1f2937;font-size:14px;vertical-align:top;border-bottom:1px solid #f0edf5;">' . $value . '</td>'
                . '</tr>';
        }
        return $html . '</table>';
    }

    /** A quoted message block (for cover notes, messages, feedback). */
    private function messageBlock(string $heading, string $text): string
    {
        if (trim($text) === '') {
            return '';
        }
        return '<p style="margin:24px 0 8px;color:#6b7280;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">' . e($heading) . '</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf8fc;border-radius:8px;">'
            . '<tr><td style="padding:14px 16px;border-left:3px solid #ee942f;color:#374151;font-size:14px;line-height:1.6;">' . nl2br(e($text)) . '</td></tr></table>';
    }

    private function successMessage(string $type): string
    {
        return match ($type) {
            'career'     => 'Application received! Our talent team will review and reach out if there is a fit.',
            'quote'      => 'Request received! You will get a tailored proposal within one business day.',
            'newsletter' => "You're subscribed! Watch your inbox — the first edition lands soon.",
            'feedback'   => 'Thank you for your feedback! We truly appreciate you sharing your experience.',
            default      => "Message sent! We'll be in touch within 24 hours.",
        };
    }

    private function respond(Request $request, bool $ok, string $message, int $status = 200, array $errors = []): void
    {
        // Rotate the CSRF token only after a SUCCESSFUL state change, so a
        // failed attempt (e.g. validation error) can be retried with the same token.
        if ($ok) {
            Csrf::rotate();
        }

        if ($request->expectsJson()) {
            $this->json([
                'ok'      => $ok,
                'message' => $message,
                'errors'  => $errors,
                'token'   => Csrf::token(),
            ], $ok ? 200 : $status);
            return;
        }

        // Non-JS fallback: flash + redirect back to referring page.
        $this->flash($ok ? 'success' : 'error', $message);
        $back = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($back);
    }
}
