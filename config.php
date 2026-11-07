<?php
declare(strict_types=1);

// ---- DATABASE SETTINGS (XAMPP defaults) ----
const DB_HOST = 'localhost';
const DB_NAME = 'projectweb';
const DB_USER = 'root';
const DB_PASS = '';

const SITE_NAME   = 'Kick Flip Collection';
const UPLOAD_DIR  = __DIR__ . '/uploads/';
const MAX_IMG_MB  = 2;

// Secret code required to register an ADMIN account. CHANGE THIS to your own secret!
const ADMIN_CODE  = 'kickflip-admin-2026';

session_start();

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Invalid or expired form. Go back and try again.');
    }
}

/* ---------- AUTH ---------- */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(string $root = ''): void
{
    if (!current_user()) {
        redirect($root . 'login.php');
    }
}

function require_admin(string $root = '../'): void
{
    require_login($root);
    if (current_user()['role'] !== 'admin') {
        http_response_code(403);
        exit('Admins only.');
    }
}

/* ---------- FLASH MESSAGES ---------- */
function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- CART / PRODUCTS ---------- */
function cart_count(): int
{
    $u = current_user();
    if (!$u) return 0;
    $st = db()->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id = ?');
    $st->execute([$u['id']]);
    return (int)$st->fetchColumn();
}

function product_image(?string $file, string $root = ''): string
{
    if ($file && is_file(UPLOAD_DIR . $file)) {
        return $root . 'uploads/' . rawurlencode($file);
    }
    return $root . 'images/placeholder.svg';
}

function money($n): string
{
    return '₱' . number_format((float)$n, 2);
}
