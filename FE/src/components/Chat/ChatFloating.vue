<!-- src/components/Chat/ChatFloating.vue -->
<template>
  <div class="chat-floating-container">
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
</template>

<script>
export default {
  name: 'ChatFloating',
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

      this.messages.push({
        type: 'user',
        text: this.newMessage,
        time: this.getCurrentTime()
      });

      this.newMessage = '';
      this.$nextTick(() => {
        this.scrollToBottom();
      });

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
.chat-floating-container {
  position: fixed;
  bottom: 30px;
  right: 30px;
  z-index: 9999;
}

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
  font-family: 'Inter', sans-serif;
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
}

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

.chat-window__header-info { display: flex; align-items: center; gap: 12px; }
.chat-window__avatar { width: 48px; height: 48px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.chat-window__avatar .material-symbols-outlined { font-size: 28px; color: #60a5fa; }
.chat-window__title { font-size: 16px; font-weight: 700; margin: 0; }
.chat-window__status { font-size: 13px; color: #94a3b8; margin: 0; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; display: inline-block; }

.chat-window__close { background: rgba(255, 255, 255, 0.1); border: none; width: 36px; height: 36px; border-radius: 50%; color: white; cursor: pointer; }

.chat-window__messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.message { display: flex; }
.message.bot { justify-content: flex-start; }
.message.user { justify-content: flex-end; }
.message__content { max-width: 80%; padding: 12px 16px; border-radius: 16px; font-size: 14px; }
.message.bot .message__content { background: white; color: #1e293b; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.message.user .message__content { background: #2563eb; color: white; }

.chat-window__input { display: flex; gap: 10px; padding: 16px 20px; border-top: 1px solid #e2e8f0; }
.chat-input { flex: 1; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 20px; outline: none; }
.send-button { background: #2563eb; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }

.chat-slide-enter-active, .chat-slide-leave-active { transition: all 0.3s ease; }
.chat-slide-enter-from, .chat-slide-leave-to { opacity: 0; transform: translateY(20px); }
</style>
