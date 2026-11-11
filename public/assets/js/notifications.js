/**
 * Prowave ERP — Real-time notifications
 * Polls for new notifications, plays sound, shows toast + browser alert.
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    let pollTimer = null;
    let soundEnabled = true;
    let browserNotifPerm = 'default';
    let audioCtx = null;

    const seenNotifIds = new Set();
    let initialized = false;
    const POLL_INTERVAL = 45000; // 45s

    function playSound() {
        if (!soundEnabled) return;
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const ctx = audioCtx;
            if (ctx.state === 'suspended') ctx.resume();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.08);
            gain.gain.setValueAtTime(0.18, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.35);
        } catch (e) {
            // sound is best-effort
        }
    }

    function showNotifToast(n) {
        if (!window.toastr) return;
        const html = `<div style="min-width:240px">
            <div style="display:flex;align-items:center;gap:.55rem;margin-bottom:.25rem">
                <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:rgba(78,139,255,.15);color:#7db3ff">
                    <i class="bi ${n.icon || 'bi-bell'}"></i>
                </span>
                <strong style="font-size:.9rem">${n.title}</strong>
            </div>
            ${n.message ? `<div style="font-size:.82rem;color:#94a3b8;margin-left:2.1rem">${n.message}</div>` : ''}
        </div>`;
        const t = window.toastr.info(html, '', {
            timeOut: 6000,
            extendedTimeOut: 2000,
            closeButton: true,
            positionClass: 'toast-top-right',
        });
        if (t && window.jQuery) {
            t.css('background-color', '#141e3a').css('color', '#eaeeff');
            if (n.url) {
                window.jQuery(t).css('cursor', 'pointer').on('click', () => {
                    window.location.href = n.url;
                });
            }
        }
    }

    function showBrowserNotif(n) {
        if (browserNotifPerm !== 'granted' || document.visibilityState === 'visible') return;
        try {
            const bn = new Notification(n.title, {
                body: n.message || '',
                icon: '/images/logo.png',
                tag: n.id,
            });
            bn.onclick = () => {
                window.focus();
                if (n.url) window.location.href = n.url;
                bn.close();
            };
        } catch (e) {
            // browser notification best-effort
        }
    }

    function updateBadge(count) {
        const badge = document.querySelector('.notif-badge');
        if (count > 0) {
            if (!badge) {
                const btn = document.querySelector('[data-bs-toggle="dropdown"] .bi-bell, .icon-btn .bi-bell')?.closest('.icon-btn');
                if (btn) {
                    const span = document.createElement('span');
                    span.className = 'notif-badge';
                    span.textContent = count > 99 ? '99+' : count;
                    btn.appendChild(span);
                }
            } else {
                badge.textContent = count > 99 ? '99+' : count;
            }
        } else if (badge) {
            badge.remove();
        }
    }

    async function poll() {
        try {
            const url = window.Prowave?.url ? `${window.Prowave.url}/notifications/recent` : '/notifications/recent';
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const data = await res.json();

            updateBadge(data.unread);

            const list = data.notifications || [];

            if (!initialized) {
                list.forEach((n) => seenNotifIds.add(n.id));
                initialized = true;
                return;
            }

            const fresh = list.filter((n) => !seenNotifIds.has(n.id) && !n.read);
            list.forEach((n) => seenNotifIds.add(n.id));

            if (fresh.length > 0) {
                fresh.reverse().forEach((n) => {
                    playSound();
                    showNotifToast(n);
                    showBrowserNotif(n);
                });
            }
        } catch (e) {
            // polling is best-effort
        }
    }

    function requestBrowserPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'granted') {
            browserNotifPerm = 'granted';
            return;
        }
        Notification.requestPermission().then((perm) => {
            browserNotifPerm = perm;
        });
    }

    function toggleNotifSound(enabled) {
        soundEnabled = enabled;
        localStorage.setItem('pw-notif-sound', enabled ? '1' : '0');
    }

    function isNotifSoundEnabled() {
        return soundEnabled;
    }

    function initRealtimeNotifications() {
        soundEnabled = localStorage.getItem('pw-notif-sound') !== '0';

        // Sound toggle button
        const soundToggle = document.getElementById('notifSoundToggle');
        if (soundToggle) {
            const soundIcon = soundToggle.querySelector('i');
            const renderSoundBtn = () => {
                if (soundEnabled) {
                    if (soundIcon) soundIcon.className = 'bi bi-volume-up';
                    soundToggle.classList.remove('muted');
                    soundToggle.title = 'Sound on — click to mute';
                } else {
                    if (soundIcon) soundIcon.className = 'bi bi-volume-mute';
                    soundToggle.classList.add('muted');
                    soundToggle.title = 'Sound off — click to enable';
                }
            };
            renderSoundBtn();
            soundToggle.addEventListener('click', () => {
                soundEnabled = !soundEnabled;
                localStorage.setItem('pw-notif-sound', soundEnabled ? '1' : '0');
                renderSoundBtn();
                if (soundEnabled) playSound();
            });
        }

        if ('Notification' in window) {
            browserNotifPerm = Notification.permission;
        }

        const bellBtn = document.querySelector('.icon-btn .bi-bell')?.closest('.icon-btn');
        if (bellBtn && 'Notification' in window && Notification.permission === 'default') {
            bellBtn.addEventListener('click', () => {
                if (Notification.permission === 'default') {
                    Notification.requestPermission().then((perm) => {
                        browserNotifPerm = perm;
                    });
                }
            }, { once: true });
        }

        // Initial notifications are already rendered server-side in navbar; poll in background only
        pollTimer = setInterval(poll, POLL_INTERVAL);

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                poll();
            }
        });
    }

    function stopRealtimeNotifications() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    window.Prowave.requestBrowserPermission = requestBrowserPermission;
    window.Prowave.toggleNotifSound = toggleNotifSound;
    window.Prowave.isNotifSoundEnabled = isNotifSoundEnabled;
    window.Prowave.initRealtimeNotifications = initRealtimeNotifications;
    window.Prowave.stopRealtimeNotifications = stopRealtimeNotifications;
})(window, document);
