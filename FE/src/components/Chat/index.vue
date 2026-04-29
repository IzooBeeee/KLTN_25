<!-- src/components/ChatSupport.vue -->
<template>
  <div class="chat-support-container">
    <!-- Floating Button -->
    <button
      @click="toggleChat"
      class="chat-button"
      :class="{ 'chat-button--active': isOpen }"
    >
      <span class="material-symbols-outlined">
        {{ isOpen ? 'close' : 'chat' }}
      </span>
      <span class="chat-button__text">Hỗ trợ</span>
      <span v-if="!isOpen && hasNotification" class="chat-button__badge"></span>
    </button>

    <!-- Chat Window -->
    <transition name="chat-slide">
      <div v-if="isOpen" class="chat-window">
        <!-- Header -->
        <div class="chat-window__header">
          <div class="chat-window__header-info">
            <div class="chat-window__avatar">
              <span class="material-symbols-outlined">support_agent</span>
            </div>
            <div>
              <h4 class="chat-window__title">Hỗ trợ trực tuyến</h4>
              <p class="chat-window__status">
                <span class="status-dot status-dot--online"></span>
                Đang online
              </p>
            </div>
          </div>
          <button @click="toggleChat" class="chat-window__close">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <!-- Messages -->
        <div class="chat-window__messages" ref="messagesContainer">
          <div
            v-for="(msg, index) in messages"
            :key="index"
            :class="['message', msg.type]"
          >
            <div class="message__content">
              <p>{{ msg.text }}</p>
              <span class="message__time">{{ msg.time }}</span>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="chat-window__input">
          <input
            v-model="newMessage"
            @keyup.enter="sendMessage"
            type="text"
            placeholder="Nhập tin nhắn..."
            class="chat-input"
          />
          <button @click="sendMessage" class="send-button">
            <span class="material-symbols-outlined">send</span>
          </button>
        </div>
      </div>
    </transition>
  </div>
  <!-- <nav class="iq-float-menu">
         <input type="checkbox" href="#" class="iq-float-menu-open" name="menu-open" id="menu-open">
         <label class="iq-float-menu-open-button" for="menu-open">
            <span class="lines line-1"></span>
            <span class="lines line-2"></span>
            <span class="lines line-3"></span>
         </label>
         <button class="iq-float-menu-item bg-info" data-toggle="tooltip" data-placement="top" title="" data-mode="rtl" data-active="true" data-original-title="Direction Mode"><i class="ri-text-direction-r"></i></button>
         <button class="iq-float-menu-item bg-danger" data-toggle="tooltip" data-placement="top" title="" id="dark-mode" data-active="false" data-original-title="Color Mode"><i class="ri-moon-clear-line"></i></button>
         <button class="iq-float-menu-item bg-warning" data-toggle="tooltip" data-placement="top" title="" data-original-title="Comming Soon"><i class="ri-palette-line"></i></button> 
      </nav> -->
</template>

<script>
export default {
  name: 'ChatSupport',
  data() {
    return {
      isOpen: false,
      hasNotification: true,
      newMessage: '',
      messages: [
        {
          type: 'bot',
          text: 'Xin chào! Tôi có thể giúp gì cho bạn?',
          time: this.getCurrentTime()
        }
      ]
    };
  },
  methods: {
    toggleChat() {
      this.isOpen = !this.isOpen;
      if (this.isOpen) {
        this.hasNotification = false;
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      }
    },
    sendMessage() {
      if (!this.newMessage.trim()) return;

      // Add user message
      this.messages.push({
        type: 'user',
        text: this.newMessage,
        time: this.getCurrentTime()
      });

      this.newMessage = '';
      this.$nextTick(() => {
        this.scrollToBottom();
      });

      // Simulate bot response (replace with API call)
      setTimeout(() => {
        this.messages.push({
          type: 'bot',
          text: 'Cảm ơn bạn đã liên hệ. Đội ngũ hỗ trợ sẽ phản hồi sớm!',
          time: this.getCurrentTime()
        });
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      }, 1000);
    },
    getCurrentTime() {
      const now = new Date();
      return now.toLocaleTimeString('vi-VN', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    },
    scrollToBottom() {
      const container = this.$refs.messagesContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    }
  }
};
</script>

<style scoped>
.chat-support-container {
  position: fixed;
  bottom: 30px;
  right: 30px;
  z-index: 9999;
}

/* Floating Button */
.chat-button {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 24px;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  border: none;
  border-radius: 50px;
  font-family: 'Be_Vietnam_Pro', sans-serif;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 8px 30px rgba(37, 99, 235, 0.3);
  transition: all 0.3s ease;
  z-index: 10001;
}

.chat-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(37, 99, 235, 0.4);
}

