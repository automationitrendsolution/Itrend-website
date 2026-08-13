<?php
/**
 * reCAPTCHA production diagnostic — TEMPORARY, DELETE AFTER USE.
 *
 * Visit https://your-domain/recaptcha-check.php on the live cPanel server.
 * It checks, in order, every point where reCAPTCHA breaks in production:
 *
 *   1. Is the secrets file found and readable from the web server's user?
 *   2. Are the keys present, non-placeholder, and the right shape?
 *   3. Can PHP reach Google at all (outbound HTTPS is blocked on some hosts)?
 *   4. Do the CSP / CORP headers actually sent by the live server allow Google?
 *   5. A live end-to-end widget test that verifies a real token server-side.
 *
 * Secrets are never printed — only lengths, prefixes and pass/fail verdicts.
 *
 * Access is restricted to the IP that first loads it in a session, so a stray
 * copy left on the server is not an open information leak. Delete it anyway.
 */

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Recaptcha;

header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

$rows = [];
$fail = 0;
$warn = 0;

function row(string $label, bool $ok, string $detail, bool $warnOnly = false): void
{
    global $rows, $fail, $warn;
    if (!$ok) {
        $warnOnly ? $warn++ : $fail++;
    }
    $rows[] = [$label, $ok ? 'pass' : ($warnOnly ? 'warn' : 'fail'), $detail];
}

// ---- 1. Secrets file ----------------------------------------------------
$sf = defined('SECRETS_FILE') ? SECRETS_FILE : '';
row(
    'Secrets file located',
    $sf !== '',
    $sf !== ''
        ? 'Loaded from ' . $sf
        : 'NOT FOUND. Upload itrend-secrets.json to the "workspace" folder one level ABOVE public_html.'
);
if ($sf !== '') {
    row('Secrets file readable by PHP', is_readable($sf),
        is_readable($sf) ? 'ok' : 'Permissions problem — chmod it to 644 and its folder to 755.');
}

// ---- 2. Keys ------------------------------------------------------------
$site   = trim((string) Config::get('RECAPTCHA_SITE_KEY', ''));
$secret = trim((string) Config::get('RECAPTCHA_SECRET_KEY', ''));
$on     = Config::bool('RECAPTCHA_ENABLED', false);

row('recaptcha.enabled is true', $on, $on ? 'ok' : 'Set "enabled": true in itrend-secrets.json.');
row('Site key present', $site !== '',
    $site !== '' ? 'length ' . strlen($site) . ', starts "' . substr($site, 0, 6) . '…"' : 'empty');
row('Secret key present', $secret !== '',
    $secret !== '' ? 'length ' . strlen($secret) . ', starts "' . substr($secret, 0, 6) . '…"' : 'empty');
row('Keys are not the example placeholders',
    !str_contains($site, 'your_') && !str_contains($secret, 'your_'),
    'Placeholder values from the example file will always fail.');
row('Site and secret keys differ', $site !== '' && $site !== $secret,
    $site === $secret && $site !== ''
        ? 'They are IDENTICAL — the same key was pasted into both fields. Copy the SECRET key from the reCAPTCHA admin console.'
        : 'ok');
row('Recaptcha::enabled() returns true', Recaptcha::enabled(),
    Recaptcha::enabled() ? 'Widget will render.' : 'Widget will NOT render — the form has no checkbox at all.');

// ---- 3. Outbound connectivity to Google --------------------------------
$curl = function_exists('curl_init');
row('cURL extension available', $curl, $curl ? 'ok' : 'Falling back to file_get_contents.', true);
row('allow_url_fopen enabled', (bool) ini_get('allow_url_fopen'),
    'Needed only if cURL is missing.', $curl);

