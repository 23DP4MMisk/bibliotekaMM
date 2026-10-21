<template>
  <div>
  
    <v-btn
      @click="isOpen = !isOpen"
      color="#003D3A"
      icon
      large
      fixed
      bottom
      right
      class="chat-fab shadow-lg"
    >
      <v-icon size="28" color="white">
        {{ isOpen ? 'mdi-close' : 'mdi-robot-happy' }}
      </v-icon>
    </v-btn>

   
    <v-slide-x-transition>
      <v-card
        v-if="isOpen"
        class="chat-window elevation-10"
        width="350"
        max-height="500"
      >
       
        <v-card-title class="bg-teal-darken-4 text-white py-3 d-flex align-center">
          <v-icon left color="white">mdi-robot</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Bibliotekāra Asistents</span>
          <v-spacer></v-spacer>
          <v-btn icon small color="white" @click="isOpen = false">
            <v-icon>mdi-minus</v-icon>
          </v-btn>
        </v-card-title>

       
        <v-card-text class="chat-history overflow-y-auto pa-4 bg-grey-lighten-5">
          <div 
            v-for="(msg, index) in messages" 
            :key="index" 
            class="mb-3 d-flex"
            :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
          >
           
            <v-avatar v-if="msg.role === 'ai'" color="#003D3A" size="28" class="mr-2 mt-1">
              <v-icon size="16" color="white">mdi-robot</v-icon>
            </v-avatar>

           
            <div 
              class="msg-bubble pa-2 px-3"
              :class="msg.role === 'user' ? 'bg-primary text-white' : 'bg-white text-black'"
            >
              {{ msg.text }}
            </div>
          </div>
          
         
          <div v-if="loading" class="d-flex justify-start mb-3">
            <v-avatar color="#003D3A" size="28" class="mr-2 mt-1">
              <v-icon size="16" color="white">mdi-robot</v-icon>
            </v-avatar>
            <div class="bg-white pa-2 px-3 msg-bubble d-flex align-center">
              <v-progress-circular indeterminate size="16" width="2" color="#003D3A"></v-progress-circular>
              <span class="ml-2 text-caption text-grey">Raksta...</span>
            </div>
          </div>
        </v-card-text>

      
        <v-divider></v-divider>
        <v-card-actions class="pa-2 bg-white">
          <v-text-field
            v-model="userInput"
            placeholder="Uzdod jautājumu..."
            density="compact"
            variant="solo"
            hide-details
            flat
            bg-color="grey-lighten-4"
            rounded
            @keyup.enter="sendMessage"
            :disabled="loading"
          ></v-text-field>
          <v-btn 
            icon 
            color="#003D3A" 
            @click="sendMessage" 
            :disabled="!userInput.trim() || loading"
            class="ml-2"
          >
            <v-icon>mdi-send</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-slide-x-transition>
  </div>
</template>

<script>
export default {
  name: 'AiChatWidget',
  data() {
    return {
      isOpen: false,
      loading: false,
      userInput: '',
      messages: [
        { role: 'ai', text: 'Sveiki! Es esmu jūsu virtuālais bibliotekārs. Kādu grāmatu jūs meklējat šodien?' }
      ]
    };
  },
  methods: {
    async sendMessage() {
      const text = this.userInput.trim();
      if (!text || this.loading) return;

     
      this.messages.push({ role: 'user', text: text });
      this.userInput = '';
      this.loading = true;

      
      this.$nextTick(() => {
        this.scrollToBottom();
      });

      try {
        
        const response = await fetch('/api/chat', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
            
          },
          body: JSON.stringify({ message: text })
        });

        const data = await response.json();

        if (response.ok) {
          this.messages.push({ role: 'ai', text: data.reply });
        } else if (response.status === 429) {
          this.messages.push({ role: 'ai', text: 'Atvainojiet, esat sasniedzis jautājumu limitu. Lūdzu, uzgaidiet minūti!' });
        } else {
          this.messages.push({ role: 'ai', text: data.error || 'Radās kļūda. Lūdzu, mēģiniet vēlreiz.' });
        }
      } catch (error) {
        console.error('Chat error:', error);
        this.messages.push({ role: 'ai', text: 'Nav savienojuma ar serveri.' });
      } finally {
        this.loading = false;
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      }
    },
    scrollToBottom() {
      const chatHistory = document.querySelector('.chat-history');
      if (chatHistory) {
        chatHistory.scrollTop = chatHistory.scrollHeight;
      }
    }
  }
};
</script>

<style scoped>
.chat-fab {
  position: fixed !important;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0, 61, 58, 0.4) !important;
}

.chat-window {
  position: fixed !important;
  bottom: 90px;
  right: 24px;
  z-index: 9998;
  border-radius: 12px !important;
  display: flex;
  flex-direction: column;
}

.chat-history {
  height: 320px;
  scroll-behavior: smooth;
}

.msg-bubble {
  border-radius: 12px;
  max-width: 80%;
  word-wrap: break-word;
  font-size: 0.9rem;
  line-height: 1.4;
}


.justify-end .msg-bubble {
  border-bottom-right-radius: 0px;
}
.justify-start .msg-bubble {
  border-bottom-left-radius: 0px;
  border: 1px solid #e0e0e0;
}
</style>