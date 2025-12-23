<?php

/**
 * ============================================================================
 * FILTERS CONFIGURATION
 * ============================================================================
 * 
 * Path: app/Config/Filters.php
 * 
 * Deskripsi:
 * Konfigurasi untuk mendaftarkan filters dan route mappings.
 * 
 * IMPORTANT: File ini harus di-copy ke app/Config/Filters.php
 * dan replace/merge dengan file existing.
 * ============================================================================
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, string>
     * @phpstan-var array<string, class-string>
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        
        // Custom Filters
        'auth'          => \App\Filters\AuthFilter::class,
        'guest'         => \App\Filters\GuestFilter::class,
        'superadmin'    => \App\Filters\SuperadminFilter::class,
        'owner'         => \App\Filters\OwnerFilter::class,
        'viewer'        => \App\Filters\ViewerFilter::class,
        'cors'          => \App\Filters\CorsFilter::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array<string, array<string, array<string, string>>>|array<string, list<string>>
     */
    public array $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
            'invalidchars',
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [
        // Guest pages (only for non-logged users)
        'guest' => [
            'before' => [
                'login',
                'register',
                'forgot-password',
                'reset-password/*'
            ]
        ],
        
        // Auth required (all logged-in users)
        'auth' => [
            'before' => [
                'dashboard',
                'profile',
                'profile/*',
                'superadmin',
                'superadmin/*',
                'owner',
                'owner/*',
                'viewer',
                'viewer/*'
            ]
        ],
        
        // Superadmin only
        'superadmin' => [
            'before' => [
                'superadmin',
                'superadmin/*'
            ]
        ],
        
        // Owner only (atau Superadmin)
        'owner' => [
            'before' => [
                'owner',
                'owner/*'
            ]
        ],
        
        // Viewer only (atau Owner/Superadmin)
        'viewer' => [
            'before' => [
                'viewer',
                'viewer/*'
            ]
        ],
        
        // CORS for API (optional)
        // 'cors' => [
        //     'before' => ['api/*'],
        //     'after' => ['api/*']
        // ]
    ];
}