<template>
  <div class="chatbot-widget">
    <!-- Toggle Button -->
    <button 
      class="chatbot-toggle" 
      @click="toggleChat"
      :class="{ 'active': isOpen }"
      aria-label="Chat hỗ trợ"
    >
      <i v-if="!isOpen" class="fa-solid fa-robot"></i>
      <i v-else class="fa-solid fa-xmark"></i>
      <span v-if="unreadCount > 0" class="chatbot-badge">{{ unreadCount }}</span>
    </button>

    <!-- Chat Window -->
    <transition name="chatbot-slide">
      <div v-if="isOpen" class="chatbot-window">
        <!-- Header -->
        <div class="chatbot-header">
          <div class="chatbot-avatar">
            <i class="fa-solid fa-robot"></i>
          </div>
          <div class="chatbot-info">
            <h3 class="chatbot-name">SmartBot</h3>
            <span class="chatbot-status">
              <span class="status-dot"></span> Trực tuyến
            </span>
          </div>
          <button class="chatbot-close" @click="toggleChat" aria-label="Đóng chat">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Messages -->
        <div class="chatbot-messages" ref="messagesContainer">
          <div 
            v-for="(msg, index) in messages" 
            :key="index"
            class="chatbot-message"
            :class="{ 'bot': msg.type === 'bot', 'user': msg.type === 'user' }"
          >
            <div class="message-avatar" v-if="msg.type === 'bot'">
              <i class="fa-solid fa-robot"></i>
            </div>
            <div class="message-content">
              <p v-html="msg.text"></p>
              <span class="message-time">{{ msg.time }}</span>
            </div>
          </div>

          <!-- Typing indicator -->
          <div v-if="isTyping" class="chatbot-message bot typing">
            <div class="message-avatar">
              <i class="fa-solid fa-robot"></i>
            </div>
            <div class="typing-indicator">
              <span></span><span></span><span></span>
            </div>
          </div>
        </div>

        <!-- Quick Questions -->
        <div v-if="showQuickQuestions" class="chatbot-quick">
          <p class="quick-title">Câu hỏi thường gặp:</p>
          <div class="quick-buttons">
            <button 
              v-for="q in quickQuestions" 
              :key="q.id"
              class="quick-btn"
              @click="sendQuickQuestion(q)"
            >
              {{ q.label }}
            </button>
          </div>
        </div>

        <!-- Input -->
        <div class="chatbot-input">
          <input 
            v-model="inputMessage"
            type="text"
            placeholder="Nhập câu hỏi của bạn..."
            @keyup.enter="sendMessage"
            ref="inputField"
          />
          <button 
            class="send-btn"
            @click="sendMessage"
            :disabled="!inputMessage.trim()"
            aria-label="Gửi tin nhắn"
          >
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue';

const isOpen = ref(false);
const isTyping = ref(false);
const inputMessage = ref('');
const messagesContainer = ref(null);
const inputField = ref(null);
const unreadCount = ref(0);
const showQuickQuestions = ref(true);

const messages = ref([
  {
    type: 'bot',
    text: 'Xin chào! 👋 Tôi là SmartBot, trợ lý ảo của SmartEstate. Tôi có thể giúp bạn với:\n\n• Tìm kiếm bất động sản\n• Hướng dẫn đăng tin\n• Thông tin về gói tin\n• Cách liên hệ môi giới\n\nBạn cần hỗ trợ gì?',
    time: getCurrentTime()
  }
]);

const quickQuestions = [
  { id: 1, label: '🏠 Cách đăng tin BĐS', text: 'Làm sao để đăng tin bất động sản?' },
  { id: 2, label: '💰 Giá gói tin', text: 'Gói tin đăng BĐS giá bao nhiêu?' },
  { id: 3, label: '🔍 Tìm BĐS', text: 'Tôi muốn tìm bất động sản ở Đà Nẵng' },
  { id: 4, label: '📞 Liên hệ', text: 'Làm sao để liên hệ môi giới?' },
  { id: 5, label: '⭐ Đánh giá', text: 'Cách đánh giá môi giới như thế nào?' },
  { id: 6, label: '🔒 Bảo mật', text: 'Thông tin của tôi có được bảo mật không?' }
];

