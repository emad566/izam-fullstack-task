<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderPlacedNotification;
use App\Models\Admin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOrderNotificationToAdmin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        // Get all active admins
        $admins = Admin::all();

        // Send email notification to each admin
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new OrderPlacedNotification($event->order, $event->user));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderPlaced $event, $exception): void
    {
        // Log the failure or handle it as needed
        Log::error('Failed to send order notification', [
            'order_id' => $event->order->id,
            'user_id' => $event->user->id,
            'exception' => $exception->getMessage()
        ]);
    }
}
