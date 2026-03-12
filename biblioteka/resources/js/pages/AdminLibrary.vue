<template>
  <v-app>
    
    <v-app-bar app flat height="80" class="top-nav-bar" fixed>
      <v-container class="d-flex align-center justify-space-between px-8">
        
        
        <v-btn @click="showAllBooks" variant="text" class="library-name-btn">
          <h1 class="library-name">MYLIBRARY</h1>
        </v-btn>
        
        
        <div class="search-container">
          <v-text-field
            v-model="searchQuery"
            placeholder="Meklēt grāmatas..."
            solo
            flat
            dense
            hide-details
            prepend-inner-icon="mdi-magnify"
            class="search-field"
            @keyup.enter="performSearch"
            @input="handleSearchInput"
          ></v-text-field>
        </div>

        
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
              
              <v-list-item @click="showUsersList = true">
                <v-list-item-icon>
                  <v-icon>mdi-account-group</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Lietotāju saraksts</v-list-item-title>
              </v-list-item>
              
              <v-list-item @click="showStatistics = true">
                <v-list-item-icon>
                  <v-icon>mdi-chart-bar</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Statistika</v-list-item-title>
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
        
        
        <v-row class="mb-6">
          <v-col cols="12" class="d-flex justify-center">
            <div class="category-menu">
              
              
              <div class="nodala-dropdown">
                <v-btn 
                  variant="text"
                  class="category-btn"
                  @mouseenter="showNodalaMenu = true"
                  @mouseleave="startCloseMenuTimer"
                >
                  Nodaļas
                  <v-icon right>mdi-chevron-down</v-icon>
                </v-btn>
                
                <div 
                  v-if="showNodalaMenu" 
                  class="nodala-menu"
                  @mouseenter="showNodalaMenu = true"
                  @mouseleave="startCloseMenuTimer"
                >
                  <v-list class="py-0">
                    <v-list-item 
                      @click="selectNodala('academic')"
                      class="menu-item"
                    >
                      <v-list-item-title class="text-left">
                        Akademiskas grāmatas
                      </v-list-item-title>
                    </v-list-item>
                    <v-divider></v-divider>
                    <v-list-item 
                      @click="selectNodala('leisure')"
                      class="menu-item"
                    >
                      <v-list-item-title class="text-left">
                        Grāmatas atpūtai
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </div>
              </div>

              
              <a 
                href="#" 
                @click.prevent="showAddBookForm = true" 
                class="admin-link"
              >
                Pievienot grāmatu
              </a>

              <a 
                href="#" 
                @click.prevent="showUsersList = true" 
                class="admin-link"
              >
                Lietotāju saraksts
              </a>
              
              <a 
                href="#" 
                @click.prevent="showStatistics = true" 
                class="admin-link"
              >
                Statistika
              </a>
            </div>
          </v-col>
        </v-row>

       
        
        
        <v-row class="mb-6">
          <v-col cols="12">
            <h2 class="category-title text-center">
              <span v-if="activeCategory === 'all' && !searchQuery">Visas grāmatas</span>
              <span v-else-if="selectedNodalaName">{{ selectedNodalaName }}</span>
              <span v-else-if="selectedGenreName">{{ selectedGenreName }}</span>
              <span v-else-if="searchQuery">Meklēšanas rezultāti: "{{ searchQuery }}"</span>
              
            </h2>
          </v-col>
        </v-row>

        <v-row class="mb-6" v-if="selectedNodala">
          <v-col cols="12">
            <div class="genre-menu">
              <v-btn
                v-for="genre in availableGenres"
                :key="genre.id"
                :class="['genre-btn', { 'active-genre': selectedGenre === genre.id }]"
                variant="text"
                @click="selectGenre(genre.id)"
              >
                {{ genre.name }}
                <span class="genre-count" v-if="genre.count">({{ genre.count }})</span>
              </v-btn>
            </div>
          </v-col>
        </v-row>
        
        
        <div v-if="loading" class="text-center py-12">
          <v-progress-circular indeterminate color="#003D3A" size="64"></v-progress-circular>
          <p class="mt-4">Ielādē grāmatas...</p>
        </div>
        
        
        <div v-else-if="error" class="text-center py-12">
          <div class="error-container">
            <v-icon size="100" color="#ff6b6b" class="mb-4">mdi-alert-circle-outline</v-icon>
            <h3 class="error-title mb-3">Radās kļūda</h3>
            <p class="error-message mb-4">{{ errorMessage }}</p>
            <v-btn @click="fetchBooks()" color="#003D3A" rounded>Mēģināt vēlreiz</v-btn>
          </div>
        </div>
        
        
        <div v-else>
          
          <div v-if="displayedBooks.length > 0">
            <v-row>
              <v-col 
                cols="12" 
                sm="6" 
                md="4" 
                lg="3"
                v-for="book in displayedBooks" 
                :key="book.isbn"
              >
                <v-card class="book-card" elevation="0">
                  <div class="book-cover-wrapper">
                    <div class="book-cover-container">
                      <v-img
                        :src="getBookCover(book)"
                        :alt="book.title"
                        cover
                        class="book-cover-image"
                      >
                        <template v-slot:placeholder>
                          <div class="d-flex align-center justify-center fill-height">
                            <v-icon size="64" color="#003D3A">mdi-book-open-variant</v-icon>
                          </div>
                        </template>
                      </v-img>
                    </div>
                  </div>
                  
                  <v-card-text class="pa-3 pt-4 text-center">
                    <h3 class="book-title mb-2">{{ book.title }}</h3>
                    <p class="book-author mb-3">{{ book.author }}</p>
                    
                    
                    <v-btn
                      color="#003D3A"
                      class="view-btn"
                      @click="viewBook(book.isbn)"
                      rounded
                      block
                    >
                      Apskatīt
                    </v-btn>

                    
                    <v-btn
                      color="#b71c1c"
                      class="delete-book-btn mt-2"
                      @click="confirmDeleteBook(book)"
                      rounded
                      block
                    >
                      <v-icon left small>mdi-delete</v-icon>
                      Dzēst grāmatu
                    </v-btn>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </div>
          
          
          <div v-else class="text-center py-12">
            <div class="no-books-container">
              <v-icon size="100" color="#a0a0a0" class="mb-4">mdi-book-search</v-icon>
              <h3 class="no-books-title mb-3">Grāmatas netika atrastas</h3>
              <p class="no-books-message mb-4">
                <span v-if="searchQuery">Meklēšanas vaicājumam "{{ searchQuery }}" nav rezultātu.</span>
                <span v-else>Šajā kategorijā pašlaik nav grāmatu.</span>
              </p>
              <v-btn @click="showAllBooks" color="#003D3A" rounded class="mr-3">
                <v-icon left>mdi-book-multiple</v-icon>
                Apskatīt visas grāmatas
              </v-btn>
            </div>
          </div>
        </div>
      </v-container>
    </v-main>

    
    <v-dialog v-model="showAddBookForm" max-width="600" persistent>
      <v-card>
        <v-card-title class="headline" style="background-color: #003D3A; color: white;">
          Pievienot jaunu grāmatu
          <v-spacer></v-spacer>
          <v-btn icon dark @click="showAddBookForm = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pt-4">
          <v-form ref="addBookForm">
            <v-text-field
              v-model="newBook.isbn"
              label="ISBN *"
              required
              outlined
              dense
              class="mb-3"
            ></v-text-field>
            
            <v-text-field
              v-model="newBook.nosaukums"
              label="Nosaukums *"
              required
              outlined
              dense
              class="mb-3"
            ></v-text-field>
            
            <v-text-field
              v-model="newBook.autors"
              label="Autors *"
              required
              outlined
              dense
              class="mb-3"
            ></v-text-field>
            
            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model="newBook.gads"
                  label="Gads"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="newBook.lapu_skaits"
                  label="Lapu skaits"
                  type="number"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
            </v-row>
            
            <v-textarea
              v-model="newBook.apraksts"
              label="Apraksts"
              outlined
              dense
              rows="3"
              class="mb-3"
            ></v-textarea>
            
            <v-select
              v-model="newBook.nodala_id"
              :items="nodalaOptions"
              item-text="tips"
              item-value="Nodala_ID"
              label="Nodaļa *"
              outlined
              dense
              class="mb-3"
              required
            ></v-select>
            
            <v-select
              v-model="newBook.zanra_id"
              :items="genreOptions"
              item-text="nosaukums"
              item-value="Zanra_ID"
              label="Žanrs *"
              outlined
              dense
              class="mb-3"
              required
            ></v-select>
            
            <v-text-field
              v-model="newBook.faila_pdf"
              label="PDF fails (ceļš)"
              outlined
              dense
              class="mb-3"
              placeholder="pdf/12345623.pdf"
            ></v-text-field>
            
            <v-text-field
              v-model="newBook.vaku_attels"
              label="Vāka attēls (ceļš)"
              outlined
              dense
              class="mb-3"
              placeholder="uploids/cover/12345623.jpg"
            ></v-text-field>
          </v-form>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="showAddBookForm = false">
            Atcelt
          </v-btn>
          <v-btn color="#003D3A" dark @click="addBook">
            Pievienot
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    
    <v-dialog v-model="showUsersList" max-width="800" scrollable>
      <v-card>
        <v-card-title class="headline" style="background-color: #003D3A; color: white;">
          Lietotāju saraksts
          <v-spacer></v-spacer>
          <v-btn icon dark @click="showUsersList = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-0">
          <v-list>
            <v-list-item
              v-for="user in usersList"
              :key="user.kodsID"
            >
              <v-list-item-avatar>
                <v-icon :color="user.status === 'aktivs' ? 'success' : 'error'">
                  {{ user.status === 'aktivs' ? 'mdi-check-circle' : 'mdi-close-circle' }}
                </v-icon>
              </v-list-item-avatar>
              
              <v-list-item-content>
                <v-list-item-title>{{ user.lietotaja_vards }}</v-list-item-title>
                <v-list-item-subtitle>{{ user.epasts }}</v-list-item-subtitle>
                <v-list-item-subtitle>Loma: {{ user.loma }}</v-list-item-subtitle>
              </v-list-item-content>
              
              <v-list-item-action>
                <v-btn
                  :color="user.status === 'aktivs' ? 'error' : 'success'"
                  small
                  @click="toggleUserStatus(user)"
                >
                  {{ user.status === 'aktivs' ? 'Bloķēt' : 'Aktivizēt' }}
                </v-btn>
              </v-list-item-action>
            </v-list-item>
          </v-list>
        </v-card-text>
      </v-card>
    </v-dialog>

    
    <v-dialog v-model="showStatistics" max-width="1200" scrollable>
      <v-card class="statistics-card">
        <v-card-title class="statistics-header">
          <div class="header-content">
            <div>
              <h2 class="statistics-title">Grāmatu statistika</h2>
              <p class="statistics-subtitle">Skatījumu un lejupielāžu analīze</p>
            </div>
            <v-spacer></v-spacer>
            <v-btn icon dark @click="showStatistics = false" class="close-btn">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        
        <v-card-text class="statistics-content">
          <!-- Statistikas kopsavilkums -->
          <v-row class="stats-summary mb-6">
            <v-col cols="12" sm="6" md="3">
              <v-card class="stat-card" flat>
                <v-card-text class="text-center py-8">
                  <v-icon size="48" color="#003D3A" class="mb-3">mdi-eye</v-icon>
                  <div class="stat-value">{{ totalViews }}</div>
                  <div class="stat-label">Kopējie skatījumi</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card class="stat-card" flat>
                <v-card-text class="text-center py-8">
                  <v-icon size="48" color="#2e7d32" class="mb-3">mdi-download</v-icon>
                  <div class="stat-value">{{ totalDownloads }}</div>
                  <div class="stat-label">Kopējās lejupielādes</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card class="stat-card" flat>
                <v-card-text class="text-center py-8">
                  <v-icon size="48" color="#f57c00" class="mb-3">mdi-book</v-icon>
                  <div class="stat-value">{{ allBooks.length }}</div>
                  <div class="stat-label">Kopējās grāmatas</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card class="stat-card" flat>
                <v-card-text class="text-center py-8">
                  <v-icon size="48" color="#c62828" class="mb-3">mdi-chart-line</v-icon>
                  <div class="stat-value">{{ averageViews }}</div>
                  <div class="stat-label">Vidējie skatījumi</div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- Grāmatu tabula -->
          <div class="table-wrapper">
            <v-simple-table class="statistics-table">
              <template v-slot:default>
                <thead>
                  <tr class="table-header">
                    <th class="text-left header-cell">Grāmata</th>
                    <th class="text-left header-cell">Autors</th>
                    <th class="text-center header-cell">Skatījumi</th>
                    <th class="text-center header-cell">Lejupielādes</th>
                    
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(book, index) in displayedBooks" :key="book.isbn" :class="['data-row', index % 2 === 0 ? 'even-row' : 'odd-row']">
                    <td class="cell-text book-cell">
                      <div class="book-info">
                        <div class="book-name">{{ book.title }}</div>
                      </div>
                    </td>
                    <td class="cell-text author-cell">{{ book.author }}</td>
                    <td class="cell-center">
                      <v-chip small color="#1565C0" text-color="white" class="stat-chip">
                        <v-icon left x-small>mdi-eye</v-icon>
                        {{ book.views || 0 }}
                      </v-chip>
                    </td>
                    <td class="cell-center">
                      <v-chip small color="#2E7D32" text-color="white" class="stat-chip">
                        <v-icon left x-small>mdi-download</v-icon>
                        {{ book.downloads || 0 }}
                      </v-chip>
                    </td>
                   
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

    
    <v-dialog v-model="deleteBookConfirmation.show" max-width="400">
      <v-card>
        <v-card-title class="headline">Dzēst grāmatu?</v-card-title>
        <v-card-text>
          Vai tiešām vēlaties dzēst grāmatu 
          <strong>"{{ deleteBookConfirmation.bookTitle }}"</strong> 
          no sistēmas?
          <p class="red--text mt-2">Šī darbība ir neatgriezeniska!</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="deleteBookConfirmation.show = false">
            Atcelt
          </v-btn>
          <v-btn color="#b71c1c" dark @click="deleteBook">
            Dzēst
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
import '../../css/admin-library.css'; 

