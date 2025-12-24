<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ============================================================================
 * INITIAL SEEDER
 * ============================================================================
 * 
 * Path: app/Database/Seeds/InitialSeeder.php
 * 
 * Deskripsi:
 * Seeder untuk mengisi data awal aplikasi:
 * - Roles (superadmin, owner, viewer)
 * - Users (1 superadmin, 2 owners, 2 viewers)
 * - Applications (2 workspaces)
 * 
 * Cara Run:
 * php spark db:seed InitialSeeder
 * ============================================================================
 */

class InitialSeeder extends Seeder
{
    public function run()
    {
        // ===================================================================
        // 1. INSERT ROLES (Skip if already exists)
        // ===================================================================
        
        echo "Checking Roles...\n";
        
        // Check if roles already exist
        $existingRoles = $this->db->table('roles')->countAll();
        
        if ($existingRoles > 0) {
            echo "⚠ Roles already exist, skipping role insertion\n";
        } else {
            echo "Inserting Roles...\n";
            
            $roles = [
                [
                    'role_name' => 'superadmin',
                    'description' => 'Full system access with all permissions',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'role_name' => 'owner',
                    'description' => 'Workspace owner with full CRUD access',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'role_name' => 'viewer',
                    'description' => 'Read-only access to dashboards and statistics',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            
            $this->db->table('roles')->insertBatch($roles);
            echo "✓ Roles inserted successfully\n";
        }
        
        // Get role IDs (whether inserted or already existing)
        $superadminRoleId = $this->db->table('roles')->where('role_name', 'superadmin')->get()->getRow()->id;
        $ownerRoleId = $this->db->table('roles')->where('role_name', 'owner')->get()->getRow()->id;
        $viewerRoleId = $this->db->table('roles')->where('role_name', 'viewer')->get()->getRow()->id;
        
        // ===================================================================
        // 2. INSERT USERS (Skip if already exists)
        // ===================================================================
        
        echo "Checking Users...\n";
        
        // Check if superadmin already exists
        $existingSuperadmin = $this->db->table('users')
            ->where('email', 'superadmin@datastat.com')
            ->countAllResults();
        
        if ($existingSuperadmin > 0) {
            echo "⚠ Users already exist, skipping user insertion\n";
            
            // Get existing user IDs
            $superadminId = $this->db->table('users')->where('email', 'superadmin@datastat.com')->get()->getRow()->id;
            $ownerBpsId = $this->db->table('users')->where('email', 'owner.bps@datastat.com')->get()->getRow()->id ?? null;
            $ownerDinkesId = $this->db->table('users')->where('email', 'owner.dinkes@datastat.com')->get()->getRow()->id ?? null;
            $viewerBpsId = $this->db->table('users')->where('email', 'viewer.bps@datastat.com')->get()->getRow()->id ?? null;
            $viewerDinkesId = $this->db->table('users')->where('email', 'viewer.dinkes@datastat.com')->get()->getRow()->id ?? null;
        } else {
            echo "Inserting Users...\n";
        
        $users = [
            // SUPERADMIN
            [
                'email' => 'superadmin@datastat.com',
                'password' => password_hash('superadmin123', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Super Administrator',
                'bidang' => 'System Administration',
                'role_id' => $superadminRoleId,
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ],
            // OWNER 1 - BPS
            [
                'email' => 'owner.bps@datastat.com',
                'password' => password_hash('owner123', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Ahmad Rizki',
                'bidang' => 'Statistik & Data',
                'role_id' => $ownerRoleId,
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ],
            // OWNER 2 - DINKES
            [
                'email' => 'owner.dinkes@datastat.com',
                'password' => password_hash('owner123', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Siti Aminah',
                'bidang' => 'Kesehatan Masyarakat',
                'role_id' => $ownerRoleId,
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ],
            // VIEWER 1 - BPS
            [
                'email' => 'viewer.bps@datastat.com',
                'password' => password_hash('viewer123', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Budi Santoso',
                'bidang' => 'Analisis Data',
                'role_id' => $viewerRoleId,
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ],
            // VIEWER 2 - DINKES
            [
                'email' => 'viewer.dinkes@datastat.com',
                'password' => password_hash('viewer123', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Dewi Lestari',
                'bidang' => 'Monitoring & Evaluasi',
                'role_id' => $viewerRoleId,
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('users')->insertBatch($users);
        
        // Get user IDs
        $superadminId = $this->db->table('users')->where('email', 'superadmin@datastat.com')->get()->getRow()->id;
        $ownerBpsId = $this->db->table('users')->where('email', 'owner.bps@datastat.com')->get()->getRow()->id;
        $ownerDinkesId = $this->db->table('users')->where('email', 'owner.dinkes@datastat.com')->get()->getRow()->id;
        $viewerBpsId = $this->db->table('users')->where('email', 'viewer.bps@datastat.com')->get()->getRow()->id;
        $viewerDinkesId = $this->db->table('users')->where('email', 'viewer.dinkes@datastat.com')->get()->getRow()->id;
        
        echo "✓ Users inserted successfully\n";
        }
        
        // ===================================================================
        // 3. INSERT APPLICATIONS (Skip if already exists)
        // ===================================================================
        
        echo "Checking Applications...\n";
        
        // Check if applications already exist
        $existingApps = $this->db->table('applications')->countAll();
        
        if ($existingApps > 0) {
            echo "⚠ Applications already exist, skipping application insertion\n";
            
            // Get existing application IDs
            $bpsApp = $this->db->table('applications')->where('app_name', 'BPS Kalimantan Selatan')->get()->getRow();
            $dinkesApp = $this->db->table('applications')->where('app_name', 'Dinas Kesehatan Kalsel')->get()->getRow();
            
            $bpsAppId = $bpsApp ? $bpsApp->id : null;
            $dinkesAppId = $dinkesApp ? $dinkesApp->id : null;
            
            // If not exist, create them
            if (!$bpsAppId && $ownerBpsId) {
                $this->db->table('applications')->insert([
                    'app_name' => 'BPS Kalimantan Selatan',
                    'app_description' => 'Sistem Statistik Daerah BPS Provinsi Kalimantan Selatan',
                    'owner_id' => $ownerBpsId,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $bpsAppId = $this->db->insertID();
                echo "✓ BPS Application created\n";
            }
            
            if (!$dinkesAppId && $ownerDinkesId) {
                $this->db->table('applications')->insert([
                    'app_name' => 'Dinas Kesehatan Kalsel',
                    'app_description' => 'Sistem Informasi Kesehatan Dinas Kesehatan Provinsi Kalimantan Selatan',
                    'owner_id' => $ownerDinkesId,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $dinkesAppId = $this->db->insertID();
                echo "✓ Dinkes Application created\n";
            }
        } else {
            echo "Inserting Applications...\n";
        
        $applications = [
            [
                'app_name' => 'BPS Kalimantan Selatan',
                'app_description' => 'Sistem Statistik Daerah BPS Provinsi Kalimantan Selatan',
                'owner_id' => $ownerBpsId,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'app_name' => 'Dinas Kesehatan Kalsel',
                'app_description' => 'Sistem Informasi Kesehatan Dinas Kesehatan Provinsi Kalimantan Selatan',
                'owner_id' => $ownerDinkesId,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('applications')->insertBatch($applications);
        
        // Get application IDs
        $bpsAppId = $this->db->table('applications')->where('app_name', 'BPS Kalimantan Selatan')->get()->getRow()->id;
        $dinkesAppId = $this->db->table('applications')->where('app_name', 'Dinas Kesehatan Kalsel')->get()->getRow()->id;
        
        echo "✓ Applications inserted successfully\n";
        }
        
        // ===================================================================
        // 4. INSERT USER APPLICATIONS (Skip if already assigned)
        // ===================================================================
        
        echo "Checking User Applications...\n";
        
        // Only insert if we have valid IDs and assignments don't exist yet
        if ($ownerBpsId && $bpsAppId && $viewerBpsId && $ownerDinkesId && $dinkesAppId && $viewerDinkesId) {
            
            // Check if already assigned
            $existingAssignments = $this->db->table('user_applications')->countAll();
            
            if ($existingAssignments > 0) {
                echo "⚠ User applications already assigned, skipping\n";
            } else {
                echo "Assigning Users to Applications...\n";
        
        $userApplications = [
            // Owner BPS ke BPS App
            [
                'user_id' => $ownerBpsId,
                'application_id' => $bpsAppId,
                'role_id' => $ownerRoleId,
                'created_at' => date('Y-m-d H:i:s')
            ],
            // Viewer BPS ke BPS App
            [
                'user_id' => $viewerBpsId,
                'application_id' => $bpsAppId,
                'role_id' => $viewerRoleId,
                'created_at' => date('Y-m-d H:i:s')
            ],
            // Owner Dinkes ke Dinkes App
            [
                'user_id' => $ownerDinkesId,
                'application_id' => $dinkesAppId,
                'role_id' => $ownerRoleId,
                'created_at' => date('Y-m-d H:i:s')
            ],
            // Viewer Dinkes ke Dinkes App
            [
                'user_id' => $viewerDinkesId,
                'application_id' => $dinkesAppId,
                'role_id' => $viewerRoleId,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('user_applications')->insertBatch($userApplications);
        
        echo "✓ User Applications assigned successfully\n";
            }
        } else {
            echo "⚠ Skipping user applications (missing user or application IDs)\n";
        }
        
        // ===================================================================
        // SUMMARY
        // ===================================================================
        
        echo "\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "✓ SEEDING COMPLETED SUCCESSFULLY!\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "\n";
        echo "📊 SEEDED DATA SUMMARY:\n";
        echo "─────────────────────────────────────────────────────────────\n";
        echo "Roles:        3 (superadmin, owner, viewer)\n";
        echo "Users:        5 (1 superadmin, 2 owners, 2 viewers)\n";
        echo "Applications: 2 workspaces\n";
        echo "Assignments:  4 user-workspace mappings\n";
        echo "\n";
        echo "👤 AKUN LOGIN:\n";
        echo "─────────────────────────────────────────────────────────────\n";
        echo "👑 SUPERADMIN:\n";
        echo "   Email:    superadmin@datastat.com\n";
        echo "   Password: superadmin123\n";
        echo "   Access:   Full system access\n";
        echo "\n";
        echo "💼 OWNER - BPS Kalimantan Selatan:\n";
        echo "   Email:    owner.bps@datastat.com\n";
        echo "   Password: owner123\n";
        echo "   Access:   BPS Kalsel workspace\n";
        echo "\n";
        echo "💼 OWNER - Dinas Kesehatan Kalsel:\n";
        echo "   Email:    owner.dinkes@datastat.com\n";
        echo "   Password: owner123\n";
        echo "   Access:   Dinkes Kalsel workspace\n";
        echo "\n";
        echo "👁️  VIEWER - BPS:\n";
        echo "   Email:    viewer.bps@datastat.com\n";
        echo "   Password: viewer123\n";
        echo "   Access:   BPS Kalsel (read-only)\n";
        echo "\n";
        echo "👁️  VIEWER - Dinkes:\n";
        echo "   Email:    viewer.dinkes@datastat.com\n";
        echo "   Password: viewer123\n";
        echo "   Access:   Dinkes Kalsel (read-only)\n";
        echo "\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "🚀 Aplikasi siap digunakan!\n";
        echo "════════════════════════════════════════════════════════════\n";
    }
}