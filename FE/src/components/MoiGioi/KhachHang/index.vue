<template>
  <div class="page-content">
    <div class="broker-chat-container">
      
      <!-- SIDEBAR -->
      <aside class="chat-sidebar">
        <div class="chat-sidebar__header">
          <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0 fw-bold text-dark">Tin nhắn</h5>
            <span class="badge bg-emerald ms-2">{{ conversations.length }}</span>
            <div class="ms-auto dropdown">
              <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="#" @click.prevent="markAllAsRead">Đánh dấu tất cả đã đọc</a></li>
                <li><a class="dropdown-item" href="#">Cài đặt tin nhắn tự động</a></li>
              </ul>
            </div>
          </div>

          <div class="search-box">
            <i class="bx bx-search search-icon"></i>
            <input type="text" class="form-control" placeholder="Tìm kiếm khách hàng..." v-model="searchQuery" />
          </div>

          <div class="chat-filters mt-3">
            <button class="filter-btn active" @click="filterType = 'all'">Tất cả</button>
            <button class="filter-btn" @click="filterType = 'unread'" :class="{ active: filterType === 'unread' }">Chưa đọc</button>
            <button class="filter-btn" @click="filterType = 'interested'" :class="{ active: filterType === 'interested' }">Đang quan tâm</button>
          </div>
        </div>

        <div class="chat-sidebar__list">
          <!-- Loading -->
          <div v-if="loadingConversations" class="empty-state">
            <div class="spinner-border text-emerald" role="status"></div>
            <p class="mt-2">Đang tải tin nhắn...</p>
          </div>

          <!-- Empty -->
          <div v-else-if="filteredContacts.length === 0" class="empty-state">
            <i class="bx bx-chat"></i>
            <p>Chưa có cuộc trò chuyện nào</p>
          </div>
          
          <!-- List -->
          <a href="javascript:;" 
             v-for="contact in filteredContacts" 
             :key="contact.id" 
             class="chat-contact-item" 
             :class="{ 'is-active': activeChatId === contact.id }"
             @click="selectChat(contact.id)">
            
            <div class="contact-avatar">
              <img :src="getAvatarUrl(contact.khach_hang)" :alt="contact.khach_hang?.ten" />
              <span class="status-dot" :class="contact.khach_hang?.online ? 'online' : 'offline'"></span>
            </div>
            
            <div class="contact-info">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="contact-name mb-0">{{ contact.khach_hang?.ten || 'Khách hàng' }}</h6>
                <span class="contact-time">{{ formatTime(contact.updated_at || contact.created_at) }}</span>
              </div>
              <p class="contact-last-msg mb-0" :class="{ 'fw-bold text-dark': contact.unread_count > 0 }">
                {{ contact.last_message?.content || 'Bắt đầu trò chuyện...' }}
              </p>
              <div v-if="contact.bat_dong_san?.tieu_de" class="property-tag mt-1">
                <i class="bx bx-building-house"></i> {{ contact.bat_dong_san.tieu_de }}
              </div>
            </div>
            
            <div v-if="contact.unread_count > 0" class="unread-badge">{{ contact.unread_count > 9 ? '9+' : contact.unread_count }}</div>
          </a>
        </div>
      </aside>

      <!-- MAIN CHAT -->
      <main class="chat-main">
        <template v-if="activeConversationId">
          <header class="chat-main__header">
            <div class="d-flex align-items-center">
              <img :src="getAvatarUrl(activeContact?.khach_hang)" class="header-avatar" alt="" />
              <div class="ms-3">
                <h5 class="mb-0 fw-bold">{{ activeContact?.khach_hang?.ten || 'Đang tải...' }}</h5>
                <span class="status-text" :class="activeContact?.khach_hang?.online ? 'text-emerald' : 'text-muted'">
                  <i class="bx bxs-circle fs-6 me-1"></i>
                  {{ activeContact?.khach_hang?.online ? 'Đang hoạt động' : 'Ngoại tuyến' }}
                </span>
              </div>
            </div>
            
            <div class="header-actions">
              <button class="action-btn" title="Gọi điện" @click="makeCall">
                <i class="bx bx-phone"></i>
              </button>
              <button class="action-btn" title="Gửi hợp đồng/báo giá" @click="sendDocument">
                <i class="bx bx-file"></i>
              </button>
              <button class="action-btn" title="Thông tin khách hàng" @click="viewCustomerInfo">
                <i class="bx bx-info-circle"></i>
              </button>
            </div>
          </header>

          <div class="chat-main__content" ref="chatContent">
            <!-- Security Notice -->
            <div class="security-notice">
              <i class="bx bx-lock-alt"></i> Cuộc trò chuyện được mã hóa đầu cuối. Không chia sẻ mật khẩu của bạn.
            </div>

            <!-- Loading Messages -->
            <div v-if="loadingMessages" class="text-center py-4">
              <div class="spinner-border text-emerald spinner-border-sm" role="status"></div>
              <span class="ms-2 text-muted">Đang tải tin nhắn...</span>
            </div>

            <!-- Messages List -->
            <template v-else>
              <div v-for="msg in messages" :key="msg.id" class="message-wrapper" :class="msg.sender === 'me' ? 'is-me' : 'is-other'">
                <div class="message-bubble">
                  <p class="message-text">{{ msg.text }}</p>
                  <span class="message-time">{{ msg.time }}</span>
                </div>
              </div>
            </template>
          </div>

          <footer class="chat-main__footer">
            <div class="chat-input-wrapper">
              <button class="input-action-btn" title="Đính kèm" @click="attachFile">
                <i class="bx bx-plus-circle"></i>
              </button>
              <button class="input-action-btn" title="Gửi ảnh" @click="sendImage">
                <i class="bx bx-image"></i>
              </button>
              
              <input 
                type="text" 
                class="chat-input" 
                placeholder="Nhập tin nhắn hỗ trợ khách hàng..." 
                v-model="newMessage" 
                @keyup.enter="sendMessage" 
                :disabled="sending"
              />
              
              <button 
                class="send-btn" 
                :class="{ 'active': newMessage.trim() && !sending }" 
                @click="sendMessage"
                :disabled="!newMessage.trim() || sending"
              >
                <i v-if="!sending" class="bx bx-send"></i>
                <div v-else class="spinner-border spinner-border-sm text-white" role="status"></div>
              </button>
            </div>
          </footer>
        </template>
        
        <!-- Empty State -->
        <div v-else class="chat-empty-state">
          <div class="empty-icon">💬</div>
          <h4>Quản lý tin nhắn khách hàng</h4>
          <p>Chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu hỗ trợ tư vấn bất động sản.</p>
        </div>
      </main>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/axios/config';

