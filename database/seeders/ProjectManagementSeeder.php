<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Project, Client, User, ProjectMember, ProjectTask, TaskComment, ProjectFile, ProjectTimeEntry, ProjectExpense, ProjectActivity};
use Carbon\Carbon;

class ProjectManagementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $client = Client::first();

        if (!$client) {
            $client = Client::create([
                'client_number' => 'CLI-0001',
                'company_name' => 'Acme Corporation Tech',
                'contact_person' => 'John Doe',
                'email' => 'acme@example.com',
                'phone' => '+1234567890',
                'status' => 'active',
            ]);
        }

        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        // Demo Project 1: Enterprise ERP Upgrade
        $project1 = Project::updateOrCreate(
            ['project_code' => 'PRJ-0001'],
            [
                'name' => 'ERP Platform Modernization & AI Integration',
                'client_id' => $client->id,
                'project_manager_id' => $admin->id,
                'description' => 'Modernizing legacy ERP architecture with Laravel 10, real-time analytics, and automated project accounting modules.',
                'project_type' => 'ERP',
                'start_date' => Carbon::now()->subDays(15),
                'deadline' => Carbon::now()->addDays(45),
                'priority' => 'High',
                'status' => 'In Progress',
                'budget' => 45000.00,
                'estimated_hours' => 320.00,
                'actual_hours' => 84.50,
                'progress' => 35,
                'notes' => 'Modernizing legacy ERP architecture with Laravel 10, real-time analytics, and automated project accounting modules.',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        // Assign Members
        foreach ($users->take(4) as $idx => $u) {
            ProjectMember::updateOrCreate(
                ['project_id' => $project1->id, 'user_id' => $u->id],
                [
                    'role' => $idx === 0 ? 'Project Manager' : ($idx === 1 ? 'Team Lead' : 'Developer'),
                    'assigned_date' => Carbon::now()->subDays(15),
                    'hours_allocation' => 80.00,
                    'status' => 'active',
                ]
            );
        }

        $task1 = ProjectTask::updateOrCreate(
            ['task_code' => 'TSK-0001'],
            [
                'title' => 'Design Project Management Migration & Models',
                'description' => 'Create robust database schema with proper indexes and cascade delete rules.',
                'project_id' => $project1->id,
                'assigned_to' => $admin->id,
                'assigned_by' => $admin->id,
                'priority' => 'Urgent',
                'status' => 'Completed',
                'start_date' => Carbon::now()->subDays(14),
                'deadline' => Carbon::now()->subDays(10),
                'estimated_hours' => 20.00,
                'actual_hours' => 18.50,
                'progress' => 100,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        $task2 = ProjectTask::updateOrCreate(
            ['task_code' => 'TSK-0002'],
            [
                'title' => 'Implement Mobile-Responsive Kanban Drag-and-Drop Board',
                'description' => 'Build interactive Kanban board supporting To Do, In Progress, Review, Testing, and Completed columns.',
                'project_id' => $project1->id,
                'assigned_to' => $admin->id,
                'assigned_by' => $admin->id,
                'priority' => 'High',
                'status' => 'In Progress',
                'start_date' => Carbon::now()->subDays(5),
                'deadline' => Carbon::now()->addDays(5),
                'estimated_hours' => 35.00,
                'actual_hours' => 16.00,
                'progress' => 60,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        // Expense
        ProjectExpense::updateOrCreate(
            ['project_id' => $project1->id, 'description' => 'AWS Cloud Infrastructure for Dev Environment'],
            [
                'category' => 'Hosting & Domain',
                'amount' => 450.00,
                'expense_date' => Carbon::now()->subDays(10),
                'added_by' => $admin->id,
            ]
        );

        // Time Entry
        ProjectTimeEntry::create([
            'project_id' => $project1->id,
            'task_id' => $task1->id,
            'user_id' => $admin->id,
            'start_time' => Carbon::now()->subDays(3)->setHour(9),
            'end_time' => Carbon::now()->subDays(3)->setHour(14),
            'duration_minutes' => 300,
            'description' => 'Completed initial setup and tested database migrations.',
            'is_running' => false,
        ]);

        // Activity Log
        ProjectActivity::log($project1->id, 'Project created', "Project '{$project1->name}' was initialized successfully.");
    }
}
