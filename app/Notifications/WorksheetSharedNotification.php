<?php
namespace App\Notifications;

use App\Models\WorksheetHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorksheetSharedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public WorksheetHeader $worksheet,
        public string $action // 'granted' or 'withdrawn'
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'worksheet_id' => $this->worksheet->id,
            'worksheet_name' => $this->worksheet->name,
            'owner_name' => auth()->user()->contact_person,
            'action' => $this->action,
            'message' => $this->action === 'granted' 
                ? "You have been granted access to collaborate on: {$this->worksheet->name}"
                : "Your access to worksheet '{$this->worksheet->name}' has been withdrawn.",
            'link' => route('worksheets.create', ['active_id' => $this->worksheet->id]),
        ];
    }
}