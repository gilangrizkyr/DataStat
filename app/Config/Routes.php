<?php

/**
 * ============================================================================
 * ROUTES CONFIGURATION
 * ============================================================================
 * 
 * Path: app/Config/Routes.php
 * 
 * Deskripsi:
 * Konfigurasi routes untuk semua endpoints aplikasi DataStat.
 * Includes routes untuk Auth, Superadmin, Owner, dan Viewer.
 * 
 * IMPORTANT: File ini harus di-copy ke app/Config/Routes.php
 * ============================================================================
 */

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route - Redirect to login
$routes->get('/', 'Auth\LoginController::index');

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// Home/Landing (optional, jika butuh landing page)
// $routes->get('home', 'Home::index');

// ============================================================================
// AUTH ROUTES (Guest Only - with 'guest' filter)
// ============================================================================
$routes->group('', ['filter' => 'guest'], function($routes) {
    // Login
    $routes->get('login', 'Auth\LoginController::index');
    $routes->post('login', 'Auth\LoginController::authenticate');
    
    // Register
    $routes->get('register', 'Auth\RegisterController::index');
    $routes->post('register', 'Auth\RegisterController::store');
    
    // Forgot Password
    $routes->get('forgot-password', 'Auth\ForgotPasswordController::index');
    $routes->post('forgot-password', 'Auth\ForgotPasswordController::send');
    
    // Reset Password
    $routes->get('reset-password/(:any)', 'Auth\ResetPasswordController::index/$1');
    $routes->post('reset-password', 'Auth\ResetPasswordController::update');
});

// Logout (Authenticated users only)
$routes->get('logout', 'Auth\LogoutController::index', ['filter' => 'auth']);

// ============================================================================
// AUTHENTICATED ROUTES (All logged-in users)
// ============================================================================
$routes->group('', ['filter' => 'auth'], function($routes) {
    
    // Common Dashboard
    $routes->get('dashboard', 'DashboardController::index');
    
    // Profile
    $routes->get('profile', 'ProfileController::index');
    $routes->get('profile/edit', 'ProfileController::edit');
    $routes->post('profile/update', 'ProfileController::update');
    $routes->post('profile/change-password', 'ProfileController::changePassword');
    $routes->post('profile/upload-avatar', 'ProfileController::uploadAvatar');
    $routes->delete('profile/delete-avatar', 'ProfileController::deleteAvatar');
    
});

// ============================================================================
// SUPERADMIN ROUTES (Superadmin Only)
// ============================================================================
$routes->group('superadmin', ['filter' => 'superadmin', 'namespace' => 'App\Controllers\Superadmin'], function($routes) {
    
    // Dashboard
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');
    
    // Users Management
    $routes->group('users', function($routes) {
        $routes->get('/', 'UserController::index');
        $routes->get('create', 'UserController::create');
        $routes->post('store', 'UserController::store');
        $routes->get('view/(:num)', 'UserController::view/$1');
        $routes->get('edit/(:num)', 'UserController::edit/$1');
        $routes->post('update/(:num)', 'UserController::update/$1');
        $routes->delete('delete/(:num)', 'UserController::delete/$1');
        $routes->post('toggle-active/(:num)', 'UserController::toggleActive/$1');
        $routes->post('reset-password/(:num)', 'UserController::resetPassword/$1');
        $routes->get('export', 'UserController::export');
    });
    
    // Applications Management
    $routes->group('applications', function($routes) {
        $routes->get('/', 'ApplicationController::index');
        $routes->get('view/(:num)', 'ApplicationController::view/$1');
        $routes->post('toggle-active/(:num)', 'ApplicationController::toggleActive/$1');
        $routes->delete('delete/(:num)', 'ApplicationController::delete/$1');
        $routes->get('statistics/(:num)', 'ApplicationController::statistics/$1');
        $routes->get('export', 'ApplicationController::export');
    });
    
    // Roles Management
    $routes->group('roles', function($routes) {
        $routes->get('/', 'RoleController::index');
        $routes->get('create', 'RoleController::create');
        $routes->post('store', 'RoleController::store');
        $routes->get('edit/(:num)', 'RoleController::edit/$1');
        $routes->post('update/(:num)', 'RoleController::update/$1');
        $routes->delete('delete/(:num)', 'RoleController::delete/$1');
        $routes->post('update-permissions/(:num)', 'RoleController::updatePermissions/$1');
    });
    
    // Activity Logs
    $routes->group('logs', function($routes) {
        $routes->get('/', 'LogController::index');
        $routes->get('view/(:num)', 'LogController::view/$1');
        $routes->get('filter', 'LogController::filter');
        $routes->delete('delete/(:num)', 'LogController::delete/$1');
        $routes->post('clean-old', 'LogController::cleanOld');
        $routes->get('export', 'LogController::export');
    });
    
    // Reports
    $routes->group('reports', function($routes) {
        $routes->get('/', 'ReportController::index');
        $routes->get('user-growth', 'ReportController::userGrowth');
        $routes->get('application-usage', 'ReportController::applicationUsage');
        $routes->get('activity-report', 'ReportController::activityReport');
        $routes->get('system-overview', 'ReportController::systemOverview');
        $routes->post('generate', 'ReportController::generate');
        $routes->get('export/(:any)', 'ReportController::export/$1');
    });
    
    // Settings
    $routes->group('settings', function($routes) {
        $routes->get('/', 'SettingController::index');
        $routes->post('update', 'SettingController::update');
        $routes->post('update-smtp', 'SettingController::updateSmtp');
        $routes->post('test-email', 'SettingController::testEmail');
    });
    
});

