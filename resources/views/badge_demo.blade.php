<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Badge Visibility Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4" x-data="{
    shareBadges: true,
    allBadges: [
        { id: 1, name: 'Pendatang Baru', icon: 'fas fa-door-open', color: 'green' },
        { id: 2, name: 'Ulat Buku', icon: 'fas fa-book-reader', color: 'blue' },
        { id: 3, name: 'Pakar Kuiz', icon: 'fas fa-brain', color: 'purple' },
        { id: 4, name: 'Rakan Baik', icon: 'fas fa-hands-helping', color: 'pink' },
        { id: 5, name: 'Penyumbang Aktif', icon: 'fas fa-comment-dots', color: 'yellow' }
    ],
    selectedBadgeIds: [1, 2, 3],
    
    get visibleBadges() {
        if (!this.shareBadges) return [];
        
        // Filter selected badges based on IDs
        let visible = this.allBadges.filter(b => this.selectedBadgeIds.includes(b.id));
        
        // Limit to 3
        return visible.slice(0, 3);
    },
    
    toggleBadge(id) {
        if (this.selectedBadgeIds.includes(id)) {
            this.selectedBadgeIds = this.selectedBadgeIds.filter(bid => bid !== id);
        } else {
            this.selectedBadgeIds.push(id);
        }
    }
}">

    <div class="max-w-4xl mx-auto space-y-8">
        
        <!-- SECTION 1: PREVIEW (Profile Header Simulation) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-4">
                <h2 class="text-white font-bold text-lg">Pratonton Profil (Profile Preview)</h2>
            </div>
            
            <div class="p-8">
                <div class="flex items-center gap-4">
                    <!-- Avatar -->
                    <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-2xl font-bold border-4 border-white shadow-sm">
                        NAS
                    </div>
                    
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold text-gray-900">Nur Aina Syafina</h1>
                            
                            <!-- DYNAMIC BADGES CONTAINER -->
                            <div class="flex items-center -space-x-2 overflow-hidden py-1 transition-all duration-300 ease-in-out"
                                 x-show="visibleBadges.length > 0"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 active:scale-95"
                                 x-transition:enter-end="opacity-100 active:scale-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                
                                <template x-for="badge in visibleBadges" :key="badge.id">
                                    <div class="relative inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-white z-10 transition-transform hover:scale-110 hover:z-20 cursor-help"
                                         :title="badge.name">
                                         <!-- Dynamic Color Classes -->
                                        <div :class="`w-full h-full flex items-center justify-center rounded-full bg-${badge.color}-100 text-${badge.color}-600`">
                                            <i :class="`${badge.icon} text-xs`"></i>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <!-- END BADGES CONTAINER -->
                            
                        </div>
                        <span class="inline-block mt-2 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Pelajar
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: SETTINGS (Tetapan Simulation) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Tetapan Privasi Lencana</h2>
            
            <!-- Main Toggle -->
            <div class="flex items-center justify-between mb-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div>
                    <h3 class="font-semibold text-gray-900">Kongsi Lencana pada Profil</h3>
                    <p class="text-sm text-gray-500">Benarkan orang lain melihat lencana yang anda perolehi.</p>
                </div>
                <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                    <input type="checkbox" name="toggle" id="toggle" 
                           class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-300 ease-in-out shadow-sm"
                           :class="{ 'translate-x-full border-blue-600': shareBadges, 'border-gray-300': !shareBadges }"
                           x-model="shareBadges"/>
                    <label for="toggle" 
                           class="toggle-label block overflow-hidden h-6 rounded-full cursor-pointer transition-colors duration-300"
                           :class="{ 'bg-blue-600': shareBadges, 'bg-gray-300': !shareBadges }"></label>
                </div>
            </div>

            <!-- Badge Selection Area -->
            <div class="border-t border-gray-200 pt-6" x-show="shareBadges" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-900">Pilih Lencana untuk Dipaparkan</h3>
                    <span class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded-md font-medium">
                        <span x-text="visibleBadges.length"></span>/3 Dipaparkan
                    </span>
                </div>
                
                <p class="text-gray-500 text-sm mb-4">Pilih sehingga 3 lencana untuk dipaparkan di profil anda.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="badge in allBadges" :key="badge.id">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                               :class="{ 'border-blue-500 ring-1 ring-blue-500 bg-blue-50': selectedBadgeIds.includes(badge.id), 'border-gray-200': !selectedBadgeIds.includes(badge.id) }">
                            
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500" 
                                   :value="badge.id" 
                                   :checked="selectedBadgeIds.includes(badge.id)"
                                   @change="toggleBadge(badge.id)">
                            
                            <div class="ml-3 flex items-center gap-3">
                                <div :class="`h-8 w-8 rounded-full flex items-center justify-center bg-${badge.color}-100 text-${badge.color}-600`">
                                    <i :class="badge.icon"></i>
                                </div>
                                <span class="font-medium text-gray-900" x-text="badge.name"></span>
                            </div>
                        </label>
                    </template>
                </div>
            </div>
            
            <div x-show="!shareBadges" x-transition class="mt-4 p-4 bg-gray-100 rounded text-center text-gray-500 text-sm">
                Perkongsian lencana dimatikan. Tiada lencana akan dipaparkan di profil anda.
            </div>

        </div>
        
    </div>

    <style>
        /* Custom Toggle Styles just in case Tailwind forms plugin isn't active */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #2563EB;
        }
        .toggle-checkbox {
            right: auto;
            left: 0;
        }
    </style>
</body>
</html>
