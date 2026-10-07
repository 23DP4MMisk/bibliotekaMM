<!-- resources/js/pages/RegisterPage.vue -->
<template>
  <v-app>
  
    <v-main>
      <v-container fluid class="fill-height">
        <v-row class="justify-center">
          <v-col cols="12" sm="8" md="6" lg="4">
           
            <v-card class="register-card" elevation="0">
             
              <div class="register-header text-center pa-6">
                <div class="register-title">Reģistrācija</div>
              </div>
              
            
              <v-form ref="form">
               
                <div class="input-field pa-4 pt-0">
                  <div class="input-label mb-2">E-pasts</div>
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
                  <div class="input-label mb-2">Lietotājvārds</div>
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
                  <div class="input-label mb-2">Parole</div>
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
                  <div class="role-label mb-3 text-center">Izvēlieties lomu</div>
                  
                  <div class="role-buttons d-flex justify-space-between">
                   
                    <v-btn
                      class="role-button"
                      :class="{ 'active-role': role === 'admins' }"
                      @click="role = 'admins'"
                      height="48"
                      width="48%"
                    >
                      <span class="role-button-text">Administrators</span>
                    </v-btn>
                    
                   
                    <v-btn
                      class="role-button"
                      :class="{ 'active-role': role === 'klients' }"
                      @click="role = 'klients'"
                      height="48"
                      width="48%"
                    >
                      <span class="role-button-text">Klients</span>
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
                    <span class="button-text">Reģistrēties</span>
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
import { ERROR_MESSAGES, getApiErrorMessage } from '../utils/errorMessages.js';
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
    this.errorMessage = ERROR_MESSAGES.missingFields;
    return;
  }
  
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(this.email)) {
    this.errorMessage = ERROR_MESSAGES.invalidEmail;
    return;
  }
  
 
  if (this.username.length > 10) {
    this.errorMessage = ERROR_MESSAGES.usernameTooLong;
    return;
  }
  

  if (this.password.length < 6) {
    this.errorMessage = ERROR_MESSAGES.passwordTooShort;
    return;
  }
  this.loading = true;
  this.errorMessage = '';
  
  try {
    ('Sending registration request...');

    const checkResponse = await fetch('/api/check-user', {
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
      this.errorMessage = ERROR_MESSAGES.emailAlreadyRegistered;
      this.loading = false;
      return;
    }
    
   
    const response = await fetch('/api/register', {
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

   
    
    const data = await response.json();
   
    if (data.success) {
      

          
     
      if (data.token) {
       localStorage.setItem('auth_token', data.token);
      }
      if (data.lietotajs) {
       localStorage.setItem('user', JSON.stringify(data.lietotajs));
      }

      localStorage.setItem('last_registered_email', this.email);
          
     

     
     
      this.$router.push({
        path: '/login',
        query: { registered: 'true' }
      });
       } else {
          console.error(' Kļūda:', data);
          this.errorMessage = getApiErrorMessage(data, ERROR_MESSAGES.registrationFailed);
        }
        
      } catch (error) {
        console.error('Tīkla kļūda:', error);
        this.errorMessage = ERROR_MESSAGES.network;
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

