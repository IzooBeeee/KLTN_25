import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let _subscribedUserId = null;
let _userChannel = null;
let _subscribedAdminId = null;
let _adminChannel = null;

const safeLeave = (channelName) => {
    if (window.Echo) {
        window.Echo.leave(channelName);
        console.log(`[Echo] Left channel: ${channelName}`);
    }
};

export const leaveAllChannels = () => {
    if (_subscribedUserId) safeLeave(`user.${_subscribedUserId}`);
    if (_subscribedAdminId) safeLeave(`admin.${_subscribedAdminId}`);
    _subscribedUserId = null;
    _userChannel = null;
    _subscribedAdminId = null;
    _adminChannel = null;
};

export const updateEchoToken = (token) => {
    const bearerToken = token ? `Bearer ${token}` : '';
    
    if (window.Echo?.options?.auth?.headers) {
        window.Echo.options.auth.headers.Authorization = bearerToken;
    }

    if (window.Echo?.connector?.options?.auth?.headers) {
        window.Echo.connector.options.auth.headers.Authorization = bearerToken;
    }
    
    console.log('[Echo] Token updated:', !!token);
};

export const subscribeUser = (userId, onNotify, isCustomer = false) => {
    if (!window.Echo || !userId) return null;

    const channelPrefix = isCustomer ? 'khach-hang' : 'user';
    const channelName = `${channelPrefix}.${userId}`;

    // Tránh subscribe lặp lại
    if (_subscribedUserId === channelName && _userChannel) return _userChannel;

    if (_subscribedUserId) safeLeave(_subscribedUserId);

    _subscribedUserId = channelName;
    _userChannel = window.Echo.private(channelName);

    if (onNotify) {
        // 1. Lắng nghe thông báo hệ thống (Laravel Notifications)
        _userChannel.notification((notification) => {
            console.log('[Echo] System notification:', notification);
            onNotify({ ...notification, loai: notification.loai || 'system' });
        });

        // 2. Lắng nghe tin nhắn chat trực tiếp (Custom Event)
        _userChannel.listen('.message.sent', (data) => {
            console.log('[Echo] Chat event received:', data);
            onNotify({ ...data, loai: 'tin_nhan' });
        });
    }

    console.log(`[Echo] Subscribed to private-${channelName}`);
    return _userChannel;
};

export const subscribeAdmin = (adminId, onNotify) => {
    if (!window.Echo || !adminId) return null;

    // Tránh subscribe lặp lại
    if (_subscribedAdminId === adminId && _adminChannel) return _adminChannel;

    if (_subscribedAdminId) safeLeave(`admin.${_subscribedAdminId}`);

    _subscribedAdminId = adminId;
    _adminChannel = window.Echo.private(`admin.${adminId}`);

    if (onNotify) {
        _adminChannel.notification((notification) => {
            console.log('[Echo] Admin notification:', notification);
            try {
                onNotify(notification);
            } catch (error) {
                console.error('[Echo] Admin notification handler failed:', error);
            }
        });
    }

    console.log(`[Echo] Subscribed to private-admin.${adminId}`);
    return _adminChannel;
};

// Aliases for compatibility
export const subscribeCustomer = subscribeUser;