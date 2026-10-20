<?php
/**
 * functions.php - small presentation helpers shared by all views.
 */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'Pending'     => 'badge-status-pending',
        'In Progress' => 'badge-status-inprogress',
        'Completed'   => 'badge-status-completed',
        'Cancelled'   => 'badge-status-cancelled',
        default       => 'badge-status-pending',
    };
}

function priority_badge_class(string $priority): string
{
    return match ($priority) {
        'Urgent' => 'badge-priority-urgent',
        'High'   => 'badge-priority-high',
        'Medium' => 'badge-priority-medium',
        'Low'    => 'badge-priority-low',
        default  => 'badge-priority-medium',
    };
}

function format_dt(?string $datetime): string
{
    if (!$datetime) return '&mdash;';
    $ts = strtotime($datetime);
    return $ts ? date('M d, Y g:i A', $ts) : e($datetime);
}

function flash_message(): string
{
    $msg = $_GET['msg'] ?? null;
    $error = $_GET['error'] ?? null;

    $map = [
        'added'   => 'Maintenance request submitted successfully.',
        'updated' => 'Maintenance request updated successfully.',
        'deleted' => 'Maintenance request deleted.',
    ];

    if ($error) {
        return '<div class="alert alert-error" data-autohide><i class="fa-solid fa-triangle-exclamation"></i> ' . e($error) . '</div>';
    }
    if ($msg && isset($map[$msg])) {
        return '<div class="alert alert-success" data-autohide><i class="fa-solid fa-circle-check"></i> ' . e($map[$msg]) . '</div>';
    }
    return '';
}
