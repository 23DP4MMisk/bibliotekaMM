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
                <span class="user-initial">{{ userInitial }}</span>
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
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <v-divider></v-divider>
              <v-list-item @click="goToMyLibrary">
                <v-list-item-icon>
                  <v-icon>mdi-book-multiple</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Mana bibliotēka</v-list-item-title>
              </v-list-item>
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
        <div v-if="loading" class="text-center py-12">
          <v-progress-circular indeterminate color="#003D3A" size="64"></v-progress-circular>
          <p class="mt-4">Ielādē grāmatas informāciju...</p>
        </div>

        <div v-else-if="error" class="text-center py-12">
          <div class="error-container">
            <v-icon size="100" color="#ff6b6b" class="mb-4">mdi-alert-circle-outline</v-icon>
            <h3 class="error-title mb-3">Radās kļūda</h3>
            <p class="error-message mb-4">{{ errorMessage }}</p>
            <v-btn @click="goToLibrary" color="#003D3A" rounded>Atpakaļ uz bibliotēku</v-btn>
          </div>
        </div>

        <div v-else-if="book" class="book-detail-container">
          <v-row>
            <v-col cols="12" md="5" lg="4">
              <div class="book-cover-large">
                <v-img
                  :src="getBookCover(book)"
                  :alt="book.nosaukums || book.title"
                  cover
                  class="book-cover-image-large"
                  height="400"
                >
                  <template v-slot:placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                      <v-icon size="64" color="#003D3A">mdi-book-open-variant</v-icon>
                    </div>
                  </template>
                </v-img>
              </div>
            </v-col>

            <v-col cols="12" md="7" lg="8">
              <div class="book-info-container">
                <h1 class="book-title-large">{{ book.nosaukums || book.title }}</h1>
                
                <h2 class="book-author-large">{{ book.autors || book.author }}</h2>

                <div class="book-meta">
                  <div class="meta-item" v-if="book.gads">
                    <v-icon color="#003D3A" class="mr-2">mdi-calendar</v-icon>
                    <span class="meta-text">Gads: {{ book.gads }}</span>
                  </div>
                  <div class="meta-item" v-if="book.lapu_skaits">
                    <v-icon color="#003D3A" class="mr-2">mdi-book-open-page-variant</v-icon>
                    <span class="meta-text">Lapu skaits: {{ book.lapu_skaits }}</span>
                  </div>
                </div>

                <div class="book-description" v-if="book.apraksts">
                  <p class="description-text">{{ book.apraksts }}</p>
                </div>

                <div class="guest-info" v-if="!isLoggedIn">
                  <p class="guest-message">
                    Lai lejupielādētu grāmatu, pievienotu to bibliotēkai un rakstītu atsauksmes, vajag reģistrēties vai ienākt.
                  </p>
                </div>



                <div class="action-buttons">
                 <template v-if="!isLoggedIn">
                  <v-btn
                    color="#003D3A"
                    class="action-btn"
                    @click="goToRegister"
                    rounded
                    x-large
                    depressed
                  >
                    <span class="button-text-white">Ienākt</span>
                  </v-btn>
                 </template>

                 <template v-else>
                   
                    <div class="action-buttons-row">
                      <div style="position: relative; width: 100%;">
                      <v-btn
                        color="#003D3A"
                        class="action-btn"
                        @click="downloadBook"
                        rounded
                        x-large
                        depressed
                      >
                        <span class="button-text-white">Lejupielādēt</span>
                      </v-btn>
                       <v-alert
                          v-if="notifications.download.show"
                          :type="notifications.download.type"
                          class="mt-2 notification-alert"
                          dense
                          outlined
                        >
                          {{ notifications.download.message }}
                      </v-alert>
                    </div>
                    <div style="position: relative; width: 100%;">
                      <v-btn
                        color="#003D3A"
                        class="action-btn"
                        @click="addToLibrary"
                        :loading="addingToLibrary"
                        :disabled="addingToLibrary"
                        rounded
                        x-large
                        depressed
                      >
                        <span class="button-text-white">Pievienot bibliotēkai</span>
                      </v-btn>
                      <v-alert
                        v-if="notifications.add.show"
                        :type="notifications.add.type"
                        class="mt-2 notification-alert"
                        dense
                        outlined
                      >
                        {{ notifications.add.message }}
                      </v-alert>
                    </div>
                    </div>
                 </template>
                </div>
              </div>
            </v-col>
          </v-row>

          <v-row class="mt-8">
            <v-col cols="12">
              <div class="reviews-section">
                <h2 class="reviews-title">Atsauksmes</h2>
                
                <div class="reviews-card">
                  <div class="reviews-icon">
                    <v-icon size="48" color="#003D3A">mdi-chat-outline</v-icon>
                  </div>
                  <div class="reviews-text">
                    <p class="reviews-message">
                      Par grāmatu <strong>"{{ book.nosaukums || book.title }}"</strong> pašlaik publiski pieejamu atsauksmju nav.
                    </p>
                    <p class="reviews-message">
                      Informācija par lasītāju vērtējumiem vai recenzijām nav atrasta, tāpēc grāmata vēl nav plaši apspriesta.
                    </p>
                    <p class="reviews-author">
                      Autors — <strong>{{ book.autors || book.author }}</strong>.
                    </p>
                  </div>
                </div>
              </div>
            </v-col>
          </v-row>
        </div>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import '../../css/book-view.css';
