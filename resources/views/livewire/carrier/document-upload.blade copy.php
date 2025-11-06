<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

new class extends Component {
    use WithFileUploads;

    public $user;
    public $showUpload = false;
    public $companyRegistration;
    public $insuranceCertificate;
    public $operatingLicense;

    public function toggleUpload()
    {
        $this->showUpload = !$this->showUpload;
    }

    public function uploadDocuments()
    {
        $this->validate([
            'companyRegistration' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'insuranceCertificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'operatingLicense' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Store files and update database
        $this->user->update([
            'company_registration_path' => $this->companyRegistration->store('company-documents'),
            'insurance_certificate_path' => $this->insuranceCertificate->store('company-documents'),
            'operating_license_path' => $this->operatingLicense->store('company-documents'),
            'documents_uploaded_at' => now(),
        ]);

        // Reset form
        $this->reset(['companyRegistration', 'insuranceCertificate', 'operatingLicense', 'showUpload']);
        
        // Dispatch event to refresh profile completion
        $this->dispatch('profile-updated');
        
        session()->flash('message', 'Documents uploaded successfully!');
    }
}; ?>

<div>
    @if(!$showUpload)
        <button 
            wire:click="toggleUpload"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Upload Company Documents
        </button>
    @else
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Company Documents</h3>
            
            <form wire:submit="uploadDocuments" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Registration *</label>
                    <input type="file" wire:model="companyRegistration" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('companyRegistration') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Insurance Certificate *</label>
                    <input type="file" wire:model="insuranceCertificate" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('insuranceCertificate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Operating License *</label>
                    <input type="file" wire:model="operatingLicense" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('operatingLicense') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex space-x-3 pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Upload All Documents
                    </button>
                    <button type="button" wire:click="toggleUpload" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif
    
    
</div>