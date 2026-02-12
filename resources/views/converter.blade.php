@extends('layouts.app')

@section('title', 'Currency Converter - Real-time Exchange Rates')

@section('content')
<div class="max-w-6xl mx-auto" 
     x-data="currencyConverterApp()" 
     x-init="init()">
    
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-5xl md:text-6xl font-bold text-purple-700 mb-4">
            Currency Converter
        </h1>
        <p class="text-xl text-gray-600">Convert currencies with real-time exchange rates</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        
        <!-- Main Converter Card - Takes 2 columns on large screens -->
        <div class="lg:col-span-2">
            <div class="card backdrop-blur-lg bg-white/80 border border-gray-200">
                
                <!-- Amount Input Section -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Amount</label>
                    <div class="relative">
                        <input 
                            type="number" 
                            x-model="amount"
                            @input="debouncedConvert()"
                            class="input-field text-2xl font-bold pr-20"
                            placeholder="100"
                            min="0"
                            step="0.01"
                        >
                        <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                            <span class="text-2xl" x-text="getSymbol(fromCurrency)"></span>
                        </div>
                    </div>
                </div>

                <!-- Currency Selection Grid -->
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    
                    <!-- From Currency -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">From</label>
                        <div class="relative">
                            <select 
                                x-model="fromCurrency"
                                @change="convert()"
                                class="select-field appearance-none"
                            >
                                <option value="">Select Currency</option>
                                <template x-for="currency in currencies" :key="currency.code">
                                    <option :value="currency.code" x-text="`${currency.code} - ${currency.name}`"></option>
                                </template>
                            </select>
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- To Currency -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">To</label>
                        <div class="relative">
                            <select 
                                x-model="toCurrency"
                                @change="convert()"
                                class="select-field appearance-none"
                            >
                                <option value="">Select Currency</option>
                                <template x-for="currency in currencies" :key="currency.code">
                                    <option :value="currency.code" x-text="`${currency.code} - ${currency.name}`"></option>
                                </template>
                            </select>
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Swap Button -->
                <div class="flex justify-center -my-3 mb-6">
                    <button 
                        @click="swapCurrencies()"
                        class="bg-white hover:bg-primary-50 border-2 border-primary-300 rounded-full p-3 shadow-md hover:shadow-lg transition-all duration-200 transform hover:rotate-180"
                        :disabled="loading"
                    >
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                    </button>
                </div>

                <!-- Result Display -->
                <div 
                    x-show="result !== null"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="bg-gradient-to-r from-primary-500 to-purple-600 rounded-xl p-6 text-white mb-6"
                >
                    <div class="text-center">
                        <p class="text-sm font-medium opacity-90 mb-2">Converted Amount</p>
                        <p class="text-4xl md:text-5xl font-bold mb-3">
                            <span x-text="getSymbol(toCurrency)"></span>
                            <span x-text="formatNumber(result)"></span>
                        </p>
                        <p class="text-sm opacity-75" x-text="conversionText"></p>
                    </div>
                </div>

                <!-- Exchange Rate Info -->
                <div 
                    x-show="exchangeRate !== null"
                    class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                >
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Exchange Rate</span>
                        <span class="font-semibold text-gray-900">
                            1 <span x-text="fromCurrency"></span> = 
                            <span x-text="formatNumber(exchangeRate)"></span> 
                            <span x-text="toCurrency"></span>
                        </span>
                    </div>
                </div>

                <!-- Convert Button -->
                <button 
                    @click="convert()"
                    class="btn-primary w-full mt-6 relative overflow-hidden group"
                    :disabled="loading || !amount || !fromCurrency || !toCurrency"
                    :class="{'opacity-50 cursor-not-allowed': loading || !amount || !fromCurrency || !toCurrency}"
                >
                    <span x-show="!loading">Convert Now</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Converting...
                    </span>
                </button>

                <!-- Add to Favorites -->
                <button 
                    @click="addToFavorites()"
                    x-show="result !== null"
                    class="btn-secondary w-full mt-3"
                >
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                    Add to Favorites
                </button>

                <!-- Error Message -->
                <div 
                    x-show="error"
                    x-transition
                    class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4"
                >
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-red-700 text-sm" x-text="error"></p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Quick Convert (Favorites) -->
            <div class="card">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    Favorites
                </h3>
                <div x-show="favorites.length === 0" class="text-center py-6 text-gray-500 text-sm">
                    No favorites yet. Convert and add some!
                </div>
                <div class="space-y-2">
                    <template x-for="fav in favorites" :key="fav.id">
                        <button 
                            @click="quickConvert(fav.from_currency, fav.to_currency)"
                            class="w-full text-left p-3 bg-gray-50 hover:bg-primary-50 rounded-lg transition-colors border border-gray-200 hover:border-primary-300"
                        >
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-900">
                                    <span x-text="fav.from_currency"></span> → <span x-text="fav.to_currency"></span>
                                </span>
                                <span class="text-xs text-gray-500" x-text="`Used ${fav.usage_count}x`"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Popular Pairs -->
            <div class="card">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-primary-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Popular Pairs
                </h3>
                <div class="space-y-2">
                    <button @click="quickConvert('USD', 'EUR')" class="w-full text-left p-3 bg-gray-50 hover:bg-primary-50 rounded-lg transition-colors">
                        <span class="font-semibold">USD → EUR</span>
                    </button>
                    <button @click="quickConvert('EUR', 'USD')" class="w-full text-left p-3 bg-gray-50 hover:bg-primary-50 rounded-lg transition-colors">
                        <span class="font-semibold">EUR → USD</span>
                    </button>
                    <button @click="quickConvert('GBP', 'USD')" class="w-full text-left p-3 bg-gray-50 hover:bg-primary-50 rounded-lg transition-colors">
                        <span class="font-semibold">GBP → USD</span>
                    </button>
                    <button @click="quickConvert('USD', 'JPY')" class="w-full text-left p-3 bg-gray-50 hover:bg-primary-50 rounded-lg transition-colors">
                        <span class="font-semibold">USD → JPY</span>
                    </button>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card bg-gradient-to-br from-primary-50 to-purple-50 border border-primary-200">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-primary-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">Live Exchange Rates</h4>
                        <p class="text-sm text-gray-600">Rates are updated every hour from trusted sources.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Recent Conversions -->
    <div class="mt-8">
        <div class="card">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 text-primary-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recent Conversions
            </h3>
            
            <div x-show="history.length === 0" class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p>No conversion history yet</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" x-show="history.length > 0">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">From</th>
                            <th class="text-center py-3 px-4 text-sm font-semibold text-gray-700"></th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">To</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Result</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Rate</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in history" :key="item.id">
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4 font-medium" x-text="formatNumber(item.amount)"></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="item.from_currency"></span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="item.to_currency"></span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-primary-600" x-text="formatNumber(item.result)"></td>
                                <td class="py-3 px-4 text-sm text-gray-600" x-text="item.rate"></td>
                                <td class="py-3 px-4 text-sm text-gray-500" x-text="formatTime(item.created_at)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function currencyConverterApp() {
    return {
        // Data
        currencies: [],
        favorites: [],
        history: [],
        
        amount: 100,
        fromCurrency: 'USD',
        toCurrency: 'EUR',
        result: null,
        exchangeRate: null,
        conversionText: '',
        
        loading: false,
        error: null,
        
        debounceTimer: null,
        
        // Currency symbols mapping
        currencySymbols: {
            'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥',
            'INR': '₹', 'AUD': 'A$', 'CAD': 'C$', 'CHF': 'CHF',
            'CNY': '¥', 'SEK': 'kr', 'NZD': 'NZ$', 'KRW': '₩',
            'SGD': 'S$', 'HKD': 'HK$', 'NOK': 'kr', 'MXN': '$',
            'BRL': 'R$', 'RUB': '₽', 'ZAR': 'R', 'TRY': '₺',
        },
        
        // Initialize
        async init() {
            console.log('Initializing currency converter...');
            await this.loadCurrencies();
            console.log('Currencies loaded:', this.currencies.length);
            await this.loadFavorites();
            await this.loadHistory();
            // Auto-convert with default values
            this.convert();
        },
        
        // Load currencies from API
        async loadCurrencies() {
            console.log('Loading currencies from API...');
            try {
                const response = await fetch('/api/currencies');
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    this.currencies = data.data;
                    console.log('Currencies set:', this.currencies.length);
                } else {
                    console.error('API returned success=false:', data);
                }
            } catch (error) {
                console.error('Error loading currencies:', error);
            }
        },
        
        // Load favorites
        async loadFavorites() {
            try {
                const response = await fetch('/api/favorites');
                const data = await response.json();
                if (data.success) {
                    this.favorites = data.data;
                }
            } catch (error) {
                console.error('Error loading favorites:', error);
            }
        },
        
        // Load history
        async loadHistory() {
            try {
                const response = await fetch('/api/conversions/history?limit=10');
                const data = await response.json();
                if (data.success) {
                    this.history = data.data;
                }
            } catch (error) {
                console.error('Error loading history:', error);
            }
        },
        
        // Convert currency
        async convert() {
            if (!this.amount || !this.fromCurrency || !this.toCurrency) {
                return;
            }
            
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch('/api/convert', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        amount: parseFloat(this.amount),
                        from: this.fromCurrency,
                        to: this.toCurrency
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.result = data.result;
                    this.exchangeRate = data.rate;
                    this.conversionText = data.formatted;
                    
                    // Reload history
                    await this.loadHistory();
                    
                    // Show success toast
                    this.showToast('Conversion successful!', 'success');
                } else {
                    this.error = data.message || 'Conversion failed';
                    this.showToast(this.error, 'error');
                }
            } catch (error) {
                this.error = 'Network error. Please try again.';
                this.showToast(this.error, 'error');
            } finally {
                this.loading = false;
            }
        },
        
        // Debounced convert (for real-time conversion as user types)
        debouncedConvert() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.convert();
            }, 500);
        },
        
        // Swap currencies
        swapCurrencies() {
            [this.fromCurrency, this.toCurrency] = [this.toCurrency, this.fromCurrency];
            this.convert();
        },
        
        // Quick convert from favorites/popular
        quickConvert(from, to) {
            this.fromCurrency = from;
            this.toCurrency = to;
            this.convert();
        },
        
        // Add to favorites
        async addToFavorites() {
            try {
                const response = await fetch('/api/favorites', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        from: this.fromCurrency,
                        to: this.toCurrency
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await this.loadFavorites();
                    this.showToast('Added to favorites!', 'success');
                }
            } catch (error) {
                console.error('Error adding to favorites:', error);
            }
        },
        
        // Helper: Get currency symbol
        getSymbol(code) {
            return this.currencySymbols[code] || code;
        },
        
        // Helper: Format number
        formatNumber(num) {
            return parseFloat(num).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        
        // Helper: Format time
        formatTime(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // seconds
            
            if (diff < 60) return 'Just now';
            if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
            return date.toLocaleDateString();
        },
        
        // Show toast notification
        showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            
            if (type === 'success') {
                toast.classList.add('bg-green-500', 'text-white');
            } else {
                toast.classList.add('bg-red-500', 'text-white');
            }
            
            setTimeout(() => {
                toast.classList.add('hidden');
                toast.classList.remove('bg-green-500', 'bg-red-500', 'text-white');
            }, 3000);
        }
    }
}
</script>
@endpush