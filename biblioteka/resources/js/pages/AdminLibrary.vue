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

    
    <v-dialog v-model="showStatistics" max-width="900" scrollable>
      <v-card>
        <v-card-title class="headline" style="background-color: #003D3A; color: white;">
          Grāmatu statistika
          <v-spacer></v-spacer>
          <v-btn icon dark @click="showStatistics = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text>
          <v-simple-table>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">Grāmata</th>
                  <th class="text-left">Autors</th>
                  <th class="text-center">Skatījumi</th>
                  <th class="text-center">Lejupielādes</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="book in allBooks" :key="book.isbn">
                  <td>{{ book.title }}</td>
                  <td>{{ book.author }}</td>
                  <td class="text-center">
                    <v-chip small color="primary">
                      <v-icon left x-small>mdi-eye</v-icon>
                      {{ book.views || 0 }}
                    </v-chip>
                  </td>
                  <td class="text-center">
                    <v-chip small color="success">
                      <v-icon left x-small>mdi-download</v-icon>
                      {{ book.downloads || 0 }}
                    </v-chip>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
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
import '../../css/library-pages.css'; 

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
    
    await this.checkAuth();
    await this.fetchGenres();
    await this.fetchBooks();
    await this.loadUsersList();
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
      try {
        const response = await fetch('http://localhost:8000/api/admin/users', {
          headers: {
            'Authorization': 'Bearer ' + this.authToken
          }
        });
        const data = await response.json();
        if (data.success) {
          this.usersList = data.data;
        }
      } catch (error) {
        console.error('Kļūda ielādējot lietotājus:', error);
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
}
</script>

<style scoped>



.library-name {
  font-size: 2.2rem;
  color: #003D3A;
  font-weight: 800;
  letter-spacing: 1px;
  cursor: pointer;
  text-transform: uppercase;
}

.search-container {
  flex: 1;
  max-width: 600px;
  margin: 0 40px;
}

.search-field {
  background-color: #f5f5f5;
  border-radius: 25px;
  border: 1px solid #ddd;
}

.search-field .v-input__slot {
  border-radius: 25px !important;
  padding-left: 20px !important;
}

.top-nav-bar {
  background-color: white !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  z-index: 1000;
  border-bottom: 1px solid rgba(0, 61, 58, 0.1);
}

.main-content {
  background-color: #fafafa;
  min-height: calc(100vh - 80px);
  padding-top: 20px;
}


.admin-btn {
  background-color: #003D3A !important;
  color: white !important;
  font-weight: 600;
  border-radius: 25px;
  min-width: 140px;
  height: 45px;
  padding: 0 20px !important;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 8px rgba(0, 61, 58, 0.3);
}

.admin-btn:hover {
  background-color: #002c29 !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 61, 58, 0.4);
}

.admin-text {
  font-size: 1rem;
  font-weight: 600;
  text-transform: uppercase;
  margin-right: 5px;
}


.category-btn,
.admin-link,
.nav-link-btn {
  font-size: 1.3rem;
  font-weight: 500;
  text-transform: none;
  letter-spacing: normal;
  color: #666 !important;
  padding: 8px 20px;
  height: auto;
  transition: all 0.3s ease;
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  text-decoration: none;
  display: inline-block;
  cursor: pointer;
}

.category-btn:hover,
.admin-link:hover,
.nav-link-btn:hover {
  color: #003D3A !important;
  background-color: rgba(0, 61, 58, 0.05) !important;
}

.category-btn::before,
.admin-link::before,
.nav-link-btn::before {
  display: none;
}


.nodala-dropdown {
  position: relative;
  display: inline-block;
}

.nodala-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 12px;
  z-index: 1000;
  min-width: 250px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  margin-top: 8px;
  overflow: hidden;
}

.menu-item {
  padding: 16px 24px;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 56px;
}

.menu-item:hover {
  background-color: rgba(0, 61, 58, 0.08);
  padding-left: 28px;
}


.zanri-dropdown {
  position: relative;
  display: inline-block;
}

.zanri-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 12px;
  z-index: 1000;
  min-width: 250px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  margin-top: 8px;
  overflow: hidden;
}

.genre-count {
  font-size: 0.9rem;
  color: #888;
  margin-left: 5px;
}


.category-title {
  color: #003D3A;
  font-size: 2.8rem;
  font-weight: 700;
  margin-bottom: 30px;
  letter-spacing: 0.5px;
}

.book-card {
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: transparent !important;
  border: none !important;
  position: relative;
}

.book-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.book-cover-wrapper {
  position: relative;
  width: 100%;
  padding-top: 150%;
  overflow: hidden;
  border-radius: 10px;
  background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
  margin-bottom: 20px;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
}

