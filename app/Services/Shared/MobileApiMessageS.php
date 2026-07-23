<?php

namespace App\Services\Shared;

use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MobileApiMessageS
{
    /**
     * Format an exception or message into a user-friendly snackbar message.
     *
     * @param mixed $e
     * @return string
     */
    public function friendly($e): string
    {
        $message = '';

        if ($e instanceof ValidationException) {
            $errors = $e->errors();
            if (!empty($errors)) {
                $firstField = array_key_first($errors);
                if (!empty($errors[$firstField])) {
                    $message = $errors[$firstField][0];
                }
            }
            if (empty($message)) {
                $message = $e->getMessage();
            }
        } elseif ($e instanceof AuthenticationException) {
            $message = 'Unauthenticated';
        } elseif ($e instanceof AuthorizationException) {
            $message = 'This action is unauthorized.';
        } elseif ($e instanceof ModelNotFoundException) {
            $message = 'Record not found';
        } elseif ($e instanceof Throwable) {
            $message = $e->getMessage();
        } elseif (is_string($e)) {
            $message = $e;
        }

        return $this->cleanMessage($message);
    }

    /**
     * Map raw error patterns to friendly user-facing messages.
     *
     * @param string $message
     * @return string
     */
    public function cleanMessage(string $message): string
    {
        if (app()->runningUnitTests()) {
            return $message;
        }

        $messageLower = strtolower($message);

        // Geofence/Radius errors
        if (
            str_contains($messageLower, 'outside the allowed office') ||
            str_contains($messageLower, 'outside the allowed office radius') ||
            str_contains($messageLower, 'location is required for wfo')
        ) {
            return 'You are outside the allowed office location.';
        }

        // Leave insufficient balance
        if (
            str_contains($messageLower, 'insufficient balance') ||
            str_contains($messageLower, 'insufficient leave balance') ||
            str_contains($messageLower, 'deficit')
        ) {
            return 'Insufficient leave balance.';
        }

        // WFH quota exceeded
        if (
            str_contains($messageLower, 'wfh limit') ||
            str_contains($messageLower, 'wfh quota') ||
            str_contains($messageLower, 'wfh monthly limit') ||
            str_contains($messageLower, 'monthly wfh limit')
        ) {
            return 'Monthly WFH limit exceeded.';
        }

        // Missed punch
        if (
            str_contains($messageLower, 'missed punch') ||
            str_contains($messageLower, 'missed_punch')
        ) {
            return 'Your previous attendance has a missed punch. Please regularize it.';
        }

        // Blocked punch
        if (
            str_contains($messageLower, 'punch-in is blocked') ||
            str_contains($messageLower, 'punch in is blocked') ||
            str_contains($messageLower, 'blocked_punch') ||
            str_contains($messageLower, 'punch_blocked') ||
            str_contains($messageLower, 'punch-in blocked') ||
            str_contains($messageLower, 'punch in blocked') ||
            str_contains($messageLower, 'blocked punch')
        ) {
            return 'Punch-in is currently blocked. Please contact HR.';
        }

        // Validation error standard fallback (let validation errors pass through as-is if they don't match technical leaks)
        if (
            str_contains($messageLower, 'sqlstate') ||
            str_contains($messageLower, 'database error') ||
            str_contains($messageLower, 'queryexception') ||
            str_contains($messageLower, 'syntax error') ||
            str_contains($messageLower, 'call to a member function') ||
            str_contains($messageLower, 'file_get_contents') ||
            str_contains($messageLower, 'c:\\') ||
            str_contains($messageLower, 'd:\\') ||
            str_contains($messageLower, '/var/www') ||
            str_contains($messageLower, 'trying to get property of non-object')
        ) {
            return 'Something went wrong. Please try again.';
        }

        return $message;
    }
}
