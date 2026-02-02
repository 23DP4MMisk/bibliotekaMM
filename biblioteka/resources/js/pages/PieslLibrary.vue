<template>
  <v-app>
    <!-- Основной контент -->
    <v-main>
      <v-container fluid class="fill-height">
        <v-row class="justify-center">
          <v-col cols="12" sm="8" md="6" lg="4">
            <!-- Основная карточка входа -->
            <v-card class="login-card" elevation="0">
              <!-- Заголовок "Pieslegties" -->
              <div class="login-header text-center pa-6">
                <div class="login-title">Pieslegties</div>
              </div>
              
              <!-- Форма входа -->
              <v-form ref="form">
                <!-- Поле E-mail -->
                <div class="input-field pa-4 pt-0">
                  <div class="input-label mb-2">e-mail</div>
                  <v-text-field
                    v-model="email"
                    placeholder=""
                    hide-details
                    solo
                    flat
                    class="custom-text-field"
                  ></v-text-field>
                </div>
                
                <!-- Поле Password -->
                <div class="input-field pa-4 pt-0">
                  <div class="input-label mb-2">password</div>
                  <v-text-field
                    v-model="password"
                    placeholder=""
                    type="password"
                    hide-details
                    solo
                    flat
                    class="custom-text-field"
                  ></v-text-field>
                </div>
                
                <!-- Кнопка Iejiet -->
                <div class="button-container pa-4">
                  <v-btn
                    class="login-button"
                    block
                    height="56"
                    @click="handleLogin"
                  >
                    <span class="button-text">Iejiet</span>
                  </v-btn>
                </div>
                
                <!-- Кнопка REGISTRACIJA -->
                <div class="button-container pa-4 pt-0">
                  <v-btn
                    class="register-button"
                    block
                    height="56"
                    @click="goToRegister"
                  >
                    <span class="button-text">REGISTRACIJA</span>
                  </v-btn>
                </div>
              </v-form>
              
              <!-- Блок ошибок -->
              <div v-if="errorMessage" class="error-container pa-4">
                <div class="error-message">
                  {{ errorMessage }}
                </div>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import '../../css/piesl-pages.css';
export default {
  name: 'PieslLibrary',
  data() {
    return {
      email: '',
      password: '',
      errorMessage: ''
    };
  },
  methods: {
    

    handleLogin() {
      // Здесь будет логика входа
      console.log('Login attempt:', this.email, this.password);
      
      // Пример проверки для демонстрации ошибок
      if (!this.email || !this.password) {
        this.errorMessage = 'Lūdzu, aizpildiet visus laukus';
      } else if (this.email === 'test@example.com' && this.password === 'wrongpassword') {
        this.errorMessage = 'Nepareiza parole vai lietotājs nav reģistrēts';
      } else if (this.email === 'nonexistent@example.com') {
        this.errorMessage = 'Lietotājs nav atrasts. Lūdzu, reģistrējieties.';
      } else {
        this.errorMessage = '';
        // Успешный вход
        alert('Pieslēgšanās veiksmīga!');
      }
    },
    
    goToRegister() {
      // Переход на страницу регистрации
      console.log('Go to registration');
      alert('Tiks atvērta reģistrācijas lapa');
    }
  }
}
</script>