.book-cover-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.book-cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.book-card:hover .book-cover-image {
  transform: scale(1.08);
}

.book-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #003D3A;
  line-height: 1.4;
  margin: 0 0 10px 0;
  min-height: 3.5em;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-align: center;
}

.book-author {
  font-size: 1.1rem;
  color: #666;
  font-style: italic;
  margin-bottom: 20px;
  line-height: 1.4;
  min-height: 1.4em;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: center;
  font-weight: 500;
}


.view-btn {
  background: linear-gradient(135deg, #003D3A 0%, #005a52 100%) !important;
  color: white !important;
  border-radius: 25px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  font-size: 1.05rem;
  height: 48px;
  margin: 0 auto;
  display: block;
  max-width: 220px;
  box-shadow: 0 4px 12px rgba(0, 61, 58, 0.25);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.view-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 61, 58, 0.35);
}

.view-btn::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.view-btn:hover::after {
  width: 300px;
  height: 300px;
}


.delete-book-btn {
  background-color: #b71c1c !important;
  color: white !important;
  border-radius: 25px;
  font-weight: 600;
  text-transform: none;
  font-size: 1rem;
  height: 40px;
  margin-top: 8px;
  box-shadow: 0 4px 8px rgba(183, 28, 28, 0.3);
  transition: all 0.3s ease;
}

.delete-book-btn:hover {
  background-color: #d32f2f !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(183, 28, 28, 0.4);
}


.error-container, .no-books-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 40px;
  border-radius: 20px;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.error-title, .no-books-title {
  color: #003D3A;
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 15px;
}

.error-message, .no-books-message {
  color: #666;
  font-size: 1.2rem;
  line-height: 1.6;
  margin-bottom: 30px;
}


.v-dialog .v-card {
  border-radius: 12px;
}

.v-dialog .v-card__title {
  background-color: #003D3A;
  color: white;
  font-weight: 600;
  padding: 16px 24px;
}

.v-dialog .v-card__text {
  padding: 24px;
  font-size: 1.1rem;
}

.v-dialog .v-card__actions {
  padding: 16px 24px;
  border-top: 1px solid #eee;
}


.v-simple-table {
  border-radius: 8px;
  overflow: hidden;
}

.v-simple-table th {
  background-color: #f5f5f5;
  color: #003D3A;
  font-weight: 600;
  font-size: 1rem;
}

.v-simple-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #eee;
}


.notification-alert {
  margin-top: 10px !important;
  width: 100%;
  font-size: 0.9rem;
  border-radius: 4px;
}

.v-alert--success {
  background-color: #e8f5e8 !important;
  color: #2e7d32 !important;
  border-left: 4px solid #2e7d32 !important;
}

.v-alert--error {
  background-color: #ffebee !important;
  color: #c62828 !important;
  border-left: 4px solid #c62828 !important;
}


.v-list-item {
  min-height: 60px !important;
  border-bottom: 1px solid #f0f0f0;
}

.v-list-item:hover {
  background-color: #f9f9f9;
}

.v-list-item__avatar {
  margin-right: 16px;
}

.v-list-item__title {
  font-weight: 600;
  color: #333;
}

.v-list-item__subtitle {
  color: #666;
  font-size: 0.9rem;
}


@media (max-width: 960px) {
  .search-container {
    max-width: 400px;
    margin: 0 20px;
  }
  
  .library-name {
    font-size: 1.8rem;
  }
  
  .category-title {
    font-size: 2.2rem;
  }
  
  .error-title, .no-books-title {
    font-size: 1.8rem;
  }
  
  .book-title {
    font-size: 1.15rem;
  }
  
  .book-author {
    font-size: 1rem;
  }
}

@media (max-width: 768px) {
  .category-menu {
    flex-direction: column;
    gap: 15px;
    align-items: center;
  }
  
  .admin-link {
    margin-left: 0 !important;
  }
  
  .nodala-menu {
    left: 50%;
    transform: translateX(-50%);
  }
  
  .zanri-menu {
    left: 50%;
    transform: translateX(-50%);
  }
  
  .book-cover-wrapper {
    padding-top: 140%;
  }
}

@media (max-width: 600px) {
  .search-container {
    display: none;
  }
  
  .admin-btn {
    min-width: 100px;
    font-size: 0.9rem;
    height: 40px;
  }
  
  .library-name {
    font-size: 1.5rem;
  }
  
  .category-title {
    font-size: 1.8rem;
  }
  
  .book-title {
    font-size: 1.1rem;
  }
  
  .error-container, .no-books-container {
    padding: 30px 20px;
  }
  
  .error-title, .no-books-title {
    font-size: 1.6rem;
  }
  
  .view-btn, .delete-book-btn {
    max-width: 100%;
  }
}
</style>