<template>
  <div class="chatbot-widget">
    <button class="chatbot-toggle" @click="toggleChat" :class="{ active: isOpen }" aria-label="Chat hỗ trợ">
      <i v-if="!isOpen" class="fa-solid fa-robot"></i>
      <i v-else class="fa-solid fa-xmark"></i>
      <span v-if="unreadCount > 0" class="chatbot-badge">{{ unreadCount }}</span>
    </button>

    <transition name="chatbot-slide">
      <div v-if="isOpen" class="chatbot-window">
        <div class="chatbot-header">
          <div class="chatbot-avatar"><i class="fa-solid fa-robot"></i></div>
          <div class="chatbot-info">
            <h3 class="chatbot-name">AI Real Estate Assistant</h3>
            <span class="chatbot-status"><span class="status-dot"></span> Trực tuyến</span>
          </div>
          <button class="chatbot-close" @click="toggleChat" aria-label="Đóng chat">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="chatbot-messages" ref="messagesContainer">
          <div v-for="(msg, index) in messages" :key="index" class="chatbot-message" :class="{ bot: msg.type === 'bot', user: msg.type === 'user' }">
            <div class="message-avatar" v-if="msg.type === 'bot'"><i class="fa-solid fa-robot"></i></div>
            <div class="message-content">
              <p v-html="msg.text"></p>
              <div v-if="msg.suggestions?.length" class="property-cards">
                <a v-for="item in msg.suggestions" :key="item.id" class="property-card" :href="item.url || '#'" target="_blank">
                  <img v-if="item.anh_dai_dien_url" :src="item.anh_dai_dien_url" alt="Hình ảnh" />
                  <div class="property-card-body">
                    <b>{{ item.title || item.tieu_de }}</b>
                    <span>{{ item.gia_text || formatMoney(item.gia) }}<template v-if="item.dien_tich"> · {{ item.dien_tich }}m²</template></span>
                    <small v-if="item.dia_chi">{{ item.dia_chi }}</small>
                  </div>
                </a>
              </div>
              <div v-if="msg.quickReplies?.length" class="inline-replies">
                <button
                  v-for="reply in msg.quickReplies"
                  :key="reply.label || reply"
                  @click="sendQuickReply(reply)"
                >
                  {{ reply.label || reply }}
                </button>
              </div>
              <span class="message-time">{{ msg.time }}</span>
            </div>
          </div>

          <div v-if="isTyping" class="chatbot-message bot typing">
            <div class="message-avatar"><i class="fa-solid fa-robot"></i></div>
            <div class="typing-indicator"><span></span><span></span><span></span></div>
          </div>
        </div>

        <div v-if="showQuickQuestions" class="chatbot-quick">
          <p class="quick-title">Bạn có thể hỏi nhanh:</p>
          <div class="quick-buttons">
            <button v-for="q in quickQuestions" :key="q.id" class="quick-btn" @click="sendQuickQuestion(q)">{{ q.label }}</button>
          </div>
        </div>

        <div class="chatbot-input">
          <input v-model="inputMessage" type="text" placeholder="VD: Tìm căn hộ Sơn Trà khoảng 2 tỷ..." @keyup.enter="sendMessage" ref="inputField" />
          <button class="send-btn" @click="sendMessage" :disabled="!inputMessage.trim() || isTyping" aria-label="Gửi tin nhắn">
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue';
import api from '@/axios/config';
import { getToken, getUserInfo } from '@/js/auth';

const isOpen = ref(false);
const isTyping = ref(false);
const inputMessage = ref('');
const messagesContainer = ref(null);
const inputField = ref(null);
const unreadCount = ref(0);
const showQuickQuestions = ref(true);

const ACTOR = 'customer';

const messages = ref([{ type: 'bot', text: 'Xin chào! Mình có thể giúp bạn tìm BĐS, lọc theo ngân sách/khu vực, xem chi tiết, định giá và đặt lịch xem nhà. Bạn đang cần tìm căn như thế nào?', time: getCurrentTime() }]);

const quickQuestions = [
  { id: 1, label: 'Tìm căn hộ 4 tỷ',           text: 'Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ' },
  { id: 2, label: 'Tìm nhà gần biển',            text: 'Gợi ý nhà gần biển, view đẹp' },
  { id: 3, label: 'Định giá BĐS',                text: 'Tôi muốn định giá bất động sản' },
  { id: 4, label: 'Đặt lịch xem nhà',            text: 'Tôi muốn đặt lịch xem nhà' },
  { id: 5, label: 'Làm môi giới như thế nào?',  text: 'Làm sao trở thành môi giới?' },
];

