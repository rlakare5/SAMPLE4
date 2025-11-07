<?php
session_start();

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/translations.php';

if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'register':
        include BASE_PATH . '/public/register.php';
        break;
    case 'login':
        include BASE_PATH . '/public/login.php';
        break;
    case 'farmer':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'farmer') {
            header('Location: index.php?page=login');
            exit;
        }
        include BASE_PATH . '/public/farmer-dashboard.php';
        break;
    case 'weather':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'farmer') {
            header('Location: index.php?page=login');
            exit;
        }
        include BASE_PATH . '/public/weather.php';
        break;
    case 'ai-insights':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'farmer') {
            header('Location: index.php?page=login');
            exit;
        }
        include BASE_PATH . '/public/ai-insights.php';
        break;
    case 'chat':
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'farmer' && $_SESSION['user_role'] !== 'vendor')) {
            header('Location: index.php?page=login');
            exit;
        }
        include BASE_PATH . '/public/chat.php';
        break;
    case 'vendor':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'vendor') {
            header('Location: index.php?page=login');
            exit;
        }
        include BASE_PATH . '/public/vendor-dashboard.php';
        break;
    case 'admin':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }
        include BASE_PATH . '/public/admin-dashboard.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php');
        exit;
    default:
        include BASE_PATH . '/public/home.php';
        break;
}
