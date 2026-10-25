<template>
  <v-app>
    <v-main class="pa-4">
      <router-view />
    </v-main>
    <AiChatWidget v-if="userRole !== 'admins' && userRole !== 'admin'" />
  </v-app>
</template>

<script>
import AiChatWidget from './AiChatWidget.vue';

export default {
  name: 'App',
  components: {
    AiChatWidget
  },
  computed: {
    userRole() {
      const currentPath = this.$route.path; 
      try {
        const userStr = localStorage.getItem('user');
        if (!userStr) return null;
        
        const user = JSON.parse(userStr);
        return user ? user.loma : null;
      } catch (e) {
        return null;
      }
    
    }
  }
}
</script>