function getCurrentTime() {
  const now = new Date();
  return `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
}

function getRoleFromPath() {
  if (window.location.pathname.startsWith('/moi-gioi')) return 'moi-gioi';
  return 'khach-hang';
}

function buildSessionId() {
  const role = getRoleFromPath();
  const token = getToken(role);
  const user = getUserInfo(role);
  if (token) return `${role === 'moi-gioi' ? 'mg' : 'kh'}_${user?.id || token.slice(-10)}`;
  let guestId = localStorage.getItem('chatbot_guest_id');
  if (!guestId) {
    guestId = `guest_${Math.random().toString(36).slice(2, 11)}`;
    localStorage.setItem('chatbot_guest_id', guestId);
  }
  return guestId;
}

function toggleChat() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    unreadCount.value = 0;
    nextTick(() => { inputField.value?.focus(); scrollToBottom(); });
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  });
}

function formatMoney(value) {
  const amount = Number(value || 0);
  if (amount >= 1_000_000_000) return `${(amount / 1_000_000_000).toFixed(1).replace('.0', '')} tỷ`;
  if (amount >= 1_000_000) return `${Math.round(amount / 1_000_000)} triệu`;
  return amount.toLocaleString('vi-VN') + 'đ';
}

async function callChatbotAPI(text) {
  const response = await api.post('/chatbot', {
    message:    text,
    session_id: buildSessionId(),
    role:       getRoleFromPath(),
    actor:      ACTOR,
  }, { timeout: 30000 });
  return response.data?.data || {};
}

async function sendMessage() {
  const text = inputMessage.value.trim();
  if (!text || isTyping.value) return;
  messages.value.push({ type: 'user', text, time: getCurrentTime() });
  inputMessage.value = '';
  showQuickQuestions.value = false;
  isTyping.value = true;
  scrollToBottom();

  try {
    const data = await callChatbotAPI(text);
    messages.value.push({
      type: 'bot',
      text: data.reply || 'Mình chưa xử lý được câu hỏi này. Bạn thử hỏi lại rõ hơn nhé.',
      suggestions: data.suggestions || [],
      quickReplies: data.quick_replies || [],
      time: getCurrentTime(),
    });
  } catch (error) {
    messages.value.push({
      type: 'bot',
      text: 'Trợ lý AI đang kết nối chưa ổn định. Bạn thử lại sau vài giây, hoặc hỏi ngắn gọn theo mẫu: tìm căn hộ Đà Nẵng khoảng 2 tỷ.',
      quickReplies: ['Tìm BĐS', 'Định giá', 'Gói tin'],
      time: getCurrentTime(),
    });
  } finally {
    isTyping.value = false;
    scrollToBottom();
  }
}

function sendQuickQuestion(q) { inputMessage.value = q.text; sendMessage(); }
function sendQuickText(text) { inputMessage.value = text; sendMessage(); }

function sendQuickReply(reply) {
  if (typeof reply === 'string') return sendQuickText(reply);
  if (reply?.action_type) {
    return sendActionReply(reply);
  }
  return sendQuickText(reply?.label || '');
}

async function sendActionReply(reply) {
  const payloadText = reply.payload?.text || reply.label || '';
  messages.value.push({ type: 'user', text: payloadText, time: getCurrentTime() });
  showQuickQuestions.value = false;
  isTyping.value = true;
  scrollToBottom();

  try {
    const response = await api.post('/chatbot', {
      message: payloadText,
      session_id: buildSessionId(),
      role: getRoleFromPath(),
      action_type: reply.action_type,
      action_payload: reply.payload || {},
    }, { timeout: 30000 });
    const data = response.data?.data || {};
    messages.value.push({
      type: 'bot',
      text: data.reply || 'Mình đã xử lý yêu cầu của bạn.',
      suggestions: data.suggestions || [],
      quickReplies: data.quick_replies || [],
      time: getCurrentTime(),
    });
  } catch (error) {
    messages.value.push({
      type: 'bot',
      text: 'Trợ lý AI đang kết nối chưa ổn định. Bạn thử lại sau vài giây nhé.',
      quickReplies: ['Tìm BĐS', 'Định giá', 'Gói tin'],
      time: getCurrentTime(),
    });
  } finally {
    isTyping.value = false;
    scrollToBottom();
  }
}

onMounted(() => {
  setTimeout(() => { if (!isOpen.value) unreadCount.value = 1; }, 10000);
});
</script>

<style scoped>
.chatbot-widget { position: fixed; bottom: 20px; right: 20px; z-index: 9999; font-family: 'Inter', 'Be Vietnam Pro', sans-serif; }
.chatbot-toggle { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); border: none; color: #fff; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4); transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; position: relative; }
.chatbot-toggle:hover { transform: scale(1.05); box-shadow: 0 6px 25px rgba(59, 130, 246, 0.5); }
.chatbot-toggle.active { background: linear-gradient(135deg, #ef4444, #f87171); box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4); }
.chatbot-badge { position: absolute; top: -2px; right: -2px; width: 22px; height: 22px; background: #ef4444; color: #fff; border-radius: 50%; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
.chatbot-window { position: absolute; bottom: 75px; right: 0; width: 400px; max-width: calc(100vw - 40px); height: 600px; max-height: calc(100vh - 120px); background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #e2e8f0; }
.chatbot-header { padding: 1rem 1.25rem; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; gap: 0.75rem; }
.chatbot-avatar { width: 42px; height: 42px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; }
.chatbot-info { flex: 1; } .chatbot-name { color: #fff; font-size: 1rem; font-weight: 700; margin: 0; } .chatbot-status { color: rgba(255, 255, 255, 0.85); font-size: 0.75rem; display: flex; align-items: center; gap: 0.35rem; }
.status-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: blink 2s infinite; } @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.chatbot-close { width: 32px; height: 32px; border-radius: 50%; background: rgba(255, 255, 255, 0.15); border: none; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; } .chatbot-close:hover { background: rgba(255, 255, 255, 0.25); }
.chatbot-messages { flex: 1; overflow-y: auto; padding: 1rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.75rem; }
.chatbot-message { display: flex; gap: 0.5rem; max-width: 92%; } .chatbot-message.bot { align-self: flex-start; } .chatbot-message.user { align-self: flex-end; flex-direction: row-reverse; }
.message-avatar { width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.85rem; flex-shrink: 0; }
.message-content { background: #fff; padding: 0.75rem 1rem; border-radius: 14px; border-bottom-left-radius: 4px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); font-size: 0.7rem; line-height: 1.5; color: #334155; }
.chatbot-message.user .message-content { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border-bottom-left-radius: 14px; border-bottom-right-radius: 4px; }
.message-content :deep(a) { color: #3b82f6; text-decoration: none; font-weight: 600; } .chatbot-message.user .message-content :deep(a) { color: #bfdbfe; }
.message-time { font-size: 0.65rem; color: #94a3b8; margin-top: 0.25rem; display: block; } .chatbot-message.user .message-time { color: rgba(255, 255, 255, 0.7); }
.typing-indicator { background: #fff; padding: 0.75rem 1rem; border-radius: 14px; border-bottom-left-radius: 4px; display: flex; align-items: center; gap: 4px; } .typing-indicator span { width: 8px; height: 8px; background: #cbd5e1; border-radius: 50%; animation: typing 1.4s infinite; } .typing-indicator span:nth-child(2) { animation-delay: 0.2s; } .typing-indicator span:nth-child(3) { animation-delay: 0.4s; } @keyframes typing { 0%, 60%, 100% { transform: translateY(0); } 30% { transform: translateY(-6px); } }
.chatbot-quick { padding: 0.75rem 1rem; background: #fff; border-top: 1px solid #e2e8f0; } .quick-title { font-size: 0.75rem; color: #64748b; margin: 0 0 0.5rem; } .quick-buttons, .inline-replies { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem; }
.quick-btn, .inline-replies button { padding: 0.4rem 0.75rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 20px; font-size: 0.75rem; color: #475569; cursor: pointer; transition: all 0.2s; white-space: nowrap; } .quick-btn:hover, .inline-replies button:hover { background: #dbeafe; border-color: #3b82f6; color: #2563eb; }
.property-cards { display: grid; gap: 0.5rem; margin-top: 0.75rem; } .property-card { display: flex; gap: 0.6rem; padding: 0.55rem; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; color: inherit; text-decoration: none; } .property-card img { width: 58px; height: 58px; object-fit: cover; border-radius: 10px; flex-shrink: 0; } .property-card-body { display: flex; flex-direction: column; gap: 0.15rem; min-width: 0; } .property-card-body b { font-size: 0.8rem; color: #1e293b; } .property-card-body span { color: #2563eb; font-weight: 700; font-size: 0.78rem; } .property-card-body small { color: #64748b; font-size: 0.7rem; }
.chatbot-input { padding: 0.75rem 1rem; background: #fff; border-top: 1px solid #e2e8f0; display: flex; gap: 0.5rem; } .chatbot-input input { flex: 1; padding: 0.6rem 1rem; border: 1px solid #e2e8f0; border-radius: 24px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; } .chatbot-input input:focus { border-color: #3b82f6; }
.send-btn { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; } .send-btn:hover:not(:disabled) { transform: scale(1.05); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35); } .send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.chatbot-slide-enter-active, .chatbot-slide-leave-active { transition: all 0.3s ease; } .chatbot-slide-enter-from, .chatbot-slide-leave-to { opacity: 0; transform: translateY(20px) scale(0.95); }
@media (max-width: 480px) { .chatbot-window { width: calc(100vw - 40px); height: calc(100vh - 100px); right: 0; left: 0; margin: 0 auto; } }
</style>
