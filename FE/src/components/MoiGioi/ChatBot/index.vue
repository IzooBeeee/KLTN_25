<template>
  <div class="chatbot-widget broker-chatbot">
    <button class="chatbot-toggle" @click="toggleChat" :class="{ active: isOpen }" aria-label="Hỗ trợ môi giới">
      <i v-if="!isOpen" class="fa-solid fa-headset"></i>
      <i v-else class="fa-solid fa-xmark"></i>
      <span v-if="unreadCount > 0" class="chatbot-badge">{{ unreadCount }}</span>
    </button>

    <transition name="chatbot-slide">
      <div v-if="isOpen" class="chatbot-window">
        <div class="chatbot-header broker-header">
          <div class="chatbot-avatar"><i class="fa-solid fa-headset"></i></div>
          <div class="chatbot-info">
            <h3 class="chatbot-name">Hỗ trợ Môi giới</h3>
            <span class="chatbot-status"><span class="status-dot"></span> Trực tuyến</span>
          </div>
          <button class="chatbot-close" @click="toggleChat" aria-label="Đóng chat">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="chatbot-messages" ref="messagesContainer">
          <div v-for="(msg, index) in messages" :key="index" class="chatbot-message" :class="{ bot: msg.type === 'bot', user: msg.type === 'user' }">
            <div class="message-avatar" v-if="msg.type === 'bot'"><i class="fa-solid fa-headset"></i></div>
            <div class="message-content">
              <p v-html="msg.text"></p>
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
            <div class="message-avatar"><i class="fa-solid fa-headset"></i></div>
            <div class="typing-indicator"><span></span><span></span><span></span></div>
          </div>
        </div>

        <!-- Quick questions for broker -->
        <div v-if="showQuickQuestions" class="chatbot-quick">
          <p class="quick-title">Bạn có thể hỏi nhanh:</p>
          <div class="quick-buttons">
            <button v-for="q in quickQuestions" :key="q.id" class="quick-btn" @click="sendQuickQuestion(q)">{{ q.label }}</button>
          </div>
        </div>

        <div class="chatbot-input">
          <input v-model="inputMessage" type="text" placeholder="VD: Tôi còn bao nhiêu lượt đăng?" @keyup.enter="sendMessage" ref="inputField" />
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

const ACTOR = 'broker';
const ROLE  = 'moi-gioi';

const messages = ref([{
  type: 'bot',
  text: 'Xin chào! Mình có thể hỗ trợ bạn về gói tin, cách mua gói, hướng dẫn đăng bài, quản lý tin đăng và lịch khách hẹn xem nhà.',
  time: getCurrentTime(),
}]);

const quickQuestions = [
  { id: 1, label: 'Gói tin đăng BĐS giá bao nhiêu?', text: 'Gói tin đăng BĐS giá bao nhiêu?' },
  { id: 2, label: 'Cách mua gói tin',                text: 'Cách mua gói tin' },
  { id: 3, label: 'Hướng dẫn đăng bài',              text: 'Hướng dẫn đăng bài' },
  { id: 4, label: 'Lịch khách hẹn xem nhà',          text: 'Lịch khách hẹn xem nhà' },
  { id: 5, label: 'Tôi còn bao nhiêu lượt đăng?',   text: 'Tôi còn bao nhiêu lượt đăng?' },
];

function getCurrentTime() {
  const now = new Date();
  return `${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}`;
}

