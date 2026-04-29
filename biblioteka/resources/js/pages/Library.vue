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
        
       
        <v-row class="mb-6">
          <v-col cols="12">
            <h2 class="category-title text-center">
              <span v-if="activeCategory === 'my-library' && isLoggedIn">Mana bibliotēka</span>
              <span v-else-if="activeCategory === 'all' && !searchQuery">Visas grāmatas</span>
              <span v-else-if = "selectedGenreName">{{ selectedGenreName }}</span>
              <span v-else-if="selectedNodalaName">{{ selectedNodalaName }}</span>
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
          <div v-if="activeCategory === 'my-library' && isLoggedIn">
            <!-- Statuss menu -->
            <div class="status-menu">
              <v-btn
                v-for="status in statusOptions"
                :key="status.value"
                :class="['status-btn', { 'active-status': selectedStatus === status.value }]"
                variant="text"
                @click="selectStatus(status.value)"
              >
                {{ status.label }}
                <span class="status-count">({{ getStatusCount(status.value) }})</span>
              </v-btn>
            </div>

            <div v-if="userBooks.length > 0" class="user-books-container">
              <v-row>
                <v-col cols="12" v-for="userBook in filteredUserBooks" :key="userBook.LietotajGramatas_ID">
                  <v-card class="user-book-card" elevation="0">
                    <v-row>
                      <v-col cols="12" sm="3" md="2">
                        <div class="user-book-cover">
                          <v-img
                            :src="getBookCover(userBook)"
                            :alt="userBook.nosaukums"
                            cover
                            height="150"
                          >
                           <template v-slot:placeholder>
                              <div class="d-flex align-center justify-center fill-height">
                                <v-icon color="#003D3A">mdi-book-open-variant</v-icon>
                              </div>
                            </template>
                          </v-img>
                        </div>
                      </v-col>
                      <v-col cols="12" sm="9" md="10">
                        <div class="user-book-info">
                          <h3 class="user-book-title">{{ userBook.nosaukums }}</h3>
                          <p class="user-book-author">{{ userBook.autors }}</p>
                          <p class="user-book-pdf-id" v-if="userBook.faila_pdf">
                            <v-icon small color="#003D3A">mdi-file-pdf-box</v-icon>
                            PDF ID: {{ userBook.faila_pdf.split('/').pop() }}
                          </p>
                          <p class="user-book-status">
                            Statuss: <span :class="'status-badge status-' + userBook.statuss">
                              {{ getStatusLabel(userBook.statuss) }}
                            </span>
                          </p>
                          <div class="user-book-actions">
                            <v-btn
                              color="#003D3A"
                              class="action-btn-small"
                              @click="downloadBook(userBook)"
                              rounded
                              depressed
                            >
                              <v-icon left small>mdi-download</v-icon>
                              Lejupielādēt
                            </v-btn>
                            <v-menu offset-y>
                              <template v-slot:activator="{ props }">
                                <v-btn
                                  color="#003D3A"
                                  class="action-btn-small"
                                  v-bind="props"
                                  rounded
                                  depressed
                                >
                                  <v-icon left small>mdi-pencil</v-icon>
                                  Izmainīt statusu
                                </v-btn>
                              </template>
                             <v-list>
                                <v-list-item
                                  v-for="status in statusOptions"
                                  :key="status.value"
                                  @click="updateBookStatus(userBook, status.value)"
                                >
                                  <v-list-item-title>{{ status.label }}</v-list-item-title>
                                </v-list-item>
                              </v-list>
                            </v-menu>

                           <v-btn
                             v-if="userBook.statuss === 'izlasiju'"
                             color="#003D3A"
                             class="action-btn-small review-btn"
                             @click="goToReviewPage(userBook)"
                             rounded
                             depressed
                             >
                             <v-icon left small>mdi-star</v-icon>
                             Rakstīt atsauksmi
                           </v-btn>  
                           <v-btn
                              color="#b71c1c"
                              class="action-btn-small delete-btn"
                              @click="deleteBook(userBook)"
                              rounded
                              depressed
                            >
                              <v-icon left small>mdi-delete</v-icon>
                              Dzēst
                            </v-btn>
                          </div>
                          <v-alert
                            v-if="notifications.download.show && notifications.download.bookId === userBook.LietotajGramatas_ID"
                            :type="notifications.download.type"
                            class="mt-2 notification-alert"
                            dense
                            outlined
                          >
                            {{ notifications.download.message }}
                          </v-alert>

                          <v-alert
                            v-if="notifications.status.show && notifications.status.bookId === userBook.LietotajGramatas_ID"
                            :type="notifications.status.type"
                            class="mt-2 notification-alert"
                            dense
                            outlined
                          >
                            {{ notifications.status.message }}
                          </v-alert>
                          
                          <v-alert
                            v-if="notifications.delete.show && notifications.delete.bookId === userBook.LietotajGramatas_ID"
                            :type="notifications.delete.type"
                            class="mt-2 notification-alert"
                            dense
                            outlined
                          >
                            {{ notifications.delete.message }}
                          </v-alert>
                        </div>
                      </v-col>
                    </v-row>
                  </v-card>
                </v-col>
              </v-row>
            </div>
            <div v-else class="text-center py-12">
              <v-icon size="80" color="#003D3A" class="mb-4">mdi-book-plus</v-icon>
              <h3 class="mb-4">Jūsu bibliotēka ir tukša</h3>
              <p class="mb-6">Pievienojiet grāmatas no galvenās lapas</p>
              <v-btn color="#003D3A" @click="showAllBooks">Skatīt grāmatas</v-btn>
            </div>
          </div>


          <div v-else-if="activeCategory === 'my-library'" class="text-center py-12">
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
          
        
          <div v-else-if="!loading && !booksLoading"  class="text-center py-12">
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

    <!-- Dialogs pret gramatas dzēšanai -->
    <v-dialog v-model="deleteConfirmation.show" max-width="400">
      <v-card>
        <v-card-title class="headline">Dzēst grāmatu?</v-card-title>
        <v-card-text>
          Vai tiešām vēlaties dzēst grāmatu 
          <strong>"{{ deleteConfirmation.bookTitle }}"</strong> 
          no savas bibliotēkas?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="deleteConfirmation.show = false">
            Atcelt
          </v-btn>
          <v-btn color="#b71c1c" dark @click="confirmDelete">
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

      selectedNodala: null, 
      selectedGenre: null,   
      genres: [],

      booksLoading: true,
      initialLoadComplete: false,
     
      isLoggedIn: false,
      user: null,
      authLoading: false,

      userBooks: [],
      selectedStatus: 'all',
      statusOptions: [
        { value: 'all', label: 'Visas' },
        { value: 'izlasiju', label: 'Izlāsīju' },
        { value: 'lasu', label: 'Lasu' },
        { value: 'vel nelasiju', label: 'Vēl nelasīju' }
      ],

      notifications: {
       download: { show: false, message: '', type: 'success', bookId: null },
       status: { show: false, message: '', type: 'success', bookId: null },
       delete: { show: false, message: '', type: 'success', bookId: null },
       add: { show: false, message: '', type: 'success', bookId: null }
      },

      deleteConfirmation: {
       show: false,
       bookId: null,
       bookTitle: ''
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

    filteredUserBooks() {
      if (this.selectedStatus === 'all') {
        return this.userBooks;
      }
      return this.userBooks.filter(book => book.statuss === this.selectedStatus);
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
    },
    
    authToken() {
     return localStorage.getItem('auth_token');
    }
    
  },

     

  async mounted() {

    ('📌 LibraryPage mounted');
    ('URL parametri:', this.$route.query);
    
    (' checkAdminAndRedirect...');
    await this.checkAdminAndRedirect();
    this.loadUserFromStorage();

    
  
   

    ('tab parametrs:', this.$route.query.tab);
    ('isLoggedIn:', this.isLoggedIn);
   
    
    if (this.$route.query.tab === 'my-library' && this.isLoggedIn) {
      this.activeCategory = 'my-library';
      
      await this.checkAuth(); 
      await this.fetchGenres();
      this.activeCategory = 'my-library';

    }  else {
     await this.checkAuth();
     await this.fetchGenres();
     await this.fetchBooks();
    }
  },
  methods: { 
      async checkAdminAndRedirect() {
      const token = localStorage.getItem('auth_token');
      const savedUser = localStorage.getItem('user');
      
      if (!token || !savedUser) {
        return; // Nav autorizēts, turpinām normāli
      }
      
      let isAdmin = false;
      let userData = null;
      
      try {
        // Pārbauda, vai lietotājs ir administrators
        userData = JSON.parse(savedUser);
        isAdmin = userData.loma === 'admins' || 
                  userData.is_admin === true || 
                  userData.role === 'administrator';
      } catch (e) {
        console.error('Error parsing user data:', e);
        return; // Ja nevar parsēt, turpinām bez pārbaudes
      }

      if (isAdmin) {
        ('👑 Atrasts administrators localStorage, pārbaudu ar serveri...');
        
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
            // Pārbauda, vai serveris arī atgriež administratoru
            const serverIsAdmin = data.lietotajs.loma === 'admins' || 
                                  data.lietotajs.is_admin === true || 
                                  data.lietotajs.role === 'administrator';
            
            if (serverIsAdmin) {
              ('🚫 Administrators mēģina piekļūt bibliotēkas lapai - novirzu uz admin paneli');
              
              // Pārbauda, vai nav īpašs parametrs, kas atļauj palikt (piemēram, piespiedu režīms)
              const forceStay = this.$route.query.force === 'true';
              
              if (!forceStay) {
                // Novirza uz administratora lapu
                this.$router.replace({ 
                  path: '/admin', 
                  query: { redirect: 'library', message: 'Jūs jau esat pieslēdzies kā administrators' }
                });
                return;
              }
            }
          }
        } catch (error) {
          console.error('Kļūda pārbaudot administratora statusu:', error);
          // Kļūdas gadījumā turpinām normāli
        }
      }
    },


    showNotification(type, bookId, message, isSuccess = true) {
      // Atjaunojam notifikaciju konkretai  gramatai
      this.notifications[type] = {
        show: true,
        message: message,
        type: isSuccess ? 'success' : 'error',
        bookId: bookId
      };
      
      // Automatiski paslēpt notifikaciju pēc 3 sekundem
      setTimeout(() => {
        if (this.notifications[type]?.bookId === bookId) {
          this.notifications[type].show = false;
        }
      }, 3000);
    },

    goToReviewPage(userBook) {
      ('📝 Dati priekš atsauksmi:', userBook);
  
      this.$router.push({
        name: 'RewievPage',
        params: { 
          isbn: userBook.gramatas_id || userBook.ISBN, 
        },
        query: { 
          title: encodeURIComponent(userBook.nosaukums || userBook.title || 'Grāmata'),
          author: encodeURIComponent(userBook.autors || userBook.author || ''),
          cover: userBook.vaku_attels || ''
        }
      });
    },

    loadUserFromStorage() {
      const savedUser = localStorage.getItem('user');
      if (savedUser) {
        try {
          const user = JSON.parse(savedUser);
          this.isLoggedIn = true;
          this.user = user;
          ('✅ Lietotājs ielādēts no localStorage:', user.lietotaja_vards);
        } catch (e) {
          console.error('Kļūda ielādējot lietotāju:', e);
        }
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
          ('✅ Ielādēti žanri:', this.genres);
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
      if (this.selectedGenre === genreId) {
       
        this.selectedGenre = null;
      } else {
        this.selectedGenre = genreId;
      }
    },
  
   
    async checkAuth() {
      if (this.authLoading) return;
      
      this.authLoading = true;
      ('🔐 Pārbaudu autentifikāciju...');
      
      const token = localStorage.getItem('auth_token');
      ('Parbaudes tokens:', token ? token.substring(0, 20) + '...' : 'нет');


      if (!token) {
        this.setGuest();
        this.authLoading = false;
        return;
      }

      try {
        ('Atsūtu pieprasijumu tokenam:', 'Bearer ' + token.substring(0, 20) + '...');
        const response = await fetch('/api/check-auth', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ` + token
          }
        });
        
        ('Atbildes statuss:', response.status);
        const data = await response.json();
        ('Auth check response:', data);
        
        if (data.authenticated && data.lietotajs) {
          this.isLoggedIn = true;
          this.user = data.lietotajs;
          localStorage.setItem('user', JSON.stringify(data.lietotajs));
          ('✅ Lietotājs autentificēts:', this.userName);
          
          await this.loadUserBooks();
          ('📚 Gramatas ir ieladeti pec autorizacijas:', this.userBooks.length);
        } else {
         this.setGuest();
         localStorage.removeItem('auth_token');
         localStorage.removeItem('user');
         ('❌ Lietotājs NAV autentificēts');
          
        }
        
      } catch (error) {
        console.error('Auth check error:', error);
        this.setGuest();
        
       
      } finally {
        this.authLoading = false;
      }
    },
    
    async logout() {
      ('🚪 Mēģinu izrakstīties...');

      const token = this.authToken;
      
      try {
       if(token) {
        const response = await fetch('/api/izrakstīties', {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          }
        });
       }
        } catch (error) {
         console.error('Logout error:', error);
        } finally {
         this.setGuest();
         localStorage.removeItem('auth_token');
         localStorage.removeItem('user');
         
         this.$router.push('/library');
        }
      },
        
        
      setGuest() {
      this.isLoggedIn = false;
      this.user = null;
    },

    async showMyLibrary() {
      this.activeCategory = 'my-library';
      this.selectedNodala = null;
      this.selectedGenre = null;
      this.searchQuery = '';
      this.showNodalaMenu = false;
      this.selectedStatus = 'all';
      
      if (this.isLoggedIn) {
        await this.loadUserBooks();
      }
    },

    async loadUserBooks() {
      if (!this.isLoggedIn) return;
      
      this.loading = true;
      const token = this.authToken;
     
      try {
        const response = await fetch('/api/user/books', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          }
        });

        if (response.status === 401) {
          this.setGuest();
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          return;
        }

        const data = await response.json();
        ('📚 Lietotāja grāmatas:', data);
        
        if (data.success && data.data) {
          this.userBooks = data.data;
        } else {
          this.userBooks = [];
        }
      } catch (error) {
        console.error('❌ Kļūda ielādējot lietotāja grāmatas:', error);
        this.userBooks = [];
      } finally {
        this.loading = false;
      }
    },

      selectStatus(status) {
      this.selectedStatus = status;
    },
    
    getStatusCount(status) {
      if (status === 'all') {
        return this.userBooks.length;
      }
      return this.userBooks.filter(book => book.statuss === status).length;
    },
    
    getStatusLabel(status) {
      const labels = {
        'lasu': 'Lasu',
        'izlasiju': 'Izlāsīju',
        'vel nelasiju': 'Vēl nelasīju'
      };
      return labels[status] || status;
    },
    async updateBookStatus(userBook, newStatus) {
     ('📤 Mainu grāmatas statusu:', userBook);
     ('Jauns statuss:', newStatus);
     ('Grāmatas ID bibliotēkā:', userBook.LietotajGramatas_ID);
  
     const token = this.authToken;
     ('Tokens:', token ? token.substring(0, 20) + '...' : 'nav');

      if (!token) {
       this.showNotification('status', userBook.LietotajGramatas_ID, 'Jūsu sesija ir beigusies. Lūdzu, pieslēdzieties vēlreiz.', false);
       this.goToLogin();
       return;
      }

      try {
        const requestBody = {
         book_id: userBook.LietotajGramatas_ID,
         status: newStatus
        };
    
        ('Atsūtu datus:', requestBody);
    
        const response = await fetch('/api/user/book/status', {
        method: 'PUT',
        headers: {
         'Content-Type': 'application/json',
         'Accept': 'application/json',
         'Authorization': 'Bearer ' + token
        },
         body: JSON.stringify(requestBody)
        });

        ('Statussa atbilde:', response.status);
    
        // kļudu apraksts
        const responseText = await response.text();
        ('Teksta atbilde:', responseText);
    
        // kļudu apraksts JSON
        let data;
        try {
         data = JSON.parse(responseText);
         ('Atbildes dati:', data);
        } catch (e) {
         console.error('Kļūda parsējot JSON:', e);
         this.showNotification('status', userBook.LietotajGramatas_ID, 'Servera atbilde nav JSON formātā', false);
         return;
        }

        if (response.status === 401) {
         ('❌ Nav avtorizets');
         this.showNotification('status', userBook.LietotajGramatas_ID, 'Jūsu sesija ir beigusies. Lūdzu, pieslēdzieties vēlreiz.', false);
         this.goToLogin();
         return;
        }

        if (response.status === 404) {
         ('❌ Ieraksts nav atrasta');
         this.showNotification('status', userBook.LietotajGramatas_ID, 'Grāmata nav atrasta jūsu bibliotēkā', false);
         return;
        }

        if (response.status === 422) {
         ('❌ Validācijas kļūda:', data.errors);
         alert('Validācijas kļūda: ' + JSON.stringify(data.errors));
         return;
        }

        if (response.status === 500) {
         ('❌ Servera kļūda 500');
         this.showNotification('status', userBook.LietotajGramatas_ID, 'Servera kļūda. Lūdzu, mēģiniet vēlāk.', false);
         return;
        }

        if (data.success) {
      
         userBook.statuss = newStatus;
         (`✅ Statuss veiksmīgi mainīts uz: ${newStatus}`);
      
      
         this.showNotification('status', userBook.LietotajGramatas_ID, 'Statuss veiksmīgi mainīts!', true);
        } else {
         ('❌ Kļūda no servera:', data.message);
         this.showNotification('status', userBook.LietotajGramatas_ID, data.message || 'Neizdevās mainīt statusu', false);
        }
    
      } catch (error) {
       console.error('❌ Kļūda fetch:', error);
       this.showNotification('status', userBook.LietotajGramatas_ID, 'Neizdevās mainīt statusu: ' + error.message, false);
      } 
    },

    
      async deleteBook(userBook) {
        // Paradam apstiprinajumu 
        this.deleteConfirmation = {
          show: true,
          bookId: userBook.LietotajGramatas_ID,
          bookTitle: userBook.nosaukums
        };
      },

      // Jauns metods priekš gramatas dzešanai
     async confirmDelete() {
        const bookId = this.deleteConfirmation.bookId;
        const token = this.authToken;
        
        this.deleteConfirmation.show = false;
        
        try {
          const response = await fetch(`/api/user/book/${bookId}`, {
            method: 'DELETE',
            headers: {
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          });

          const data = await response.json();

          if (data.success) {
            this.userBooks = this.userBooks.filter(b => b.LietotajGramatas_ID !== bookId);
            ('✅ Grāmata dzēsta');
            this.showNotification('delete', bookId, 'Grāmata veiksmīgi dzēsta!', true);
          } else {
            this.showNotification('delete', bookId, 'Kļūda dzēšot grāmatu', false);
          }
        } catch (error) {
          console.error('Kļūda dzēšot grāmatu:', error);
          this.showNotification('delete', bookId, 'Kļūda dzēšot grāmatu', false);
        }
      },

    async downloadBook(userBook) {
      if (userBook.faila_pdf) {

        const bookIsbn = userBook.gramatas_id || userBook.ISBN || userBook.isbn;
        
        try {
          const response = await fetch(`/api/admin/books/${bookIsbn}/download`, {
            method: 'POST',
            headers: {
              'Authorization': 'Bearer ' + this.authToken,
              'Content-Type': 'application/json'
            }
          });
          if (!response.ok) {
            console.error('Neizdavas reģistrēt lejupielādi');
          }
        } catch (error) {
          console.error('Kļūda reģistrējot lejupielādic:', error);
        }
        
        const bookTitle = userBook.nosaukums || userBook.title || 'gramata';
        const fileName = `${bookTitle}.pdf`;
        const fileResponse = await fetch(`/${userBook.faila_pdf}`);
        const blob = await fileResponse.blob();
        const url = window.URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        this.showNotification(
          'download', 
          userBook.LietotajGramatas_ID, 
          `Lejupielādē: "${bookTitle}"`, 
          true
        );
        this.showNotification('download', userBook.LietotajGramatas_ID, 'Lejupielāde sākta!', true);
      } else {
        this.showNotification('download', userBook.LietotajGramatas_ID, 'PDF fails nav pieejams', false);
      }
    },
    
    async fetchBooks(searchQuery = '') {
      this.booksLoading = true;
      this.loading = true;
      this.error = false;
      this.errorMessage = '';
      
      try {
        let apiUrl;
        
        if (searchQuery) {
          apiUrl = `/api/books/search/${encodeURIComponent(searchQuery)}`;
        } else {
          apiUrl = '/api/books';
        }
        
        ('📡 Sūtu pieprasījumu:', apiUrl);
        
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
            nodala_id: book.nodala_id || (book.category ? book.category.id : 1),
            zanra_id: book.zanra_id
          }));
          
          (`✅ Ielādētas ${this.allBooks.length} grāmatas no datubāzes`);
        } else {
          throw new Error(data.message || 'Neparezi dati no API');
        }
        
      } catch (error) {
        console.error('❌ Kļūda ielādējot grāmatas:', error.message);
        this.error = true;
        this.errorMessage = this.getErrorMessage(error);
      } finally {
        this.loading = false;
        this.booksLoading = false;
        this.initialLoadComplete = true;
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
          return `/${cleanPath}`;
        }
      }

      if (book?.vaku_attels && book.vaku_attels.trim() !== '') {
        ('✅ Izmanto vaku_attels:', book.vaku_attels);
        // Formatējam URL bez atsevišķas metodes
          
        if (book.vaku_attels.startsWith('http')) {
          return book.vaku_attels;
        } else {
          const cleanPath = book.vaku_attels.replace(/^\/+/, '');
          return `/${cleanPath}`;
          
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
      this.selectNodala(category);
    },
    
    async showAllBooks() {
      this.activeCategory = 'all';
      this.selectedNodala = null;
      this.selectedGenre = null;
      this.searchQuery = '';

      await this.fetchBooks();
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
      this.$router.push(`/book/${isbn}`);
    }
  }
}
</script>