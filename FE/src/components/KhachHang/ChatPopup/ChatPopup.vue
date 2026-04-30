<template>
  <transition name="chat-slide">
    <div v-if="visible" class="chat-wrapper">
      
      <!-- CHAT CONTAINER -->
      <div class="chat-container">
        
        <!-- HEADER -->
        <div class="chat-header">
          <div class="header-left">
            <div class="broker-avatar">
              <span class="avatar-initial">{{ getBrokerInitial }}</span>
              <span class="online-dot"></span>
            </div>
            <div class="broker-info">
              <h4 class="broker-name">{{ brokerName }}</h4>
              <p class="broker-status">Đang hoạt động</p>
            </div>
          </div>
          <div class="header-actions">
            <button class="header-btn" @click="minimizeChat" title="Thu nhỏ">
              <span class="material-symbols-outlined">remove</span>
            </button>
            <button class="header-btn close-btn" @click="closeChat" title="Đóng">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
        </div>

        <!-- PROPERTY BADGE -->
        <div class="property-badge">
          <span class="material-symbols-outlined">home</span>
          <span class="property-name">{{ propertyName }}</span>
        </div>

        <!-- MESSAGES BODY -->
        <div class="chat-body" ref="chatBody">
          
          <!-- Empty State -->
          <div v-if="messages.length === 0" class="empty-state">
            <div class="empty-icon">💬</div>
            <p>Bắt đầu trò chuyện với môi giới</p>
            <span class="empty-hint">Nhập tin nhắn bên dưới để gửi</span>
          </div>

          <!-- Messages List -->
          <template v-else>
            <div 
              v-for="msg in messages" 
              :key="msg.id"
              :class="['message-wrapper', msg.isMine ? 'mine' : 'other']"
            >
              <div class="message-bubble">
                <p class="message-text">{{ msg.noi_dung }}</p>
                <span class="message-time">{{ formatTime(msg.created_at) }}</span>
              </div>
            </div>
          </template>
          
        </div>

        <!-- TYPING INDICATOR -->
        <div v-if="isTyping" class="typing-indicator">
          <span class="typing-dot"></span>
          <span class="typing-dot"></span>
          <span class="typing-dot"></span>
        </div>

        <!-- INPUT AREA -->
        <div class="chat-input-wrapper">
          <div class="chat-input-container">
            <button class="input-btn emoji-btn" title="Biểu cảm">
              <span class="material-symbols-outlined">sentiment_satisfied</span>
            </button>
            
            <input
              v-model="text"
              @keyup.enter="sendMessage"
              placeholder="Nhập tin nhắn..."
              class="message-input"
            />
            
            <button 
              @click="sendMessage" 
              :disabled="!text.trim()"
              class="input-btn send-btn"
              :class="{ active: text.trim() }"
              title="Gửi tin nhắn"
            >
              <span class="material-symbols-outlined">send</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </transition>
</template>

<script>
import api from "@/axios/config";

export default {
  name: "ChatPopup",

  data() {
    return {
      visible: false,
      conversationId: null,
      brokerName: "",
      propertyName: "",
      messages: [],
      text: "",
      isTyping: false
    };
  },
 

  computed: {
    getBrokerInitial() {
      if (!this.brokerName) return 'M';
      return this.brokerName.trim().charAt(0).toUpperCase();
    }
  },

  watch: {
    messages: {
      handler() {
        this.$nextTick(() => this.scrollToBottom());
      },
      deep: true
    }
  },

  mounted() {
  this._handler = (e) => this.openChat(e);
  window.addEventListener("open-chat", this._handler);

  this.interval = setInterval(() => {
    if (this.visible && this.conversationId) {
      this.loadMessages();
    }
  }, 3000);
},

beforeUnmount() {
  window.removeEventListener("open-chat", this._handler);
  clearInterval(this.interval);
},


  methods: {
  // 🔥 MỞ CHAT
  async openChat(e) {
    this.visible = true;
    this.conversationId = e.detail.conversationId;
    this.brokerName = e.detail.brokerName;
    this.propertyName = e.detail.propertyName;

    await this.loadMessages();
    this.$nextTick(() => this.scrollToBottom());
  },

  // 🔥 LOAD TIN NHẮN
  async loadMessages() {
    try {
      const token = localStorage.getItem("khach_hang_auth_token");

      const res = await api.get(`/khach-hang/chat/${this.conversationId}/messages`);

      // 🔥 FIX FIELD THEO BE
      this.messages = res.data.data.map(msg => ({
        ...msg,
        noi_dung: msg.content || msg.noi_dung, // 🔥 fallback
        isMine: msg.sender_type === "khach_hang"
      }));

      this.$nextTick(() => this.scrollToBottom());

    } catch (err) {
      console.error("Lỗi load chat:", err.response?.data || err);
    }
  },

  // 🔥 GỬI TIN
  async sendMessage() {
    if (!this.text.trim()) return;

    const content = this.text.trim();

    try {
      const token = localStorage.getItem("khach_hang_auth_token");

      await api.post(`/khach-hang/chat/${this.conversationId}/message`, { content });

      // 🔥 HIỆN NGAY (optimistic UI)
      this.messages.push({
        noi_dung: content,
        isMine: true,
        created_at: new Date().toISOString()
      });

      this.text = "";

      this.$nextTick(() => this.scrollToBottom());

    } catch (err) {
      console.error("Lỗi gửi:", err.response?.data || err);
    }
  },

  // 🔥 ĐÓNG CHAT
  closeChat() {
    this.visible = false;
  },

  // 🔥 THU NHỎ CHAT
  minimizeChat() {
    console.log("Minimize chat");
  },

  // 🔥 SCROLL
  scrollToBottom() {
    const el = this.$refs.chatBody;
    if (el) {
      el.scrollTop = el.scrollHeight;
    }
  },

  // 🔥 FORMAT TIME
  formatTime(dateStr) {
    if (!dateStr) return '';

    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return 'Vừa xong';
    if (diff < 3600) return `${Math.floor(diff / 60)}p`;
    if (diff < 86400)
      return date.toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit'
      });

    return date.toLocaleDateString('vi-VN', {
      day: '2-digit',
      month: '2-digit'
    });
  }
}
};
</script>

