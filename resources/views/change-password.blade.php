<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="min-h-[70vh] flex items-start justify-center py-10 px-4">
        <div class="w-full max-w-md">

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

                <!-- Card Header -->
                <div class="bg-green-700 px-6 py-6">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 rounded-full p-2.5">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-white font-semibold text-base">Ganti Password</h2>
                            <p class="text-green-100 text-xs mt-0.5">{{ Auth::user()->userid }} &bull; {{ Auth::user()->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition
                    class="flex items-start gap-3 mx-6 mt-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="flex-1">{{ session('success') }}</span>
                    <button @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
                </div>
                @endif

                @if($errors->any())
                <div class="flex items-start gap-3 mx-6 mt-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <ul class="flex-1 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('change-password.update') }}"
                    x-data="passwordForm()"
                    class="px-6 py-6 space-y-5">
                    @csrf

                    <!-- Password Lama -->
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                            Password Lama
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input :type="showCurrent ? 'text' : 'password'"
                                name="current_password" id="current_password"
                                autocomplete="current-password"
                                placeholder="Masukkan password lama"
                                class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition @error('current_password') border-red-400 bg-red-50 @enderror">
                            <button type="button" @click="showCurrent = !showCurrent"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <!-- Password Baru -->
                    <div>
                        <label for="new_password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                            Password Baru
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input :type="showNew ? 'text' : 'password'"
                                name="new_password" id="new_password"
                                autocomplete="new-password"
                                placeholder="Masukkan password baru"
                                x-model="newPassword"
                                @input="checkStrength()"
                                class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition @error('new_password') border-red-400 bg-red-50 @enderror">
                            <button type="button" @click="showNew = !showNew"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Strength Meter -->
                        <div x-show="newPassword.length > 0" x-transition class="mt-2.5 space-y-1.5">
                            <div class="flex gap-1.5">
                                <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                    :class="strength >= 1 ? strengthColor : 'bg-gray-200'"></div>
                                <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                    :class="strength >= 2 ? strengthColor : 'bg-gray-200'"></div>
                                <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                    :class="strength >= 3 ? strengthColor : 'bg-gray-200'"></div>
                                <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                    :class="strength >= 4 ? strengthColor : 'bg-gray-200'"></div>
                            </div>
                            <span class="text-xs font-medium transition-colors" :class="strengthTextColor" x-text="strengthLabel"></span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Minimal 6 karakter, harus ada huruf dan angka.</p>
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                            Konfirmasi Password Baru
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input :type="showConfirm ? 'text' : 'password'"
                                name="new_password_confirmation" id="new_password_confirmation"
                                autocomplete="new-password"
                                placeholder="Ulangi password baru"
                                x-model="confirmPassword"
                                class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                                :class="confirmPassword.length > 0 && confirmPassword !== newPassword ? 'border-red-400 bg-red-50' : (confirmPassword.length > 0 && confirmPassword === newPassword ? 'border-green-400 bg-green-50' : '')">
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p x-show="confirmPassword.length > 0 && confirmPassword !== newPassword"
                            class="mt-1 text-xs text-red-500">Password tidak cocok.</p>
                        <p x-show="confirmPassword.length > 0 && confirmPassword === newPassword"
                            class="mt-1 text-xs text-green-600">Password cocok.</p>
                    </div>

                    <!-- Submit -->
                    <div class="pt-1">
                        <button type="submit"
                            class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 shadow-sm">
                            Perbarui Password
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <script>
        function passwordForm() {
            return {
                showCurrent: false,
                showNew: false,
                showConfirm: false,
                newPassword: '',
                confirmPassword: '',
                strength: 0,
                hasLetter: false,
                hasNumber: false,
                hasSpecial: false,
                get strengthLabel() {
                    return ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'][this.strength] ?? '';
                },
                get strengthColor() {
                    return ['', 'bg-red-400', 'bg-yellow-400', 'bg-blue-500', 'bg-green-500'][this.strength] ?? 'bg-gray-200';
                },
                get strengthTextColor() {
                    return ['', 'text-red-500', 'text-yellow-600', 'text-blue-600', 'text-green-600'][this.strength] ?? '';
                },
                checkStrength() {
                    const p = this.newPassword;
                    this.hasLetter  = /[a-zA-Z]/.test(p);
                    this.hasNumber  = /[0-9]/.test(p);
                    this.hasSpecial = /[^a-zA-Z0-9]/.test(p);

                    let score = 0;
                    if (p.length >= 6 && this.hasLetter && this.hasNumber) score = 1; // Lemah
                    if (p.length >= 8 && this.hasLetter && this.hasNumber) score = 2; // Cukup
                    if (p.length >= 10 && this.hasLetter && this.hasNumber) score = 3; // Kuat
                    if (p.length >= 10 && this.hasLetter && this.hasNumber && this.hasSpecial) score = 4; // Sangat Kuat

                    this.strength = score;
                }
            }
        }
    </script>
</x-layout>