const responses = {
  'đăng tin': `Để đăng tin bất động sản, bạn cần:\n\n1. Đăng ký/Đăng nhập tài khoản môi giới\n2. Mua gói tin phù hợp\n3. Vào mục "Đăng tin" và điền thông tin\n4. Chờ admin duyệt tin (thường trong 24h)\n\n<a href="/moi-gioi/dang-nhap" target="_blank">👉 Đăng nhập môi giới</a>`,
  
  'gói tin': `Chúng tôi có các gói tin sau:\n\n• <b>Gói Cơ bản</b>: 100.000đ/30 ngày - 10 tin\n• <b>Gói Tiêu chuẩn</b>: 300.000đ/30 ngày - 50 tin\n• <b>Gói Chuyên nghiệp</b>: 500.000đ/30 ngày - 100 tin\n• <b>Gói Doanh nghiệp</b>: 1.000.000đ/30 ngày - Không giới hạn\n\n<a href="/moi-gioi/goi-tin" target="_blank">👉 Xem chi tiết gói tin</a>`,
  
  'tìm': `Để tìm bất động sản, bạn có thể:\n\n• Dùng thanh tìm kiếm ở trang chủ\n• Vào trang <a href="/khach-hang/danh-sach-bat-dong-san">Danh sách BĐS</a>\n• Xem trên <a href="/khach-hang/ban-do">Bản đồ</a>\n• Lọc theo giá, diện tích, loại BĐS\n\nBạn muốn tìm BĐS ở khu vực nào?`,
  
  'liên hệ': `Bạn có thể liên hệ qua:\n\n• 💬 Chat trực tiếp với môi giới trên trang chi tiết BĐS\n• 📧 Email: support@smartestate.com\n• 📞 Hotline: 1900 xxxx\n• 🌐 <a href="/khach-hang/lien-he">Trang liên hệ</a>`,
  
  'đánh giá': `Hệ thống đánh giá môi giới:\n\n• ⭐ 1-5 sao dựa trên chất lượng phục vụ\n• 📝 Nhận xét chi tiết sau khi giao dịch\n• 👀 Xem đánh giá trên profile môi giới\n• ✅ Chỉ khách hàng đã tương tác mới được đánh giá`,
  
  'bảo mật': `🔒 Chúng tôi cam kết:\n\n• Mã hóa thông tin cá nhân\n• Không chia sẻ dữ liệu cho bên thứ 3\n• Xác thực 2 lớp cho tài khoản\n• Tuân thủ quy định pháp luật về bảo mật\n\nXem thêm tại <a href="/chinh-sach-bao-mat">Chính sách bảo mật</a>`,
  
  'giá': `Để định giá BĐS, bạn có thể:\n\n• Dùng công cụ <a href="/khach-hang/dinh-gia-ai">Định giá AI</a>\n• Xem giá tham khảo các BĐS tương tự\n• Liên hệ môi giới để được tư vấn\n• Sử dụng tính năng <a href="/khach-hang/tinh-vay">Tính vay mua nhà</a>`,
  
  'vay': `Về vay mua nhà:\n\n• Tính toán ngưỡng trả nợ tại <a href="/khach-hang/tinh-vay">Tính vay</a>\n• So sánh lãi suất các ngân hàng\n• Tư vấn miễn phí từ chuyên gia\n• Hỗ trợ làm hồ sơ vay vốn`,
  
  'default': `Cảm ơn câu hỏi của bạn! 🙏\n\nTôi chưa có thông tin cụ thể về vấn đề này. Bạn có thể:\n\n• Liên hệ hotline 1900 xxxx\n• Gửi email đến support@smartestate.com\n• Chat với môi giới trực tiếp\n• Xem thêm tại <a href="/khach-hang/lien-he">trang liên hệ</a>`
};

