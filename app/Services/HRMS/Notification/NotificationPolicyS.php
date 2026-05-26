<?php

namespace App\Services\HRMS\Notification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationPolicyS
{
    /**
     * Determine if an email should be sent for a given event.
     */
    public function shouldSendEmail(string $eventType, array $context = []): bool
    {
        $eventType = strtolower(trim($eventType));

        switch ($eventType) {
            case 'employee_created':
            case 'credentials':
            case 'password_reset':
            case 'otp':
            case 'offer_letter':
            case 'appointment_letter':
            case 'confirmation_letter':
            case 'salary_revision':
            case 'experience_letter':
            case 'relieving_letter':
            case 'internship_certificate':
            case 'payslip_release':
            case 'payslip':
            case 'payslip_available':
            case 'enterprise_payslip_available':
                return true;

            case 'announcement':
            case 'announcement_published':
                $priority = strtolower((string) ($context['priority'] ?? 'normal'));
                return in_array($priority, ['urgent', 'important', 'high'], true);
            case 'probation_ending_reminder':
            case 'internship_ending_reminder':
                return false;

            case 'leave_rejected':
                // Calculate duration if not present
                $duration = (float) ($context['duration'] ?? 0);
                if ($duration <= 0 && !empty($context['start_date']) && !empty($context['end_date'])) {
                    try {
                        $start = Carbon::parse($context['start_date']);
                        $end = Carbon::parse($context['end_date']);
                        $duration = $start->diffInDays($end) + 1;
                    } catch (\Throwable $e) {
                        Log::warning('NotificationPolicyS: could not parse leave dates', ['error' => $e->getMessage()]);
                    }
                }

                $leaveType = strtolower((string) ($context['leave_type'] ?? ''));
                $emailRequired = (bool) ($context['email_required'] ?? false);

                // Email only if leave duration > 2 days OR is sick leave OR explicitly requested
                if ($duration > 2 || str_contains($leaveType, 'sick') || $emailRequired) {
                    return true;
                }
                return false;

            case 'reimbursement_approved':
            case 'reimbursement_rejected':
            case 'enterprise_reimbursement_approved':
            case 'enterprise_reimbursement_rejected':
                $amount = (float) ($context['amount'] ?? 0);
                $hasReason = !empty($context['reason']) || !empty($context['rejection_reason']);
                $emailRequired = (bool) ($context['email_required'] ?? false);

                // Fallback DB lookup if parameters are missing
                if ($amount <= 0 && !empty($context['reimbursement_id'])) {
                    try {
                        $reimb = DB::table('enterprise_reimbursements')
                            ->where('id', $context['reimbursement_id'])
                            ->first();
                        if ($reimb) {
                            $amount = (float) $reimb->amount;
                            if (empty($context['rejection_reason']) && !empty($reimb->rejection_reason)) {
                                $hasReason = true;
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('NotificationPolicyS: could not look up reimbursement', ['error' => $e->getMessage()]);
                    }
                }

                $isRejectedEvent = in_array($eventType, ['reimbursement_rejected', 'enterprise_reimbursement_rejected'], true);

                if ($amount >= 5000 || ($isRejectedEvent && $hasReason) || $emailRequired) {
                    return true;
                }
                return false;

            default:
                return false;
        }
    }

    /**
     * Determine if an FCM push should be sent for a given event.
     */
    public function shouldSendFcm(string $eventType, array $context = []): bool
    {
        $eventType = strtolower(trim($eventType));

        // Password resets / OTPs do NOT send FCM notifications
        if (in_array($eventType, ['password_reset', 'otp'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if a database notification should be created for a given event.
     */
    public function shouldCreateDbNotification(string $eventType, array $context = []): bool
    {
        $eventType = strtolower(trim($eventType));

        // Password resets / OTPs do NOT write to database notifications
        if (in_array($eventType, ['password_reset', 'otp'], true)) {
            return false;
        }

        return true;
    }
}