function buildSessionId() {
  const token  = getToken(ROLE);
  const user   = getUserInfo(ROLE);
  if (token) return `broker:${user?.id || token.slice(-10)}`;
  let guestId = localStorage.getItem('chatbot_broker_guest_id');
  if (!guestId) {
    guestId = `broker:guest:${Math.random().toString(36).slice(2, 11)}`;
    localStorage.setItem('chatbot_broker_guest_id', guestId);
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

async function callChatbotAPI(text) {
  const response = await api.post('/chatbot', {
    message:    text,
    session_id: buildSessionId(),
    role:       ROLE,
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
      text: data.reply || 'Mình chưa xử lý được câu hỏi này. Bạn thử hỏi lại nhé.',
      quickReplies: data.quick_replies || [],
      time: getCurrentTime(),
    });
  } catch {
    messages.value.push({
      type: 'bot',
      text: 'Trợ lý đang bận. Bạn thử lại sau vài giây nhé.',
      quickReplies: ['Gói tin đăng BĐS', 'Hướng dẫn đăng bài', 'Lịch khách hẹn'],
      time: getCurrentTime(),
    });
  } finally {
    isTyping.value = false;
    scrollToBottom();
  }
}

function sendQuickQuestion(q) { inputMessage.value = q.text; sendMessage(); }
function sendQuickReply(reply) {
  inputMessage.value = typeof reply === 'string' ? reply : (reply?.label || '');
  sendMessage();
}

onMounted(() => {
  setTimeout(() => { if (!isOpen.value) unreadCount.value = 1; }, 12000);
});
</script>

<style scoped>
.chatbot-widget { position: fixed; bottom: 20px; right: 20px; z-index: 9999; font-family: 'Inter', 'Be Vietnam Pro', sans-serif; }
.chatbot-toggle { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); border: none; color: #fff; font-size: 1.4rem; cursor: pointer; box-shadow: 0 4px 20px rgba(16,185,129,.4); transition: all .3s ease; display: flex; align-items: center; justify-content: center; position: relative; }
.chatbot-toggle:hover { transform: scale(1.05); }
.chatbot-toggle.active { background: linear-gradient(135deg, #ef4444, #f87171); box-shadow: 0 4px 20px rgba(239,68,68,.4); }
.chatbot-badge { position: absolute; top: -2px; right: -2px; width: 20px; height: 20px; background: #ef4444; color: #fff; border-radius: 50%; font-size: .68rem; font-weight: 700; display: flex; align-items: center; justify-content: center; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }
.chatbot-window { position: absolute; bottom: 70px; right: 0; width: 380px; max-width: calc(100vw - 40px); height: 560px; max-height: calc(100vh - 120px); background: #fff; border-radius: 18px; box-shadow: 0 20px 60px rgba(0,0,0,.18); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #d1fae5; }
.chatbot-header.broker-header { padding: 1rem 1.25rem; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; gap: .75rem; }
.chatbot-avatar { width: 40px; height: 40px; background: rgba(255,255,255,.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; }
.chatbot-info { flex: 1; }
.chatbot-name { color: #fff; font-size: .95rem; font-weight: 700; margin: 0; }
.chatbot-status { color: rgba(255,255,255,.85); font-size: .72rem; display: flex; align-items: center; gap: .3rem; }
.status-dot { width: 7px; height: 7px; background: #a7f3d0; border-radius: 50%; animation: blink 2s infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.5} }
.chatbot-close { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,.15); border: none; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.chatbot-messages { flex: 1; overflow-y: auto; padding: 1rem; background: #f0fdf4; display: flex; flex-direction: column; gap: .7rem; }
.chatbot-message { display: flex; gap: .5rem; max-width: 92%; }
.chatbot-message.bot { align-self: flex-start; }
.chatbot-message.user { align-self: flex-end; flex-direction: row-reverse; }
.message-avatar { width: 30px; height: 30px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .8rem; flex-shrink: 0; }
.message-content { background: #fff; padding: .7rem .95rem; border-radius: 14px; border-bottom-left-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,.07); font-size: .82rem; line-height: 1.5; color: #1e293b; }
.chatbot-message.user .message-content { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-bottom-left-radius: 14px; border-bottom-right-radius: 4px; }
.message-time { font-size: .62rem; color: #94a3b8; margin-top: .2rem; display: block; }
.chatbot-message.user .message-time { color: rgba(255,255,255,.7); }
.typing-indicator { background: #fff; padding: .7rem .95rem; border-radius: 14px; border-bottom-left-radius: 4px; display: flex; align-items: center; gap: 4px; }
.typing-indicator span { width: 7px; height: 7px; background: #a7f3d0; border-radius: 50%; animation: typing 1.4s infinite; }
.typing-indicator span:nth-child(2){animation-delay:.2s} .typing-indicator span:nth-child(3){animation-delay:.4s}
@keyframes typing { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-5px)} }
.chatbot-quick { padding: .7rem 1rem; background: #fff; border-top: 1px solid #d1fae5; }
.quick-title { font-size: .72rem; color: #64748b; margin: 0 0 .5rem; }
.quick-buttons, .inline-replies { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .4rem; }
.quick-btn, .inline-replies button { padding: .35rem .7rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 20px; font-size: .72rem; color: #065f46; cursor: pointer; transition: all .2s; white-space: nowrap; }
.quick-btn:hover, .inline-replies button:hover { background: #d1fae5; border-color: #10b981; color: #047857; }
.chatbot-input { padding: .7rem 1rem; background: #fff; border-top: 1px solid #d1fae5; display: flex; gap: .5rem; }
.chatbot-input input { flex: 1; padding: .55rem .9rem; border: 1px solid #d1fae5; border-radius: 24px; font-size: .85rem; outline: none; transition: border-color .2s; }
.chatbot-input input:focus { border-color: #10b981; }
.send-btn { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); border: none; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .2s; }
.send-btn:hover:not(:disabled) { transform: scale(1.05); }
.send-btn:disabled { opacity: .5; cursor: not-allowed; }
.chatbot-slide-enter-active, .chatbot-slide-leave-active { transition: all .3s ease; }
.chatbot-slide-enter-from, .chatbot-slide-leave-to { opacity: 0; transform: translateY(20px) scale(.95); }
@media(max-width:480px) { .chatbot-window { width: calc(100vw - 40px); height: calc(100vh - 100px); right: 0; left: 0; margin: 0 auto; } }
</style>
