<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckProjectRenewalsCommand extends Command
{
    protected $signature = 'projects:check-renewals';

    protected $description = 'Check recurring projects (monthly/annually) for renewals and unpaid client alerts';

    public function handle(NotificationService $notifications): int
    {
        $this->info('Checking recurring project renewals and billing dates...');

        $recurringProjects = Project::query()
            ->whereIn('billing_type', ['monthly', 'annually'])
            ->whereNotNull('next_billing_date')
            ->where('status', '!=', 'Cancelled')
            ->with(['client', 'manager'])
            ->get();

        $alertsTriggered = 0;

        foreach ($recurringProjects as $project) {
            if ($project->isRenewalDue()) {
                $clientName = $project->client?->company_name ?? 'Client';
                $dueDateStr = $project->next_billing_date ? $project->next_billing_date->format('d M Y') : 'Due';

                $title = $project->billing_type === 'monthly'
                    ? "Monthly Payment Due: {$project->name}"
                    : "Annual Contract Renewal Due: {$project->name}";

                $body = "{$clientName} — Next billing date was/is {$dueDateStr}. Please review payment or generate the cycle invoice.";

                $notifications->sendToPermission(
                    'projects.view',
                    $title,
                    $body,
                    [
                        'category' => 'project_renewal',
                        'url' => route('projects.show', $project),
                        'color' => 'danger',
                    ]
                );

                $alertsTriggered++;
                $this->line("Alert triggered for project: {$project->name} ({$clientName})");
            }
        }

        $this->info("Completed. Total renewal alerts triggered: {$alertsTriggered}");

        return self::SUCCESS;
    }
}