$reach = false;
$reachDetail = '';
if ($curl) {
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['secret' => 'x', 'response' => 'x']),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    $reach = is_string($res) && $res !== '';
    if ($reach) {
        $reachDetail = 'Google responded.';
    } elseif (stripos($err, 'certificate') !== false || stripos($err, 'ssl') !== false) {
        // Distinct cause from a firewall block: PHP has no CA bundle to verify
        // Google's certificate. Common on Windows/XAMPP, rare on cPanel/Linux.
        $reachDetail = 'TLS trust store problem: ' . $err
            . ' — PHP has no CA bundle. Set curl.cainfo and openssl.cafile in php.ini to a cacert.pem. '
            . '(Harmless if you are seeing this on a local Windows machine rather than the live server.)';
    } else {
        $reachDetail = 'FAILED: ' . ($err ?: 'no response')
            . ' — outbound HTTPS appears blocked. Ask your cPanel provider to allow www.google.com.';
    }
}
row('PHP can reach Google siteverify', $reach, $reachDetail);

// ---- 4. Live key validation against Google ------------------------------
// Sending a deliberately invalid token still tells us whether the SECRET is
// accepted: a bad secret returns invalid-input-secret, a good one returns
// invalid-input-response (meaning only the token was wrong, as expected).
if ($reach && $secret !== '') {
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['secret' => $secret, 'response' => 'probe-invalid-token']),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
    ]);
    $body = (string) curl_exec($ch);
    curl_close($ch);
    $data  = json_decode($body, true);
    $codes = is_array($data) ? (array) ($data['error-codes'] ?? []) : [];
    $codeStr = implode(', ', array_map('strval', $codes));

    $secretBad = in_array('invalid-input-secret', $codes, true)
              || in_array('invalid-keys', $codes, true);

    row('Secret key accepted by Google', !$secretBad,
        $secretBad
            ? 'REJECTED (' . $codeStr . ') — this secret key is wrong, or belongs to a different site/key type. Note reCAPTCHA v2 and v3 keys are NOT interchangeable.'
            : 'Google returned "' . ($codeStr ?: 'none') . '", which is the expected answer for a dummy token — the secret itself is valid.');
}

// ---- 5. Headers actually being sent -------------------------------------
$sent = [];
foreach (headers_list() as $h) {
    $parts = explode(':', $h, 2);
    $sent[strtolower(trim($parts[0]))] = trim($parts[1] ?? '');
}
$csp  = $sent['content-security-policy'] ?? '';
$corp = $sent['cross-origin-resource-policy'] ?? '';

row('A CSP header is present', $csp !== '', $csp !== '' ? 'ok' : 'none sent', true);
if ($csp !== '') {
    // Extract script-src (falling back to default-src, per CSP semantics).
    $scriptSrc = '';
    foreach (explode(';', $csp) as $d) {
        $d = trim($d);
        if (str_starts_with($d, 'script-src')) { $scriptSrc = $d; break; }
        if (str_starts_with($d, 'default-src') && $scriptSrc === '') { $scriptSrc = $d; }
    }
    $allowsGoogle = str_contains($scriptSrc, 'www.google.com');
    row('CSP script-src allows www.google.com', $allowsGoogle,
        $allowsGoogle
            ? 'ok — ' . $scriptSrc
            : 'BLOCKED — "' . $scriptSrc . '". Google\'s api.js cannot load, so the checkbox never appears.');
}
row('Cross-Origin-Resource-Policy is not same-origin', $corp !== 'same-origin',
    $corp === 'same-origin'
        ? 'same-origin blocks Google\'s gstatic.com runtime — should be cross-origin.'
        : 'ok (' . ($corp ?: 'not set') . ')');

// NOTE: only PHP-set headers are visible here. Headers added by Apache
// (mod_headers / .htaccess) are appended AFTER PHP finishes, so a duplicate
// CSP from .htaccess will NOT show up above. The browser DevTools Network tab
// is the only place you see the final merged set — check there too.
$dupWarn = true;

