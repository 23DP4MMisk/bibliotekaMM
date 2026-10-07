
<template>
  <v-app>
    
    <v-main>
      <v-container fluid class="fill-height">
        <v-row class="justify-center">
          <v-col cols="12" sm="8" md="6" lg="4">
            
            <v-card class="login-card" elevation="0">
             
              <div class="login-header text-center pa-6">
                <div class="login-title">Pieslēgties</div>
              </div>
              
              <v-alert
                v-if="showRegisterSuccess"
                type="success"
                class="mx-4 mb-4"
                dense
                outlined
                dismissible
                @click:close="showRegisterSuccess = false"
              >
                Reģistrācija veiksmīga! Lūdzu pieslēdzieties.
              </v-alert>
              
              
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

                <v-alert
                  v-if="showLoginSuccess"
                  type="success"
                  class="mx-4 mb-4"
                  dense
                  outlined
                >
                  Pieslēgšanās veiksmīga! Pāreja uz bibliotēku...
                </v-alert>
                
               
                <div class="button-container pa-4">
                  <v-btn
                    class="login-button"
                    block
                    height="56"
                    @click="handleLogin"
                  >
                    <span class="button-text">Pieslēgties</span>
                  </v-btn>
                </div>
                
               
                <div class="button-container pa-4 pt-0">
                  <v-btn
                    class="register-button"
                    block
                    height="56"
                    @click="goToRegister"
                  >
                    <span class="button-text">Reģistrēties</span>
                  </v-btn>
                </div>
              </v-form>
              
             
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
import { ERROR_MESSAGES, getApiErrorMessage } from '../utils/errorMessages.js';
export default {
  name: 'PieslLibrary',
  data() {
    return {
      email: '',
      password: '',
      errorMessage: '',
      showRegisterSuccess: false,
      showLoginSuccess: false
    };
  },

  mounted() {
  
    const lastEmail = localStorage.getItem('last_registered_email');
    if (lastEmail) {
      this.email = lastEmail;
      localStorage.removeItem('last_registered_email');
    }

    if (this.$route.query.registered === 'true') {
      this.showRegisterSuccess = true;
      
      
      setTimeout(() => {
        this.showRegisterSuccess = false;
      }, 5000);
    }

    
   
  },
  
  methods: {
    async handleLogin() {
     
      if (!this.email || !this.password) {
        this.errorMessage = ERROR_MESSAGES.missingFields;
        return;
      }
      
      this.loading = true;
      this.errorMessage = '';
      try {
        
        
        const response = await fetch('/api/pieslēgties', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          
          body: JSON.stringify({
            epasts: this.email,
            parole: this.password
          })
        });
        const data = await response.json();
        

        if (data.lietotajs && data.lietotajs.status !== 'aktivs') {
          this.errorMessage = ERROR_MESSAGES.accountBlocked;
          this.loading = false;
          return; 
        }

        if (data.success) {
          
          

          localStorage.setItem('auth_token', data.token);
          localStorage.setItem('user', JSON.stringify(data.lietotajs));
          this.showLoginSuccess = true;

          
         

          const userRole = data.lietotajs.loma;
          if (userRole === 'admins' || userRole === 'admin') {
            
           
            this.$router.push('/admin');
          } else {
            
            this.$router.push('/library');
          }
          

          } else {
          console.error(' Kļūda:', data);
          this.errorMessage = getApiErrorMessage(data, ERROR_MESSAGES.login);
        }
        
      } catch (error) {
        console.error(' Tīkla kļūda:', error);
        this.errorMessage = ERROR_MESSAGES.network;
      } finally {
        this.loading = false;
      }
    },
    
    goToRegister() {
      this.$router.push('/register');
    }
    
    
  }
}
</script>

