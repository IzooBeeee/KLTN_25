// main.js
import { createApp } from "vue";
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import App from "../App.vue";
import router from "./router";
import VueToaster from "@meforma/vue-toaster";
import "./style.css";
import Swal from "sweetalert2";
import 'leaflet/dist/leaflet.css';
import 'boxicons/css/boxicons.min.css';

import api from "./axios/config";
import AdminLayout from "./layout/wrapper/Admin/index.vue";
import BlankLayout from "./layout/wrapper/Blank/index.vue";
import KhachHangLayout from "./layout/wrapper/KhachHang/index.vue";
import VueApexCharts from 'vue3-apexcharts';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// ✅ Khởi tạo Echo token theo role của URL hiện tại
// (Mỗi role dùng key riêng để không xung đột khi mở 3 tab cùng lúc)
const _currentPath = window.location.pathname;
const _tokenKey = _currentPath.startsWith('/admin')
  ? 'admin_auth_token'
  : _currentPath.startsWith('/moi-gioi')
    ? 'moi_gioi_auth_token'
    : 'khach_hang_auth_token';
const _initToken = localStorage.getItem(_tokenKey) || '';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
    wsPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    // ✅ Fix: VITE_API_URL đã có sẵn /api, nên không cộng thêm 'api/' thủ công nữa
    authEndpoint: `${import.meta.env.VITE_API_URL}/broadcasting/auth`,
    auth: {
        headers: {
            Authorization: _initToken ? `Bearer ${_initToken}` : '',
        }
    },
});

const echoConnection = window.Echo?.connector?.pusher?.connection;
if (echoConnection?.bind) {
    echoConnection.bind('connected', () => {
        console.log('[Echo] WebSocket connected ✅');
    });
    echoConnection.bind('error', (err) => {
        console.error('[Echo] WebSocket error:', err);
    });
    // ✅ Log auth attempts
    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        console.log('[Echo] Connection state changed:', states.current);
    });
}

// ✅ Log the Echo configuration for debugging
console.log('[Echo] Config:', {
    host: window.Echo.options.wsHost,
    port: window.Echo.options.wsPort,
    authEndpoint: window.Echo.options.authEndpoint,
    hasToken: !!window.Echo.options.auth?.headers?.Authorization
});

const app = createApp(App);

app.component("admin-layout", AdminLayout);
app.component("blank-layout", BlankLayout);
app.component("khach-hang-layout", KhachHangLayout);

app.use(VueApexCharts);
app.use(VueToaster, {
  position: "top-right",
  duration: 3500,
});
app.use(router);

app.config.globalProperties.$axios = api;
app.config.errorHandler = (err, instance, info) => {
  console.error("Vue Error:", err);
  console.error("Context:", instance);
  console.error("Info:", info);
};
app.mount("#app");