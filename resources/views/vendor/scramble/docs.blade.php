<!doctype html>
<html lang="en" data-theme="{{ $config->get('ui.theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="color-scheme" content="{{ $config->get('ui.theme', 'light') }}">
    <meta name="theme-color" content="#dd3404">
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/marifatun-mark.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/marifatun-mark.svg">

    <title>{{ $config->get('ui.title') ?? config('app.name') . ' - API Docs' }}</title>

    {{-- ============================================================ --}}
    {{-- Marifatun API Docs - Bearer token auto-inject                 --}}
    {{-- ============================================================ --}}
    {{-- PENTING: interceptor fetch + XHR harus dipasang SEBELUM       --}}
    {{-- Stoplight Elements dimuat, karena library tsb bisa menangkap  --}}
    {{-- referensi `window.fetch` pada saat inisialisasi modul.        --}}
    <script>
        (function () {
            const MARIFATUN_TOKEN_KEY = 'marifatun_api_token';
            const LOGIN_PATH = '/api/v1/auth/login';
            const LOGOUT_PATH = '/api/v1/auth/logout';

            const getToken = () => localStorage.getItem(MARIFATUN_TOKEN_KEY) || '';
            const setToken = (token) => {
                if (token) {
                    localStorage.setItem(MARIFATUN_TOKEN_KEY, token);
                } else {
                    localStorage.removeItem(MARIFATUN_TOKEN_KEY);
                    clearStoplightAuthStorage();
                }
                // Sync input "Token" di UI. Fungsi ini didefinisikan di bawah;
                // karena pemanggilan hanya terjadi saat runtime (login/logout),
                // safe untuk menunggu deklarasinya.
                if (typeof syncAuthInputsToToken === 'function') {
                    requestAnimationFrame(syncAuthInputsToToken);
                    setTimeout(syncAuthInputsToToken, 150);
                    setTimeout(syncAuthInputsToToken, 600);
                }
            };

            function clearStoplightAuthStorage() {
                const isAuthKey = (k) =>
                    /stoplight|sl-|elements|mosaic|try[-_ ]?it|http[-_ ]?auth|security|bearer/i.test(k);
                [localStorage, sessionStorage].forEach((store) => {
                    try {
                        Object.keys(store).forEach((k) => { if (isAuthKey(k)) store.removeItem(k); });
                    } catch (e) { /* no-op */ }
                });
            }

            function marifatunToast(icon, title, text) {
                if (typeof Swal === 'undefined') {
                    // SweetAlert belum termuat -> coba lagi sebentar.
                    setTimeout(() => marifatunToast(icon, title, text), 150);
                    return;
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon,
                    title,
                    text,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
            }

            // ---------- expose helpers ke window ----------
            window.MARIFATUN = {
                getToken,
                setToken,
                clearStoplightAuthStorage,
                toast: marifatunToast,
                TOKEN_KEY: MARIFATUN_TOKEN_KEY,
            };

            // ---------- sync token ke input UI "Token" Stoplight ----------
            // Stoplight Elements render TryIt dalam light DOM, tapi kita tetap
            // telusuri shadow root sebagai pengaman. Gunakan native setter agar
            // React mendeteksi perubahan value dan meng-update state internalnya.
            const nativeInputValueSetter = Object.getOwnPropertyDescriptor(
                window.HTMLInputElement.prototype, 'value'
            ).set;

            function* walkAllInputs(root) {
                if (!root) return;
                try {
                    const inputs = root.querySelectorAll ? root.querySelectorAll('input') : [];
                    for (const el of inputs) yield el;
                    const all = root.querySelectorAll ? root.querySelectorAll('*') : [];
                    for (const el of all) {
                        if (el.shadowRoot) yield* walkAllInputs(el.shadowRoot);
                    }
                } catch (e) { /* no-op */ }
            }

            function isLikelyAuthInput(input) {
                // Heuristik: naik ke parent sampai 10 tingkat; kalau ada teks
                // "Auth" / "Security" / "Bearer" / "Token" -> anggap ini input auth.
                let el = input;
                for (let i = 0; i < 10 && el; i++, el = el.parentElement) {
                    const txt = ((el.textContent || '') + ' ' + (el.getAttribute?.('aria-label') || '')).slice(0, 300);
                    if (/\b(auth|security|bearer|token|authorization)\b/i.test(txt)) {
                        return true;
                    }
                }
                const ph = (input.getAttribute?.('placeholder') || '').toLowerCase();
                if (/token|bearer|auth/.test(ph)) return true;
                return false;
            }

            function syncAuthInputsToToken() {
                const token = getToken();
                for (const input of walkAllInputs(document)) {
                    if (!isLikelyAuthInput(input)) continue;
                    const desired = token; // string kosong kalau logout.
                    if ((input.value || '') === desired) continue;
                    try {
                        nativeInputValueSetter.call(input, desired);
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    } catch (e) { /* ignore */ }
                }
            }
            window.MARIFATUN.syncUi = syncAuthInputsToToken;

            // Observer: setiap DOM berubah (user pindah endpoint, toggle panel)
            // langsung sinkronkan input Token ke nilai terbaru.
            function startAuthUiObserver() {
                const target = document.body || document.documentElement;
                if (!target) {
                    requestAnimationFrame(startAuthUiObserver);
                    return;
                }
                const observer = new MutationObserver(() => {
                    // debounce kecil via rAF
                    cancelAnimationFrame(startAuthUiObserver._raf);
                    startAuthUiObserver._raf = requestAnimationFrame(syncAuthInputsToToken);
                });
                observer.observe(target, { childList: true, subtree: true });
                // juga jalan berkala sebagai jaring pengaman.
                setInterval(syncAuthInputsToToken, 1500);
                syncAuthInputsToToken();
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startAuthUiObserver);
            } else {
                startAuthUiObserver();
            }


            // ---------- fetch interceptor ----------
            const originalFetch = window.fetch.bind(window);

            const patchedFetch = async function (url, options = {}) {
                const urlStr = typeof url === 'string' ? url : (url?.url ?? '');
                const isLogin = urlStr.includes(LOGIN_PATH);
                const isLogout = urlStr.includes(LOGOUT_PATH);
                const headers = new Headers(options.headers || {});

                const csrfCookie = document.cookie
                    .split(';')
                    .find((c) => c.trim().startsWith('XSRF-TOKEN'));
                if (csrfCookie) {
                    headers.set('X-XSRF-TOKEN', decodeURIComponent(csrfCookie.split('=')[1]));
                }

                const token = getToken();
                if (isLogin) {
                    headers.delete('Authorization');
                } else if (token) {
                    headers.set('Authorization', 'Bearer ' + token);
                } else {
                    headers.delete('Authorization');
                }

                console.debug('[Marifatun] fetch ->', urlStr, 'Auth?', headers.has('Authorization'));

                const response = await originalFetch(url, { ...options, headers });

                if (isLogin && response.ok) {
                    try {
                        const json = await response.clone().json();
                        const newToken = json?.data?.token ?? json?.token;
                        const userInfo = json?.data?.user ?? json?.user ?? null;
                        if (newToken) {
                            setToken(newToken);
                            const whoami = userInfo ? (userInfo.name || userInfo.email || 'user') : 'user';
                            marifatunToast('success', 'Login berhasil - Token aktif',
                                'Halo ' + whoami + '! Token sudah otomatis terpasang di semua endpoint.');
                        }
                    } catch (e) { /* not JSON */ }
                }
                if (isLogout && response.ok) {
                    setToken(null);
                    marifatunToast('success', 'Logout berhasil - Token dihapus',
                        'Semua endpoint kini tidak memakai token apa pun.');
                }
                if (isLogin && !response.ok) {
                    marifatunToast('error', 'Login gagal',
                        'Periksa kembali email/password Anda. Token belum terpasang.');
                }
                if (isLogout && !response.ok) {
                    marifatunToast('warning', 'Logout gagal', 'Request logout tidak berhasil.');
                }

                return response;
            };

            // Kunci patched fetch agar library apapun yang nanti menyimpan
            // `window.fetch` ke variabel lokal tetap pakai versi kita.
            try {
                Object.defineProperty(window, 'fetch', {
                    configurable: false,
                    writable: false,
                    value: patchedFetch,
                });
            } catch (e) {
                window.fetch = patchedFetch;
            }

            // ---------- XMLHttpRequest interceptor (fallback) ----------
            const OriginalXHR = window.XMLHttpRequest;
            function PatchedXHR() {
                const xhr = new OriginalXHR();
                const origOpen = xhr.open;
                const origSetHeader = xhr.setRequestHeader;
                const origSend = xhr.send;
                let urlStr = '';
                let isLogin = false;
                let isLogout = false;
                let hasAuthFromCaller = false;

                xhr.open = function (method, url) {
                    urlStr = String(url || '');
                    isLogin = urlStr.includes(LOGIN_PATH);
                    isLogout = urlStr.includes(LOGOUT_PATH);
                    return origOpen.apply(xhr, arguments);
                };

                xhr.setRequestHeader = function (name, value) {
                    if (String(name).toLowerCase() === 'authorization') {
                        hasAuthFromCaller = true;
                        // Untuk login -> jangan kirim Authorization sama sekali.
                        if (isLogin) return;
                        // Kalau ada token kita, override value-nya.
                        const token = getToken();
                        if (token) {
                            return origSetHeader.call(xhr, name, 'Bearer ' + token);
                        }
                        // Tidak ada token -> drop saja.
                        return;
                    }
                    return origSetHeader.apply(xhr, arguments);
                };

                xhr.send = function () {
                    const token = getToken();
                    if (!isLogin && token && !hasAuthFromCaller) {
                        try { origSetHeader.call(xhr, 'Authorization', 'Bearer ' + token); } catch (e) {}
                    }
                    console.debug('[Marifatun] xhr ->', urlStr, 'token?', !!token, 'isLogin', isLogin);

                    xhr.addEventListener('load', function () {
                        try {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                if (isLogin) {
                                    const json = JSON.parse(xhr.responseText || '{}');
                                    const newToken = json?.data?.token ?? json?.token;
                                    const userInfo = json?.data?.user ?? json?.user ?? null;
                                    if (newToken) {
                                        setToken(newToken);
                                        const whoami = userInfo ? (userInfo.name || userInfo.email || 'user') : 'user';
                                        marifatunToast('success', 'Login berhasil - Token aktif',
                                            'Halo ' + whoami + '! Token sudah otomatis terpasang di semua endpoint.');
                                    }
                                }
                                if (isLogout) {
                                    setToken(null);
                                    marifatunToast('success', 'Logout berhasil - Token dihapus',
                                        'Semua endpoint kini tidak memakai token apa pun.');
                                }
                            } else {
                                if (isLogin) marifatunToast('error', 'Login gagal', 'Periksa email/password.');
                                if (isLogout) marifatunToast('warning', 'Logout gagal', 'Request logout tidak berhasil.');
                            }
                        } catch (e) { /* ignore */ }
                    });

                    return origSend.apply(xhr, arguments);
                };

                return xhr;
            }
            PatchedXHR.prototype = OriginalXHR.prototype;
            window.XMLHttpRequest = PatchedXHR;

            console.info('[Marifatun] fetch + XHR interceptor terpasang. Token saat ini:',
                getToken() ? '(ada)' : '(belum login)');
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@stoplight/elements@8.4.2/web-components.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@stoplight/elements@8.4.2/styles.min.css">

    <style>
        html, body { margin: 0; height: 100%; }
        body { background-color: var(--color-canvas); }

        [data-theme="dark"] .token.property { color: rgb(128, 203, 196) !important; }
        [data-theme="dark"] .token.operator { color: rgb(255, 123, 114) !important; }
        [data-theme="dark"] .token.number { color: rgb(247, 140, 108) !important; }
        [data-theme="dark"] .token.string { color: rgb(165, 214, 255) !important; }
        [data-theme="dark"] .token.boolean { color: rgb(121, 192, 255) !important; }
        [data-theme="dark"] .token.punctuation { color: #dbdbdb !important; }
    </style>
</head>
<body style="height: 100vh; overflow-y: hidden">
<elements-api
    id="docs"
    tryItCredentialsPolicy="{{ $config->get('ui.try_it_credentials_policy', 'include') }}"
    router="hash"
    @if($config->get('ui.hide_try_it')) hideTryIt="true" @endif
    @if($config->get('ui.hide_schemas')) hideSchemas="true" @endif
    @if($config->get('ui.logo')) logo="{{ $config->get('ui.logo') }}" @endif
    @if($config->get('ui.layout')) layout="{{ $config->get('ui.layout') }}" @endif
/>
<script>
    (async () => {
        const docs = document.getElementById('docs');
        docs.apiDescriptionDocument = @json($spec);
    })();
</script>

@if($config->get('ui.theme', 'light') === 'system')
    <script>
        var mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        function updateTheme(e) {
            if (e.matches) {
                window.document.documentElement.setAttribute('data-theme', 'dark');
                window.document.getElementsByName('color-scheme')[0].setAttribute('content', 'dark');
            } else {
                window.document.documentElement.setAttribute('data-theme', 'light');
                window.document.getElementsByName('color-scheme')[0].setAttribute('content', 'light');
            }
        }
        mediaQuery.addEventListener('change', updateTheme);
        updateTheme(mediaQuery);
    </script>
@endif
</body>
</html>