export default {
  name: 'AdminLibraryPage',
  data() {
    return {
      activeCategory: 'all',
      searchQuery: '',
      showNodalaMenu: false,
      showZanriMenu: false,
      loading: true,
      error: false,
      errorMessage: '',
      allBooks: [],
      closeMenuTimer: null,
      closeZanriMenuTimer: null,
      searchTimeout: null,

      selectedNodala: null, 
      selectedGenre: null,   
      genres: [],
      
      user: null,
      authLoading: false,

      
      showAddBookForm: false,
      showUsersList: false,
      showStatistics: false,
      usersList: [],
      
      
      newBook: {
        isbn: '',
        nosaukums: '',
        autors: '',
        gads: '',
        lapu_skaits: '',
        apraksts: '',
        zanra_id: null,
        nodala_id: null,
        faila_pdf: '',
        vaku_attels: ''
      },

      stats: {
        totalViews: 0,
        totalDownloads: 0,
        totalBooks: 0,
        averageViews: '0.0'
      },
      
      
      deleteBookConfirmation: {
        show: false,
        bookIsbn: null,
        bookTitle: ''
      },
      
      notifications: {
        add: { show: false, message: '', type: 'success' }
      }
    };
  },
  computed: {
    totalViews() {
     return this.stats.totalViews;
    },

    totalDownloads() {
     return this.stats.totalDownloads;
    },

    averageViews() {
      return this.stats.averageViews;
    },

    displayedBooks() {
      let filtered = this.allBooks;
      
      if (this.searchQuery.trim()) {
        return filtered;
      }
      
      if (this.selectedNodala === 'academic') {
        filtered = filtered.filter(book => book.nodala_id === 1);
      } else if (this.selectedNodala === 'leisure') {
        filtered = filtered.filter(book => book.nodala_id === 2);
      }

      if (this.selectedGenre) {
        filtered = filtered.filter(book => book.zanra_id === this.selectedGenre);
      }
      
      return filtered;
    },

    availableGenres() {
      if (!this.selectedNodala) return [];
      
      const nodalaId = this.selectedNodala === 'academic' ? 1 : 2;
      
      return this.genres
        .filter(genre => genre.nodala === nodalaId)
        .map(genre => ({
          id: genre.id,
          name: genre.name,
          count: this.allBooks.filter(book => book.zanra_id === genre.id).length
        }));
    },

    selectedNodalaName() {
      if (this.selectedNodala === 'academic') return 'Akadēmiskās grāmatas';
      if (this.selectedNodala === 'leisure') return 'Grāmatas atpūtai';
      return '';
    },
    
    selectedGenreName() {
      if (!this.selectedGenre) return '';
      const genre = this.genres.find(g => g.id === this.selectedGenre);
      return genre ? genre.name : '';
    },
    
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
    console.log('📌 AdminLibraryPage mounted');
    console.log('Auth token:', this.authToken);
    await this.debugToken();
    
    await this.checkAuth();
    await this.debugUserToken();
    await this.fetchGenres();
    await this.fetchBooks();
    await this.loadUsersList();
    await this.loadStats();
  },

  methods: {

   
    async loadStats() {
      try {
        const response = await fetch('http://localhost:8000/api/admin/stats', {
          method: 'GET',
          headers: {
            'Authorization': 'Bearer ' + this.authToken,
            'Content-Type': 'application/json'
          }
        });

        const data = await response.json();
        if (data.success) {
          this.stats = data.data;
        }
      } catch (error) {
        console.error('Kļuda ieladejot statistiku:', error);
      }
    },


    calculateRatio(views, downloads) {
     if (!views || views === 0) return 0;
     return Math.round((downloads / views) * 100);
    },

    async debugToken() {
      try {
        const response = await fetch('http://localhost:8000/api/debug-token', {
          headers: {
            'Authorization': 'Bearer ' + this.authToken
          }
        });
        const data = await response.json();
        console.log('🔍 DEBUG token:', data);
      } catch (error) {
        console.error('Debug token error:', error);
      }
    },
    
    async debugUserToken() {
    try {
      const response = await fetch('http://localhost:8000/api/debug-user-from-token', {
        headers: {
          'Authorization': 'Bearer ' + this.authToken
        }
      });
      const data = await response.json();
      console.log('🔍 DEBUG token info:', data);
      return data;
    } catch (error) {
      console.error('Debug kļūda:', error);
    }
   },
    
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

    async checkAuth() {
      const token = this.authToken;
      
      if (!token) {
        this.$router.push('/login');
        return;
      }

      try {
        const response = await fetch('http://localhost:8000/api/check-auth', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ` + token
          }
        });
        
        const data = await response.json();
        
        if (data.authenticated && data.lietotajs) {
          this.user = data.lietotajs;
          
          
          if (this.user.loma !== 'admins' && this.user.loma !== 'admin') {
            this.$router.push('/library');
          }
        } else {
          this.$router.push('/login');
        }
        
      } catch (error) {
        console.error('Auth check error:', error);
        this.$router.push('/login');
      }
    },
    
    async logout() {
      const token = this.authToken;
      
      try {
        if(token) {
          await fetch('http://localhost:8000/api/izrakstīties', {
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

    async fetchGenres() {
      try {
        const response = await fetch('http://localhost:8000/api/genres');
        const data = await response.json();
        
        if (data.success && data.data) {
          this.genres = data.data.map(genre => ({
            id: genre.Zanra_ID,
            name: genre.nosaukums,
            nodala: genre.Nodala
          }));
          console.log('✅ Ielādēti žanri:', this.genres);
        }
      } catch (error) {
        console.error('❌ Kļūda ielādējot žanrus:', error);
      }
    },

    selectNodala(nodala) {
      this.selectedNodala = nodala;
      this.selectedGenre = null; 
      this.searchQuery = '';
      this.showNodalaMenu = false;
      this.activeCategory = nodala;
    },

    selectGenre(genreId) {
      this.selectedGenre = genreId;
      this.showZanriMenu = false;
      console.log('Izvēlēts žanrs ID:', genreId);
    },
    
    showAllBooks() {
      this.activeCategory = 'all';
      this.selectedNodala = null;
      this.selectedGenre = null;
      this.searchQuery = '';
    },
    
    startCloseMenuTimer() {
      clearTimeout(this.closeMenuTimer);
      this.closeMenuTimer = setTimeout(() => {
        this.showNodalaMenu = false;
      }, 300);
    },
    
    startCloseZanriMenuTimer() {
      clearTimeout(this.closeZanriMenuTimer);
      this.closeZanriMenuTimer = setTimeout(() => {
        this.showZanriMenu = false;
      }, 300);
    },
    
    async fetchBooks(searchQuery = '') {
      this.loading = true;
      this.error = false;
      
      try {
        let apiUrl = searchQuery 
          ? `http://localhost:8000/api/books/search/${encodeURIComponent(searchQuery)}`
          : 'http://localhost:8000/api/books';
        
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        if (data.success && data.data) {
          this.allBooks = data.data.map(book => ({
            isbn: book.isbn,
            title: book.nosaukums,
            author: book.autors,
            cover_image: book.vaku_attels,
            nodala_id: book.nodala_id || 1,
            zanra_id: book.zanra_id,
            views: book.views || 0,
            downloads: book.downloads || 0
          }));
          
          console.log(`✅ Ielādētas ${this.allBooks.length} grāmatas`);
        }
      } catch (error) {
        console.error('❌ Kļūda:', error);
        this.error = true;
        this.errorMessage = 'Neizdevās ielādēt grāmatas';
      } finally {
        this.loading = false;
      }
    },
    
    async performSearch() {
      const query = this.searchQuery.trim();
      if (!query) {
        await this.fetchBooks();
      } else {
        this.loading = true;
        await this.fetchBooks(query);
      }
    },
    
    handleSearchInput() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        if (this.searchQuery.trim().length >= 2 || this.searchQuery.trim() === '') {
          this.performSearch();
        }
      }, 500);
    },

    getBookCover(book) {
      if (book.cover_image && book.cover_image.trim() !== '') {
        const imagePath = book.cover_image;
        if (imagePath.startsWith('http')) {
          return imagePath;
        } else {
          const cleanPath = imagePath.replace(/^\/+/, '');
          return `http://localhost:8000/${cleanPath}`;
        }
      }
      return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&h=600&fit=crop';
    },

    viewBook(isbn) {
      window.location.href = `/book/${isbn}`;
    },

    
    async loadUsersList() {
      console.log('Mēģinu ielādēt lietotājus...');
      console.log('Auth token:', this.authToken);
      console.log('Lietotājs no this.user:', this.user);
        
      if (!this.authToken) {
        console.error('Nav auth token');
        return;
      }
    
      try {
        const response = await fetch('http://localhost:8000/api/admin/users', {
          method: 'GET',
          headers: {
            'Authorization': 'Bearer ' + this.authToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

      console.log('Response status:', response.status);
    
      // Pārbaudām vai atbilde ir JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Atbilde nav JSON:', text.substring(0, 200));
        throw new Error('Serveris atgrieza HTML, nevis JSON');
      }
    
      const data = await response.json();
      console.log('Saņemtie dati:', data);
      
      if (data.success) {
        this.usersList = data.data;
        console.log('Lietotāji ielādēti:', this.usersList);
      } else {
        console.error('Kļūda ielādējot lietotājus:', data.message);
      }
        } catch (error) {
          console.error('Kļūda ielādējot lietotājus:', error);
        }
      }
    
    },

    async toggleUserStatus(user) {
      try {
        const newStatus = user.status === 'aktivs' ? 'blokets' : 'aktivs';
        const response = await fetch(`http://localhost:8000/api/admin/users/${user.kodsID}/status`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken
          },
          body: JSON.stringify({ status: newStatus })
        });
        
        const data = await response.json();
        if (data.success) {
          user.status = newStatus;
          this.showNotification('add', `Lietotāja statuss mainīts uz ${newStatus}`, true);
        }
      } catch (error) {
        console.error('Kļūda mainot lietotāja statusu:', error);
      }
    },

    confirmDeleteBook(book) {
      this.deleteBookConfirmation = {
        show: true,
        bookIsbn: book.isbn,
        bookTitle: book.title
      };
    },

    async deleteBook() {
      try {
        const response = await fetch(`http://localhost:8000/api/admin/books/${this.deleteBookConfirmation.bookIsbn}`, {
          method: 'DELETE',
          headers: {
            'Authorization': 'Bearer ' + this.authToken
          }
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.allBooks = this.allBooks.filter(b => b.isbn !== this.deleteBookConfirmation.bookIsbn);
          this.showNotification('add', 'Grāmata veiksmīgi dzēsta', true);
          this.deleteBookConfirmation.show = false;
        } else {
          this.showNotification('add', 'Kļūda dzēšot grāmatu', false);
        }
      } catch (error) {
        console.error('Kļūda dzēšot grāmatu:', error);
        this.showNotification('add', 'Kļūda dzēšot grāmatu', false);
      }
    },

    async addBook() {
      try {
        const response = await fetch('http://localhost:8000/api/admin/books', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken
          },
          body: JSON.stringify(this.newBook)
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('add', 'Grāmata veiksmīgi pievienota', true);
          this.showAddBookForm = false;
          this.fetchBooks(); 
          
          
          this.newBook = {
            isbn: '',
            nosaukums: '',
            autors: '',
            gads: '',
            lapu_skaits: '',
            apraksts: '',
            zanra_id: null,
            nodala_id: null,
            faila_pdf: '',
            vaku_attels: ''
          };
        } else {
          this.showNotification('add', 'Kļūda pievienojot grāmatu', false);
        }
      } catch (error) {
        console.error('Kļūda pievienojot grāmatu:', error);
        this.showNotification('add', 'Kļūda pievienojot grāmatu', false);
      }
    }
  }

</script>

