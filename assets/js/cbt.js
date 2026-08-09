/**
 * CBT Engine (Computer Based Test Engine)
 */
class CBTEngine {
    constructor(durationMinutes, formId, timerDisplayId) {
        this.durationSeconds = durationMinutes * 60;
        this.form = document.getElementById(formId);
        this.timerDisplay = document.getElementById(timerDisplayId);
        this.timerInterval = null;

        this.init();
    }

    init() {
        if (!this.form || !this.timerDisplay) return;

        // Restore saved timer from session storage if exists
        const savedTime = sessionStorage.getItem('cbt_remaining_time');
        if (savedTime && parseInt(savedTime) > 0) {
            this.durationSeconds = parseInt(savedTime);
        }

        this.startTimer();
        this.enableAntiCheat();
    }

    startTimer() {
        this.updateTimerDisplay();
        this.timerInterval = setInterval(() => {
            this.durationSeconds--;
            sessionStorage.setItem('cbt_remaining_time', this.durationSeconds);
            this.updateTimerDisplay();

            if (this.durationSeconds <= 0) {
                clearInterval(this.timerInterval);
                sessionStorage.removeItem('cbt_remaining_time');
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Waktu Habis!',
                    text: 'Jawaban Anda otomatis dikirimkan ke server.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                }).then(() => {
                    this.form.submit();
                });
            }
        }, 1000);
    }

    updateTimerDisplay() {
        const hours = Math.floor(this.durationSeconds / 3600);
        const minutes = Math.floor((this.durationSeconds % 3600) / 60);
        const seconds = this.durationSeconds % 60;

        const h = hours < 10 ? '0' + hours : hours;
        const m = minutes < 10 ? '0' + minutes : minutes;
        const s = seconds < 10 ? '0' + seconds : seconds;

        this.timerDisplay.textContent = `${h}:${m}:${s}`;
    }

    enableAntiCheat() {
        // Prevent tab switching alert
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                Swal.fire({
                    icon: 'error',
                    title: 'Peringatan Kecurangan!',
                    text: 'Dilarang berpindah tab saat ujian berlangsung!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });
            }
        });

        // Anti Refresh / Close window prompt
        window.addEventListener('beforeunload', (e) => {
            e.preventDefault();
            e.returnValue = 'Ujian Anda sedang berlangsung. Yakin ingin meninggalkan halaman?';
        });

        this.form.addEventListener('submit', () => {
            sessionStorage.removeItem('cbt_remaining_time');
            window.removeEventListener('beforeunload', () => {});
        });
    }
}
