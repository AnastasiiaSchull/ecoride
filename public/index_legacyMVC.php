<?php
session_start();
define('ROOT', dirname(__DIR__));
$pdo = require ROOT . '/config/db.php';

$autoload = ROOT . '/vendor/autoload.php';
if (file_exists($autoload)) { require $autoload; }

// d'abord, on récupère le chemin
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

// 2) Подключаем контроллеры, которые будем дергать
require_once ROOT.'/app/Controllers/HomeController.php';
require_once ROOT.'/app/Controllers/AuthController.php';
require_once ROOT.'/app/Controllers/AdminController.php';
require_once ROOT.'/app/Controllers/CovoiturageController.php';
require_once ROOT.'/app/Controllers/ReservationController.php';
require_once ROOT.'/app/Controllers/TrajetController.php';
require_once ROOT.'/app/Controllers/ApiController.php';
require_once ROOT.'/app/Controllers/ProfileController.php';
require_once ROOT.'/app/Controllers/PageController.php';
require_once ROOT.'/app/Controllers/RoleController.php';
require_once ROOT.'/app/Controllers/VehicleController.php';
require_once ROOT.'/app/Controllers/EmployeController.php';
require_once ROOT.'/app/Controllers/AvisController.php';

// créer les instances
$home  = new HomeController($pdo);
$auth  = new AuthController($pdo);
$admin = new AdminController($pdo);
$cov   = new CovoiturageController($pdo);
$res   = new ReservationController($pdo);
$traj  = new TrajetController($pdo);
$api   = new ApiController($pdo);
$prof  = new ProfileController($pdo);
$pages = new PageController($pdo);
$role = new RoleController($pdo);
$veh = new VehicleController($pdo);
$emp = new EmployeController($pdo);
$avis = new AvisController($pdo);

// routage

// home
if ($path === '/' || $path === '/home') { $home->index(); exit; }

// recherche / création de trajet
if ($path === '/covoiturages') { $cov->search(); exit; }

// reservations
if ($path === '/reservations'     && $_SERVER['REQUEST_METHOD'] === 'POST') { $res->create(); exit; }
if ($path === '/mes_reservations' && $_SERVER['REQUEST_METHOD'] === 'GET')  { $res->my(); exit; }
if ($path === '/confirmation'     && $_SERVER['REQUEST_METHOD'] === 'GET')  { $res->confirmation(); exit; }

// details
if ($path === '/trajets/details') { $traj->details(); exit; }

// API для AJAX
if ($path === '/api/trajets/dates')        { $api->dates(); exit; }
if ($path === '/api/trajets/departs')      { $api->departs(); exit; }
if ($path === '/api/trajets/destinations') { $api->destinations(); exit; }
if ($path === '/api/trajets/places')       { $api->places(); exit; }

// Auth
if ($path === '/connexion'   && $_SERVER['REQUEST_METHOD'] === 'GET')  { $auth->loginForm();    exit; }
if ($path === '/connexion'   && $_SERVER['REQUEST_METHOD'] === 'POST') { $auth->login();        exit; }
if ($path === '/inscription' && $_SERVER['REQUEST_METHOD'] === 'GET')  { $auth->registerForm(); exit; }
if ($path === '/inscription' && $_SERVER['REQUEST_METHOD'] === 'POST') { $auth->register();     exit; }
if ($path === '/logout') { $auth->logout(); exit; }

// Admin
if ($path === '/admin'            && $_SERVER['REQUEST_METHOD'] === 'GET')  { $admin->dashboard(); exit; }
if ($path === '/admin/suspend'    && $_SERVER['REQUEST_METHOD'] === 'POST') { $admin->suspend();   exit; }
if ($path === '/admin/restore'    && $_SERVER['REQUEST_METHOD'] === 'POST') { $admin->restore();   exit; }
if ($path === '/admin/employes'    && $_SERVER['REQUEST_METHOD']==='POST') { $admin->createEmployee(); exit; }

if ($path === '/employe'             && $_SERVER['REQUEST_METHOD']==='GET')  { $emp->dashboard();    exit; }
if ($path === '/employe/dashboard'  && $_SERVER['REQUEST_METHOD'] === 'GET') { $emp->dashboard(); exit; } 
if ($path === '/employe/moderation' && $_SERVER['REQUEST_METHOD'] === 'POST'){ $emp->moderate();  exit; }

// profil
if ($path === '/mon_espace' && $_SERVER['REQUEST_METHOD'] === 'GET') { 
    $prof->dashboard(); 
    exit; 
}
if ($path === '/profil/upload-photo' && $_SERVER['REQUEST_METHOD'] === 'POST') { $prof->uploadPhoto(); exit; }
if ($path === '/contact' && $_SERVER['REQUEST_METHOD']==='GET') { $pages->contact(); exit; }
if ($path === '/roles/edit' && $_SERVER['REQUEST_METHOD'] === 'GET')  { $role->edit();   exit; }
if ($path === '/roles'      && $_SERVER['REQUEST_METHOD'] === 'POST') { $role->update(); exit; }

// trajets (conducteur)
if ($path==='/mes_trajets'       && $_SERVER['REQUEST_METHOD']==='GET')  { $traj->mine(); exit; }
if ($path==='/trajets/creer'     && $_SERVER['REQUEST_METHOD']==='GET')  { $traj->createForm(); exit; }
if ($path==='/trajets'           && $_SERVER['REQUEST_METHOD']==='POST') { $traj->store(); exit; }
if ($path==='/trajets/statut'    && $_SERVER['REQUEST_METHOD']==='POST') { $traj->updateStatus(); exit; }

// reservations
if ($path==='/mes_reservations'  && $_SERVER['REQUEST_METHOD']==='GET')  { $res->my(); exit; }

// profile (credits + upload )
if ($path==='/credits'           && $_SERVER['REQUEST_METHOD']==='GET')  { $prof->creditsForm(); exit; }
if ($path==='/credits'           && $_SERVER['REQUEST_METHOD']==='POST') { $prof->creditsStore(); exit; }

// vehicles
if ($path==='/vehicules/creer' && $_SERVER['REQUEST_METHOD']==='GET')  { $veh->createForm(); exit; }
if ($path==='/vehicules/creer' && $_SERVER['REQUEST_METHOD']==='POST') { $veh->store();      exit; }

// avis
if ($path === '/avis/nouveau' && $_SERVER['REQUEST_METHOD'] === 'GET')  { $avis->createForm(); exit; }
if ($path === '/avis'         && $_SERVER['REQUEST_METHOD'] === 'POST') { $avis->store();      exit; }

http_response_code(404);
echo '<h1>404</h1><p>Route introuvable.</p>';
exit;
