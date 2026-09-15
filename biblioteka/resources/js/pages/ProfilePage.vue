<template>
  <v-app>
    
    <v-app-bar app flat height="80" class="top-nav-bar" fixed>
      <v-container class="d-flex align-center justify-space-between px-8">
        <v-btn @click="goToLibrary" variant="text" class="library-name-btn">
          <h1 class="library-name">MYLIBRARY</h1>
        </v-btn>
        
        <div></div>

        <div v-if="isLoggedIn && user" class="user-container">
          <v-menu offset-y>
            <template v-slot:activator="{ props }">
              <v-btn 
                color="#003D3A" 
                class="user-initial-btn"
                rounded
                v-bind="props"
              >
                <v-avatar size="40" color="#003D3A">
                    <v-img
                        v-if="user?.foto"
                        :src="user.foto"
                        cover
                    ></v-img>
                    <span v-else class="user-initial">{{ userInitial }}</span>
                </v-avatar>
               
              </v-btn>
            </template>
            
            <v-list>
             
              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold">
                    {{ userName }}
                  </v-list-item-title>
                  <v-list-item-subtitle>
                    {{ userEmail }}
                    <v-chip v-if="isAdmin" x-small color="error" class="ml-2">ADMIN</v-chip>
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <v-divider></v-divider>
              
              
              <v-list-item @click="goToProfile">
                <v-list-item-icon>
                  <v-icon>mdi-account</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Mans profils</v-list-item-title>
              </v-list-item>
              
             
              <v-list-item @click="goToMyLibrary">
                <v-list-item-icon>
                  <v-icon>mdi-book-multiple</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Mana bibliotēka</v-list-item-title>
              </v-list-item>
              
              
              <template v-if="isAdmin">
                <v-divider></v-divider>
               <v-list-item @click="showStatistics = true">
                  <v-list-item-icon>
                    <v-icon>mdi-chart-bar</v-icon>
                  </v-list-item-icon>
                  <v-list-item-title>Statistika</v-list-item-title>
                </v-list-item>
                <v-list-item @click="goToAdminPanel">
                  <v-list-item-icon>
                    <v-icon>mdi-shield-account</v-icon>
                  </v-list-item-icon>
                  <v-list-item-title>Admin panelis</v-list-item-title>
                </v-list-item>
              </template>


              
              <v-divider></v-divider>
              
              <v-list-item @click="logout">
                <v-list-item-icon>
                  <v-icon>mdi-logout</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Iziet</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
        <div v-else></div>
      </v-container>
    </v-app-bar>

   
    <v-main style="margin-top: 80px;">
      <v-container fluid class="main-content pa-8">
        <v-row class="justify-center">
          <v-col cols="12" md="10" lg="8">
            
            <v-card class="profile-card" elevation="2">
              <v-card-title class="profile-header">
                <div class="d-flex align-center">
                  <v-icon size="32" color="#003D3A" class="mr-3">mdi-account-cog</v-icon>
                  <span class="profile-title">Mans profils</span>
                  <v-chip v-if="isAdmin" class="ml-3" color="error" small>ADMIN</v-chip>
                </div>
              </v-card-title>

              <v-divider></v-divider>

              
              <div v-if="loading" class="text-center py-12">
                <v-progress-circular indeterminate color="#003D3A" size="48"></v-progress-circular>
                <p class="mt-4">Ielādē profilu...</p>
              </div>

             
              <div v-else-if="error" class="text-center py-12">
                <v-icon size="64" color="error">mdi-alert-circle</v-icon>
                <p class="mt-4">{{ errorMessage }}</p>
                <v-btn color="#003D3A" @click="loadProfile">Mēģināt vēlreiz</v-btn>
              </div>

              
              <div v-else>
                <v-card-text class="pa-6">
                  <v-row>
                    
                    <v-col cols="12" md="4" class="text-center">
                      <div class="avatar-section">
                        <div class="avatar-wrapper">
                          <v-avatar size="150" color="#003D3A" class="profile-avatar">
                            <v-img
                              v-if="profile.foto"
                              :src="profile.foto"
                              cover
                            ></v-img>
                            <span v-else class="avatar-text">
                              {{ getUserInitial(profile.lietotaja_vards) }}
                            </span>
                          </v-avatar>
                          
                          <v-btn
                            small
                            color="#003D3A"
                            class="avatar-upload-btn"
                            @click="$refs.fileInput.click()"
                          >
                            <v-icon small>mdi-camera</v-icon>
                            Mainīt
                          </v-btn>
                          <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            style="display: none"
                            @change="uploadAvatar"
                          />
                        </div>

                        <v-chip
                          :color="isAdmin ? 'error' : 'success'"
                          class="mt-3 role-chip"
                        >
                          {{ isAdmin ? 'Administrators' : 'Reģistrēts lietotājs' }}
                        </v-chip>
                      </div>
                    </v-col>

                   
                    <v-col cols="12" md="8">
                      <v-row>
                        
                        <v-col cols="12">
                          <v-text-field
                            v-model="profile.lietotaja_vards"
                            label="Lietotājvārds"
                            outlined
                            dense
                            class="mb-0"
                            @input="hasChanges = true"
                          ></v-text-field>
                        </v-col>

                        
                        <v-col cols="12">
                          <v-text-field
                            v-model="profile.epasts"
                            label="E-pasts"
                            outlined
                            dense
                            disabled
                            class="mb-0"
                          ></v-text-field>
                        </v-col>

                        
                        <v-col cols="12" sm="6">
                          <v-text-field
                            v-model="profile.pilseta"
                            label="Pilsēta"
                            outlined
                            dense
                            placeholder="Ievadiet savu pilsētu"
                            class="mb-0"
                            @input="hasChanges = true"
                          ></v-text-field>
                        </v-col>

                       
                        <v-col cols="12" sm="6">
                          <v-text-field
                            v-model="profile.dzim_datums"
                            label="Dzimšanas datums"
                            outlined
                            dense
                            type="date"
                            class="mb-0"
                            @input="hasChanges = true"
                          ></v-text-field>
                        </v-col>

                        
                        <v-col cols="12">
                          <v-textarea
                            v-model="profile.bio"
                            label="Par mani"
                            outlined
                            dense
                            rows="3"
                            placeholder="Īsi pastāstiet par sevi..."
                            class="mb-0"
                            @input="hasChanges = true"
                          ></v-textarea>
                        </v-col>

                        
                        <v-col cols="12" sm="6">
                          <v-btn
                            color="#003D3A"
                            block
                            :loading="saving"
                            :disabled="!hasChanges"
                            @click="saveProfile"
                          >
                            <v-icon left>mdi-content-save</v-icon>
                            Saglabāt izmaiņas
                          </v-btn>
                        </v-col>
                        <v-col cols="12" sm="6">
                          <v-btn
                            color="#ff8c00"
                            block
                            @click="showPasswordDialog = true"
                          >
                            <v-icon left>mdi-lock-reset</v-icon>
                            Mainīt paroli
                          </v-btn>
                        </v-col>
                      </v-row>
                    </v-col>
                  </v-row>
                </v-card-text>
              </div>
            </v-card>

           
            <v-card class="settings-card mt-6" elevation="2">
              <v-card-title class="settings-header">
                <div class="d-flex align-center">
                  <v-icon size="28" color="#b71c1c" class="mr-3">mdi-alert</v-icon>
                  <span class="settings-title danger-title">Bīstamā zona</span>
                </div>
              </v-card-title>

              <v-divider></v-divider>

              <v-card-text class="pa-6">
                <p class="danger-text">Dzēšot profilu, visi dati tiks neatgriezeniski zaudēti.</p>
                <v-btn color="#b71c1c" @click="showDeleteDialog = true">
                  <v-icon left>mdi-delete</v-icon>
                  Dzēst profilu
                </v-btn>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>

   
    <v-dialog v-model="showPasswordDialog" max-width="500" persistent>
      <v-card>
        <v-card-title class="password-dialog-header">
          <span>Mainīt paroli</span>
          <v-spacer></v-spacer>
          <v-btn icon dark @click="showPasswordDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-4">
          <v-form ref="passwordForm">
            <v-text-field
              v-model="passwordData.current_password"
              label="Pašreizējā parole *"
              type="password"
              outlined
              dense
              class="mb-3"
              :error-messages="passwordErrors.current_password"
            ></v-text-field>
            
            <v-text-field
              v-model="passwordData.new_password"
              label="Jaunā parole *"
              type="password"
              outlined
              dense
              class="mb-3"
              :error-messages="passwordErrors.new_password"
            ></v-text-field>
            
            <v-text-field
              v-model="passwordData.new_password_confirmation"
              label="Apstiprināt jauno paroli *"
              type="password"
              outlined
              dense
              class="mb-3"
            ></v-text-field>
          </v-form>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="showPasswordDialog = false">
            Atcelt
          </v-btn>
          <v-btn color="#003D3A" dark :loading="changingPassword" @click="changePassword">
            Saglabāt
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    
    <v-dialog v-model="showDeleteDialog" max-width="400" persistent>
      <v-card>
        <v-card-title class="delete-dialog-header">
          <v-icon large color="error">mdi-alert-circle</v-icon>
          <span class="ml-3">Dzēst profilu?</span>
        </v-card-title>
        
        <v-card-text class="pa-4">
          <p>Vai tiešām vēlaties dzēst savu profilu?</p>
          <p class="red--text text--darken-2">Šī darbība ir neatgriezeniska!</p>
          
          <v-text-field
            v-model="deletePassword"
            label="Ievadiet paroli, lai apstiprinātu"
            type="password"
            outlined
            dense
          ></v-text-field>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="showDeleteDialog = false">
            Atcelt
          </v-btn>
          <v-btn color="#b71c1c" dark :loading="deletingAccount" @click="deleteAccount">
            Dzēst profilu
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

   
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      timeout="4000"
      top
      right
      elevation="6"
    >
      {{ snackbar.text }}
      <template v-slot:action>
        <v-btn text color="white" @click="snackbar.show = false">OK</v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script>


