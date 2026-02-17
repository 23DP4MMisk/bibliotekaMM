<!-- resources/js/pages/RegisterPage.vue -->
<template>
  <v-app>
  
    <v-main>
      <v-container fluid class="fill-height">
        <v-row class="justify-center">
          <v-col cols="12" sm="8" md="6" lg="4">
           
            <v-card class="register-card" elevation="0">
             
              <div class="register-header text-center pa-6">
                <div class="register-title">REGISTRACIJA</div>
              </div>
              
            
              <v-form ref="form">
               
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
                
               
                <div class="input-field pa-4 pt-0">
                  <div class="input-label mb-2">username</div>
                  <v-text-field
                    v-model="username"
                    placeholder=""
                    hide-details
                    solo
                    flat
                    class="custom-text-field"
                  ></v-text-field>
                </div>
                
              
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
                
               
                <div class="role-selection pa-4">
                  <div class="role-label mb-3 text-center">IZVELIETIES LOMU</div>
                  
                  <div class="role-buttons d-flex justify-space-between">
                   
                    <v-btn
                      class="role-button"
                      :class="{ 'active-role': role === 'admins' }"
                      @click="role = 'admins'"
                      height="48"
                      width="48%"
                    >
                      <span class="role-button-text">admins</span>
                    </v-btn>
                    
                   
                    <v-btn
                      class="role-button"
                      :class="{ 'active-role': role === 'klients' }"
                      @click="role = 'klients'"
                      height="48"
                      width="48%"
                    >
                      <span class="role-button-text">klients</span>
                    </v-btn>
                  </div>
                </div>
                
                
                <div class="button-container pa-4">
                  <v-btn
                    class="register-submit-button"
                    block
                    height="56"
                    @click="handleRegister"
                  >
                    <span class="button-text">REGISTRETIES</span>
                  </v-btn>
                </div>

                <div v-if="loading" class="loading-container pa-4 text-center">
                 <v-progress-circular
                   indeterminate
                   color="#003D3A"
                   size="24"
                   class="mr-2"
                  ></v-progress-circular>
                  <span>Reģistrējas...</span>
                </div>
              </v-form>

             
             
              <div v-if="errorMessage" class="error-container pa-4">
                <div class="error-message">
                  {{ errorMessage }}
                </div>
              </div>
            </v-card>
            
           
            <div class="back-to-login text-center mt-4">
              <v-btn 
                variant="text" 
                color="#003D3A" 
                @click="goToLogin"
                class="back-button"
              >
                <v-icon left>mdi-arrow-left</v-icon>
                Atpakaļ uz pieslēgšanos
              </v-btn>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
export default {
  name: 'RegisterPage',
  data() {
    return {
      email: '',
      username: '',
      password: '',
      role: 'klients',
      errorMessage: '',
      loading: false
    };
  },
  methods: {
    async handleRegister() {
  
  if (!this.email || !this.username || !this.password) {
    this.errorMessage = 'Lūdzu, aizpildiet visus laukus';
    return;
  }
  
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(this.email)) {
    this.errorMessage = 'Lūdzu, ievadiet derīgu e-pasta adresi';
    return;
  }
  
 
  if (this.username.length > 10) {
    this.errorMessage = 'Lietotājvārdam jābūt ne vairāk kā 10 rakstzīmes garam';
    return;
  }
  

  if (this.password.length < 6) {
    this.errorMessage = 'Parolei jābūt vismaz 6 rakstzīmes garai';
    return;
  }
  this.loading = true;
  this.errorMessage = '';
  
  try {
    console.log('Sending registration request...');
    
   
    const response = await fetch('http://localhost:8000/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        epasts: this.email,
        lietotaja_vards: this.username,
        parole: this.password,
        loma: this.role === 'admins' ? 'admins' : 'registretajsklients'
      })
    });

    console.log('📨 Atbilde no servera:', response.status);
    
    const data = await response.json();
    console.log('📊 Atbildes dati:', data);
    if (data.success) {
      console.log('✅ Reģistrācija veiksmīga!');
          
     
      localStorage.setItem('last_registered_email', this.email);
          
      alert('Reģistrācija veiksmīga! Lūdzu pieslēdzieties.');

     
      console.log('🏠 Pāreja uz pieslēgšanās lapu...');
      this.$router.push({
        path: '/login',
        query: { registered: 'true' }
      });
       } else {
          console.error('❌ Kļūda:', data);
          this.errorMessage = data.message || 'Reģistrācija neizdevās';
        }
        
      } catch (error) {
        console.error('❌ Tīkla kļūda:', error);
        this.errorMessage = 'Tīkla kļūda. Pārbaudiet savienojumu ar serveri.';
      } finally {
        this.loading = false;
      }
    },
      goToLogin() {
      this.$router.push('/login');
    }
  }


}
</script>