export default {
  
  name: 'BookView',
  data() {
    return {
      book: null,
      loading: true,
      error: false,
      errorMessage: '',
      
      isLoggedIn: false,
      user: null,
      authLoading: false,

      addingToLibrary: false,

      notifications: {
       download: { show: false, message: '', type: 'success' },
       add: { show: false, message: '', type: 'success' }
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
      if (this.userName) {
        return this.userName.charAt(0).toUpperCase();
      } else if (this.userEmail) {
        return this.userEmail.charAt(0).toUpperCase();
      }
      
      const savedUser = localStorage.getItem('user');
      if (savedUser) {
        try {
          const user = JSON.parse(savedUser);
          if (user.lietotaja_vards) {
            return user.lietotaja_vards.charAt(0).toUpperCase();
          } else if (user.epasts) {
            return user.epasts.charAt(0).toUpperCase();
          }
        } catch (e) {
          console.error('Error parsing saved user:', e);
        }
      }
      
      return 'U';
    },

    authToken() {
      return localStorage.getItem('auth_token');
    }
  },
  async mounted() {
    this.loadUserFromStorage();
    const isAuthenticated = await this.checkAuth();

    if (!isAuthenticated && localStorage.getItem('user')) {
      console.log('⚠️ localStorage ir lietotājs, bet sesija ir beigusies');
      localStorage.removeItem('user');
      this.isLoggedIn = false;
      this.user = null;
    }
    await this.loadBookDetails();
  },
  methods: {

    showNotification(type, message, isSuccess = true) {
      
      this.notifications[type] = {
        show: true,
        message: message,
        type: isSuccess ? 'success' : 'error'
      };
      
      
      setTimeout(() => {
        this.notifications[type].show = false;
      }, 3000);
    },
    
    loadUserFromStorage() {
      const savedUser = localStorage.getItem('user');
      if (savedUser) {
        try {
          const user = JSON.parse(savedUser);
          this.isLoggedIn = true;
          this.user = user;
          console.log('✅ Lietotājs ielādēts no localStorage:', user.lietotaja_vards);
        } catch (e) {
          console.error('Kļūda ielādējot lietotāju:', e);
        }
      }
    },

    async checkAuth() {
      if (this.authLoading) return;
      
      this.authLoading = true;
      console.log('🔐 Pārbaudu autentifikāciju...');
      
      const token = this.authToken;
      console.log('Tokens parbaudei:', token ? token.substring(0, 20) + '...' : 'nē');
      
      if (!token) {
       this.isLoggedIn = false;
       this.user = null;
       this.authLoading = false;
       return false;
      }
      
      try {
        const response = await fetch('http://localhost:8000/api/check-auth', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          }
        });
        
        const data = await response.json();
        console.log('Auth check atbilde:', data);
        
        if (data.authenticated && data.lietotajs) {
          this.isLoggedIn = true;
          this.user = data.lietotajs;
          localStorage.setItem('user', JSON.stringify(data.lietotajs));
          console.log('✅ Lietotājs autentificēts:', this.userName);
          return true;
        } else {
         console.log('❌ Lietotājs NAV autentificēts pēc API');
         this.isLoggedIn = false;
         this.user = null;
         localStorage.removeItem('auth_token');
         localStorage.removeItem('user');
         return false;
        }
        
      } catch (error) {
        console.error('Auth check kļūda:', error);
        return false;
      } finally {
        this.authLoading = false;
      }
    },

    async logout() {
      console.log('🚪 Mēģinu izrakstīties...');

      const token = this.authToken;
      
      try {
       if (token) {  
        const response = await fetch('http://localhost:8000/api/izrakstīties', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
          }
         });
        }
        } catch (error) {
         console.error('Logout kļūda:', error);
        } finally {
         this.isLoggedIn = false;
         this.user = null;
         localStorage.removeItem('auth_token');
         localStorage.removeItem('user');
         
         this.$router.push('/library');
        }
    },
        
        
      

    async loadBookDetails() {
      this.loading = true;
      this.error = false;
      
      try {
        const isbn = this.$route.params.isbn;
        console.log('📡 Ielādē grāmatu ar ISBN:', isbn);
        
        const response = await fetch(`http://localhost:8000/api/books/${isbn}`);
        
        if (!response.ok) {
          throw new Error(`HTTP kļūda: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📊 Saņemtie dati:', data);
        
        if (data.success && data.data) {
          this.book = data.data;
          console.log('📖 Grāmata no API:', this.book);
          console.log('📖 ISBN no API:', this.book.ISBN);
          console.log('📖 ISBN tips:', typeof this.book.ISBN);
          console.log('📖 Grāmatas apraksts:', this.book.apraksts);
          console.log('📖 isbn lauks:', this.book.isbn); 
          console.log('📖 Gramatas_ID:', this.book.Gramatas_ID); 
        } else {
          throw new Error('Grāmata nav atrasta');
        }
        
      } catch (error) {
        console.error('❌ Kļūda:', error.message);
        this.error = true;
        this.errorMessage = 'Neizdevās ielādēt grāmatas informāciju';
      } finally {
        this.loading = false;
      }
    },

    getBookCover(book) {
      if (book.vaku_attels && book.vaku_attels.trim() !== '') {
        const imagePath = book.vaku_attels;
        
        if (imagePath.startsWith('http')) {
          return imagePath;
        } else {
          const cleanPath = imagePath.replace(/^\/+/, '');
          return `http://localhost:8000/${cleanPath}`;
        }
      }
      
      return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&h=600&fit=crop';
    },

    downloadBook() {
    if (this.book.faila_pdf) {
        window.open(`http://localhost:8000/${this.book.faila_pdf}`, '_blank');
        this.showNotification('download', 'Lejupielāde sākta!', true);
      } else {
        this.showNotification('download', 'PDF fails nav pieejams', false);
      }
    },

    async addToLibrary() {
      
      console.log('📤 Mēģinu pievienot grāmatu bibliotēkai...');
      
      const token = this.authToken;
      console.log('Tokiens priekš pievienošanas:', token ? token.substring(0, 20) + '...' : 'nē');
      console.log('ISBN no grāmatas:', this.book?.isbn); 
      console.log('ISBN tips:', typeof this.book?.isbn);
      
      if (!token) {
        this.showNotification('add', 'Jūsu sesija ir beigusies. Lūdzu, pieslēdzieties vēlreiz.', false);
        this.goToLogin();
        return;
      }

      if (!this.book?.isbn) {
        this.showNotification('add', 'Grāmatas ISBN nav atrasts', false);
        return;
      }

      this.addingToLibrary = true;
      
      try {
        const requestBody = {
          isbn: this.book.isbn,
          statuss: 'vel nelasiju'
        };
        
        console.log('Atsūtu ISBN:', requestBody.isbn);
        
        const response = await fetch('http://localhost:8000/api/user/books/add', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify(requestBody) 
        });

        
        const responseText = await response.text();
        console.log('Atbilde no servera(teksts):', responseText);
        
        let data;
        try {
          data = JSON.parse(responseText);
          console.log('Atbilde no servera(JSON):', data);
        } catch (e) {
          console.error('Kļuda no parsinga:', e);
          this.showNotification('add', 'Servera atbilde nav JSON formātā', false);
          return;
        }

        if (response.status === 401) {
          console.log('❌ Sesija beigusies, nepieciešama atkārtota autentifikācija');
          this.isLoggedIn = false;
          this.user = null;
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          this.showNotification('add', 'Jūsu sesija ir beigusies. Lūdzu, pieslēdzieties vēlreiz.', false);
          this.goToLogin();
          return;
        }

        if (response.status === 422) {
          console.log('❌ 422 Unprocessable Entity');
          console.log('Validacijas kļudas:', data?.errors);
          
          let errorMessage = 'Validācijas kļūda:\n';
          if (data?.errors) {
            for (let field in data.errors) {
              errorMessage += `${field}: ${data.errors[field].join(', ')}\n`;
            }
          } else if (data?.message) {
            errorMessage = data.message;
          } else {
            errorMessage = 'Nezināma validācijas kļūda';
          }
          
          this.showNotification('add', errorMessage, false);
          return;
        }

        if (response.status === 500) {
          console.log('❌ Servera kļūda 500');
          this.showNotification('add', 'Servera kļūda. Lūdzu, mēģiniet vēlāk.', false);
          return;
        }

        if (data?.success) {
          this.showNotification('add', '✅ Grāmata pievienota jūsu bibliotēkai!', true);

        } else {
          this.showNotification('add', '❌ ' + (data?.message || 'Kļūda pievienojot grāmatu'), false);
        }
        
      } catch (error) {
        console.error('❌ Kļūda:', error);
        this.showNotification('add', 'Neizdevās pievienot grāmatu: ' + error.message, false);
      } finally {
        this.addingToLibrary = false;
      }

    },
    
    goToLibrary() {
      this.$router.push('/library');
    },

    goToMyLibrary() {
      this.$router.push('/library?tab=my-library');
    },

    goToLogin() {  
      this.$router.push('/login');
    },


    goToRegister() {
      this.$router.push('/login');
    }
  }
}
</script>

