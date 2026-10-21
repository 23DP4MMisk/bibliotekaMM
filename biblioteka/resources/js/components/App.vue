<template>
  <v-app>
    <v-main class="pa-4">
      <router-view />
    </v-main>
    <AiChatWidget />
  </v-app>
</template>

<script>
import AiChatWidget from './AiChatWidget.vue';

export default {
  name: 'App',
  components: {
    AiChatWidget
  }
}
</script>

