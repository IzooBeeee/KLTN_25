/**
 * Echo Service — Quản lý subscribe/unsubscribe WebSocket channels
 *
 * Dùng duy nhất WebSocket / Reverb cho thông báo realtime.
 * Channel names:
 *   - private-admin.{id}
 *   - private-user.{id}
 */

let _userChannel = null;
let _adminChannel = null;
let _subscribedUserId = null;
let _subscribedAdminId = null;

const safeLeave = (channelName) => {
    try {
        window.Echo?.leave(channelName);
    } catch (error) {
        console.warn('[Echo] Failed to leave channel:', channelName, error);
    }
};

export const updateEchoToken = (token) => {
    if (!window.Echo?.connector?.options?.auth?.headers) return;

    window.Echo.connector.options.auth.headers.Authorization = token
        ? `Bearer ${token}`
        : '';
};

export const subscribeUser = (userId, onNotify) => {
    if (!window.Echo || !userId) return null;

    if (_subscribedUserId === userId && _userChannel) return _userChannel;

    if (_subscribedUserId) {
        safeLeave(`user.${_subscribedUserId}`);
    }

    _subscribedUserId = userId;
    _userChannel = window.Echo.private(`user.${userId}`);

    if (onNotify) {
        _userChannel.notification((notification) => {
            console.log('[Echo] User notification:', notification);
            try {
                onNotify(notification);
            } catch (error) {
                console.error('[Echo] User notification handler failed:', error);
            }
        });
    }

    // Lắng nghe thêm event tin nhắn mới trên cùng channel
    _userChannel.listen('.message.sent', (data) => {
        console.log('[Echo] New message received:', data);
        if (onNotify) {
            try {
                // Đóng gói data lại giống format notification để xử lý chung
                onNotify({
                    id: 'msg_' + data.id,
                    loai: 'tin_nhan',
                    tieu_de: 'Tin nhắn mới từ ' + data.sender_name,
                    noi_dung: data.type === 'image' ? '[Hình ảnh]' : data.content,
                    khach_hang_id: data.sender_id,
                    conversation_id: data.conversation_id,
                    sender_type: data.sender_type
                });
            } catch (error) {
                console.error('[Echo] Message handler failed:', error);
            }
        }
    });

    console.log(`[Echo] Subscribed to private-user.${userId}`);
    return _userChannel;
};

export const subscribeAdmin = (adminId, onNotify) => {
    if (!window.Echo || !adminId) return null;

    if (_subscribedAdminId === adminId && _adminChannel) return _adminChannel;

    if (_subscribedAdminId) {
        safeLeave(`admin.${_subscribedAdminId}`);
    }

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

// KhachHang Channel
let _customerChannel = null;
let _subscribedCustomerId = null;

export const subscribeCustomer = (customerId, onNotify) => {
    if (!window.Echo || !customerId) return null;

    if (_subscribedCustomerId === customerId && _customerChannel) return _customerChannel;

    if (_subscribedCustomerId) {
        safeLeave(`khach-hang.${_subscribedCustomerId}`);
    }

    _subscribedCustomerId = customerId;
    _customerChannel = window.Echo.private(`khach-hang.${customerId}`);

    // Lắng nghe event tin nhắn mới trên cùng channel
    _customerChannel.listen('.message.sent', (data) => {
        console.log('[Echo] Customer message received:', data);
        if (onNotify) {
            try {
                onNotify({
                    id: 'msg_' + data.id,
                    loai: 'tin_nhan',
                    tieu_de: 'Tin nhắn mới từ ' + data.sender_name,
                    noi_dung: data.type === 'image' ? '[Hình ảnh]' : data.content,
                    moi_gioi_id: data.sender_id,
                    conversation_id: data.conversation_id,
                    sender_type: data.sender_type
                });
            } catch (error) {
                console.error('[Echo] Customer message handler failed:', error);
            }
        }
    });

    console.log(`[Echo] Subscribed to private-khach-hang.${customerId}`);
    return _customerChannel;
};

export const leaveAllChannels = () => {
    if (window.Echo) {
        if (_subscribedUserId) {
            safeLeave(`user.${_subscribedUserId}`);
        }
        if (_subscribedAdminId) {
            safeLeave(`admin.${_subscribedAdminId}`);
        }
        if (_subscribedCustomerId) {
            safeLeave(`khach-hang.${_subscribedCustomerId}`);
        }
    }

    _userChannel = null;
    _adminChannel = null;
    _customerChannel = null;
    _subscribedUserId = null;
    _subscribedAdminId = null;
    _subscribedCustomerId = null;
};