// ============================================================================
// OWNER ROUTES (Owner Only - or Superadmin)
// ============================================================================
$routes->group('owner', ['filter' => 'owner', 'namespace' => 'App\Controllers\Owner'], function($routes) {
    
    // Dashboard
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');
    
    // Datasets Management
    $routes->group('datasets', function($routes) {
        $routes->get('/', 'DatasetController::index');
        $routes->get('upload', 'DatasetController::upload');
        $routes->post('store', 'DatasetController::store');
        $routes->post('process-upload', 'DatasetController::processUpload');
        $routes->get('view/(:num)', 'DatasetController::view/$1');
        $routes->get('records/(:num)', 'DatasetController::records/$1');
        $routes->get('edit/(:num)', 'DatasetController::edit/$1');
        $routes->post('update/(:num)', 'DatasetController::update/$1');
        $routes->delete('delete/(:num)', 'DatasetController::delete/$1');
        $routes->post('update-schema/(:num)', 'DatasetController::updateSchema/$1');
        $routes->get('export/(:num)', 'DatasetController::export/$1');
        $routes->get('download-template', 'DatasetController::downloadTemplate');
    });
    
    // Statistics Management
    $routes->group('statistics', function($routes) {
        $routes->get('/', 'StatisticController::index');
        $routes->get('create', 'StatisticController::create');
        $routes->post('store', 'StatisticController::store');
        $routes->get('view/(:num)', 'StatisticController::view/$1');
        $routes->get('edit/(:num)', 'StatisticController::edit/$1');
        $routes->post('update/(:num)', 'StatisticController::update/$1');
        $routes->delete('delete/(:num)', 'StatisticController::delete/$1');
        $routes->post('toggle-active/(:num)', 'StatisticController::toggleActive/$1');
        $routes->post('calculate/(:num)', 'StatisticController::calculate/$1');
        $routes->post('duplicate/(:num)', 'StatisticController::duplicate/$1');
        $routes->get('export/(:num)', 'StatisticController::export/$1');
    });
    
    // Statistic Builder (AJAX)
    $routes->group('statistic-builder', function($routes) {
        $routes->post('preview', 'StatisticBuilderController::preview');
        $routes->get('get-datasets', 'StatisticBuilderController::getDatasets');
        $routes->post('get-fields', 'StatisticBuilderController::getFields');
        $routes->post('validate-config', 'StatisticBuilderController::validateConfig');
    });
    
    // Dashboards Management
    $routes->group('dashboards', function($routes) {
        $routes->get('/', 'DashboardController::index');
        $routes->get('create', 'DashboardController::create');
        $routes->post('store', 'DashboardController::store');
        $routes->get('view/(:num)', 'DashboardController::view/$1');
        $routes->get('edit/(:num)', 'DashboardController::edit/$1');
        $routes->post('update/(:num)', 'DashboardController::update/$1');
        $routes->delete('delete/(:num)', 'DashboardController::delete/$1');
        $routes->post('set-default/(:num)', 'DashboardController::setDefault/$1');
        $routes->post('toggle-public/(:num)', 'DashboardController::togglePublic/$1');
        $routes->post('regenerate-token/(:num)', 'DashboardController::regenerateToken/$1');
        $routes->get('builder/(:num)', 'DashboardController::builder/$1');
    });
    
    // Dashboard Widgets (AJAX)
    $routes->group('widgets', function($routes) {
        $routes->post('add', 'DashboardWidgetController::add');
        $routes->post('update/(:num)', 'DashboardWidgetController::update/$1');
        $routes->delete('delete/(:num)', 'DashboardWidgetController::delete/$1');
        $routes->post('update-position/(:num)', 'DashboardWidgetController::updatePosition/$1');
        $routes->post('batch-update-positions', 'DashboardWidgetController::batchUpdatePositions');
        $routes->post('toggle-visibility/(:num)', 'DashboardWidgetController::toggleVisibility/$1');
        $routes->post('duplicate/(:num)', 'DashboardWidgetController::duplicate/$1');
    });
    
    // Team Users Management
    $routes->group('users', function($routes) {
        $routes->get('/', 'UserManagementController::index');
        $routes->get('invite', 'UserManagementController::invite');
        $routes->post('send-invite', 'UserManagementController::sendInvite');
        $routes->get('edit-role/(:num)', 'UserManagementController::editRole/$1');
        $routes->post('update-role/(:num)', 'UserManagementController::updateRole/$1');
        $routes->delete('remove/(:num)', 'UserManagementController::remove/$1');
    });
    
    // Workspace Settings
    $routes->group('settings', function($routes) {
        $routes->get('/', 'SettingController::index');
        $routes->post('update-workspace', 'SettingController::updateWorkspace');
        $routes->post('update-appearance', 'SettingController::updateAppearance');
        $routes->post('upload-logo', 'SettingController::uploadLogo');
        $routes->delete('delete-logo', 'SettingController::deleteLogo');
    });
    
});

