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
import '../../css/register-pages.css';
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

    const checkResponse = await fetch('http://localhost:8000/api/check-user', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        epasts: this.email
      })
    });
    
    const checkData = await checkResponse.json();
    
    if (checkData.exists) {
      this.errorMessage = 'Lietotājs ar šo e-pasta adresi jau eksistē';
      this.loading = false;
      return;
    }
    
   
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

          
     
      if (data.token) {
       localStorage.setItem('auth_token', data.token);
      }
      if (data.lietotajs) {
       localStorage.setItem('user', JSON.stringify(data.lietotajs));
      }

      localStorage.setItem('last_registered_email', this.email);
          
     

     
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

