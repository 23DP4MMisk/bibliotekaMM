<template>
  <v-app>
  
    <v-app-bar 
      app 
      flat 
      height="80" 
      class="top-nav-bar"
      fixed
    >
      <v-container class="d-flex align-center justify-space-between px-8">
        
        <v-btn 
          @click="showAllBooks" 
          variant="text" 
          class="library-name-btn"
        >
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
          <div v-if="isLoggedIn && user" class="d-flex align-center">
           
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
  
          <v-btn 
            v-else
            color="#003D3A" 
            class="login-btn"
            @click="goToLogin"
            rounded
          >
            Pieslēgties
          </v-btn>
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
                      @click="selectCategory('academic')"
                      class="menu-item"
                    >
                      <v-list-item-title class="text-left">
                        Akademiskas grāmatas
                      </v-list-item-title>
                    </v-list-item>
                    <v-divider></v-divider>
                    <v-list-item 
                      @click="selectCategory('leisure')"
                      class="menu-item"
                    >
                      <v-list-item-title class="text-left">
                        Grāmatas atpūtai
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </div>
              </div>
              
              
              <v-btn 
                @click="showMyLibrary" 
                variant="text"
                class="category-btn ml-4"
              >
                Mana biblioteka
              </v-btn>
            </div>
          </v-col>
        </v-row>
        
       
        <v-row class="mb-6">
          <v-col cols="12">
            <h2 class="category-title text-center">
              <span v-if="activeCategory === 'all' && !searchQuery">Visas grāmatas</span>
              <span v-else-if="activeCategory === 'academic'">Akademiskas grāmatas</span>
              <span v-else-if="activeCategory === 'leisure'">Grāmatas atpūtai</span>
              <span v-else-if="searchQuery">Meklēšanas rezultāti: "{{ searchQuery }}"</span>
              <span v-else>Mana biblioteka</span>
            </h2>
          </v-col>
        </v-row>
        
       
        <div v-if="loading" class="text-center py-12">
          <v-progress-circular
            indeterminate
            color="#003D3A"
            size="64"
          ></v-progress-circular>
          <p class="mt-4">Ielādē grāmatas...</p>
        </div>
        
       
        <div v-else-if="error" class="text-center py-12">
          <div class="error-container">
            <v-icon size="100" color="#ff6b6b" class="mb-4">mdi-alert-circle-outline</v-icon>
            <h3 class="error-title mb-3">Radās kļūda</h3>
            <p class="error-message mb-4">{{ errorMessage }}</p>
            <v-btn @click="fetchBooks()" color="#003D3A" rounded>
              <v-icon left>mdi-refresh</v-icon>
              Mēģināt vēlreiz
            </v-btn>
          </div>
        </div>
        
       
        <div v-else>
        
          <div v-if="activeCategory === 'my-library'" class="text-center py-12">
            <v-icon size="80" color="#003D3A" class="mb-4">mdi-book-multiple</v-icon>
            <h3 class="mb-4">Jūsu personīgā bibliotēka</h3>
            <p class="mb-6">Pieslēdzieties, lai skatītu savas grāmatas</p>
            <v-btn color="#003D3A" @click="goToLogin">
              Pieslēgties
            </v-btn>
          </div>
          
         
          <div v-else-if="displayedBooks.length > 0">
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
              <v-btn @click="searchQuery = ''; fetchBooks();" v-if="searchQuery" color="grey" outlined rounded>
                Notīrīt meklēšanu
              </v-btn>
            </div>
          </div>
        </div>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import '../../css/library-pages.css'; 