.chat-button--active {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  box-shadow: 0 8px 30px rgba(220, 38, 38, 0.3);
}

.chat-button .material-symbols-outlined {
  font-size: 22px;
}

.chat-button__text {
  display: none;
}

@media (min-width: 640px) {
  .chat-button__text {
    display: inline;
  }
}

.chat-button__badge {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 12px;
  height: 12px;
  background: #ef4444;
  border-radius: 50%;
  border: 2px solid white;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.2); opacity: 0.8; }
}

/* Chat Window */
.chat-window {
  position: absolute;
  bottom: 90px;
  right: 0;
  width: 380px;
  max-width: calc(100vw - 60px);
  height: 600px;
  max-height: calc(100vh - 150px);
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 10000;
}

.chat-window__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: linear-gradient(135deg, #0a0e27 0%, #1a237e 100%);
  color: white;
}

.chat-window__header-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.chat-window__avatar {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chat-window__avatar .material-symbols-outlined {
  font-size: 28px;
  color: #60a5fa;
}

.chat-window__title {
  font-family: 'Be_Vietnam_Pro', sans-serif;
  font-size: 16px;
  font-weight: 700;
  margin: 0;
}

.chat-window__status {
  font-size: 13px;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 0;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #94a3b8;
}

.status-dot--online {
  background: #22c55e;
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
}

.chat-window__close {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.chat-window__close:hover {
  background: rgba(255, 255, 255, 0.2);
}

.chat-window__messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.message {
  display: flex;
  animation: messageSlide 0.3s ease;
}

@keyframes messageSlide {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message.bot {
  justify-content: flex-start;
}

.message.user {
  justify-content: flex-end;
}

.message__content {
  max-width: 80%;
  padding: 12px 16px;
  border-radius: 16px;
  font-size: 14px;
  line-height: 1.5;
}

.message.bot .message__content {
  background: white;
  color: #1e293b;
  border-bottom-left-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.message.user .message__content {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  border-bottom-right-radius: 4px;
}

.message__time {
  display: block;
  font-size: 11px;
  opacity: 0.6;
  margin-top: 4px;
}

.chat-window__input {
  display: flex;
  gap: 10px;
  padding: 16px 20px;
  background: white;
  border-top: 1px solid #e2e8f0;
}

.chat-input {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 24px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
  font-family: 'Inter', sans-serif;
}

.chat-input:focus {
  border-color: #2563eb;
}

.send-button {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  border: none;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, box-shadow 0.2s;
}

.send-button:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

.send-button:active {
  transform: scale(0.95);
}

/* Transitions */
.chat-slide-enter-active,
.chat-slide-leave-active {
  transition: all 0.3s ease;
}

.chat-slide-enter-from,
.chat-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Scrollbar */
.chat-window__messages::-webkit-scrollbar {
  width: 6px;
}

.chat-window__messages::-webkit-scrollbar-track {
  background: transparent;
}

.chat-window__messages::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.chat-window__messages::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Mobile Responsive */
@media (max-width: 640px) {
  .chat-support-container {
    bottom: 20px;
    right: 20px;
  }

  .chat-window {
    position: fixed;
    bottom: 0;
    right: 0;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    max-height: 100vh;
    border-radius: 0;
  }
}
</style>