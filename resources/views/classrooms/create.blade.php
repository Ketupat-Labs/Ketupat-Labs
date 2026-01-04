<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Cipta Kelas Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Button -->
            <a href="{{ route('classrooms.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Senarai Kelas
            </a>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8 bg-white">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-600 mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Maklumat Kelas</h3>
                        <p class="text-sm text-gray-500 mt-1">Sila isi maklumat asas untuk kelas baharu anda.</p>
                    </div>

                    <form method="POST" action="{{ route('classrooms.store') }}" 
                          x-data="{
                              name: '',
                              existingNames: @js($existingNames),
                              isDuplicate: false,
                              checkDuplicate() {
                                  let input = this.name.toLowerCase().replace(/\s/g, '');
                                  this.isDuplicate = this.existingNames.some(n => 
                                      n.toLowerCase().replace(/\s/g, '') === input
                                  );
                              }
                          }">
                        @csrf

                        <!-- Class Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" x-model="name" @input="checkDuplicate()"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 placeholder-gray-400"
                                :class="{'border-red-500 ring-red-200': isDuplicate}"
                                placeholder="Contoh: 5 Bestari"
                                oninvalid="this.setCustomValidity('Sila isi ruangan ini')"
                                oninput="this.setCustomValidity('')"
                                required autofocus>
                            <p x-show="isDuplicate" class="text-red-600 text-sm mt-2 font-bold animate-pulse">
                                Kelas dengan nama yang sama sudah wujud!
                            </p>
                        </div>

                        <!-- Subject -->
                        <div class="mb-6">
                            <label for="subject" class="block text-gray-700 text-sm font-bold mb-2">Subjek / Mata Pelajaran <span class="text-red-500">*</span></label>
                            <input type="text" name="subject" id="subject"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 placeholder-gray-400"
                                placeholder="Contoh: Matematik"
                                oninvalid="this.setCustomValidity('Sila isi ruangan ini')"
                                oninput="this.setCustomValidity('')"
                                required>
                        </div>

                        <!-- Year -->
                        <div class="mb-8">
                            <label for="year" class="block text-gray-700 text-sm font-bold mb-2">Tahun (Tidak Wajib)</label>
                            <input type="number" name="year" id="year"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 placeholder-gray-400"
                                placeholder="{{ date('Y') }}"
                                value="{{ date('Y') }}"
                                min="2000" max="2100">
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" 
                                :disabled="isDuplicate" :class="{'opacity-50 cursor-not-allowed': isDuplicate}"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transform transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cipta Kelas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>