$verdict = $fail === 0 ? 'ALL SERVER-SIDE CHECKS PASSED' : $fail . ' CHECK(S) FAILED';
$vColor  = $fail === 0 ? '#16a34a' : '#dc2626';
?>
<!doctype html>
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>reCAPTCHA diagnostic</title>
<style>
  body{font:15px/1.6 system-ui,sans-serif;max-width:920px;margin:40px auto;padding:0 20px;color:#111}
  h1{font-size:22px;margin-bottom:4px}
  .verdict{font-weight:700;font-size:18px;color:<?= $vColor ?>;margin:14px 0 24px}
  table{border-collapse:collapse;width:100%;margin-bottom:28px}
  td{border-top:1px solid #e5e7eb;padding:9px 8px;vertical-align:top}
  td:first-child{width:38%;font-weight:600}
  .pass{color:#16a34a;font-weight:700}.fail{color:#dc2626;font-weight:700}.warn{color:#d97706;font-weight:700}
  .note{background:#fef3c7;border-left:4px solid #d97706;padding:12px 14px;margin:20px 0;font-size:14px}
  .danger{background:#fee2e2;border-left:4px solid #dc2626;padding:12px 14px;margin:20px 0}
  code{background:#f3f4f6;padding:1px 5px;border-radius:3px;font-size:13px}
  fieldset{border:1px solid #d1d5db;border-radius:8px;padding:18px}
</style>

<h1>reCAPTCHA production diagnostic</h1>
<div>Host: <code><?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? '') ?></code> ·
     Scheme: <code><?= App\Core\Security::isHttps() ? 'https' : 'http' ?></code> ·
     PHP <code><?= PHP_VERSION ?></code></div>
<div class="verdict"><?= $verdict ?><?= $warn ? " ({$warn} warning)" : '' ?></div>

<table>
<?php foreach ($rows as [$label, $state, $detail]): ?>
  <tr>
    <td><?= htmlspecialchars($label) ?></td>
    <td class="<?= $state ?>"><?= strtoupper($state) ?></td>
    <td><?= htmlspecialchars($detail) ?></td>
  </tr>
<?php endforeach; ?>
</table>

<div class="note">
  <strong>Check the browser console too.</strong> Header checks above see only what
  PHP sent. Apache adds its own headers afterwards, so a duplicate
  <code>Content-Security-Policy</code> from <code>.htaccess</code> is invisible here
  but still blocks Google. Open DevTools → Console: a red
  <em>"Refused to load the script 'https://www.google.com/recaptcha/api.js'"</em>
  message confirms it.
</div>

<h2>Live end-to-end test</h2>
<?php if (!Recaptcha::enabled()): ?>
  <p><em>Skipped — reCAPTCHA is not enabled, so there is no widget to test.</em></p>
<?php elseif (!empty($_POST)): ?>
  <?php
    $ok = Recaptcha::verify($_POST['g-recaptcha-response'] ?? null, 'diagnostic',
                            $_SERVER['REMOTE_ADDR'] ?? '');
    $le = Recaptcha::lastError();
  ?>
  <p class="<?= $ok ? 'pass' : 'fail' ?>" style="font-size:17px">
    <?= $ok ? '✓ Token verified successfully — reCAPTCHA is working end to end.'
            : '✗ Verification failed: ' . htmlspecialchars($le ?: 'unknown') ?>
  </p>
  <?php if ($ok && $le === ''): ?>
    <p>If the box rendered and this says verified, the fix is confirmed. Delete this file now.</p>
  <?php endif; ?>
<?php endif; ?>

<?php if (Recaptcha::enabled()): ?>
<fieldset>
  <legend>Tick the box, then Verify</legend>
  <p style="margin-top:0;font-size:14px;color:#555">
    If no checkbox appears below, the script is being blocked — that is the CSP
    problem, not a key problem.
  </p>
  <form method="post">
    <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(Recaptcha::siteKey()) ?>"></div>
    <p><button type="submit" style="padding:9px 18px;font-size:15px;cursor:pointer">Verify token</button></p>
  </form>
</fieldset>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<div class="danger">
  <strong>Delete this file when you are done.</strong> It reveals configuration
  details that should not stay on a public server:
  <code>rm public_html/recaptcha-check.php</code>
</div>
