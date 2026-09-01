<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeadFollowup;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

class LeadMeetingRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:meeting-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send lead meeting and call reminders exactly twice: 1 hour before and 30 minutes before';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now('Asia/Karachi');

        // Fetch pending or scheduled follow-ups with reminder enabled
        $followups = LeadFollowup::with(['lead.owner'])
            ->where('reminder', true)
            ->whereIn('status', ['scheduled', 'pending'])
            ->get();

        $remindedCount = 0;

        foreach ($followups as $followup) {
            $lead = $followup->lead;
            if (!$lead) {
                continue;
            }

            $dateStr = $followup->follow_up_date->format('Y-m-d');
            $timeStr = $followup->follow_up_time;

            // ── Case 1: Date Only (Time is null/empty) ───────────────────────
            if (empty($timeStr)) {
                // Send single reminder on scheduled date (today) if not yet reminded
                if ($now->format('Y-m-d') === $dateStr && empty($followup->reminded_at)) {
                    $when = date('M d, Y', strtotime($dateStr));
                    $contactDetails = implode(' · ', array_filter([$lead->contact_person, $lead->phone ?: $lead->email]));
                    $ownerName = $lead->owner?->name ?? 'Unassigned';

                    $typeLabel = $followup->type === 'meeting' ? 'Meeting' : 'Call';
                    $title = "Today's {$typeLabel} Reminder — " . $lead->company_name;
                    $msg = "{$typeLabel} scheduled for today ({$when})\nBusiness: {$lead->company_name}\nContact: " . ($contactDetails ?: 'N/A') . "\nLead Owner: {$ownerName}";

                    $ns = app(NotificationService::class);

                    $targetRoles = ['Sales Manager', 'Sales Executive', 'Administrator', 'Super Admin'];
                    $managers = User::query()->active()->whereHas('roles', fn ($q) => $q->whereIn('name', $targetRoles))->get();

                    $recipients = collect($managers);
                    if ($lead->owner_id && ($owner = User::find($lead->owner_id))) {
                        $recipients->push($owner);
                    }
                    if ($followup->assigned_to && ($assignee = User::find($followup->assigned_to))) {
                        $recipients->push($assignee);
                    }
                    if ($followup->created_by && ($creator = User::find($followup->created_by))) {
                        $recipients->push($creator);
                    }
                    $recipients = $recipients->unique('id');

                    $ns->sendMany($recipients, $title, $msg, [
                        'category' => $followup->type === 'meeting' ? 'meeting' : 'followup',
                        'url' => route('crm.leads.show', $lead),
                        'color' => 'info',
                        'include_self' => true,
                    ]);

                    $followup->update([
                        'reminded_at' => now(),
                        'reminded_60m_at' => now(),
                        'reminded_30m_at' => now(),
                    ]);

                    $remindedCount++;
                }
                continue;
            }

            // ── Case 2: Time Specified (Send 60m and 30m before) ─────────────
            if (strlen($timeStr) === 5) {
                $timeStr .= ':00';
            }

            try {
                $followupDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $timeStr, 'Asia/Karachi');
            } catch (\Exception $e) {
                continue;
            }

            // Calculate minutes remaining until meeting (positive = future, negative = past)
            $diffInMinutes = (int) $now->diffInMinutes($followupDateTime, false);

            $stage = null;
            $titleText = '';

            // Stage 1: 1 Hour Before (between 31m and 65m away)
            if ($diffInMinutes > 30 && $diffInMinutes <= 65 && empty($followup->reminded_60m_at)) {
                $stage = '60m';
                $titleText = 'in 1 hour';
            }
            // Stage 2: 30 Minutes Before (between -15m and 30m away)
            elseif ($diffInMinutes >= -15 && $diffInMinutes <= 30 && empty($followup->reminded_30m_at)) {
                $stage = '30m';
                $titleText = 'in 30 minutes';
            }

            if ($stage !== null) {
                $when = date('M d, Y', strtotime($dateStr)) . ' at ' . date('h:i A', strtotime($timeStr));
                $contactDetails = implode(' · ', array_filter([$lead->contact_person, $lead->phone ?: $lead->email]));
                $ownerName = $lead->owner?->name ?? 'Unassigned';

                $typeLabel = $followup->type === 'meeting' ? 'Meeting' : 'Call';
                $title = "Reminder: {$typeLabel} {$titleText} — " . $lead->company_name;
                $msg = "{$typeLabel} scheduled for {$when}\nBusiness: {$lead->company_name}\nContact: " . ($contactDetails ?: 'N/A') . "\nLead Owner: {$ownerName}";

                $ns = app(NotificationService::class);

                $targetRoles = ['Sales Manager', 'Sales Executive', 'Administrator', 'Super Admin'];
                $managers = User::query()->active()->whereHas('roles', fn ($q) => $q->whereIn('name', $targetRoles))->get();

                $recipients = collect($managers);
                if ($lead->owner_id) {
                    $owner = User::find($lead->owner_id);
                    if ($owner) {
                        $recipients->push($owner);
                    }
                }
                if ($followup->assigned_to) {
                    $assignee = User::find($followup->assigned_to);
                    if ($assignee) {
                        $recipients->push($assignee);
                    }
                }
                if ($followup->created_by) {
                    $creator = User::find($followup->created_by);
                    if ($creator) {
                        $recipients->push($creator);
                    }
                }
                $recipients = $recipients->unique('id');

                $ns->sendMany($recipients, $title, $msg, [
                    'category' => $followup->type === 'meeting' ? 'meeting' : 'followup',
                    'url' => route('crm.leads.show', $lead),
                    'color' => $stage === '30m' ? 'warning' : 'info',
                    'include_self' => true,
                ]);

                // Update respective reminder timestamp
                if ($stage === '60m') {
                    $followup->update(['reminded_60m_at' => now(), 'reminded_at' => now()]);
                } else {
                    $followup->update(['reminded_30m_at' => now(), 'reminded_at' => now()]);
                }

                $remindedCount++;
            }
        }

        $this->info("Successfully sent {$remindedCount} meeting reminders.");
        return Command::SUCCESS;
    }
}
