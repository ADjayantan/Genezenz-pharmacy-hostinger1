<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\LeadController;
use App\Controllers\ProductsController;
use App\Controllers\AuthController;
use App\Controllers\ShopController;
use App\Controllers\PrescriptionController;
use App\Controllers\ContentController;
use App\Controllers\AdminController;
use App\Controllers\MetaController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Router;
use App\Repositories\LeadRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PrescriptionRepository;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$catalog = require BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'catalog.php';
$database = Database::tryConnection();
if (!$database && \App\Core\Env::get('APP_ENV', 'production') !== 'local') {
    (new \App\Core\Response('<h1>Temporarily unavailable</h1><p>Please try again shortly.</p>', 503, ['Content-Type' => 'text/html; charset=UTF-8', 'Retry-After' => '300', 'Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex']))->send();
}
Auth::initialize($database);
$products = new ProductRepository($database, $catalog);
$users = new UserRepository($database);
$orders = new OrderRepository($database);
$prescriptions = new PrescriptionRepository($database);
$leads = new LeadRepository($database);
$limiter = new RateLimiter($database);

$homeController = new HomeController($products);
$productsController = new ProductsController($products, $limiter);
$leadController = new LeadController($leads, $limiter);
$authController = new AuthController($users, $limiter);
$shopController = new ShopController($orders, $prescriptions, $limiter);
$prescriptionController = new PrescriptionController($prescriptions, $limiter);
$contentController = new ContentController($products);
$adminController = new AdminController($database, $products, $orders, $prescriptions, $leads, $users);
$metaController = new MetaController($database);

$router = new Router();
$router->get('/', $homeController);
$router->get('/products', [$productsController, 'index']);
$router->get('/products/{slug}', [$productsController, 'show']);
$router->get('/api/search', [$productsController, 'search']);
$router->post('/api/leads', [$leadController, 'store']);
$router->get('/login', [$authController, 'loginPage']);
$router->post('/login', [$authController, 'login']);
$router->get('/register', [$authController, 'registerPage']);
$router->post('/register', [$authController, 'register']);
$router->post('/logout', [$authController, 'logout']);
$router->get('/cart', [$shopController, 'cart']);
$router->get('/checkout', [$shopController, 'checkout']);
$router->post('/api/orders', [$shopController, 'placeOrder']);
$router->get('/profile', [$shopController, 'profile']);
$router->get('/order/{orderNo}', [$shopController, 'order']);
$router->get('/upload-prescription', [$prescriptionController, 'page']);
$router->post('/upload-prescription', [$prescriptionController, 'store']);
$router->post('/api/prescriptions/upload', [$prescriptionController, 'store']);
$router->get('/api/prescriptions/file/{id}', [$prescriptionController, 'file']);
$router->get('/about', [$contentController, 'page']);
$router->get('/contact', [$contentController, 'page']);
$router->get('/insurance', [$contentController, 'page']);
$router->get('/legal', [$contentController, 'legalIndex']);
$router->get('/legal/{slug}', [$contentController, 'legal']);
$router->get('/pharmacy-in-{area}-coimbatore', [$contentController, 'area']);
$router->get('/sitemap.xml', [$contentController, 'sitemap']);
$router->get('/robots.txt', [$contentController, 'robots']);
$router->get('/admin/login', [$adminController, 'loginPage']);
$router->get('/admin', [$adminController, 'dashboard']);
$router->get('/admin/{section}', [$adminController, 'list']);
$router->get('/admin/products/new', [$adminController, 'productForm']);
$router->post('/admin/products/new', [$adminController, 'saveProduct']);
$router->get('/admin/products/{id}', [$adminController, 'productForm']);
$router->post('/admin/products/{id}', [$adminController, 'saveProduct']);
$router->post('/admin/products/{id}/delete', [$adminController, 'removeProduct']);
$router->post('/admin/orders/{orderNo}', [$adminController, 'updateOrder']);
$router->get('/admin/orders/{orderNo}/invoice', [$adminController, 'invoice']);
$router->post('/admin/prescriptions/{id}', [$adminController, 'reviewRx']);
$router->post('/admin/leads/{id}', [$adminController, 'updateLead']);
$router->get('/api/webhooks/meta', [$metaController, 'verify']);
$router->post('/api/webhooks/meta', [$metaController, 'receive']);

$router->dispatch(Request::capture())->send();