// ===== STATE =====
const route = useRoute();
const searchQuery = ref('');
const filterType = ref('all');
const activeChatId = ref(null);
const activeConversationId = ref(null);
const newMessage = ref('');
const sending = ref(false);
const chatContent = ref(null);
let currentEchoChannel = null; // 🔥 Echo channel reference

// API Data
const conversations = ref([]);
const messages = ref([]);
const loadingConversations = ref(false);
const loadingMessages = ref(false);

// ===== COMPUTED =====
const filteredContacts = computed(() => {
  let list = [...conversations.value];
  
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(c => 
      c.khach_hang?.ten?.toLowerCase().includes(q) || 
      c.bat_dong_san?.tieu_de?.toLowerCase().includes(q)
    );
  }
  
  if (filterType.value === 'unread') {
    list = list.filter(c => c.unread_count > 0);
  }
  
  return list.sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at));
});

const activeContact = computed(() => {
  return conversations.value.find(c => c.id === activeConversationId.value) || null;
});

// ===== HELPERS =====
const getAuthToken = () => localStorage.getItem('moi_gioi_auth_token');

const getAvatarUrl = (user) => {
  if (user?.avatar) {
    const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
    return user.avatar.startsWith('http') ? user.avatar : `${base}/storage/${user.avatar}`;
  }
  const name = user?.ten || 'K';
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=10b981&color=fff&size=128`;
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  // Fix UTC parsing
  const safeDateStr = dateStr.endsWith('Z') ? dateStr : dateStr + 'Z';
  const date = new Date(safeDateStr);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);
  
  if (diff < 60) return 'Vừa xong';
  if (diff < 3600) return `${Math.floor(diff/60)}p`;
  if (diff < 86400) return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
};

const formatMessageTime = (dateStr) => {
  if (!dateStr) return '';
  const safeDateStr = dateStr.endsWith('Z') ? dateStr : dateStr + 'Z';
  return new Date(safeDateStr).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

const scrollToBottom = () => {
  if (chatContent.value) {
    chatContent.value.scrollTop = chatContent.value.scrollHeight;
  }
};

// ===== API FUNCTIONS =====

async function loadConversations() {
  loadingConversations.value = true;
  try {
    const res = await api.get('/moi-gioi/chat/conversations');
    conversations.value = res.data.data || [];
  } catch (err) {
    console.error('Lỗi load conversations:', err);
    conversations.value = [];
  } finally {
    loadingConversations.value = false;
  }
}

async function loadMessages() {
  if (!activeConversationId.value) return;
  
  loadingMessages.value = true;
  try {
    const res = await api.get(`/moi-gioi/chat/${activeConversationId.value}/messages`);
    
    messages.value = (res.data.data || []).map(msg => ({
      id: msg.id,
      text: msg.content,
      sender: msg.sender_type === 'moi_gioi' ? 'me' : 'other',
      time: formatMessageTime(msg.created_at),
      created_at: msg.created_at
    }));
    
    await nextTick();
    scrollToBottom();
    await markAsRead(activeConversationId.value);
    
  } catch (err) {
    console.error('Lỗi load messages:', err);
    messages.value = [];
  } finally {
    loadingMessages.value = false;
  }
}

async function sendMessage() {
  if (!newMessage.value.trim() || !activeConversationId.value || sending.value) return;
  
  const content = newMessage.value.trim();
  sending.value = true;
  
  try {
    const tempId = Date.now();
    
    messages.value.push({
      id: tempId,
      text: content,
      sender: 'me',
      time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
    });
    await nextTick();
    scrollToBottom();
    
    await api.post(`/moi-gioi/chat/${activeConversationId.value}/message`, { content });
    
    newMessage.value = '';
    
    const conv = conversations.value.find(c => c.id === activeConversationId.value);
    if (conv) {
      conv.last_message = { content };
      conv.updated_at = new Date().toISOString();
    }
    
  } catch (err) {
    console.error('Lỗi gửi tin:', err);
    messages.value = messages.value.filter(m => m.id !== tempId);
    alert('Gửi tin nhắn thất bại. Vui lòng thử lại.');
  } finally {
    sending.value = false;
  }
}

async function markAsRead(conversationId) {
  try {
    await api.post(`/moi-gioi/chat/${conversationId}/read`, {});
    const conv = conversations.value.find(c => c.id === conversationId);
    if (conv) conv.unread_count = 0;
  } catch (err) {
    console.warn('Lỗi mark as read:', err);
  }
}

async function markAllAsRead() {
  try {
    const unread = conversations.value.filter(c => c.unread_count > 0);
    await Promise.allSettled(
      unread.map(c => api.post(`/moi-gioi/chat/${c.id}/read`, {}))
    );
    conversations.value.forEach(c => c.unread_count = 0);
  } catch (err) {
    console.error('Lỗi mark all as read:', err);
  }
}

async function selectChat(id) {
  if (activeConversationId.value === id) return;
  
  activeChatId.value = id;
  activeConversationId.value = id;
  
  await loadMessages();
  
  // 🔥 BẬT ECHO REALTIME CHO ROOM NÀY
  listenRealtime(id);
}

// ===== ACTION BUTTONS =====
const makeCall = () => {
  const phone = activeContact.value?.khach_hang?.so_dien_thoai;
  if (phone) window.location.href = `tel:${phone}`;
};

const sendDocument = () => alert('Tính năng gửi tài liệu đang được phát triển');
const viewCustomerInfo = () => console.log('View customer:', activeContact.value?.khach_hang?.id);
const attachFile = () => alert('Tính năng đính kèm file đang được phát triển');
const sendImage = () => alert('Tính năng gửi ảnh đang được phát triển');


// 🚀 ===== ECHO (REVERB): CHAT REALTIME =====
function listenRealtime(conversationId) {
  // Check nếu Echo đã được init (trong app.js hoặc bootstrap.js)
  if (!window.Echo) {
    console.warn('❌ Laravel Echo chưa được khởi tạo. Kiểm tra bootstrap.js');
    return;
  }

  // 🔥 Rời channel cũ nếu có (tránh duplicate listeners)
  if (currentEchoChannel) {
    console.log('🔴 Leave channel:', currentEchoChannel);
    window.Echo.leave(currentEchoChannel);
    currentEchoChannel = null;
  }

  const channelName = `chat.${conversationId}`;
  currentEchoChannel = channelName;

  console.log('🟢 Join channel:', channelName);

  window.Echo.channel(channelName)
    .listen('.message.sent', (e) => {
      console.log('🔥 Chat realtime received:', e);

      // 🔥 Tránh duplicate: kiểm tra message đã tồn tại chưa
      if (messages.value.find(m => m.id === e.id)) {
        return;
      }

      // 🔥 Nếu đang xem đúng conversation này → push vào UI
      if (Number(e.conversation_id) === Number(activeConversationId.value)) {
        messages.value.push({
          id: e.id,
          text: e.content,
          sender: e.sender_type === 'moi_gioi' ? 'me' : 'other',
          time: formatMessageTime(e.created_at),
          created_at: e.created_at
        });

        nextTick(() => scrollToBottom());
      }

      // 🔥 Update sidebar: last message + unread count
      const conv = conversations.value.find(c => c.id === e.conversation_id);
      if (conv) {
        conv.last_message = { content: e.content };
        conv.updated_at = e.created_at;

        // Tăng unread nếu KHÔNG đang xem conversation này
        if (Number(e.conversation_id) !== Number(activeConversationId.value)) {
          conv.unread_count = (conv.unread_count || 0) + 1;
        }
      } else {
        // Nếu là conversation mới chưa có trong list → reload list
        loadConversations();
      }
    });
}

// ===== LIFECYCLE =====
onMounted(async () => {
  await loadConversations();
  
  // 🔥 Kiểm tra nếu có active_chat_id hoặc customer_id từ URL
  const queryChatId = route.query.active_chat_id;
  const queryCustomerId = route.query.customer_id;

  if (queryChatId) {
    const convId = Number(queryChatId);
    const exists = conversations.value.find(c => c.id === convId);
    if (exists) selectChat(convId);
    else {
      activeConversationId.value = convId;
      await loadMessages();
      listenRealtime(convId);
    }
  } else if (queryCustomerId) {
    // Tìm conversation của khách hàng này
    const customerId = Number(queryCustomerId);
    const conv = conversations.value.find(c => c.khach_hang?.id === customerId);
    if (conv) selectChat(conv.id);
    else {
        console.warn('Không tìm thấy cuộc hội thoại với khách hàng ID:', customerId);
    }
  }
});

onUnmounted(() => {
  // Cleanup Echo channel
  if (currentEchoChannel && window.Echo) {
    console.log('🔴 Cleanup Echo channel:', currentEchoChannel);
    window.Echo.leave(currentEchoChannel);
    currentEchoChannel = null;
  }
});

</script>

<style scoped>
/* ===== GIỮ NGUYÊN 100% CSS CŨ ===== */
.broker-chat-container {
  --emerald-50: #ecfdf5;
  --emerald-100: #d1fae5;
  --emerald-500: #10b981;
  --emerald-600: #059669;
  --emerald-700: #047857;
  --bg-gray: #f8fafc;
  --border-color: #e2e8f0;
  --text-dark: #1e293b;
  --text-muted: #64748b;
  
  display: flex;
  height: calc(100vh - 120px);
  min-height: 600px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  border: 1px solid var(--border-color);
  font-family: 'Inter', sans-serif;
}

.bg-emerald { background-color: var(--emerald-500) !important; color: white; }
.text-emerald { color: var(--emerald-500) !important; }

.chat-sidebar {
  width: 340px;
  background: white;
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
}

.chat-sidebar__header {
  padding: 20px;
  border-bottom: 1px solid var(--border-color);
}

.search-box {
  position: relative;
}
.search-box .search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  font-size: 1.2rem;
}
.search-box input {
  padding-left: 36px;
  border-radius: 99px;
  background: var(--bg-gray);
  border: 1px solid transparent;
  transition: all 0.2s;
}
.search-box input:focus {
  background: white;
  border-color: var(--emerald-500);
  box-shadow: 0 0 0 3px var(--emerald-100);
}

.chat-filters {
  display: flex;
  gap: 8px;
}
.filter-btn {
  background: var(--bg-gray);
  border: none;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.filter-btn.active, .filter-btn:hover {
  background: var(--emerald-50);
  color: var(--emerald-600);
}

.chat-sidebar__list {
  flex: 1;
  overflow-y: auto;
  padding: 10px 0;
}
.chat-sidebar__list::-webkit-scrollbar { width: 4px; }
.chat-sidebar__list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-muted);
}
.empty-state i { font-size: 3rem; color: #cbd5e1; margin-bottom: 10px; }
.empty-state .spinner-border { width: 1.5rem; height: 1.5rem; }

.chat-contact-item {
  display: flex;
  padding: 12px 20px;
  text-decoration: none;
  color: inherit;
  transition: background 0.2s;
  position: relative;
}
.chat-contact-item:hover { background: var(--bg-gray); }
.chat-contact-item.is-active {
  background: var(--emerald-50);
  border-right: 3px solid var(--emerald-500);
}

.contact-avatar {
  position: relative;
  width: 48px;
  height: 48px;
  flex-shrink: 0;
}
.contact-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}
.status-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
}
.status-dot.online { background: #10b981; }
.status-dot.offline { background: #94a3b8; }

.contact-info {
  flex: 1;
  min-width: 0;
  margin-left: 12px;
}
.contact-name { font-weight: 600; color: var(--text-dark); font-size: 0.95rem; }
.contact-time { font-size: 0.75rem; color: var(--text-muted); }
.contact-last-msg {
  font-size: 0.85rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.property-tag {
  display: inline-block;
  font-size: 0.7rem;
  padding: 2px 8px;
  background: #f1f5f9;
  color: #475569;
  border-radius: 4px;
  font-weight: 500;
}

.unread-badge {
  position: absolute;
  top: 50%;
  right: 20px;
  transform: translateY(-50%);
  min-width: 20px;
  height: 20px;
  background: var(--emerald-500);
  color: white;
  border-radius: 10px;
  font-size: 0.7rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--bg-gray);
  min-width: 0;
}

.chat-main__header {
  padding: 16px 24px;
  background: white;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-avatar { width: 44px; height: 44px; border-radius: 50%; }
.status-text { font-size: 0.8rem; font-weight: 500; display: flex; align-items: center; }

.header-actions { display: flex; gap: 12px; }
.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid var(--border-color);
  background: white;
  color: var(--text-dark);
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.2s;
}
.action-btn:hover { background: var(--emerald-50); color: var(--emerald-600); border-color: var(--emerald-200); }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.chat-main__content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.chat-main__content::-webkit-scrollbar { width: 6px; }
.chat-main__content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }

.security-notice {
  text-align: center;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

.message-wrapper {
  display: flex;
  flex-direction: column;
  max-width: 70%;
}
.message-wrapper.is-other { align-self: flex-start; }
.message-wrapper.is-me { align-self: flex-end; }

.message-bubble {
  padding: 12px 16px;
  border-radius: 18px;
  position: relative;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.is-other .message-bubble {
  background: white;
  color: var(--text-dark);
  border-bottom-left-radius: 4px;
}
.is-me .message-bubble {
  background: var(--emerald-600);
  color: white;
  border-bottom-right-radius: 4px;
}

.message-text { margin: 0; font-size: 0.95rem; line-height: 1.4; word-break: break-word; }
.message-time {
  font-size: 0.7rem;
  display: block;
  margin-top: 4px;
  text-align: right;
  opacity: 0.7;
}

.chat-main__footer {
  padding: 20px 24px;
  background: white;
  border-top: 1px solid var(--border-color);
}

.chat-input-wrapper {
  display: flex;
  align-items: center;
  background: var(--bg-gray);
  border-radius: 99px;
  padding: 6px 12px;
  border: 1px solid var(--border-color);
}

.input-action-btn {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 1.4rem;
  padding: 4px 8px;
  cursor: pointer;
  transition: color 0.2s;
}
.input-action-btn:hover { color: var(--emerald-500); }
.input-action-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.chat-input {
  flex: 1;
  background: transparent;
  border: none;
  padding: 8px 12px;
  outline: none;
  color: var(--text-dark);
}
.chat-input:disabled { opacity: 0.6; }

.send-btn {
  background: var(--emerald-500);
  color: white;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  opacity: 0.5;
  pointer-events: none;
}
.send-btn.active { opacity: 1; pointer-events: auto; }
.send-btn.active:hover { background: var(--emerald-600); transform: scale(1.05); }
.send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.send-btn .spinner-border { width: 1rem; height: 1rem; border-width: 2px; }

.chat-empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: white;
  text-align: center;
  color: var(--text-muted);
}
.chat-empty-state .empty-icon { font-size: 4rem; margin-bottom: 16px; animation: bounce 2s infinite ease-in-out; }
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

@media (max-width: 768px) {
  .broker-chat-container {
    flex-direction: column;
    height: 100vh;
  }
  .chat-sidebar { 
    width: 100%; 
    height: auto;
    max-height: 40%;
    border-right: none;
    border-bottom: 1px solid var(--border-color);
  }
  .chat-main { 
    height: 60%;
  }
}

.spinner-border {
  display: inline-block;
  border: 2px solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spinner-border 0.75s linear infinite;
}
@keyframes spinner-border {
  to { transform: rotate(360deg); }
}
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.15em;
}
.text-center { text-align: center; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.ms-2 { margin-left: 0.5rem; }
</style>