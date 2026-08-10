(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        const timeoutMinutes = window.sessionTimeoutMinutes;
        if (! timeoutMinutes) return; // not logged in, or not set

        const timeoutMs = timeoutMinutes * 60 * 1000;
        const warnBeforeMs = Math.min(60 * 1000, timeoutMs * 0.2); // warn 60s before, or 20% of timeout if shorter

        let idleTimer, warnTimer, countdownInterval;
        let lastHeartbeat = Date.now();

        function sendHeartbeat() {
            const now = Date.now();
            if (now - lastHeartbeat < 30000) return; // throttle: at most once per 30s
            lastHeartbeat = now;

            $.post('/session/heartbeat').fail(() => {
                // Server already considers the session gone — force logout now.
                window.location.href = '/Logout';
            });
        }

        function resetTimers() {
            clearTimeout(idleTimer);
            clearTimeout(warnTimer);
            sendHeartbeat();

            warnTimer = setTimeout(showWarning, timeoutMs - warnBeforeMs);
            idleTimer = setTimeout(forceLogout, timeoutMs);
        }

        function forceLogout() {
            window.location.href = '/Logout';
        }

        function showWarning() {
            let secondsLeft = Math.floor(warnBeforeMs / 1000);

            Swal.fire({
                icon: 'warning',
                title: 'Session expiring soon',
                html: `Your session will expire in <strong id="js-timeout-countdown">${secondsLeft}</strong> seconds due to inactivity.`,
                showCancelButton: true,
                confirmButtonText: 'Stay logged in',
                cancelButtonText: 'Logout now',
                allowOutsideClick: false,
                allowEscapeKey: false,
                timer: warnBeforeMs,
                timerProgressBar: true,
                didOpen: () => {
                    countdownInterval = setInterval(() => {
                        secondsLeft--;
                        const el = document.getElementById('js-timeout-countdown');
                        if (el) el.textContent = Math.max(secondsLeft, 0);
                    }, 1000);
                },
                willClose: () => clearInterval(countdownInterval),
            }).then(result => {
                if (result.isConfirmed) {
                    sendHeartbeat();
                    resetTimers();
                } else {
                    forceLogout();
                }
            });
        }

        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, sendHeartbeat, { passive: true });
        });

        resetTimers();
    });
});