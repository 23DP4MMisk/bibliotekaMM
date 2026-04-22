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
        
        <div v-if="loadingBook" class="text-center py-12">
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

        
        <div v-else-if="book" class="review-detail-container">
          <v-row>
            
            <v-col cols="12" md="5" lg="4">
              <div class="review-cover">
                <div class="review-cover-container">
                  <v-img
                    :src="getBookCover(book)"
                    :alt="book.nosaukums || book.title"
                    cover
                    class="review-cover-image"
                    height="400"
                  >
                    <template v-slot:placeholder>
                      <div class="d-flex align-center justify-center fill-height">
                        <v-icon size="64" color="#003D3A">mdi-book-open-variant</v-icon>
                      </div>
                    </template>
                  </v-img>
                </div>
              </div>
            </v-col>

            
            <v-col cols="12" md="7" lg="8">
              <div class="review-info-container">
                <h1 class="review-title">Atsauksmes rakstīšana</h1>
                
                <h2 class="review-subtitle">{{ book.nosaukums || book.title }}</h2>
                <h3 class="review-author">{{ book.autors || book.author }}</h3>

                
                <div v-if="existingReview" class="existing-review-block">
                  <v-alert
                    type="info"
                    class="existing-review-alert"
                    dense
                    outlined
                  >
                    <div class="d-flex align-center">
                      <v-icon left color="#003D3A">mdi-information</v-icon>
                      <span>
                        Jūs jau esat uzrakstījis atsauksmi par šo grāmatu.
                      </span>
                    </div>
                  </v-alert>
                  
                  <v-btn
                    color="#003D3A"
                    class="view-reviews-btn"
                    @click="goToBookView"
                    block
                    x-large
                    depressed
                  >
                    <span class="button-text-white">Apskatīt atsauksmes</span>
                  </v-btn>
                </div>

                
                <div v-else class="review-form">
                  
                  <v-textarea
                    v-model="reviewText"
                    label="Jūsu atsauksme"
                    placeholder="Dalieties savās domās par grāmatu..."
                    outlined
                    rows="5"
                    class="mb-4"
                    hide-details
                  ></v-textarea>

                  
                  <div class="rating-section mb-4">
                    <label class="rating-label">Jūsu vērtējums</label>
                    <div class="stars">
                      <v-icon
                        v-for="star in 5"
                        :key="star"
                        :color="star <= rating ? '#FFD700' : '#C0C0C0'"
                        size="40"
                        class="star-icon"
                        @click="rating = star"
                      >
                        mdi-star
                      </v-icon>
                    </div>
                  </div>

                  
                  <v-btn
                    color="#003D3A"
                    class="submit-btn"
                    @click="submitReview"
                    :loading="submitting"
                    :disabled="submitting || rating === 0"
                    block
                    x-large
                    depressed
                  >
                    <span class="button-text-white">IESNIEGT ATSAUKSMI</span>
                  </v-btn>
                </div>

                
                <v-alert
                  v-if="notification.show"
                  :type="notification.type"
                  class="mt-4"
                  dense
                  outlined
                >
                  {{ notification.message }}
                </v-alert>
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
  name: 'RewievPage',
  data() {
    return {
      book: null,
      bookId: null,
      loadingBook: true,
      error: false,
      errorMessage: '',
      
      reviewText: '',
      rating: 0,
      submitting: false,
      
      existingReview: false, 
      userReview: null, 
      
      notification: {
        show: false,
        message: '',
        type: 'success'
      },
      
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
      if (this.userName) return this.userName.charAt(0).toUpperCase();
      if (this.userEmail) return this.userEmail.charAt(0).toUpperCase();
      return 'U';
    },
    authToken() {
      return localStorage.getItem('auth_token');
    }
  },
  async mounted() {
    this.bookId = this.$route.params.isbn;
    console.log('📚 Загружаем книгу с ISBN:', this.bookId);
    
    await this.checkAuth();
    
    if (!this.isLoggedIn) {
      this.$router.push('/login');
      return;
    }
    
    await this.loadBookDetails();
    await this.checkExistingReview();
  },
  methods: {
    async loadBookDetails() {
      this.loadingBook = true;
      this.error = false;
      
      try {
        const token = this.authToken;
        
        const headers = {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        };

        if (token) {
          headers['Authorization'] = 'Bearer ' + token;
        }
        
        const response = await fetch(`/api/books/${this.bookId}`, {
          method: 'GET',
          headers: headers
        });
        
        if (!response.ok) {
          throw new Error(`HTTP kļūda: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📊 Saņemtie dati:', data);
        
        if (data.success && data.data) {
          this.book = data.data;
          console.log('📖 Grāmata ielādēta:', this.book);
        } else {
          throw new Error('Grāmata nav atrasta');
        }
        
      } catch (error) {
        console.error('❌ Kļūda ielādējot grāmatu:', error.message);
        this.error = true;
        this.errorMessage = 'Neizdevās ielādēt grāmatas informāciju';
      } finally {
        this.loadingBook = false;
      }
    },
    
    async checkExistingReview() {
      try {
        const token = this.authToken;
        
        const response = await fetch(`/api/reviews/check/${this.bookId}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          }
        });
        
        const data = await response.json();
        
        if (data.exists) {
          this.existingReview = true;
          this.userReview = data.review;
          console.log('📝 Отзыв уже существует:', this.userReview);
        } else {
          this.existingReview = false;
          console.log('📝 Отзыва еще нет');
        }
      } catch (error) {
        console.error('Ошибка проверки отзыва:', error);
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
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          }
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
        console.error('Auth check error:', error);
        this.isLoggedIn = false;
      } finally {
        this.authLoading = false;
      }
    },
    
    async logout() {
      const token = this.authToken;
      
      try {
        if (token) {
          await fetch('/api/izrakstīties', {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          });
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        this.$router.push('/library');
      }
    },
    
    async submitReview() {
      if (this.rating === 0) {
        this.showNotification('Lūdzu, izvēlieties vērtējumu!', 'error');
        return;
      }
      
      this.submitting = true;
      
      try {
        const response = await fetch('/api/reviews', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + this.authToken
          },
          body: JSON.stringify({
            gramatas_id: this.bookId,
            vertejums: this.rating,
            komentars: this.reviewText
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('Paldies! Jūsu atsauksme ir publicēta.', 'success');
          this.reviewText = '';
          this.rating = 0;
          
          
          this.existingReview = true;
          
          setTimeout(() => {
            this.$router.push(`/book/${this.bookId}`);
          }, 2000);
        } else {
          this.showNotification(data.message || 'Kļūda publicējot atsauksmi', 'error');
        }
      } catch (error) {
        console.error('Error submitting review:', error);
        this.showNotification('Kļūda savienojumā ar serveri', 'error');
      } finally {
        this.submitting = false;
      }
    },
    
    goToBookView() {
      this.$router.push(`/book/${this.bookId}`);
    },
    
    showNotification(message, type = 'success') {
      this.notification = {
        show: true,
        message: message,
        type: type
      };
      
      setTimeout(() => {
        this.notification.show = false;
      }, 3000);
    },
    
    goToLibrary() {
      this.$router.push('/library');
    },
    
    goToMyLibrary() {
      this.$router.push({
        path: '/library',
        query: { tab: 'my-library' }
      });
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

.review-detail-container {
  max-width: 1200px;
  margin: 0 auto;
  background-color: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.review-cover {
  border-radius: 12px;
  overflow: hidden;
  background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
  max-width: 350px;
  margin: 0 auto;
}

.review-cover-container {
  width: 100%;
  height: 400px;
}

.review-cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.review-info-container {
  padding: 20px 0 20px 30px;
}

.review-title {
  font-size: 2rem;
  font-weight: 600;
  color: #003D3A;
  margin-bottom: 10px;
}

.review-subtitle {
  font-size: 1.8rem;
  font-weight: 700;
  color: #003D3A;
  margin-bottom: 5px;
  line-height: 1.2;
}

.review-author {
  font-size: 1.3rem;
  font-weight: 500;
  color: #555;
  margin-bottom: 25px;
  font-style: italic;
}

.review-form {
  margin-top: 20px;
}

.existing-review-block {
  margin-top: 20px;
}

.existing-review-alert {
  background-color: #e3f2fd !important;
  color: #1976d2 !important;
  border-left: 4px solid #1976d2 !important;
  margin-bottom: 20px;
}

.view-reviews-btn {
  background-color: #003D3A !important;
  border-radius: 4px !important;
  height: 56px !important;
  font-size: 1.2rem !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
  letter-spacing: 1px !important;
  box-shadow: 0 4px 12px rgba(0, 61, 58, 0.3) !important;
}

.view-reviews-btn:hover {
  background-color: #002c29 !important;
}

.rating-section {
  text-align: left;
}

.rating-label {
  display: block;
  font-size: 1rem;
  font-weight: 500;
  color: #333;
  margin-bottom: 10px;
}

.stars {
  display: flex;
  gap: 5px;
}

.star-icon {
  cursor: pointer;
  transition: transform 0.2s ease;
}

.star-icon:hover {
  transform: scale(1.2);
}

.submit-btn {
  background-color: #003D3A !important;
  border-radius: 4px !important;
  height: 56px !important;
  font-size: 1.2rem !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
  letter-spacing: 1px !important;
  box-shadow: 0 4px 12px rgba(0, 61, 58, 0.3) !important;
}

.submit-btn:hover {
  background-color: #002c29 !important;
}

.button-text-white {
  color: white !important;
}

.error-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 40px;
  border-radius: 20px;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.error-title {
  color: #003D3A;
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 15px;
}

.error-message {
  color: #666;
  font-size: 1.2rem;
  line-height: 1.6;
  margin-bottom: 30px;
}

@media (max-width: 960px) {
  .review-detail-container {
    padding: 20px;
  }
  
  .review-info-container {
    padding: 20px 0 0 0;
  }
  
  .review-title {
    font-size: 1.8rem;
  }
  
  .review-subtitle {
    font-size: 1.5rem;
  }
  
  .review-author {
    font-size: 1.2rem;
  }
}

@media (max-width: 600px) {
  .library-name {
    font-size: 1.5rem;
  }
  
  .review-title {
    font-size: 1.5rem;
  }
  
  .review-subtitle {
    font-size: 1.3rem;
  }
  
  .stars {
    gap: 2px;
  }
  
  .star-icon {
    font-size: 30px !important;
  }
}
</style>