<style scoped>
/* ===== CHAT WRAPPER & ANIMATION ===== */
.chat-wrapper {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.chat-slide-enter-active,
.chat-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.chat-slide-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.98);
}
.chat-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.98);
}

/* ===== CHAT CONTAINER ===== */
.chat-container {
  width: 360px;
  height: 520px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15), 0 2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.08);
}

/* ===== HEADER ===== */
.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: linear-gradient(135deg, #0084ff, #0063cc);
  color: white;
  flex-shrink: 0;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
}

.broker-avatar {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
  flex-shrink: 0;
}

.online-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #31a24c;
  border-radius: 50%;
  border: 2px solid #0084ff;
}

.broker-info {
  min-width: 0;
}

.broker-name {
  font-size: 15px;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.broker-status {
  font-size: 12px;
  opacity: 0.9;
  margin: 2px 0 0;
}

.header-actions {
  display: flex;
  gap: 4px;
}

.header-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
  padding: 0;
}

.header-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.4);
}

.header-btn .material-symbols-outlined {
  font-size: 18px;
}

/* ===== PROPERTY BADGE ===== */
.property-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #f0f2f5;
  border-bottom: 1px solid #e4e6eb;
  font-size: 12px;
  color: #65676b;
  flex-shrink: 0;
}

.property-badge .material-symbols-outlined {
  font-size: 14px;
  color: #0084ff;
}

.property-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}

/* ===== CHAT BODY ===== */
.chat-body {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  gap: 8px;
  scroll-behavior: smooth;
}

.chat-body::-webkit-scrollbar {
  width: 6px;
}
.chat-body::-webkit-scrollbar-thumb {
  background: #bcc0c4;
  border-radius: 3px;
}
.chat-body::-webkit-scrollbar-track {
  background: transparent;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #65676b;
  text-align: center;
  padding: 20px;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 12px;
  opacity: 0.6;
}

.empty-state p {
  font-size: 14px;
  font-weight: 500;
  margin: 0 0 4px;
}

.empty-hint {
  font-size: 12px;
  opacity: 0.7;
}

/* Messages */
.message-wrapper {
  display: flex;
  max-width: 80%;
}

.message-wrapper.mine {
  margin-left: auto;
}

.message-wrapper.other {
  margin-right: auto;
}

.message-bubble {
  padding: 8px 12px;
  border-radius: 18px;
  position: relative;
  word-wrap: break-word;
}

.message-wrapper.mine .message-bubble {
  background: linear-gradient(135deg, #0084ff, #0063cc);
  color: white;
  border-bottom-right-radius: 4px;
}

.message-wrapper.other .message-bubble {
  background: #f0f2f5;
  color: #1c1e21;
  border-bottom-left-radius: 4px;
}

.message-text {
  font-size: 14px;
  line-height: 1.4;
  margin: 0 0 4px;
}

.message-time {
  font-size: 10px;
  opacity: 0.7;
  display: block;
  text-align: right;
}

.message-wrapper.mine .message-time {
  color: rgba(255, 255, 255, 0.8);
}

.message-wrapper.other .message-time {
  color: #65676b;
}

/* ===== TYPING INDICATOR ===== */
.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 12px 16px 8px;
  background: #f0f2f5;
}

.typing-dot {
  width: 8px;
  height: 8px;
  background: #65676b;
  border-radius: 50%;
  animation: typingBounce 1.4s infinite ease-in-out;
}

.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingBounce {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-6px); }
}

/* ===== INPUT AREA ===== */
.chat-input-wrapper {
  padding: 12px 16px;
  background: #ffffff;
  border-top: 1px solid #e4e6eb;
  flex-shrink: 0;
}

.chat-input-container {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f0f2f5;
  border-radius: 24px;
  padding: 6px 12px;
}

.input-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: #65676b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
  flex-shrink: 0;
}

.input-btn:hover {
  background: rgba(0, 0, 0, 0.08);
  color: #1c1e21;
}

.input-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.input-btn.send-btn.active {
  background: #0084ff;
  color: white;
}

.input-btn.send-btn.active:hover {
  background: #0063cc;
  transform: scale(1.05);
}

.input-btn .material-symbols-outlined {
  font-size: 20px;
}

.message-input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 14px;
  color: #1c1e21;
  outline: none;
  padding: 6px 4px;
}

.message-input::placeholder {
  color: #65676b;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
  .chat-wrapper {
    bottom: 0;
    right: 0;
    left: 0;
  }
  .chat-container {
    width: 100%;
    height: 100vh;
    border-radius: 0;
    max-height: 100vh;
  }
}

/* ===== MATERIAL ICONS ===== */
.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
  user-select: none;
}
</style>