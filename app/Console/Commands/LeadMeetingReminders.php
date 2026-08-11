<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class LeadMeetingReminders extends Command
{
    protected $signature = 'leads:meeting-reminders';

    protected $description = 'Send meeting reminders for scheduled lead meetings';

    public function handle(NotificationService $notifications, WhatsAppService $whatsapp): int
    {
        $localNow = now(setting('general.timezone') ?: config('app.timezone'));

        $due = LeadFollowup::query()
            ->where('type', 'meeting')
            ->whereIn('status', ['scheduled', 'pending'])
            ->where('reminder', 1)
            ->whereNull('reminded_at')
            ->whereDate('follow_up_date', $localNow->toDateString())
            ->whereTime('follow_up_time', '<=', $localNow->addMinutes(10)->format('H:i:s'))
            ->with('lead.owner')
            ->get();

        if ($due->isEmpty()) {
            return self::SUCCESS;
        }

        $managers = User::query()->active()->whereHas('roles', fn ($q) => $q->whereIn('name', ['Sales Manager', 'Administrator', 'Super Admin']))->get();

        foreach ($due as $followup) {
            $lead = $followup->lead;
            $when = $followup->follow_up_date->format('M d, Y').' at '.($followup->follow_up_time ? date('h:i A', strtotime($followup->follow_up_time)) : '');
            $title = 'Meeting reminder: '.($lead?->company_name ?? 'Lead');
            $message = $when.' ('.($lead?->lead_number ?? '').')';

            $recipients = $managers;
            if ($lead?->owner_id) {
                $recipients = $managers->merge([User::find($lead->owner_id)])->unique('id');
            }

            $notifications->sendMany($recipients, $title, $message, [
                'category' => 'meeting',
                'url' => $lead ? route('crm.leads.show', $lead) : null,
                'color' => 'warning',
            ]);

            foreach ($recipients as $user) {
                if ($user->whatsapp || $user->phone) {
                    $whatsapp->send($user->whatsapp ?: $user->phone, $title.': '.$message);
                }
            }

            $followup->update(['reminded_at' => now()]);
            $this->info("Reminder sent for follow-up #{$followup->id}");
        }

        return self::SUCCESS;
    }
}