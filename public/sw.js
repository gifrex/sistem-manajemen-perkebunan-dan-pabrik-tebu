// SW.js - Fixed version untuk production dengan subdirectory
const CACHE_VERSION = 'v10';
const CACHE_NAME = `sb-tebu-${CACHE_VERSION}`;
const STATIC_CACHE = `sb-tebu-static-${CACHE_VERSION}`;

// Detect base path dari current location
const getBasePath = () => {
    const pathname = self.location.pathname;
    // Jika SW ada di /tebu-new/sw.js, base path adalah /tebu-new/
    const match = pathname.match(/^(\/[^\/]+\/)/);
    return match ? match[1] : '/';
};

const BASE_PATH = getBasePath();

// Environment detection - IMPROVED
const isDev = () => {
    const hostname = self.location.hostname;
    const pathname = self.location.pathname;
    
    return hostname === 'localhost' || 
           hostname === '127.0.0.1' ||
           hostname.includes('192.168.') ||
           hostname.includes('.test') ||
           hostname.includes('.local') ||
           (self.location.port !== '' && self.location.port !== '80' && self.location.port !== '443');
};

// Updated static assets dengan base path
const STATIC_ASSETS = [
    `${BASE_PATH}manifest.json`,
    `${BASE_PATH}img/icon-sb-tebu-circle.png`, 
    `${BASE_PATH}img/icon-1024x1024.png`,
    `${BASE_PATH}asset/font-awesome-6.5.1-all.min.css`,
    `${BASE_PATH}asset/inter.css`,
    `${BASE_PATH}offline.html`
];

function logSW(message, data = null, level = 'info') {
    if (!isDev()) return;
    
    const prefix = '[SW]';
    const timestamp = new Date().toLocaleTimeString();
    
    switch(level) {
        case 'error':
            console.error(`${prefix} ${timestamp}`, message, data);
            break;
        case 'warn':
            console.warn(`${prefix} ${timestamp}`, message, data);
            break;
        default:
            console.log(`${prefix} ${timestamp}`, message, data);
    }
}

// Check if request is authentication related - IMPROVED
function isAuthRequest(url) {
    const authPatterns = [
        /\/login/,
        /\/logout/,
        /\/register/,
        /\/password/,
        /\/sanctum/,
        /\/csrf/,
        /\/user/,
        /\/auth/,
        /\/session/,
        /\/setSession/, // Tambahan untuk session controller
        /\/set-session/ // Tambahan untuk route session
    ];
    
    return authPatterns.some(pattern => pattern.test(url));
}

// FIXED: shouldNeverCache dengan base path awareness
function shouldNeverCache(url, method) {
    try {
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;
        
        // Auth requests must always go to network
        if (isAuthRequest(pathname)) {
            logSW('Auth request, skip cache:', url);
            return true;
        }
        
        // Only GET requests can be cached
        if (method !== 'GET') {
            return true;
        }
        
        // CRITICAL FIX: Handle both dev dan production build assets
        if (pathname.includes('/build/')) {
            if (isDev()) {
                logSW('Dev: Skip build asset (bypass SW):', url);
                return true;
            }
            // Di production, tetap skip untuk build assets
            logSW('Prod: Skip build asset:', url);
            return true;
        }
        
        // Skip URLs with dynamic parameters
        if (urlObj.search && !pathname.match(/\.(css|js)$/)) {
            return true;
        }
        
        // Laravel specific patterns - UPDATED with base path awareness
        const skipPatterns = [
            /\/api\//,
            /\/ajax\//,
            /\/livewire\//,
            /\/broadcasting\//,
            /\/storage\//,
            /\/build\/manifest\.json$/,
            /\/@vite\//,
            /\/__vite_ping/,
            /\/hot$/,
            /\/build\/.*\.js$/,
            /\/build\/.*\.css$/,
            /\/sw\.js/, // Skip service worker itself
            new RegExp(BASE_PATH.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + 'sw\\.js') // Dynamic SW path
        ];
        
        return skipPatterns.some(pattern => pattern.test(pathname));
        
    } catch (error) {
        logSW('Invalid URL:', url, 'warn');
        return true;
    }
}

function fetchWithTimeout(request, timeout = 10000) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
            reject(new Error('Request timeout'));
        }, timeout);

        fetch(request)
            .then(response => {
                clearTimeout(timer);
                resolve(response);
            })
            .catch(err => {
                clearTimeout(timer);
                reject(err);
            });
    });
}

// UPDATED fetch handler dengan base path awareness
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = request.url;
    const method = request.method;
    const destination = request.destination;
    
    // Skip non-HTTP
    if (!url.startsWith('http')) {
        return;
    }

    if (url.includes('amazonaws.com')) {
        return;
    }
    
    // CRITICAL: Bypass SW untuk service worker file itself
    if (url.includes('/sw.js')) {
        logSW('Bypassing SW file request:', url);
        return;
    }
    
    // Auth requests - NEVER cache, ALWAYS fetch fresh
    if (isAuthRequest(url)) {
        event.respondWith(
            fetchWithTimeout(request, 15000) // Longer timeout for auth
                .catch(error => {
                    logSW('Auth request failed:', error.message, 'error');
                    // Don't throw, return error response
                    return new Response('Network Error', { status: 503 });
                })
        );
        return;
    }
    
    // Skip dynamic content
    if (shouldNeverCache(url, method)) {
        if (isDev()) {
            logSW('Dev: Not intercepting:', url);
            return; // Let browser handle
        }
        event.respondWith(fetchWithTimeout(request));
        return;
    }
    
    // Continue with existing logic...
    // [Rest remains unchanged]
});

// Tambahkan message handler untuk debugging
self.addEventListener('message', event => {
    const { data } = event;
    
    if (data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (data.type === 'CLEAR_CACHE') {
        caches.keys().then(names => {
            Promise.all(names.map(name => caches.delete(name))).then(() => {
                logSW('All caches cleared');
                if (event.ports && event.ports[0]) {
                    event.ports[0].postMessage({ success: true });
                }
            });
        });
    }
    
    if (data.type === 'DEBUG_INFO') {
        logSW('SW Debug Info:', {
            basePath: BASE_PATH,
            isDev: isDev(),
            location: self.location,
            staticAssets: STATIC_ASSETS
        });
    }
});