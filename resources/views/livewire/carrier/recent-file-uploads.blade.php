<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

new class extends Component {

    public $user;
    public $showCompanyProfile = false;
    public $showLoadConfirmation = false;

    #[On('profile-updated')]
    public function checkUploadedFilesStatus()
    {
        if($this->user->profileDocuments()->count() >0){
            $this->showCompanyProfile = true;
        }
    }

    #[Computed]
    public function latestProfileDocumentCreatedTime()
    {
        $latestDocument = $this->user->profileDocuments()->latest()->first();
        return $latestDocument ? $latestDocument->created_at->diffForHumans() : null;
    }    

    public function mount($user = null)
    {
        $this->user = auth()->user();
        $this->checkUploadedFilesStatus();
    }    
}?>

<div>
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-700">
        <h4 class="font-medium text-gray-900 dark:text-white mb-4">Recent Uploads</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if ($showCompanyProfile)
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <flux:icon name="document-text" class="w-8 h-8 text-blue-500" />
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">Company Profile</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Uploaded {{ $this->latestProfileDocumentCreatedTime}}</div>
                    </div>
                </div>
            @elseif($showLoadConfirmation)
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <flux:icon name="document-text" class="w-8 h-8 text-green-500" />
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">Load Confirmation</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Uploaded 1 day ago</div>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="w-8 h-8">
                        <x-placeholder-pattern class="w-full h-full text-gray-400" />
                    </div>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">No recent files</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Waiting for your first file</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