// ============================================================================
// VIEWER ROUTES (Viewer, Owner, or Superadmin)
// ============================================================================
$routes->group('viewer', ['filter' => 'viewer', 'namespace' => 'App\Controllers\Viewer'], function($routes) {
    
    // Dashboard
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');
    
    // View Dashboards
    $routes->group('dashboards', function($routes) {
        $routes->get('/', 'DashboardController::index');
        $routes->get('view/(:num)', 'DashboardController::view/$1');
        $routes->get('fullscreen/(:num)', 'DashboardController::fullscreen/$1');
    });
    
    // View Statistics
    $routes->group('statistics', function($routes) {
        $routes->get('/', 'StatisticViewController::index');
        $routes->get('view/(:num)', 'StatisticViewController::view/$1');
        $routes->get('export/(:num)', 'StatisticViewController::export/$1');
        $routes->post('refresh/(:num)', 'StatisticViewController::refresh/$1');
    });
    
    // Public Dashboard (No auth required for public dashboards)
    $routes->get('public/(:any)', 'PublicDashboardController::view/$1');
    
});

// ============================================================================
// API ROUTES (Optional - untuk AJAX/JSON responses)
// ============================================================================
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    
    // Statistics API (for real-time updates)
    $routes->post('statistics/calculate', 'StatisticApiController::calculate');
    $routes->get('statistics/data/(:num)', 'StatisticApiController::getData/$1');
    
    // Dashboard API
    $routes->get('dashboard/widgets/(:num)', 'DashboardApiController::getWidgets/$1');
    $routes->post('dashboard/refresh/(:num)', 'DashboardApiController::refresh/$1');
    
    // Dataset API
    $routes->get('dataset/fields/(:num)', 'DatasetApiController::getFields/$1');
    $routes->get('dataset/preview/(:num)', 'DatasetApiController::preview/$1');
    
});

// ============================================================================
// FALLBACK / 404
// ============================================================================
$routes->set404Override(function() {
    echo view('errors/html/error_404');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}