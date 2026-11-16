@extends('layouts.app')

@section('title', 'Ambil Foto Presensi')

@section('content')
<div x-data="tirtafieldCameraApp()" x-init="initCamera()" class="space-y-3.5 pb-6">

    <!-- Top Modal Header (Matching Tirtafield AmbilFotoAbsensiScreen) -->
    <div class="bg-white rounded-2xl border border-[#DAE2FD] p-3.5 shadow-xs flex items-center justify-between">
        <div class="flex items-center space-x-2.5 min-w-0">
            <a href="{{ route('dashboard') }}"
               class="w-9 h-9 rounded-full bg-[#EAEDFF] text-[#131B2E] hover:bg-[#DAE2FD] flex items-center justify-center transition-colors shrink-0">
                <span class="material-symbols-rounded text-xl">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="font-bold text-sm sm:text-base text-[#131B2E] truncate"
                    x-text="photoBase64 ? 'Hasil Foto Presensi' : 'Ambil Foto Presensi'">
                    Ambil Foto Presensi
                </h1>
                <div class="flex items-center space-x-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full" :class="photoBase64 ? 'bg-emerald-600' : 'bg-[#0051D5]'"></span>
                    <span class="text-[11px] font-semibold truncate" :class="photoBase64 ? 'text-emerald-700' : 'text-[#64748B]'"
                          x-text="photoBase64 ? 'Foto Presensi Siap Dikirim' : 'Posisikan kamera di lokasi latihan dojang'">
                        Posisikan kamera di lokasi latihan dojang
                    </span>
                </div>
            </div>
        </div>

        <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-full bg-[#EAEDFF]/70 text-[#64748B] hover:text-[#131B2E] flex items-center justify-center transition-colors shrink-0">
            <span class="material-symbols-rounded text-lg">close</span>
        </a>
    </div>

    <!-- Clean Camera Viewport (4:5 Aspect Ratio - Tirtafield Exact Spec) -->
    <div class="relative w-full aspect-[4/5] bg-[#0A101D] rounded-3xl overflow-hidden shadow-xl border border-[#DAE2FD] flex items-center justify-center">

        <!-- Shutter Flash Overlay -->
        <div x-show="isFlashing" x-transition.opacity.duration.250ms class="absolute inset-0 bg-white z-30 pointer-events-none" style="display: none;"></div>

        <!-- Camera Facing Indicator Tag (Top Right Viewport) -->
        <div x-show="!photoBase64 && isCameraActive" class="absolute top-3 right-3 z-20 px-2.5 py-1 rounded-full bg-black/50 backdrop-blur-md text-white text-[11px] font-medium flex items-center space-x-1 border border-white/20">
            <span class="material-symbols-rounded text-xs" x-text="facingMode === 'user' ? 'face' : 'photo_camera_back'">face</span>
            <span x-text="facingMode === 'user' ? 'Kamera Depan' : 'Kamera Belakang'">Kamera Depan</span>
        </div>

        <!-- Live Video Stream (Mirrored horizontally only when using front camera) -->
        <video id="selfieWebcamVideo"
               x-show="!photoBase64 && isCameraActive"
               autoplay playsinline muted
               class="w-full h-full object-cover transition-transform"
               :class="facingMode === 'user' ? 'transform -scale-x-100' : ''"></video>
        <canvas id="selfieCanvasCapture" class="hidden"></canvas>

        <!-- Placeholder when camera not yet granted or loading -->
        <div x-show="!photoBase64 && !isCameraActive" class="text-center p-6 text-white space-y-3">
            <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center mx-auto text-white">
                <span class="material-symbols-rounded text-3xl">photo_camera</span>
            </div>
            <div>
                <p class="text-sm font-bold">Menyiapkan Kamera Presensi...</p>
                <p class="text-xs text-slate-400 mt-1">Izinkan akses kamera pada peramban Anda</p>
            </div>
            <button type="button" @click="startWebcam()" class="px-4 py-2 rounded-xl bg-[#0051D5] text-white text-xs font-bold hover:bg-[#00288E] transition-colors">
                Aktifkan Kamera
            </button>
        </div>

        <!-- Captured Photo Preview (Clean 4:5 Portrait, Unobstructed Full View) -->
        <img x-show="photoBase64" :src="photoBase64" alt="Hasil Foto Presensi" class="w-full h-full object-cover" style="display: none;">
    </div>

    <!-- Attendance Submission Form -->
    <form action="{{ route('attendances.store') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
        @csrf
        <input type="hidden" name="photo_base64" x-model="photoBase64">

        <!-- Phase 1: Live Camera Controls (Saat Belum Ada Foto) -->
        <div x-show="!photoBase64" class="space-y-3 pt-1">
            <div class="p-3 bg-[#EAEDFF] border border-[#DAE2FD] rounded-xl flex items-center space-x-2 text-xs text-[#00288E]">
                <span class="material-symbols-rounded text-base text-[#0051D5] shrink-0">info</span>
                <span>Arahkan kamera ke wajah atau kegiatan latihan di dojang dengan pencahayaan memadai.</span>
            </div>

            <!-- Shutter & Flip Camera Row (Tirtafield Exact Spec with Switch Camera Beside Shutter) -->
            <div class="pt-2">
                <div class="flex items-center justify-center space-x-4">
                    <!-- Symmetrical spacer / indicator to keep shutter button centered -->
                    <div class="w-12 h-12 flex items-center justify-center">
                        <span class="w-2.5 h-2.5 rounded-full" :class="isCameraActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'"></span>
                    </div>

                    <!-- Big Clean Circular Shutter Button (80x80 - Tirtafield Exact Spec) -->
                    <button type="button" @click="snapPhoto()"
                            class="w-20 h-20 rounded-full bg-white p-1.5 shadow-xl shadow-[#0051D5]/20 border-2 border-[#DAE2FD] active:scale-95 transition-transform"
                            title="Ambil Foto">
                        <div class="w-full h-full rounded-full bg-[#0051D5] hover:bg-[#00288E] flex items-center justify-center text-white transition-colors">
                            <span class="material-symbols-rounded text-3xl">photo_camera</span>
                        </div>
                    </button>

                    <!-- Camera Switch (Flip Front / Rear Camera Button Beside Shutter) -->
                    <button type="button"
                            @click="toggleCameraFacing()"
                            :disabled="!isCameraActive"
                            class="w-12 h-12 rounded-full bg-white border border-[#DAE2FD] shadow-md flex items-center justify-center text-[#0051D5] hover:bg-[#EAEDFF] hover:border-[#0051D5] active:scale-90 transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                            title="Ganti Kamera Depan / Belakang">
                        <span class="material-symbols-rounded text-2xl transition-transform" :class="isSwitchingCamera ? 'animate-spin' : ''">cameraswitch</span>
                    </button>
                </div>

                <div class="text-center mt-2.5">
                    <div class="font-bold text-sm text-[#131B2E] font-sans">Ambil Foto Presensi Langsung</div>
                    <div class="text-[11px] text-[#64748B]">Wajib jepret langsung melalui kamera (galeri dinonaktifkan)</div>
                </div>
            </div>
        </div>

        <!-- Phase 2: Bento Verification Module (Setelah Foto Berhasil Diambil) -->
        <div x-show="photoBase64" class="space-y-3 pt-1" style="display: none;">

            <!-- Retake Photo Action Button -->
            <div class="flex justify-end">
                <button type="button" @click="retakePhoto()"
                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl bg-white border border-[#DAE2FD] text-xs font-bold text-[#64748B] hover:text-[#131B2E] shadow-xs active:scale-95 transition-all">
                    <span class="material-symbols-rounded text-base text-[#0051D5]">refresh</span>
                    <span>Ambil Foto Ulang</span>
                </button>
            </div>

            <!-- Bento Container (Matching Tirtafield AmbilFotoAbsensiScreen) -->
            <div class="bg-white rounded-2xl border border-[#DAE2FD] p-4 shadow-xs space-y-3.5">
                <!-- Header Row -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <span class="material-symbols-rounded text-xl">task_alt</span>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-[#131B2E]">Foto Bukti Terverifikasi</div>
                            <div class="text-[11px] text-emerald-600 font-semibold">Foto siap dikirim dan disimpan otomatis</div>
                        </div>
                    </div>
                    <div class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold flex items-center">
                        <span>Siap Kirim</span>
                    </div>
                </div>

                <div class="border-t border-[#EAEDFF]"></div>

                <!-- Row 1: Bento Stat Cards (Tanggal & Waktu) -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-2.5 rounded-xl bg-[#F2F3FF] border border-[#DAE2FD] text-center">
                        <div class="flex items-center justify-center space-x-1 text-[10px] font-bold text-[#64748B] uppercase">
                            <span class="material-symbols-rounded text-xs">calendar_today</span>
                            <span>TANGGAL</span>
                        </div>
                        <div class="font-bold text-xs text-[#131B2E] mt-1 truncate">
                            {{ $now->locale('id')->isoFormat('D MMMM Y') }}
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-[#F2F3FF] border border-[#DAE2FD] text-center">
                        <div class="flex items-center justify-center space-x-1 text-[10px] font-bold text-[#64748B] uppercase">
                            <span class="material-symbols-rounded text-xs">schedule</span>
                            <span>WAKTU</span>
                        </div>
                        <div class="font-bold text-xs text-[#0051D5] mt-1" x-text="serverClock">
                            {{ $now->format('H:i') }} WIB
                        </div>
                    </div>
                </div>

                <!-- Hidden status always Hadir -->
                <input type="hidden" name="status" value="Hadir">

                <!-- Catatan Kegiatan Operasional -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Catatan Operasional (Opsional)
                    </label>
                    <div class="bg-[#F2F3FF] rounded-xl border border-[#DAE2FD] flex items-center px-3 py-1">
                        <span class="material-symbols-rounded text-lg text-[#64748B] mr-2">edit_note</span>
                        <input type="text" name="notes" placeholder="Catatan melatih atau materi khusus..."
                               class="w-full py-2 bg-transparent text-xs text-[#131B2E] outline-none">
                    </div>
                </div>
            </div>

            <!-- Primary Submit Gradient Button (54px - Tirtafield Exact Spec) -->
            <button type="submit" :disabled="isSubmitting"
                    class="w-full h-[54px] rounded-2xl bg-gradient-to-r from-[#00288E] to-[#0051D5] hover:from-[#001f70] hover:to-[#003ea8] text-white font-bold text-[15px] flex items-center justify-center space-x-2 shadow-lg shadow-[#0051D5]/25 transition-all disabled:opacity-50 active:scale-[0.99]">
                <template x-if="!isSubmitting">
                    <span class="flex items-center space-x-2">
                        <span class="material-symbols-rounded text-xl">send</span>
                        <span>Kirim &amp; Simpan Presensi</span>
                    </span>
                </template>
                <template x-if="isSubmitting">
                    <span class="flex items-center space-x-2">
                        <span class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>Menyimpan Presensi...</span>
                    </span>
                </template>
            </button>
        </div>
    </form>

</div>

<script>
    function tirtafieldCameraApp() {
        return {
            photoBase64: '',
            isCameraActive: false,
            isFlashing: false,
            isSubmitting: false,
            isSwitchingCamera: false,
            facingMode: 'user', // 'user' (front) or 'environment' (rear)
            selectedStatus: 'Hadir',
            videoStream: null,
            serverClock: '{{ $now->format("H:i") }} WIB',

            initCamera() {
                this.startWebcam();
                this.initLiveClock();
            },

            initLiveClock() {
                setInterval(() => {
                    const now = new Date();
                    const h = String(now.getHours()).padStart(2, '0');
                    const m = String(now.getMinutes()).padStart(2, '0');
                    this.serverClock = `${h}:${m} WIB`;
                }, 60000);
            },

            async startWebcam() {
                const video = document.getElementById('selfieWebcamVideo');
                if (!video) return;

                this.stopWebcam();

                try {
                    const constraints = {
                        video: {
                            facingMode: { ideal: this.facingMode },
                            width: { ideal: 1280 },
                            height: { ideal: 960 }
                        },
                        audio: false
                    };

                    this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                    video.srcObject = this.videoStream;
                    this.isCameraActive = true;
                } catch (err) {
                    console.warn('Webcam direct access with facingMode failed, falling back:', err);
                    try {
                        this.videoStream = await navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: false
                        });
                        video.srcObject = this.videoStream;
                        this.isCameraActive = true;
                    } catch (fallbackErr) {
                        console.warn('Webcam unavailable or permission denied:', fallbackErr);
                        this.isCameraActive = false;
                    }
                }
            },

            async toggleCameraFacing() {
                if (this.isSwitchingCamera) return;
                this.isSwitchingCamera = true;
                this.facingMode = (this.facingMode === 'user') ? 'environment' : 'user';
                await this.startWebcam();
                this.isSwitchingCamera = false;
            },

            stopWebcam() {
                if (this.videoStream) {
                    try {
                        this.videoStream.getTracks().forEach(track => track.stop());
                    } catch (e) {}
                    this.videoStream = null;
                }
                this.isCameraActive = false;
            },

            snapPhoto() {
                const video = document.getElementById('selfieWebcamVideo');
                const canvas = document.getElementById('selfieCanvasCapture');

                if (!video || !canvas || !this.isCameraActive) {
                    alert('Kamera belum aktif atau belum diizinkan. Silakan izinkan akses kamera terlebih dahulu.');
                    return;
                }

                // Shutter flash effect
                this.isFlashing = true;
                setTimeout(() => { this.isFlashing = false; }, 200);

                const width = video.videoWidth || 640;
                const height = video.videoHeight || 480;
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');

                // Mirror horizontally only when using front camera so it matches what user sees
                if (this.facingMode === 'user') {
                    ctx.translate(width, 0);
                    ctx.scale(-1, 1);
                }

                ctx.drawImage(video, 0, 0, width, height);

                this.photoBase64 = canvas.toDataURL('image/jpeg', 0.85);
                this.stopWebcam();
            },

            retakePhoto() {
                this.photoBase64 = '';
                this.startWebcam();
            }
        };
    }
</script>
@endsection
