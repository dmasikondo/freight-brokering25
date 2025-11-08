<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public $user;
    public $file;
    public $showUploadProfile = false;

    public function uploadDocuments()
    {
        // Validate the uploaded file
        $this->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        // Store the file
        $filename = time() . $this->file->getClientOriginalName() . $this->file->getClientOriginalExtension();
        $path = $this->file->storePubliclyAs('documents',$filename, 'public'); // Store in 'documents' directory

        // Create a new Document entry in the database
        $this->user->profileDocuments()->create([
            'filename' => $filename,
            'disk_path' => 'documents',
            'document_type' => 'company profile',
            'comment' => null,
        ]);

        // Reset form fields
        $this->reset('file');

        // Dispatch event to refresh profile completion
        $this->dispatch('profile-updated');

        session()->flash('message', 'Documents uploaded successfully!');
    }

    private function  checkProfileCompletenessStatus()
    {
       $missing = [];
        
        // Check if user has any fleet
       
        if ($this->user->fleets()->count() === 0) {
            $missing[] = 'fleet';
        }
        
        // Check if user has any directors
        
        if ($this->user->directors()->count() < 2) {
            $missing[] = 'directors';
        }
        
        // Check if user has any trade references
        if ($this->user->traderefs()->count() < 3) {
            $missing[] = 'trade references';
        }
        
       if(!empty($missing) && $this->user->profileDocuments()->count()<1)    {
        $this->showUploadProfile = true;
       }
       else{
        $this->showUploadProfile = false;
       }
    }

    public function mount($user = null)
    {
        $this->user = auth()->user();
        $this->checkProfileCompletenessStatus();
    }
}; ?>

<div>
@if($showUploadProfile)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">

            <div class="flex items-center gap-3">
                <flux:icon name="cloud-arrow-up" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Your Company Profile</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Upload your company profile and we will complete the
                registration for you</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- File Upload Area -->

                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">File Upload</h4>
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center dark:border-slate-600 hover:border-lime-400 transition-colors cursor-pointer">
                        @if (session()->has('message'))
                            <flux:callout icon="check" color='green'>
                                <flux:callout.heading>Company Profile Upload</flux:callout.heading>
                                <flux:callout.text color='green'>
                                    {{ session('message') }}
                                </flux:callout.text>
                            </flux:callout>
                        @endif
                        <flux:icon name="document-arrow-up" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload pdf File</h4>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Upload a pdf file with your detailed company profile
                        </p>
                        <form wire:submit="uploadDocuments">


                            <div x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="uploading = true"
                                x-on:livewire-upload-finish="uploading = false"
                                x-on:livewire-upload-cancel="uploading = false"
                                x-on:livewire-upload-error="uploading = false"
                                x-on:livewire-upload-progress="progress = $event.detail.progress">


                                <!-- Progress Bar -->
                                <div x-show="uploading">
                                    <progress max="100" x-bind:value="progress"></progress>
                                </div>
                                <div class="flex gap-3 justify-center">
                                    <flux:input :invalid icon="arrow-up-on-square-stack" type="file"
                                        wire:model="file" />
                                    @error('file')
                                        <span class="text-red-400 italic text-xs">{{ $message }}</span>
                                    @enderror
                                    <flux:button variant="primary" type="submit">Upload File</flux:button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <flux:icon name="information-circle" class="w-4 h-4 inline mr-1" />
                        Other Supported formats: PDF, DOCX, JPG, (Max 2MB)
                    </div>
                </div>



            </div>

        </div>
    </div>
@endif


</div>