export default {
  name: 'LibraryPage',
  data() {
    return {
      activeCategory: 'all',
      searchQuery: '',
      showNodalaMenu: false,
      loading: true,
      error: false,
      errorMessage: '',
      allBooks: [],
      closeMenuTimer: null,
      searchTimeout: null,
      
     
      isLoggedIn: false,
      user: null,
      authLoading: false
    };
  },
  computed: {
    displayedBooks() {
      let filtered = this.allBooks;
      
      if (this.searchQuery.trim()) {
        return filtered;
      }
      
      if (this.activeCategory === 'academic') {
        filtered = filtered.filter(book => book.nodala_id === 1);
      } else if (this.activeCategory === 'leisure') {
        filtered = filtered.filter(book => book.nodala_id === 2);
      }
      
      return filtered;
    },
    
  
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
   
    await this.fetchBooks();
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
        console.log('Auth check response:', data);
        
        if (data.authenticated && data.lietotajs) {
          this.isLoggedIn = true;
          this.user = data.lietotajs;
          localStorage.setItem('user', JSON.stringify(data.lietotajs));
          console.log('✅ Lietotājs autentificēts:', this.userName);
        } else {
          if (!localStorage.getItem('user')) {
            this.setGuest();
            console.log('❌ Lietotājs NAV autentificēts');
          }
          
        }
        
      } catch (error) {
        console.error('Auth check error:', error);
         if (!localStorage.getItem('user')) {
          this.setGuest();
        }
       
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
        console.log('Logout response:', data);
        
        if (data.success) {
          this.setGuest();
          localStorage.removeItem('user');
          alert('Jūs esat veiksmīgi izrakstījies');
          
         
          setTimeout(() => {
            this.$router.go(0);
          }, 1000);
        }
        
      } catch (error) {
        console.error('Logout error:', error);
        this.setGuest();
        this.$router.go(0);
      }
    },
    
    setGuest() {
      this.isLoggedIn = false;
      this.user = null;
    },
    
   
    
    async fetchBooks(searchQuery = '') {
      this.loading = true;
      this.error = false;
      this.errorMessage = '';
      
      try {
        let apiUrl;
        
        if (searchQuery) {
          apiUrl = `http://localhost:8000/api/books/search/${encodeURIComponent(searchQuery)}`;
        } else {
          apiUrl = 'http://localhost:8000/api/books';
        }
        
        console.log('📡 Sūtu pieprasījumu:', apiUrl);
        
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
          throw new Error(`HTTP kļūda: ${response.status} ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
          this.allBooks = data.data.map(book => ({
            isbn: book.isbn,
            title: book.nosaukums,
            author: book.autors,
            cover_image: book.vaku_attels,
            nodala_id: book.nodala_id || (book.category ? book.category.id : 1)
          }));
          
          console.log(`✅ Ielādētas ${this.allBooks.length} grāmatas no datubāzes`);
        } else {
          throw new Error(data.message || 'Neparezi dati no API');
        }
        
      } catch (error) {
        console.error('❌ Kļūda ielādējot grāmatas:', error.message);
        this.error = true;
        this.errorMessage = this.getErrorMessage(error);
      } finally {
        this.loading = false;
      }
    },
    
    getErrorMessage(error) {
      if (error.message.includes('404')) {
        return 'API nav atrasts. Lūdzu, pārbaudiet vai Laravel serveris darbojas (php artisan serve)';
      } else if (error.message.includes('Failed to fetch')) {
        return 'Nav savienojuma ar serveri. Pārbaudiet savienojumu.';
      }
      return error.message;
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
      
      const categoryPlaceholders = {
        1: [
          'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&h=600&fit=crop',
          'https://images.unsplash.com/photo-1541963463532-d68292c34b19?w=400&h=600&fit=crop',
          'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=400&h=600&fit=crop',
          'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400&h=600&fit=crop'
        ],
        2: [
          'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=400&h=600&fit=crop',
          'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=400&h=600&fit=crop',
          'https://images.unsplash.com/photo-1589998059171-988d887df646?w=400&h=600&fit=crop',
          'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=400&h=600&fit=crop'
        ]
      };
      
      const placeholders = categoryPlaceholders[book.nodala_id] || [
        'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=400&h=600&fit=crop'
      ];
      
      const isbnNum = parseInt(book.isbn) || 0;
      const index = isbnNum % placeholders.length;
      
      return placeholders[index];
    },
    
    handleSearchInput() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        if (this.searchQuery.trim().length >= 2 || this.searchQuery.trim() === '') {
          this.performSearch();
        }
      }, 500);
    },
    
    async performSearch() {
      const query = this.searchQuery.trim();
      
      if (!query) {
        this.activeCategory = 'all';
        await this.fetchBooks();
        return;
      }
      
      this.loading = true;
      await this.fetchBooks(query);
    },
    
    selectCategory(category) {
      this.activeCategory = category;
      this.searchQuery = '';
      this.showNodalaMenu = false;
      this.fetchBooks();
    },
    
    showAllBooks() {
      this.activeCategory = 'all';
      this.searchQuery = '';
      this.fetchBooks();
    },
    
    showMyLibrary() {
      
      this.activeCategory = 'my-library';
      this.searchQuery = '';
      this.showNodalaMenu = false;
      
    },
    
    startCloseMenuTimer() {
      clearTimeout(this.closeMenuTimer);
      this.closeMenuTimer = setTimeout(() => {
        this.showNodalaMenu = false;
      }, 300);
    },
    
    goToMyLibrary() {
      this.showMyLibrary();
    },
    
    goToLogin() {
      this.$router.push('/login');
    },
    
    viewBook(isbn) {
      window.location.href = `/book/${isbn}`;
    }
  }
}
</script>