export default {
  name: 'ProfilePage',
  data() {
    return {
      profile: {
        lietotaja_vards: '',
        epasts: '',
        foto: null,
        bio: '',             
        pilseta: '',
        dzim_datums: ''
      },
      
      isLoggedIn: false,
      user: null,
      authLoading: false,

      showUsersList: false,
      showStatistics: false,
      
      loading: true,
      error: false,
      errorMessage: '',
      saving: false,
      hasChanges: false,
      
      showPasswordDialog: false,
      changingPassword: false,
      passwordData: {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      },
      passwordErrors: {},
      
      showDeleteDialog: false,
      deletingAccount: false,
      deletePassword: '',
      
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      }
    };
  },
  computed: {
    userName() {
      return this.user?.lietotaja_vards || '';
    },
    userEmail() {
      return this.user?.epasts || '';
    },
    userInitial() {
      if (this.userName) return this.userName.charAt(0).toUpperCase();
      if (this.userEmail) return this.userEmail.charAt(0).toUpperCase();
      return 'U';
    },
    isAdmin() {
      return this.user?.loma === 'admins';
    },
    authToken() {
      return localStorage.getItem('auth_token');
    }
  },
  async mounted() {
    await this.checkAuth();
    if (!this.isLoggedIn) {
      this.$router.push('/login');
      return;
    }
    await this.loadProfile();
  },
  methods: {
    async checkAuth() {
      if (this.authLoading) return;
      this.authLoading = true;

      const token = this.authToken;
      if (!token) {
        this.isLoggedIn = false;
        this.authLoading = false;
        return;
      }

      try {
        const response = await fetch('/api/check-auth', {
          headers: { 'Authorization': 'Bearer ' + token }
        });
        const data = await response.json();
        
        if (data.authenticated && data.lietotajs) {
          this.isLoggedIn = true;
          this.user = data.lietotajs;
        } else {
          this.isLoggedIn = false;
          this.user = null;
        }
      } catch (error) {
        console.error('Auth error:', error);
        this.isLoggedIn = false;
      } finally {
        this.authLoading = false;
      }
    },

    async loadProfile() {
      this.loading = true;
      this.error = false;
      this.errorMessage = '';

      try {
        const token = this.authToken;
        console.log('Tokens:', token);
        const response = await fetch('/api/profile', {
          headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
          }
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Serveris atgrieza HTML, nevis JSON');
        }

        const data = await response.json();
        console.log('Dati no servera:', data); 
        console.log('Datums no servera:', data.data.dzim_datums);

        if (data.success) {
          console.log('profile data:', data.data);
          let dzim_datums = '';
          if (data.data.dzim_datums) {
            const date = new Date(data.data.dzim_datums);
            if (!isNaN(date.getTime())) {
              const year = date.getFullYear();
              const month = String(date.getMonth() + 1).padStart(2, '0');
              const day = String(date.getDate()).padStart(2, '0');
              dzim_datums = `${year}-${month}-${day}`;
            }
          }

          this.profile = {
            lietotaja_vards: data.data.lietotaja_vards,
            epasts: data.data.epasts,
            foto: data.data.foto,
            bio: data.data.bio,                    
            pilseta: data.data.pilseta,
            dzim_datums: dzim_datums 
          };
          this.hasChanges = false;

          if (this.user) {
            this.user.foto = data.data.foto;
            localStorage.setItem('user', JSON.stringify(this.user));
          }
        
        } else {
          this.errorMessage = data.message || 'Kļūda ielādējot profilu';
          this.error = true;
        }
      } catch (error) {
        console.error('Error loading profile:', error);
        this.error = true;
        this.errorMessage = error.message || 'Kļūda ielādējot profilu';
      } finally {
        this.loading = false;
      }
    },

    async saveProfile() {
      this.saving = true;
      try {
        console.log(' Sūtīju datus:', {
            lietotaja_vards: this.profile.lietotaja_vards,
            bio: this.profile.bio,
            pilseta: this.profile.pilseta,
            dzim_datums: this.profile.dzim_datums
        });
        
        const response = await fetch('/api/profile', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            lietotaja_vards: this.profile.lietotaja_vards,
            bio: this.profile.bio,               
            pilseta: this.profile.pilseta,
            dzim_datums: this.profile.dzim_datums
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('Profils veiksmīgi atjaunināts!', 'success');
          this.hasChanges = false;
          this.user.lietotaja_vards = this.profile.lietotaja_vards;
          localStorage.setItem('user', JSON.stringify(this.user));
        } else {
          this.showNotification(data.message || 'Kļūda saglabājot profilu', 'error');
        }
      } catch (error) {
        console.error('Error saving profile:', error);
        this.showNotification('Kļūda savienojumā ar serveri', 'error');
      } finally {
        this.saving = false;
      }
    },

    async uploadAvatar(event) {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('foto', file);

      try {
        const response = await fetch('/api/profile/avatar', {
          method: 'POST',
          headers: {
            'Authorization': 'Bearer ' + this.authToken
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.profile.foto = data.data.foto;
          this.user.foto = data.data.foto;
          localStorage.setItem('user', JSON.stringify(this.user));
          this.showNotification('Avatar veiksmīgi augšupielādēts!', 'success');
        } else {
          this.showNotification(data.message || 'Kļūda augšupielādējot avataru', 'error');
        }
      } catch (error) {
        console.error('Error uploading avatar:', error);
        this.showNotification('Kļūda savienojumā ar serveri', 'error');
      }
      
      event.target.value = '';
    },

    async changePassword() {
      this.passwordErrors = {};
      this.changingPassword = true;

      try {
        const response = await fetch('/api/profile/change-password', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.passwordData)
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('Parole veiksmīgi mainīta!', 'success');
          this.showPasswordDialog = false;
          this.passwordData = {
            current_password: '',
            new_password: '',
            new_password_confirmation: ''
          };
        } else if (data.errors) {
          this.passwordErrors = data.errors;
          this.showNotification('Lūdzu, izlabojiet kļūdas', 'error');
        } else {
          this.showNotification(data.message || 'Kļūda mainot paroli', 'error');
        }
      } catch (error) {
        console.error('Error changing password:', error);
        this.showNotification('Kļūda savienojumā ar serveri', 'error');
      } finally {
        this.changingPassword = false;
      }
    },

    async deleteAccount() {
      if (!this.deletePassword) {
        this.showNotification('Lūdzu, ievadiet paroli', 'error');
        return;
      }

      this.deletingAccount = true;

      try {
        const response = await fetch('/api/profile', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            password: this.deletePassword
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('Profils veiksmīgi dzēsts', 'success');
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          setTimeout(() => {
            this.$router.push('/login');
          }, 1500);
        } else {
          this.showNotification(data.message || 'Kļūda dzēšot profilu', 'error');
        }
      } catch (error) {
        console.error('Error deleting account:', error);
        this.showNotification('Kļūda savienojumā ar serveri', 'error');
      } finally {
        this.deletingAccount = false;
        this.showDeleteDialog = false;
      }
    },

    showNotification(message, type = 'success') {
      this.snackbar = {
        show: true,
        text: message,
        color: type === 'success' ? 'success' : 'error'
      };
    },

    getUserInitial(name) {
      if (!name) return 'U';
      return name.charAt(0).toUpperCase();
    },

    goToLibrary() {
      this.$router.push('/library');
    },

    goToProfile() {
      this.$router.push('/profile');
    },

    goToMyLibrary() {
      this.$router.push({
        path: '/library',
        query: { tab: 'my-library' }
      });
    },

    goToAdminPanel() {
      this.$router.push('/admin');
    },

    async logout() {
      const token = this.authToken;
      try {
        if (token) {
          await fetch('/api/izrakstīties', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token }
          });
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        this.$router.push('/library');
      }
    }
  }
}
</script>

