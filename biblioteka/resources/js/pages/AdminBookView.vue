<template>
  <v-app>
    <v-app-bar app flat height="80" class="top-nav-bar" fixed>
      <v-container class="d-flex align-center justify-space-between px-8">
        <v-btn @click="goToAdminLibrary" variant="text" class="library-name-btn">
          <h1 class="library-name">MYLIBRARY</h1>
        </v-btn>
        
        <div></div>

        <div class="user-container">
          <v-menu offset-y>
            <template v-slot:activator="{ props }">
              <v-btn 
                color="#003D3A" 
                class="admin-btn"
                rounded
                v-bind="props"
              >
                <span class="admin-text">ADMINS</span>
                <v-icon right color="white">mdi-chevron-down</v-icon>
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
                    <v-chip x-small color="error" class="ml-2">ADMIN</v-chip>
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <v-divider></v-divider>
              
              <v-list-item @click="goToAdminLibrary">
                <v-list-item-icon>
                  <v-icon>mdi-view-dashboard</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Admin panelis</v-list-item-title>
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
            <v-btn @click="goToAdminLibrary" color="#003D3A" rounded>Atpakaļ uz admin paneli</v-btn>
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

                <v-card class="admin-stats-card mb-4" flat outlined>
                  <v-card-text>
                    <v-row>
                      <v-col cols="6">
                        <div class="admin-stat-item">
                          <v-icon small color="#003D3A">mdi-eye</v-icon>
                          <span class="admin-stat-label">Skatījumi:</span>
                          <span class="admin-stat-value">{{ book.views || 0 }}</span>
                        </div>
                      </v-col>
                      <v-col cols="6">
                        <div class="admin-stat-item">
                          <v-icon small color="#003D3A">mdi-download</v-icon>
                          <span class="admin-stat-label">Lejupielādes:</span>
                          <span class="admin-stat-value">{{ book.downloads || 0 }}</span>
                        </div>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>

                <div class="book-meta">
                  <div class="meta-item" v-if="book.gads">
                    <v-icon color="#003D3A" class="mr-2">mdi-calendar</v-icon>
                    <span class="meta-text">Gads: {{ book.gads }}</span>
                  </div>
                  <div class="meta-item" v-if="book.lapu_skaits">
                    <v-icon color="#003D3A" class="mr-2">mdi-book-open-page-variant</v-icon>
                    <span class="meta-text">Lapu skaits: {{ book.lapu_skaits }}</span>
                  </div>
                  <div class="meta-item" v-if="book.faila_pdf">
                    <v-icon color="#003D3A" class="mr-2">mdi-file-pdf-box</v-icon>
                    <span class="meta-text">PDF fails: {{ book.faila_pdf.split('/').pop() }}</span>
                  </div>
                </div>

                <div class="book-description" v-if="book.apraksts">
                  <p class="description-text">{{ book.apraksts }}</p>
                </div>

                <div class="action-buttons">
                  <v-btn
                    color="#ff8c00"
                    class="edit-book-btn"
                    @click="openEditForm"
                    rounded
                    x-large
                    depressed
                    block
                  >
                    <v-icon left>mdi-pencil</v-icon>
                    Rediģēt grāmatu
                  </v-btn>
                </div>
              </div>
            </v-col>
          </v-row>

          <v-row class="mt-8">
            <v-col cols="12">
              <div class="reviews-section">
                <h2 class="reviews-title">Atsauksmes</h2>

                <div v-if="reviewsLoading" class="text-center py-4">
                  <v-progress-circular indeterminate color="#003D3A" size="40"></v-progress-circular>
                  <p class="mt-2">Ielādē atsauksmes...</p>
                </div>

                <div v-else-if="reviews.length > 0" class="reviews-list">
                  <div
                    v-for="review in reviews"
                    :key="review.Atsauksmes_ID"
                    class="review-item"
                  >
                    <div class="review-header">
                      <div class="reviewer-info">
                        <v-avatar color="#003D3A" size="40" class="mr-3">
                          <span class="reviewer-initial">{{ getUserInitial(review.lietotaja_vards) }}</span>
                        </v-avatar>
                        <div>
                          <div class="reviewer-name">{{ review.lietotaja_vards }}</div>
                          <div class="review-date">{{ formatDate(review.created_at) }}</div>
                        </div>
                      </div>
                      <div class="review-rating">
                        <v-icon
                          v-for="star in 5"
                          :key="star"
                          :color="star <= review.vertejums ? '#FFD700' : '#C0C0C0'"
                          size="18"
                        >
                          mdi-star
                        </v-icon>
                        <span class="rating-value">({{ review.vertejums }}/5)</span>
                      </div>
                    </div>
                    <p class="review-text">{{ review.komentārs }}</p>
                  </div>
                </div>

                <div v-else class="reviews-card">
                  <div class="reviews-icon">
                    <v-icon size="48" color="#003D3A">mdi-chat-outline</v-icon>
                  </div>
                  <div class="reviews-text">
                    <p class="reviews-message">
                      Par grāmatu <strong>"{{ book.nosaukums || book.title }}"</strong> pašlaik publiski pieejamu atsauksmju nav.
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

    <v-dialog v-model="showEditForm" max-width="600" persistent>
      <v-card class="editBook-card">
        <v-card-title class="editBook-header">
          <div class="editBook-content">
            <div>
              <h2 class="editBook-title">Rediģēt grāmatu</h2>
              <p class="editBook-subtitle">ISBN: {{ book?.isbn }}</p>
            </div>
            <v-spacer></v-spacer>
            <v-btn icon dark @click="showEditForm = false" class="close-btn-editBook">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        
        <v-card-text class="pt-4">
          <v-form ref="editForm">
            <v-text-field
              v-model="editBookData.nosaukums"
              label="Nosaukums *"
              required
              outlined
              dense
              class="mb-3"
            ></v-text-field>
            
            <v-text-field
              v-model="editBookData.autors"
              label="Autors *"
              required
              outlined
              dense
              class="mb-3"
            ></v-text-field>
            
            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model="editBookData.gads"
                  label="Gads"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="editBookData.lapu_skaits"
                  label="Lapu skaits"
                  type="number"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
            </v-row>
            
            <v-textarea
              v-model="editBookData.apraksts"
              label="Apraksts"
              outlined
              dense
              rows="3"
              class="mb-3"
            ></v-textarea>
            
            <v-select
              v-model="editBookData.Nodala_ID"
              :items="nodalaOptions"
              item-title="tips"
              item-value="Nodala_ID"
              label="Nodaļa *"
              outlined
              dense
              class="mb-3"
              required
            ></v-select>
            
            <v-select
              v-model="editBookData.Zanra_ID"
              :items="genreOptions"
              item-title="nosaukums"
              item-value="Zanra_ID"
              label="Žanrs *"
              outlined
              dense
              class="mb-3"
              required
            ></v-select>
            
            <v-text-field
              v-model="editBookData.faila_pdf"
              label="PDF fails (ID)"
              placeholder="12345623"
              hint="Ievadiet faila ID (piemēram, 12345623)"
              outlined
              dense
              class="mb-3"
            ></v-text-field>
            
            <v-text-field
              v-model="editBookData.vaku_attels"
              label="Vāka attēls (ceļš)"
              outlined
              dense
              class="mb-3"
            ></v-text-field>
          </v-form>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="showEditForm = false">
            Atcelt
          </v-btn>
          <v-btn color="#ff8c00" dark @click="updateBook">
            Saglabāt izmaiņas
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
      
      <template v-slot:action="{ attrs }">
        <v-btn
          text
          v-bind="attrs"
          @click="snackbar.show = false"
          color="white"
        >
          OK
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script>
import '../../css/adminbook-view.css';
export default {
  name: 'AdminBookView',
  data() {
    return {
      book: null,
      loading: true,
      error: false,
      errorMessage: '',
      
      user: null,
      genres: [],

      reviews: [],
      reviewsLoading: true,
      
      showEditForm: false,
      editBookData: {},

      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },

      cloudflareBaseUrl: 'https://pub-6f170bacdf6a417ca301be11f05629c4.r2.dev',
    };
  },
  computed: {
    userName() {
      return this.user?.lietotaja_vards || 'Admins';
    },
    userEmail() {
      return this.user?.epasts || '';
    },
    authToken() {
      return localStorage.getItem('auth_token');
    },
    nodalaOptions() {
      return [
        { Nodala_ID: 1, tips: 'Akadēmiskā' },
        { Nodala_ID: 2, tips: 'Atpūtas' }
      ];
    },
    genreOptions() {
      return this.genres.map(g => ({
        Zanra_ID: g.id,
        nosaukums: g.name
      }));
    }
  },
  async mounted() {
    await this.checkAuth();
    await this.fetchGenres();
    await this.loadBookDetails();
    await this.loadBookReviews();
  },
  methods: {
    async checkAuth() {
      const token = this.authToken;
      if (!token) {
        this.$router.push('/login');
        return;
      }
      try {
        const response = await fetch('/api/check-auth', {
          headers: { 'Authorization': 'Bearer ' + token }
        });
        const data = await response.json();
        if (data.authenticated && data.lietotajs) {
          this.user = data.lietotajs;
          if (this.user.loma !== 'admins') {
            this.$router.push('/library');
          }
        } else {
          this.$router.push('/login');
        }
      } catch (error) {
        console.error('Auth error:', error);
        this.$router.push('/login');
      }
    },

    async fetchGenres() {
      try {
        const response = await fetch('/api/genres');
        const data = await response.json();
        if (data.success && data.data) {
          this.genres = data.data.map(genre => ({
            id: genre.Zanra_ID,
            name: genre.nosaukums,
            nodala: genre.Nodala
          }));
        }
      } catch (error) {
        console.error('Error loading genres:', error);
      }
    },

    async loadBookDetails() {
      this.loading = true;
      this.error = false;
      
      try {
        const isbn = this.$route.params.isbn;
        

        const headers = {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + this.authToken
        };
        
        const response = await fetch(`/api/books/${isbn}`, {
          method: 'GET',
          headers: headers
        });
        
        if (!response.ok) {
          throw new Error(`HTTP kļūda: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
          this.book = data.data;
         
          await this.loadBookStats();
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

    async loadBookStats() {
        try {
            const response = await fetch(`/api/admin/stats/books/${this.$route.params.isbn}`, {
            headers: {
                'Authorization': 'Bearer ' + this.authToken
            }
            });
            const data = await response.json();
            
            if (data.data) {
            this.book.views = data.data.views || 0;
            this.book.downloads = data.data.downloads || 0;
            ('📊 Statistika ielādēta:', data.data);
            }
        } catch (error) {
            console.error('❌ Kļūda ielādējot statistiku:', error);
        }
    },

    async loadBookReviews() {
      this.reviewsLoading = true; 
      try {
        const isbn = this.$route.params.isbn;
        const response = await fetch(`/api/books/${isbn}/reviews`);
        const data = await response.json();
        
        if (data.success && data.data) {
          this.reviews = data.data;
        } else {
          this.reviews = [];
        }
      } catch (error) {
        console.error('❌ Kļūda ielādējot atsauksmes:', error);
        this.reviews = [];
      } finally {
        this.reviewsLoading = false;
      }
    },

    getBookCover(book) {
      if (book.vaku_attels && book.vaku_attels.trim() !== '') {
        const imagePath = book.vaku_attels;
        if (imagePath.startsWith('http')) {
          return imagePath;
        } else {
          const cleanPath = imagePath.replace(/^\/+/, '');
          return `/${cleanPath}`;
        }
      }
      return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&h=600&fit=crop';
    },

    getUserInitial(name) {
      if (!name) return 'U';
      return name.charAt(0).toUpperCase();
    },

    formatDate(dateString) {
      if (!dateString) return '';
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('lv-LV', options);
    },

    openEditForm() {
      this.editBookData = {
        ISBN: this.book.isbn,
        nosaukums: this.book.nosaukums,
        autors: this.book.autors,
        gads: this.book.gads || '',
        lapu_skaits: this.book.lapu_skaits || '',
        apraksts: this.book.apraksts || '',
        Nodala_id: this.book.nodala_id || 1,
        Zanra_id: this.book.zanra_id || null,
        faila_pdf: this.book.faila_pdf || '',
        vaku_attels: this.book.vaku_attels || ''
      };
      this.showEditForm = true;
    },

    async updateBook() {
      try {
        const response = await fetch(`/api/admin/books/${this.book.isbn}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken
          },
          body: JSON.stringify({
            ...this.editBookData,
            faila_pdf: this.editBookData.faila_pdf ? `https://pub-6f170bacdf6a417ca301be11f05629c4.r2.dev/${this.editBookData.faila_pdf}` : ''
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('Grāmata veiksmīgi atjaunota!', true); 
          
         
          await this.loadBookDetails();
          
          this.showEditForm = false;
        } else {
          console.error(' Kļūda atjauninot grāmatu:', data);
          this.showNotification(data.message || 'Kļūda saglabājot izmaiņas', false);
        }
        
      } catch (error) {
        console.error('Error updating book:', error);
      }
    },

    showNotification(message, isSuccess = true) {
     this.snackbar = {
        show: true,
        text: message,
        color: isSuccess ? 'success' : 'error'
      };
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

    goToAdminLibrary() {
      this.$router.push('/admin');
    }
  }
}
</script>

