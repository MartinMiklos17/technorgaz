<x-filament-widgets::widget>
    <x-filament::section
        :icon="$this->getIcon()"
        :heading="$this->getHeading()"
    >
        <div
            x-data="{
                scanning: false,
                error: null,
                _reader: null,

                initReader() {
                    if (this._reader) return true;
                    if (!window.ZXingBrowser || !window.ZXingBrowser.BrowserMultiFormatReader) {
                        this.error = 'A QR olvasó nem tölthető be.';
                        return false;
                    }
                    try {
                        this._reader = new window.ZXingBrowser.BrowserMultiFormatReader();
                        return true;
                    } catch (err) {
                        this.error = 'Init hiba: ' + err.message;
                        return false;
                    }
                },

                async startScanning() {
                    if (!this.initReader()) return;

                    this.scanning = true;
                    this.error = null;

                    this.$nextTick(async () => {
                        const video = this.$refs.video;
                        try {
                            const devices = await window.ZXingBrowser.BrowserCodeReader.listVideoInputDevices();
                            if (devices.length === 0) {
                                this.error = 'Nincs elérhető kamera.';
                                this.scanning = false;
                                return;
                            }

                            const deviceId = devices.find(d => d.label.toLowerCase().includes('back'))?.deviceId || devices[0].deviceId;

                            await this._reader.decodeFromVideoDevice(
                                deviceId,
                                video,
                                (result, err) => {
                                    if (result) {
                                        const code = result.getText();
                                        this.stopScanning();
                                        $wire.setScannedCode(code);
                                    }
                                    if (err && err.name !== 'NotFoundException' && err.constructor.name !== 'NotFoundException') {
                                        // ignore
                                    }
                                }
                            );
                        } catch (err) {
                            this.error = 'Kamera hiba: ' + (err.message || err);
                            this.scanning = false;
                        }
                    });
                },

                stopScanning() {
                    this.scanning = false;
                    if (this._reader) {
                        this._reader.reset();
                    }
                }
            }"
            class="space-y-4"
        >
            {{-- Search input + buttons --}}
            <div class="flex gap-2">
                <div class="flex-1">
                    <input
                        type="text"
                        wire:model.defer="serialNumber"
                        wire:keydown.enter="search"
                        placeholder="Gyári szám..."
                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm"
                    />
                </div>

                <button
                    type="button"
                    wire:click="search"
                    class="fi-btn fi-btn-size-md inline-flex items-center justify-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold text-white shadow-sm bg-primary-600 hover:bg-primary-500 transition-colors"
                >
                    <x-heroicon-m-magnifying-glass class="h-4 w-4" />
                </button>

                <button
                    type="button"
                    @click="scanning ? stopScanning() : startScanning()"
                    class="fi-btn fi-btn-size-md inline-flex items-center justify-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold text-white shadow-sm transition-colors"
                    :class="scanning ? 'bg-danger-600 hover:bg-danger-500' : 'bg-primary-600 hover:bg-primary-500'"
                >
                    <x-heroicon-m-qr-code class="h-4 w-4" x-show="!scanning" />
                    <x-heroicon-m-stop class="h-4 w-4" x-show="scanning" x-cloak />
                </button>

                @if($searched)
                    <button
                        type="button"
                        wire:click="clearSearch"
                        class="fi-btn fi-btn-size-md inline-flex items-center justify-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold shadow-sm bg-gray-200 hover:bg-gray-300 dark:bg-white/10 dark:hover:bg-white/20 dark:text-white transition-colors"
                    >
                        <x-heroicon-m-x-mark class="h-4 w-4" />
                    </button>
                @endif
            </div>

            {{-- Camera preview --}}
            <div x-show="scanning" x-transition x-cloak class="relative w-full aspect-video bg-gray-900 rounded-lg overflow-hidden">
                <video x-ref="video" class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-2/3 h-2/3 border-2 border-primary-500 rounded-lg opacity-50 relative">
                        <div class="absolute top-0 left-0 w-3 h-3 border-t-4 border-l-4 border-primary-500 -mt-0.5 -ml-0.5"></div>
                        <div class="absolute top-0 right-0 w-3 h-3 border-t-4 border-r-4 border-primary-500 -mt-0.5 -mr-0.5"></div>
                        <div class="absolute bottom-0 left-0 w-3 h-3 border-b-4 border-l-4 border-primary-500 -mb-0.5 -ml-0.5"></div>
                        <div class="absolute bottom-0 right-0 w-3 h-3 border-b-4 border-r-4 border-primary-500 -mb-0.5 -mr-0.5"></div>
                    </div>
                </div>
                <div class="absolute top-2 left-1/2 -translate-x-1/2 text-white text-xs bg-black/60 px-2 py-1 rounded">
                    QR / vonalkód keresése...
                </div>
            </div>

            {{-- Error --}}
            <div x-show="error" x-cloak class="text-sm text-danger-600 dark:text-danger-400" x-text="error"></div>

            {{-- Results --}}
            @if($searched && $results !== null)
                @if($results->isEmpty())
                    <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-3">
                        Nincs találat erre: <span class="font-semibold">{{ $serialNumber }}</span>
                    </div>
                @else
                    <div class="border rounded-lg overflow-hidden dark:border-white/10">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-400">Gyári szám</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-400">Készülék</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-400">Dátum</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-600 dark:text-gray-400"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @foreach($results as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                        <td class="px-3 py-2 font-mono text-xs">{{ $row['serial_number'] }}</td>
                                        <td class="px-3 py-2 text-xs">{{ $row['product_name'] }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-500">{{ $row['created_at'] }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <a
                                                href="{{ $row['url'] }}"
                                                class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-500 text-xs font-medium"
                                            >
                                                Megnyitás
                                                <x-heroicon-m-arrow-top-right-on-square class="h-3 w-3" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