function getCurrentTime() {
  const now = new Date();
  return `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
}

function toggleChat() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    unreadCount.value = 0;
    nextTick(() => {
      inputField.value?.focus();
      scrollToBottom();
    });
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}

function findResponse(text) {
  const lowerText = text.toLowerCase();
  for (const [key, response] of Object.entries(responses)) {
    if (lowerText.includes(key)) {
      return response;
    }
  }
  return responses.default;
}

function sendMessage() {
  const text = inputMessage.value.trim();
  if (!text) return;

  // Add user message
  messages.value.push({
    type: 'user',
    text: text,
    time: getCurrentTime()
  });

  inputMessage.value = '';
  showQuickQuestions.value = false;
  scrollToBottom();

  // Simulate typing
  isTyping.value = true;
  scrollToBottom();

  // Bot response after delay
  setTimeout(() => {
    isTyping.value = false;
    const response = findResponse(text);
    messages.value.push({
      type: 'bot',
      text: response,
      time: getCurrentTime()
    });
    scrollToBottom();
  }, 1500);
}

function sendQuickQuestion(q) {
  inputMessage.value = q.text;
  sendMessage();
}

onMounted(() => {
  // Show badge animation after 10 seconds
  setTimeout(() => {
    if (!isOpen.value) {
      unreadCount.value = 1;
    }
  }, 10000);
});
</script>

<style scoped>
.chatbot-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  font-family: 'Inter', 'Be Vietnam Pro', sans-serif;
}

/* Toggle Button */
.chatbot-toggle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border: none;
  color: #fff;
  font-size: 1.5rem;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.chatbot-toggle:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 25px rgba(59, 130, 246, 0.5);
}

.chatbot-toggle.active {
  background: linear-gradient(135deg, #ef4444, #f87171);
  box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4);
}

.chatbot-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  width: 22px;
  height: 22px;
  background: #ef4444;
  color: #fff;
  border-radius: 50%;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

/* Chat Window */
.chatbot-window {
  position: absolute;
  bottom: 75px;
  right: 0;
  width: 380px;
  max-width: calc(100vw - 40px);
  height: 550px;
  max-height: calc(100vh - 120px);
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

/* Header */
.chatbot-header {
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.chatbot-avatar {
  width: 42px;
  height: 42px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.2rem;
}

.chatbot-info { flex: 1; }
.chatbot-name {
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
}
.chatbot-status {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}
.status-dot {
  width: 8px;
  height: 8px;
  background: #10b981;
  border-radius: 50%;
  animation: blink 2s infinite;
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.chatbot-close {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}
.chatbot-close:hover { background: rgba(255, 255, 255, 0.25); }

/* Messages */
.chatbot-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.chatbot-message {
  display: flex;
  gap: 0.5rem;
  max-width: 85%;
}

.chatbot-message.bot { align-self: flex-start; }
.chatbot-message.user { 
  align-self: flex-end; 
  flex-direction: row-reverse;
}

.message-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.85rem;
  flex-shrink: 0;
}

.message-content {
  background: #fff;
  padding: 0.75rem 1rem;
  border-radius: 14px;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  font-size: 0.9rem;
  line-height: 1.5;
  color: #334155;
}

.chatbot-message.user .message-content {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: #fff;
  border-bottom-left-radius: 14px;
  border-bottom-right-radius: 4px;
}

.message-content :deep(a) {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
}
.chatbot-message.user .message-content :deep(a) {
  color: #bfdbfe;
}

.message-time {
  font-size: 0.65rem;
  color: #94a3b8;
  margin-top: 0.25rem;
  display: block;
}
.chatbot-message.user .message-time {
  color: rgba(255, 255, 255, 0.7);
}

/* Typing indicator */
.typing-indicator {
  background: #fff;
  padding: 0.75rem 1rem;
  border-radius: 14px;
  border-bottom-left-radius: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}
.typing-indicator span {
  width: 8px;
  height: 8px;
  background: #cbd5e1;
  border-radius: 50%;
  animation: typing 1.4s infinite;
}
.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-6px); }
}

/* Quick Questions */
.chatbot-quick {
  padding: 0.75rem 1rem;
  background: #fff;
  border-top: 1px solid #e2e8f0;
}
.quick-title {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0 0 0.5rem;
}
.quick-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.quick-btn {
  padding: 0.4rem 0.75rem;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  font-size: 0.75rem;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.quick-btn:hover {
  background: #dbeafe;
  border-color: #3b82f6;
  color: #2563eb;
}

/* Input */
.chatbot-input {
  padding: 0.75rem 1rem;
  background: #fff;
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 0.5rem;
}
.chatbot-input input {
  flex: 1;
  padding: 0.6rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
}
.chatbot-input input:focus {
  border-color: #3b82f6;
}
.send-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  border: none;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.send-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
}
.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Transitions */
.chatbot-slide-enter-active,
.chatbot-slide-leave-active {
  transition: all 0.3s ease;
}
.chatbot-slide-enter-from,
.chatbot-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Responsive */
@media (max-width: 480px) {
  .chatbot-window {
    width: calc(100vw - 40px);
    height: calc(100vh - 100px);
    right: 0;
    left: 0;
    margin: 0 auto;
  }
}
</style>
