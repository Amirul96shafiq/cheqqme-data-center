<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('links')->insert([
            [
                'title' => 'Company SharePoint',
                'url' => 'https://sharepoint.example.com',
                'description' => 'Main SharePoint access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Client Folder - Project Alpha',
                'url' => 'https://drive.google.com/project-alpha',
                'description' => 'Google Drive for Alpha project',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Internal Wiki',
                'url' => 'https://wiki.example.com',
                'description' => 'Company internal wiki for documentation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'HR Portal',
                'url' => 'https://hr.example.com',
                'description' => 'Human Resources portal for employee management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Project Management Tool',
                'url' => 'https://pmtool.example.com',
                'description' => 'Tool for managing projects and tasks',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Customer Support Portal',
                'url' => 'https://support.example.com',
                'description' => 'Portal for customer support and ticketing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
