import './bootstrap';
// import { BrowserMultiFormatReader, NotFoundException } from '@zxing/browser';

document.addEventListener('alpine:init', () => {
    Alpine.data('qrScanner', () => ({
        scanning: false,
        controls: null,
        reader: null,
        error: null,
        videoElement: null,

        init() {
            // Wait for script to load if needed, or assume it's loaded
            if (window.ZXingBrowser) {
                this.reader = new window.ZXingBrowser.BrowserMultiFormatReader();
            } else {
                console.error('ZXingBrowser not loaded');
                this.error = 'A QR olvasó nem tölthető be.';
            }
        },

        async startScanning() {
            this.scanning = true;
            this.error = null;
            // Wait for DOM update to ensure video element is visible
            await this.$nextTick();
            this.videoElement = this.$refs.video;

            if (!this.videoElement) {
                this.error = 'Video element not found.';
                return;
            }

            try {
                if (!window.ZXingBrowser) throw new Error('ZXingBrowser dependency missing');
                const videoInputDevices = await window.ZXingBrowser.BrowserMultiFormatReader.listVideoInputDevices();
                if (videoInputDevices.length === 0) {
                    this.error = 'Nincs elérhető kamera.';
                    this.scanning = false;
                    return;
                }

                // Use the first available device, or try to find back camera
                let selectedDeviceId = videoInputDevices[0].deviceId;
                const backCamera = videoInputDevices.find(device => device.label.toLowerCase().includes('back'));
                if (backCamera) {
                    selectedDeviceId = backCamera.deviceId;
                }

                this.controls = await this.reader.decodeFromVideoDevice(
                    selectedDeviceId,
                    this.videoElement,
                    (result, err) => {
                        if (result) {
                            console.log('Found:', result.getText());
                            this.handleScan(result.getText());
                        }
                        if (err && !(err instanceof window.ZXingBrowser.NotFoundException)) {
                            console.warn(err);
                        }
                    }
                );

            } catch (err) {
                console.error(err);
                this.error = 'Hiba a kamera indításakor: ' + err.message;
                this.scanning = false;
            }
        },

        handleScan(text) {
            if (navigator.vibrate) navigator.vibrate(200);
            this.stopScanning();
            Livewire.dispatch('serialNumberScanned', { value: text });
            this.$dispatch('close-modal', { id: 'qr-scanner-modal' });
        },

        stopScanning() {
            if (this.controls) {
                this.controls.stop();
                this.controls = null;
            }
            this.scanning = false;
        }
    }));
});