<style scoped>

.fill-height {
  height: 100vh;
  background-color: #FDFBED;
}


.register-card {
  background-color: #FFFFFF;
  border-radius: 8px;
  margin-top: 100px;
}


.register-header {
  background-color: #FFFFFF;
}

.register-title {
  font-size: 24px;
  font-weight: 500;
  color: #000000;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}


.input-field {
  background-color: #FFFFFF;
}

.input-label {
  font-size: 14px;
  color: #000000;
  font-weight: 400;
}

.custom-text-field {
  background-color: #FFFFFF !important;
  border: 2px solid #003D3A !important;
  border-radius: 4px !important;
}

.custom-text-field :deep(.v-field__field) {
  background-color: #FFFFFF !important;
}

.custom-text-field :deep(.v-field__input) {
  color: #FDFBED !important;
  padding-left: 12px !important;
  min-height: 48px !important;
  font-size: 16px;
  background-color: #003D3A !important;
}

.custom-text-field :deep(.v-field) {
  background-color: #003D3A !important;
}

.custom-text-field :deep(.v-field__outline) {
  display: none !important;
}


.role-selection {
  background-color: #FFFFFF;
}

.role-label {
  font-size: 14px;
  color: #000000;
  font-weight: 400;
  text-transform: uppercase;
}

.role-buttons {
  background-color: #FFFFFF;
}

.role-button {
  background-color: #FFFFFF !important;
  border: 2px solid #003D3A !important;
  border-radius: 4px !important;
  text-transform: none !important;
}

.role-button:hover {
  background-color: #f5f5f5 !important;
}

.active-role {
  background-color: #003D3A !important;
  border-color: #003D3A !important;
}

.role-button-text {
  color: #003D3A;
  font-size: 14px;
  font-weight: 500;
  letter-spacing: 0.5px;
  text-transform: lowercase;
}

.active-role .role-button-text {
  color: #FDFBED !important;
}


.button-container {
  background-color: #FFFFFF;
}


.register-submit-button {
  background-color: #003D3A !important;
  border-radius: 4px !important;
  text-transform: none !important;
}

.register-submit-button:hover {
  background-color: #002c29 !important;
}


.button-text {
  color: #FDFBED;
  font-size: 16px;
  font-weight: 500;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}


.back-to-login {
  background-color: #FDFBED;
}

.back-button {
  text-transform: none !important;
  font-size: 14px;
}


.error-container {
  background-color: #FFFFFF;
}

.error-message {
  color: #d32f2f;
  font-size: 14px;
  text-align: center;
  padding: 12px;
  background-color: #ffebee;
  border-radius: 4px;
  border: 1px solid #ffcdd2;
}


:deep(.v-field--variant-solo) {
  box-shadow: none !important;
}

:deep(.v-field--active) {
  box-shadow: none !important;
}

.loading-container {
  background-color: #FFFFFF;
  color: #003D3A;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}


@media (min-width: 1200px) {
  .register-card {
    margin-top: 120px;
    border-radius: 12px;
    max-width: 450px;
    margin-left: auto;
    margin-right: auto;
  }
  
  .register-title {
    font-size: 28px;
    font-weight: 600;
    padding: 30px 0;
  }
  
  .input-field {
    padding: 30px 40px 10px 40px;
  }
  
  .input-label {
    font-size: 16px;
    margin-bottom: 12px;
  }
  
  .custom-text-field {
    border-radius: 6px !important;
    border-width: 2.5px !important;
  }
  
  .custom-text-field :deep(.v-field__input) {
    min-height: 56px !important;
    font-size: 18px;
    padding-left: 16px !important;
  }
  
  .role-selection {
    padding: 20px 40px 10px 40px;
  }
  
  .role-label {
    font-size: 16px;
    margin-bottom: 15px;
  }
  
  .role-button {
    height: 52px !important;
    border-radius: 8px !important;
    font-size: 16px;
  }
  
  .role-button-text {
    font-size: 16px;
  }
  
  .button-container {
    padding: 20px 40px 10px 40px;
  }
  
  .register-submit-button {
    height: 60px !important;
    border-radius: 6px !important;
    font-size: 18px;
  }
  
  .button-text {
    font-size: 18px;
  }
  
  .error-message {
    font-size: 16px;
    padding: 16px;
    margin: 0 40px 20px 40px;
  }
}
</style>