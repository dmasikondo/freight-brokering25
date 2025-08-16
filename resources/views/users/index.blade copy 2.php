<x-layouts.app :title="__('users')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-6">User List</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
        <!-- User Card -->
        <div class="bg-white shadow-md rounded-lg p-4 flex flex-col">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                        M G
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-medium text-gray-900">Michael Gough</h2>
                    <p class="text-sm text-gray-500">User Role: <span class="font-semibold">Carrier</span></p>
                    <p class="text-sm text-gray-500">Email: <span class="font-semibold">email@windster.com</span></p>
                    <p class="text-sm text-gray-500">Phone: <span class="font-semibold">0772421868</span></p>
                    <p class="text-sm text-gray-500">Created At: <span class="font-semibold">2023-08-15</span></p>
                    <p class="text-sm text-gray-500">Freight Uploads: <span class="font-semibold">5</span></p>
                    <p class="text-sm text-gray-500">Route Uploads: <span class="font-semibold">3</span></p>
                </div>
            </div>
        </div>
        
        <!-- Repeat the above card for more users -->
        <div class="bg-white shadow-md rounded-lg p-4 flex flex-col">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                        J D
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-medium text-gray-900">Jessica Doe</h2>
                    <p class="text-sm text-gray-500">User Role: <span class="font-semibold">Shipper</span></p>
                    <p class="text-sm text-gray-500">Email: <span class="font-semibold">jessica@shipping.com</span></p>
                    <p class="text-sm text-gray-500">Phone: <span class="font-semibold">0772421869</span></p>
                    <p class="text-sm text-gray-500">Created At: <span class="font-semibold">2023-08-14</span></p>
                    <p class="text-sm text-gray-500">Freight Uploads: <span class="font-semibold">8</span></p>
                    <p class="text-sm text-gray-500">Route Uploads: <span class="font-semibold">2</span></p>
                </div>
            </div>
        </div>
        
        <!-- Add more cards as needed -->
    </div>
</div>      

    </div>
</x-layouts.app>
