<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'slug' => 'reminders',
                'name' => 'Reminders',
                'url_local' => 'http://127.0.0.1:9000',
                'url_production' => 'https://reminder.shahbaz.app',
                'url_staging' => 'https://reminder-staging.shahbaz.app',
                'login_path' => null,
                'target_param' => 'target',
                'is_active' => true,
            ],
            [
                'slug' => 'adminpanel',
                'name' => 'AdminPanel',
                'url_local' => 'http://127.0.0.1:9001',
                'url_production' => 'https://admin.shahbaz.app',
                'url_staging' => 'https://admin-staging.shahbaz.app',
                'login_path' => '/sso/login',
                'target_param' => 'redirect',
                'is_active' => true,
            ],
            [
                'slug' => 'api',
                'name' => 'Centralized API',
                'url_local' => 'http://127.0.0.1:8000',
                'url_production' => 'https://api.shahbaz.app',
                'url_staging' => 'https://api-staging.shahbaz.app',
                'login_path' => null,
                'target_param' => 'target',
                'is_active' => true,
            ],
        ];

        foreach ($rows as $row) {
            Application::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
