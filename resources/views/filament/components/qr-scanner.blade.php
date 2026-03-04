<div
    x-data="{
        scanning: false,
        scannedCode: null,
        error: null,
        _reader: null,

        init() {
            if (!window.ZXingBrowser || !window.ZXingBrowser.BrowserMultiFormatReader) {
                this.error = 'A QR olvasó nem tölthető be.';
                return;
            }
            try {
                this._reader = new window.ZXingBrowser.BrowserMultiFormatReader();
            } catch (err) {
                this.error = 'Init hiba: ' + err.message;
            }
        },

        async startScanning() {
            if (!this._reader) {
                this.init();
                if (!this._reader) return;
            }

            this.scanning = true;
            this.error = null;
            this.scannedCode = null;

            this.$nextTick(async () => {
                const video = this.$refs.video;
                try {
                    const videoInputDevices = await window.ZXingBrowser.BrowserCodeReader.listVideoInputDevices();

                    if (videoInputDevices.length === 0) {
                        this.error = 'Nincs elérhető kamera.';
                        this.scanning = false;
                        return;
                    }

                    const selectedDeviceId = videoInputDevices.find(device => device.label.toLowerCase().includes('back'))?.deviceId || videoInputDevices[0].deviceId;

                    await this._reader.decodeFromVideoDevice(
                        selectedDeviceId,
                        video,
                        (result, err) => {
                            if (this.scannedCode) return;

                            if (result) {
                                this.scannedCode = result.getText();
                            }
                            if (err && err.name !== 'NotFoundException' && err.constructor.name !== 'NotFoundException') {
                                // non-NotFoundException errors can be logged if needed
                            }
                        }
                    );
                } catch (err) {
                    console.error('Camera start error:', err);
                    this.error = 'Kamera hiba: ' + (err.message || err);
                    this.scanning = false;
                }
            });
        },

        confirmScan() {
            if (this.scannedCode) {
                this.$wire.dispatch('serialNumberScanned', { value: this.scannedCode });
                this.stopScanning();

                setTimeout(() => {
                    const modal = this.$root.closest('.fi-modal');
                    if (modal) {
                        const closeBtn = modal.querySelector('.fi-modal-close-btn');
                        if (closeBtn) {
                            closeBtn.click();
                        } else {
                            this.$wire.call('unmountFormComponentAction');
                        }
                    }
                }, 50);
            }
        },

        cancelScan() {
            this.scannedCode = null;
        },

        stopScanning() {
            this.scanning = false;
            this.scannedCode = null;
            if (this._reader) {
                this._reader.reset();
            }
        }
    }"
    class="flex flex-col items-center justify-center p-4 space-y-4"
>
    
    <div class="relative w-full aspect-video bg-gray-900 rounded-lg overflow-hidden">
        <video x-ref="video" class="w-full h-full object-cover"></video>
        
        <!-- Scanning Overlay (only when scanning and no code found yet) -->
        <div x-show="scanning && !scannedCode" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-2/3 h-2/3 border-2 border-primary-500 rounded-lg opacity-50 relative">
                <div class="absolute top-0 left-0 w-4 h-4 border-t-4 border-l-4 border-primary-500 -mt-1 -ml-1"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-4 border-r-4 border-primary-500 -mt-1 -mr-1"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-4 border-l-4 border-primary-500 -mb-1 -ml-1"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-4 border-r-4 border-primary-500 -mb-1 -mr-1"></div>
            </div>
            <div class="absolute top-4 text-white text-xs bg-black/50 px-2 py-1 rounded text-center">
                <p>QR vagy Vonalkód keresése...</p>
                <p class="text-[10px] opacity-80 mt-1">Vonalkódnál forgassa el a telefont!</p>
            </div>
        </div>

        <!-- Start Button Overlay -->
        <div x-show="!scanning && !scannedCode" class="absolute inset-0 flex items-center justify-center bg-black/50">
            <button 
                type="button"
                @click="startScanning()" 
                class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-lg font-medium transition-colors pointer-events-auto"
            >
                Kamera indítása
            </button>
        </div>

        <div x-show="error" class="absolute inset-0 flex items-center justify-center bg-black/80 p-4 text-center">
            <p class="text-red-500 font-medium" x-text="error"></p>
        </div>
    </div>

    <!-- Confirmation Area (Below Video) -->
    <div x-show="scannedCode" x-transition class="w-full bg-white p-4 rounded-lg border border-gray-200 text-center space-y-3">
        <h3 class="text-lg font-bold text-gray-900">Kód találat!</h3>
        
        <div class="bg-gray-100 p-3 rounded text-xl font-mono text-primary-600 break-all select-all">
            <span x-text="scannedCode"></span>
        </div>
        
        <p class="text-sm text-gray-600">Szeretné ezt a kódot használni?</p>
        
        <div class="flex space-x-3 justify-center pt-2">
             <button 
                type="button"
                @click="cancelScan()" 
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors min-w-[100px]"
            >
                Mégsem
            </button>
            <button 
                type="button"
                @click="confirmScan()" 
                class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-lg font-medium transition-colors min-w-[100px]"
            >
                Igen
            </button>
        </div>
    </div>

    <div class="flex justify-end w-full" x-show="!scannedCode">
         <button 
            type="button" 
            @click="stopScanning(); $wire.unmountFormComponentAction()" 
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
        >
            Bezárás
        </button>
    </div>
</div>