<style scoped>
.profile-card,
.settings-card {
  border-radius: 16px !important;
}

.profile-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
  padding: 16px 24px !important;
}

.profile-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #003D3A;
}

.avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.avatar-wrapper {
  position: relative;
}

.profile-avatar {
  border: 4px solid #003D3A;
  box-shadow: 0 4px 12px rgba(0, 61, 58, 0.2);
}

.avatar-text {
  font-size: 4rem;
  font-weight: 700;
  color: white;
  text-transform: uppercase;
}

.avatar-upload-btn {
  position: absolute;
  bottom: 0;
  right: 0;
  min-width: 36px !important;
  height: 36px !important;
  border-radius: 50% !important;
  background-color: #003D3A !important;
  color: white !important;
  box-shadow: 0 2px 8px rgba(0, 61, 58, 0.3);
}

.role-chip {
  font-weight: 600;
}

.settings-header {
  background: linear-gradient(135deg, #fff5f5 0%, #ffebee 100%) !important;
  padding: 16px 24px !important;
}

.danger-title {
  color: #b71c1c !important;
}

.danger-text {
  color: #666;
  font-size: 0.95rem;
}

.password-dialog-header {
  background-color: #003D3A !important;
  color: white !important;
}

.delete-dialog-header {
  background-color: #b71c1c !important;
  color: white !important;
}

.v-col {
  padding: 6px 12px;
}

@media (max-width: 600px) {
  .profile-title {
    font-size: 1.2rem;
  }
}
</style>