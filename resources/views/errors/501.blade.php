<x-layouts.app :title="__('User Login | Transpartner Logistics')">

    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-lime-50 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
        <div
            class="max-w-md w-full text-center p-8 md:p-10 bg-white/80 backdrop-blur-sm border border-lime-200 rounded-3xl shadow-2xl transition-all duration-500 dark:bg-slate-800/80 dark:border-lime-500/30">

            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl md:text-5xl font-black text-lime-600 tracking-wider dark:text-lime-400">
                    TRANSPARTNER
                    <span class="text-gray-700 font-light ml-1 dark:text-gray-300">
                        LOGISTICS
                    </span>
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Sign in to your dashboard</p>
            </div>

            <div class="space-y-6">
                <form method="POST" action="{{ route('login') }}" class="text-left">
                    @csrf

                    <div class="relative mt-5 group">
                        <input id="email" type="email" name="email" required autocomplete="email"
                            class="w-full px-4 py-3 pt-5 border border-lime-300 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition duration-150 ease-in-out dark:bg-slate-700 dark:border-slate-600 dark:text-white peer"
                            placeholder=" " {{-- IMPORTANT: The placeholder must be empty or a single space --}}>
                        <label for="email"
                            class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-500 dark:text-gray-400 pointer-events-none origin-left 
                                  transition-all duration-200 transform 
                                  peer-focus:top-2 peer-focus:text-xs peer-focus:text-lime-600 dark:peer-focus:text-lime-400 
                                  peer-focus:scale-75 
                                  peer-not-placeholder-shown:top-2 peer-not-placeholder-shown:text-xs peer-not-placeholder-shown:text-lime-600 dark:peer-not-placeholder-shown:text-lime-400
                                  peer-not-placeholder-shown:scale-75">
                            Email Address
                        </label>
                        {{-- @error('email') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror --}}
                    </div>

                    <div class="relative mt-8 group">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="w-full px-4 py-3 pt-5 border border-lime-300 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition duration-150 ease-in-out dark:bg-slate-700 dark:border-slate-600 dark:text-white peer"
                            placeholder=" " {{-- IMPORTANT: The placeholder must be empty or a single space --}}>
                        <label for="password"
                            class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-500 dark:text-gray-400 pointer-events-none origin-left 
                                  transition-all duration-200 transform 
                                  peer-focus:top-2 peer-focus:text-xs peer-focus:text-lime-600 dark:peer-focus:text-lime-400 
                                  peer-focus:scale-75 
                                  peer-not-placeholder-shown:top-2 peer-not-placeholder-shown:text-xs peer-not-placeholder-shown:text-lime-600 dark:peer-not-placeholder-shown:text-lime-400
                                  peer-not-placeholder-shown:scale-75">
                            Password
                        </label>
                        {{-- @error('password') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror --}}
                    </div>


                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center">
                            <input id="remember_me" type="checkbox"
                                class="rounded border-gray-300 text-lime-600 shadow-sm focus:ring-lime-500 dark:bg-slate-700 dark:border-slate-600"
                                name="remember">
                            <label for="remember_me"
                                class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Remember me</label>
                        </div>

                        <a href="{{ route('password.request') }}"
                            class="text-sm font-semibold text-lime-600 hover:text-lime-700 transition-colors duration-200 dark:text-lime-400 dark:hover:text-lime-300">
                            Forgot Password?
                        </a>
                    </div>

                    <button type="submit"
                        class="w-full mt-6 px-8 py-3 bg-gradient-to-r from-lime-500 to-lime-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.01] flex items-center justify-center gap-2">
                        <flux:icon name="lock-closed" class="text-white"></flux:icon>
                        Log In
                    </button>
                </form>
            </div>

            <div class="border-t border-gray-200 pt-6 mt-8 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Don't have an account?
                    <a href="{{ route('register') }}"
                        class="font-bold text-lime-600 hover:text-lime-700 transition-colors duration-200 dark:text-lime-400 dark:hover:text-lime-300 ml-1">
                        Register Now
                    </a>
                </p>
            </div>
        </div>

        <div
            class="fixed top-10 left-10 w-20 h-20 bg-lime-200 rounded-full blur-xl opacity-30 animate-pulse dark:bg-lime-900">
        </div>
        <div
            class="fixed bottom-10 right-10 w-32 h-32 bg-lime-300 rounded-full blur-xl opacity-20 animate-bounce dark:bg-lime-800">
        </div>
    </div>

</x-layouts.app>
