<div
    x-data="((scriptSrc) => {
        // Close variable for non-reactive reader instance
        let reader = null;

        return {
            scanning: false,
            error: null,
            
            init() {
                if (window.ZXingBrowser) {
                    this.initializeScanner();
                } else {
                    // Check if script is already present to avoid duplicates
                    let script = document.querySelector(`script[src='${scriptSrc}']`);
                    if (!script) {
                        script = document.createElement('script');
                        script.src = scriptSrc;
                        script.async = true;
                        document.head.appendChild(script);
                    }
                    
                    script.addEventListener('load', () => this.initializeScanner());
                    script.addEventListener('error', () => {
                        this.error = 'Nem sikerült betölteni a QR olvasót.';
                    });
                }
            },
            
            initializeScanner() {
                if (reader) return; // Already initialized
                
                try {
                    // Define constants locally
                    const DecodeHintType = { POSSIBLE_FORMATS: 2, TRY_HARDER: 3 };
                    const BarcodeFormat = {
                        QR_CODE: 11, DATA_MATRIX: 5, CODE_128: 4, CODE_39: 2,
                        EAN_13: 7, EAN_8: 6, UPC_A: 14, UPC_E: 15, CODABAR: 1, ITF: 8
                    };

                    const hints = new Map();
                    const formats = [
                        BarcodeFormat.QR_CODE, BarcodeFormat.DATA_MATRIX,
                        BarcodeFormat.CODE_128, BarcodeFormat.CODE_39,
                        BarcodeFormat.EAN_13, BarcodeFormat.EAN_8,
                        BarcodeFormat.UPC_A, BarcodeFormat.UPC_E,
                        BarcodeFormat.CODABAR, BarcodeFormat.ITF
                    ];
                    
                    hints.set(DecodeHintType.POSSIBLE_FORMATS, formats);
                    hints.set(DecodeHintType.TRY_HARDER, true);
                    
                    console.log('Initializing ZXing with hints...', hints);
                    
                    // Attempt to instantiate with hints
                    // Note: The UMD build might have different constructor signatures depending on version
                    // If this fails, we catch and retry without hints
                    reader = new window.ZXingBrowser.BrowserMultiFormatReader(hints, 300);
                    
                    console.log('ZXingBrowser initialized successfully with hints');
                } catch (err) {
                    console.error('Failed to init with hints:', err);
                    try {
                        // Fallback: simple init (mostly QR support)
                        reader = new window.ZXingBrowser.BrowserMultiFormatReader(null, 300);
                        console.log('ZXingBrowser initialized in fallback mode (no hints)');
                    } catch (err2) {
                        this.error = 'Kritikus init hiba: ' + err2.message;
                    }
                }
            },

            async startScanning() {
                if (!reader) {
                    this.initializeScanner();
                    if (!reader) return;
                }
                
                this.scanning = true;
                this.error = null;
                
                this.$nextTick(async () => {
                    const video = this.$refs.video;
                    try {
                        const videoInputDevices = await window.ZXingBrowser.BrowserMultiFormatReader.listVideoInputDevices();
                        
                        if (videoInputDevices.length === 0) {
                            this.error = 'Nincs elérhető kamera.';
                            this.scanning = false;
                            return;
                        }

                        // Prefer back camera
                        const selectedDeviceId = videoInputDevices.find(device => device.label.toLowerCase().includes('back'))?.deviceId || videoInputDevices[0].deviceId;

                        await reader.decodeFromVideoDevice(
                            selectedDeviceId,
                            video,
                            (result, err) => {
                                if (result) {
                                    console.log('Found:', result.getText());
                                    this.$wire.dispatch('serialNumberScanned', { value: result.getText() });
                                    this.stopScanning();
                                    this.$dispatch('close-modal', { id: 'scan-modal' }); 
                                }
                                if (err) {
                                    const isNotFound = err.name === 'NotFoundException' || 
                                                     err.constructor.name === 'NotFoundException' ||
                                                     (window.ZXingBrowser.NotFoundException && err instanceof window.ZXingBrowser.NotFoundException);
                                    
                                    if (!isNotFound) {
                                        // console.warn(err); // Optional logging
                                    }
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

            stopScanning() {
                this.scanning = false;
                if (reader) {
                    reader.reset();
                }
            }
        }
    })('{{ asset('js/zxing-browser.min.js') }}')"
    class="flex flex-col items-center justify-center p-4 space-y-4"
>
    
    <div class="relative w-full aspect-video bg-gray-900 rounded-lg overflow-hidden">
        <video x-ref="video" class="w-full h-full object-cover"></video>
        
        <!-- Scanning Overlay -->
        <div x-show="scanning" class="absolute inset-0 flex items-center justify-center pointer-events-none">
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

        <div x-show="!scanning" class="absolute inset-0 flex items-center justify-center bg-black/50">
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

    <div class="flex justify-end w-full">
         <button 
            type="button" 
            @click="stopScanning(); $dispatch('close-modal', { id: 'scan-modal' })" 
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
        >
            Bezárás
        </button>
    </div>
</div>
