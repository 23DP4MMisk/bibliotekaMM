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

                <div class="guest-info">
                  <p class="guest-message">
                    Lai lejupielādētu grāmatu, pievienotu to bibliotēkai un rakstītu atsauksmes, vajag reģistrēties vai ienākt.
                  </p>
                </div>

                <div class="action-buttons">
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
      authLoading: false
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
    }
  },
  async mounted() {
    this.loadUserFromStorage();
    await this.checkAuth();
    await this.loadBookDetails();
  },
  methods: {
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
      
      try {
        const response = await fetch('http://localhost:8000/api/check-auth', {
          method: 'GET',
          credentials: 'include',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        
        const data = await response.json();
        console.log('Auth check atbilde:', data);
        
        if (data.authenticated && data.lietotajs) {
          this.isLoggedIn = true;
          this.user = data.lietotajs;
          localStorage.setItem('user', JSON.stringify(data.lietotajs));
          console.log('✅ Lietotājs autentificēts:', this.userName);
        } else {
          if (!localStorage.getItem('user')) {
            this.isLoggedIn = false;
            this.user = null;
          }
        }
        
      } catch (error) {
        console.error('Auth check kļūda:', error);
      } finally {
        this.authLoading = false;
      }
    },

    async logout() {
      console.log('🚪 Mēģinu izrakstīties...');
      
      try {
        const response = await fetch('http://localhost:8000/api/izrakstīties', {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        
        const data = await response.json();
        console.log('Logout atbilde:', data);
        
        if (data.success) {
          this.isLoggedIn = false;
          this.user = null;
          localStorage.removeItem('user');
          alert('Jūs esat veiksmīgi izrakstījies');
          setTimeout(() => {
            this.$router.go(0);
          }, 1000);
        }
        
      } catch (error) {
        console.error('Logout kļūda:', error);
        this.isLoggedIn = false;
        this.user = null;
        localStorage.removeItem('user');
        this.$router.go(0);
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
          console.log('📖 Grāmatas apraksts:', this.book.apraksts);
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

    goToLibrary() {
      this.$router.push('/library');
    },

    goToMyLibrary() {
      this.$router.push('/library?tab=my-library');
    },

    goToRegister() {
      this.$router.push('/login');
    }
  }
}
</script>

<style scoped>
.top-nav-bar {
  background-color: white !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border-bottom: 1px solid rgba(0, 61, 58, 0.1);
}

.library-name {
  font-size: 2.2rem;
  color: #003D3A;
  font-weight: 800;
  letter-spacing: 1px;
  cursor: pointer;
  text-transform: uppercase;
}

.user-container {
  display: flex;
  align-items: center;
}

.user-initial-btn {
  width: 48px !important;
  height: 48px !important;
  min-width: 48px !important;
  padding: 0 !important;
  background-color: #003D3A !important;
  border-radius: 50% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  box-shadow: 0 2px 8px rgba(0, 61, 58, 0.3);
}

.user-initial {
  font-size: 20px;
  font-weight: 600;
  color: white !important;
  text-transform: uppercase;
  line-height: 1;
}

.main-content {
  background-color: #fafafa;
  min-height: calc(100vh - 80px);
  padding-top: 40px;
}

.book-detail-container {
  max-width: 1200px;
  margin: 0 auto;
  background-color: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.book-cover-large {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
  background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
  max-width: 350px;
  margin: 0 auto;
}

.book-cover-image-large {
  width: 100%;
  height: 400px;
  object-fit: cover;
}

.book-info-container {
  padding: 20px 0 20px 30px;
}

.book-title-large {
  font-size: 2.5rem;
  font-weight: 700;
  color: #003D3A;
  margin-bottom: 10px;
  line-height: 1.2;
}

.book-author-large {
  font-size: 1.5rem;
  font-weight: 500;
  color: #555;
  margin-bottom: 25px;
  font-style: italic;
}

.book-meta {
  display: flex;
  gap: 30px;
  margin-bottom: 25px;
  background-color: #f8f9fa;
  padding: 15px 20px;
  border-radius: 12px;
}

.meta-item {
  display: flex;
  align-items: center;
}

.meta-text {
  font-size: 1.1rem;
  color: #333;
  font-weight: 500;
}

.book-description {
  margin-bottom: 30px;
  background-color: white;
  padding: 20px;
  border-radius: 12px;
  border-left: 4px solid #003D3A;
}

.description-text {
  font-size: 1.1rem;
  line-height: 1.6;
  color: #444;
  text-align: justify;
}

.guest-info {
  margin: 30px 0;
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 12px;
  border: 1px solid #e0e0e0;
}

.guest-message {
  font-size: 1.1rem;
  color: #333;
  line-height: 1.5;
  margin: 0;
}

.action-buttons {
  margin-top: 20px;
}

.action-btn {
  min-width: 200px !important;
  height: 56px !important;
  font-size: 1.2rem !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
  letter-spacing: 1px !important;
  box-shadow: 0 4px 12px rgba(0, 61, 58, 0.3) !important;
}

.action-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 61, 58, 0.4) !important;
  transition: all 0.3s ease;
}

.button-text-white {
  color: white !important;
}

.reviews-section {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 2px solid #e0e0e0;
}

.reviews-title {
  font-size: 2rem;
  font-weight: 600;
  color: #003D3A;
  margin-bottom: 25px;
  text-align: center;
}

.reviews-card {
  background-color: #f8f9fa;
  border-radius: 20px;
  padding: 40px;
  text-align: center;
  max-width: 800px;
  margin: 0 auto;
  box-shadow: 0 4px 12px rgba(0, 61, 58, 0.1);
  border: 1px solid #e0e0e0;
}

.reviews-icon {
  margin-bottom: 20px;
}

.reviews-icon .v-icon {
  opacity: 0.7;
}

.reviews-text {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #444;
}

.reviews-message {
  margin-bottom: 15px;
}

.reviews-author {
  margin-top: 20px;
  font-style: italic;
  color: #666;
}

@media (max-width: 960px) {
  .book-detail-container {
    padding: 20px;
  }
  
  .book-info-container {
    padding: 20px 0 0 0;
  }
  
  .book-title-large {
    font-size: 2rem;
  }
  
  .book-author-large {
    font-size: 1.3rem;
  }
  
  .book-meta {
    flex-direction: column;
    gap: 10px;
  }
  
  .action-btn {
    width: 100%;
  }

  .reviews-card {
    padding: 30px 20px;
  }
  
  .reviews-title {
    font-size: 1.8rem;
  }
}

@media (max-width: 600px) {
  .library-name {
    font-size: 1.5rem;
  }
  
  .book-title-large {
    font-size: 1.8rem;
  }
  
  .book-author-large {
    font-size: 1.2rem;
  }

  .reviews-card {
    padding: 25px 15px;
  }
  
  .reviews-title {
    font-size: 1.6rem;
  }
  
  .reviews-text {
    font-size: 1rem;
  }
}
</style>