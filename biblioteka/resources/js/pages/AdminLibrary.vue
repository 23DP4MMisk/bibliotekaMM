<template>
  <v-app>
    
    <v-app-bar app flat height="130" class="top-nav-bar" fixed>
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
              
              
           
              <v-menu offset-y>
                <template v-slot:activator="{ props }">
                  <v-btn 
                    variant="text"
                    class="category-btn"
                    v-bind="props"
                  >
                    Nodaļas
                    <v-icon right>mdi-chevron-down</v-icon>
                  </v-btn>
                </template>

                <v-list class="py-0">
                  <v-list-item @click="selectNodala('academic')">
                    <v-list-item-title class="text-left">
                      Akademiskas grāmatas
                    </v-list-item-title>
                  </v-list-item>
                  <v-divider></v-divider>
                  <v-list-item @click="selectNodala('leisure')">
                    <v-list-item-title class="text-left">
                      Grāmatas atpūtai
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>

              
              <a 
                href="#" 
                @click.prevent="openAddBookForm" 
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
            <div class="genre-header">
              <div class="genre-header-left">
                <h3 class="genre-header-title">Žanri</h3>
              </div>
              <div class="genre-header-right">
                <v-btn
                  color="#003D3A"
                  small
                  class="add-genre-btn"
                  @click="openAddGenreForm"
                >
                  <v-icon left small>mdi-plus</v-icon>
                  Pievienot žanru
                </v-btn>
              </div>
            </div>
            <div class="genre-menu">
              <div
               v-for="genre in availableGenres" 
                :key="genre.id" 
                class="genre-item-wrapper"
              >
              <v-btn
                
                :key="genre.id"
                :class="['genre-btn', { 'active-genre': selectedGenre === genre.id }]"
                variant="text"
                @click="selectGenre(genre.id)"
              >
                {{ genre.name }}
                <span class="genre-count" v-if="genre.count">({{ genre.count }})</span>
              </v-btn>
              <div class="genre-control-buttons">
                  <v-btn
                    icon
                    x-small
                    color="primary"
                    class="ml-1"
                    @click.stop="openEditGenreForm(genre)"
                    title="Rediģet žanru"
                  >
                    <v-icon x-small>mdi-pencil</v-icon>
                  </v-btn>
                  <v-btn
                  icon
                  x-small
                  color="error"
                  class="ml-1"
                  @click.stop="deleteGenre(genre)"
                  title="Dzēst žanru"
                >
                  <v-icon x-small>mdi-delete</v-icon>
                </v-btn>
              </div>
              </div>
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
      <v-card class="addBook-card">
        <v-card-title class="addBook-header">
          <div class="addBook-content">
            <div>
              <h2 class="addBook-title">Pievienot jaunu grāmatu</h2>
              <p class="addBook-subtitle">Aizpildiet visus obligātos laukus</p>
            </div>
          
            <v-spacer></v-spacer>
            <v-btn icon dark @click="showAddBookForm = false" class="close-btn-addBook">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        
        <v-card-text class="pt-4">
          <v-form ref="addBookForm">
            <v-text-field
              v-model="newBook.ISBN"
              label="ISBN *"
              required
              outlined
              dense
              class="mb-3"
              :error-messages="validationErrors.ISBN"
              @input="validationErrors.ISBN = []"
            ></v-text-field>
            
            <v-text-field
              v-model="newBook.nosaukums"
              label="Nosaukums *"
              required
              outlined
              dense
              class="mb-3"
              :error-messages="validationErrors.nosaukums"
              @input="validationErrors.nosaukums = []"
            ></v-text-field>
            
            <v-text-field
              v-model="newBook.autors"
              label="Autors *"
              required
              outlined
              dense
              class="mb-3"
              :error-messages="validationErrors.autors"
              @input="validationErrors.autors = []"
            ></v-text-field>
            
            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model="newBook.gads"
                  label="Gads"
                  outlined
                  dense
                  :error-messages="validationErrors.gads"
                  @input="validationErrors.gads = []"
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="newBook.lapu_skaits"
                  label="Lapu skaits"
                  type="number"
                  outlined
                  dense
                  :error-messages="validationErrors.lapu_skaits"
                  @input="validationErrors.lapu_skaits = []"
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
              :error-messages="validationErrors.apraksts"
              @input="validationErrors.apraksts = []"
            ></v-textarea>
            
            <v-select
              v-model="newBook.Nodala_ID"
              :items="nodalaOptions"
              item-title="text"
              item-value="value"
              :return-object="false"
              label="Nodaļa *"
              outlined
              dense
              class="mb-3"
              required
              :error-messages="validationErrors.Nodala_ID"
              @update:model-value="validationErrors.Nodala_ID = []"
            ></v-select>
            
            <v-select
              v-model="newBook.Zanra_ID"
              :items="genreOptions"
              item-title="nosaukums"
              item-value="Zanra_ID"
              :return-object="false"
              label="Žanrs *"
              outlined
              dense
              class="mb-3"
              required
              :error-messages="validationErrors.Zanra_ID"
              @update:model-value="validationErrors.Zanra_ID = []"
            ></v-select>
            
            <v-text-field
              v-model="newBook.faila_pdf"
              label="PDF fails (ID)"
              outlined
              dense
              class="mb-3"
              :error-messages="validationErrors.faila_pdf"
              @input="validationErrors.faila_pdf = []"
              placeholder="12345623"
              hint="Ievadiet faila ID (piemēram, 12345623)"
            ></v-text-field>
            
            <v-text-field
              v-model="newBook.vaku_attels"
              label="Vāka attēls (ceļš)"
              outlined
              dense
              class="mb-3"
              :error-messages="validationErrors.vaku_attels"
              @input="validationErrors.vaku_attels = []"
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

    
    <v-dialog v-model="showUsersList" max-width="1000" scrollable>
      <v-card class="lietotaji-card">
        <v-card-title class="lietotaji-header">
          <div class="lietotaji-content">
            <div>
              <h2 class="lietotaji-title">Lietotāju saraksts</h2>
              <p class="lietotaji-subtitle">Reģistrētie lietotāji sistēmā</p>
            </div>
          
            <v-spacer></v-spacer>
            <v-btn icon dark @click="showUsersList = false" class="close-btn-lietotaji">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
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

    
    <v-dialog v-model="showStatistics" max-width="1200" >
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

    <v-dialog v-model="showAddGenreForm" max-width="500" persistent>
      <v-card>
        <v-card-title class="headline" style="background-color: #003D3A; color: white;">
          Pievienot jaunu žanru
          <v-spacer></v-spacer>
          <v-btn icon dark @click="showAddGenreForm = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pt-4">
          <v-form ref="addGenreForm">
            <v-text-field
              v-model="newGenre.nosaukums"
              label="Žanra nosaukums *"
              required
              outlined
              dense
              class="mb-3"
            ></v-text-field>
            <v-select
              v-model="newGenre.nodala_id"
              :items="nodalaOptions"
              item-title="text"
              item-value="value"
              :return-object="false"
              label="Nodaļa *"
              outlined
              dense
              class="mb-3"
              required
            ></v-select>
          </v-form>
        </v-card-text>

          <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="showAddGenreForm = false">
            Atcelt
          </v-btn>
          <v-btn color="#003D3A" dark @click="addGenre">
            Pievienot
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="showEditGenreForm" max-width="500" persistent>
      <v-card>
        <v-card-title class="headline" style="background-color: #003D3A; color: white;">
          Rediģēt žanru
          <v-spacer></v-spacer>
          <v-btn icon dark @click="showEditGenreForm = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-form ref="editGenreForm">
            <v-text-field
              v-model="editingGenre.nosaukums"
              label="Žanra nosaukums *"
              required
              outlined
              dense
              class="mb-3"
            ></v-text-field>
              <v-select
              v-model="editingGenre.nodala_id"
              :items="nodalaOptions"
              item-text="tips"
              item-value="Nodala_ID"
              label="Nodaļa"
              outlined
              dense
              class="mb-3"
              disabled
            ></v-select>
            <small class="text-grey">Nodaļu nevar mainīt, jo žanrs ir piesaistīts konkrētai nodaļai</small>
          </v-form>
        </v-card-text>
          <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="showEditGenreForm = false">
            Atcelt
          </v-btn>
          <v-btn color="#003D3A" dark @click="updateGenre">
            Saglabāt
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  

    <v-dialog v-model="deleteGenreConfirmation.show" max-width="500" persistent>
      <v-card>
        <v-card-title class="headline" style="background-color: #003D3A; color: white;">
          Žanra dzēšana
          <v-spacer></v-spacer>
          <v-btn icon dark @click="deleteGenreConfirmation.show = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pt-6 pb-2 text-center">
          <p class="text-lg">
            Vai tiešām vēlaties dzēst žanru 
            <strong>"{{ deleteGenreConfirmation.genre?.nosaukums || deleteGenreConfirmation.genre?.name || 'nezināms' }}"</strong>?
          </p>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="deleteGenreConfirmation.show = false">
            Atcelt
          </v-btn>
          <v-btn color="#003D3A" dark @click="confirmDeleteGenre">
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
     
      showZanriMenu: false,
      loading: true,
      error: false,
      errorMessage: '',
      allBooks: [],
     
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
        ISBN: '',
        nosaukums: '',
        autors: '',
        gads: '',
        lapu_skaits: '',
        apraksts: '',
        Zanra_ID: null,
        Nodala_ID: null,
        faila_pdf: '',
        vaku_attels: ''
      },

      validationErrors: {
        ISBN: [],
        nosaukums: [],
        autors: [],
        gads: [],
        lapu_skaits: [],
        apraksts: [],
        Zanra_ID: [],
        Nodala_ID: [],
        faila_pdf: [],
        vaku_attels: []
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

      showAddGenreForm: false,
      showEditGenreForm: false,
      editingGenre: null,
      newGenre: {
        nosaukums: '',
        nodala_id: null
      },
      
      notifications: {
        add: { show: false, message: '', type: 'success' }
      },

      deleteGenreConfirmation: {
        show: false,
        genre: null
      },

      cloudflareBaseUrl: 'https://pub-6f170bacdf6a417ca301be11f05629c4.r2.dev',
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
        { text: 'Akadēmiskā', value: 1 },
        { text: 'Atpūtas', value: 2 }
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
    ('📌 AdminLibraryPage mounted');
    ('Auth token:', this.authToken);
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
        const response = await fetch('/api/admin/stats', {
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
        const response = await fetch('/api/debug-token', {
          headers: {
            'Authorization': 'Bearer ' + this.authToken
          }
        });
        const data = await response.json();
        ('🔍 DEBUG token:', data);
      } catch (error) {
        console.error('Debug token error:', error);
      }
    },
    
    async debugUserToken() {
    try {
      const response = await fetch('/api/debug-user-from-token', {
        headers: {
          'Authorization': 'Bearer ' + this.authToken
        }
      });
      const data = await response.json();
      ('🔍 DEBUG token info:', data);
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
        const response = await fetch('/api/check-auth', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ` + token
          }
        });
        
        const data = await response.json();
        
        if (data.authenticated && data.lietotajs) {
          this.user = data.lietotajs;

          if (this.user.status !== 'aktivs') {
            ('Lietotajs ir bloķēts, izpildam logout');
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            this.$router.push('/login?blocked=true');
            return;
          }
          
          
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

    async fetchGenres() {
      try {
        const response = await fetch('/api/genres');
        const data = await response.json();

        ('📦 RAW DATA FROM SERVER:', data);


        
        if (data.success && data.data) {
          ('📚 First genre from server:', data.data[0]); 
          this.genres = data.data.map(genre => ({
            id: genre.Zanra_ID,
            Zanra_ID: genre.Zanra_ID,
            name: genre.nosaukums,
            nodala: genre.Nodala,
            count: genre.gramatu_skaits || 0
          }));
          ('✅ Ielādēti žanri:', this.genres);
          ('✅ First genre nodala:', this.genres[0]?.nodala);
        }
      } catch (error) {
        console.error('❌ Kļūda ielādējot žanrus:', error);
      }
    },

    

    async addGenre() {
      if (!this.newGenre.nosaukums.trim()) {
        this.showNotification('add', 'Lūdzu, ievadiet žanra nosaukumu!', false);
        return;
      }

      try {
        const response = await fetch('/api/admin/genres', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken
          },
          body: JSON.stringify({
            nosaukums: this.newGenre.nosaukums,
            Nodala: this.newGenre.nodala_id
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('add', 'Žanrs veiksmīgi pievienots!', true);
          this.showAddGenreForm = false;
          
          
          this.newGenre = {
            nosaukums: '',
            Nodala: this.selectedNodala === 'academic' ? 1 : 2
          };
          
          
          await this.fetchGenres();
          
        } else {
          this.showNotification('add', data.message || 'Kļūda pievienojot žanru', false);
        }
      } catch (error) {
        console.error('Kļūda pievienojot žanru:', error);
        this.showNotification('add', 'Kļūda pievienojot žanru', false);
      }
    },

    async updateGenre() {

      if (!this.editingGenre) {
        this.showNotification('add', 'Ошибка: жанр не выбран', false);
        return;
      }

      const genreId = this.editingGenre.Zanra_ID || this.editingGenre.id;
      if (!genreId) {
        console.error('updateGenre: genre ID is undefined', this.editingGenre);
        this.showNotification('add', 'Ошибка: ID жанра не найден', false);
        return;
      }

      const genreName = this.editingGenre.nosaukums || this.editingGenre.name;
      ('2. New genre name from form:', genreName);
      ('3. Original genre name in DB:', this.editingGenre.originalName || 'unknown');
      if (!genreName || !genreName.trim()) {
        this.showNotification('add', 'Lūdzu, ievadiet žanra nosaukumu!', false);
        return;
      }

      try {
        const response = await fetch(`/api/admin/genres/${genreId}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken
          },
          body: JSON.stringify({
            nosaukums: genreName,
            Nodala_id: this.selectedNodala === 'academic' ? 1 : 2
          })
        });
        
        const data = await response.json();
        ('Update genre response:', data);
        
        if (data.success) {
          this.showNotification('add', 'Žanrs veiksmīgi atjaunināts!', true);
          this.showEditGenreForm = false;
          
          
          await this.fetchGenres();

          const updatedGenre = this.genres.find(g => g.id === genreId);
          ('11. Genre after refresh:', updatedGenre);
          ('12. Name in DB after refresh:', updatedGenre?.name);
          
        } else {
          this.showNotification('add', data.message || 'Kļūda atjauninot žanru', false);
        }
      } catch (error) {
        console.error('Kļūda atjauninot žanru:', error);
        this.showNotification('add', 'Kļūda atjauninot žanru', false);
      }
    },

    async deleteGenre(genre) {
      ('deleteGenre called with:', genre);
  
      if (!genre) {
        console.error('deleteGenre: genre is undefined');
        this.showNotification('add', 'Kļūda: žanrs nav atrasts', false);
        return;
      }

    
      const genreId = genre.Zanra_ID || genre.id;
      if (!genreId) {
        console.error('deleteGenre: genre ID is undefined', genre);
        this.showNotification('add', 'Kļūda: žanra ID nav atrasts', false);
        return;
      }
      this.deleteGenreConfirmation.genre = genre;
      this.deleteGenreConfirmation.show = true;
    },

    async confirmDeleteGenre() {
      const genre = this.deleteGenreConfirmation.genre;
      if (!genre) return;

      const genreId = genre.Zanra_ID || genre.id;
      const genreName = genre.nosaukums || genre.name || `žanrs #${genreId}`;

      try {
        const response = await fetch(`/api/admin/genres/${genre.id}`, {
          method: 'DELETE',
          headers: {
            'Authorization': 'Bearer ' + this.authToken
          }
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('add', 'Žanrs veiksmīgi dzēsts!', true);
          
          if (this.selectedGenre === genre.id) {
            this.selectedGenre = null;
          }
          
          await this.fetchGenres();
          await this.fetchBooks();
        } else {
          this.showNotification('add', data.message || 'Kļūda dzēšot žanru', false);
        }
      } catch (error) {
        console.error('Kļūda dzēšot žanru:', error);
        this.showNotification('add', 'Kļūda dzēšot žanru', false);
      } finally {
        
        this.deleteGenreConfirmation.show = false;
        this.deleteGenreConfirmation.genre = null;
      }
    },
      
    

    openAddGenreForm() {
      this.newGenre = {
        nosaukums: '',
        nodala_id: null
      };
      this.showAddGenreForm = true;
    },

    openAddBookForm() {
      this.newBook = {
        ISBN: '',
        nosaukums: '',
        autors: '',
        gads: '',
        lapu_skaits: '',
        apraksts: '',
        Zanra_ID: null,
        Nodala_ID: null,
        faila_pdf: '',
        vaku_attels: ''
      };
      this.showAddBookForm = true;
    },

   openEditGenreForm(genre) {
      ('Opening edit form for genre:', genre);
      
      if (!genre) {
        console.error('Invalid genre object');
        this.showNotification('add', 'Kļūda: nederīgs žanra objekts', false);
        return;
      }
      
    
      const genreId = genre.Zanra_ID || genre.id;
      if (!genreId) {
        console.error('Genre has no ID:', genre);
        this.showNotification('add', 'Kļūda: žanra ID nav atrasts', false);
        return;
      }

      const originalName = genre.nosaukums || genre.name;
      const newName = genre.name || originalName; 
      
      ('Original name from DB:', originalName);
      ('Current name in form:', newName);
  
      this.editingGenre = { 
        Zanra_ID: genreId,
        id: genreId,
        name: newName,           
        nosaukums: originalName, 
        originalName: originalName, 
        nodala: genre.nodala
      };
      
   
      
      ('Editing genre prepared:', this.editingGenre);
      this.showEditGenreForm = true;
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
      ('Izvēlēts žanrs ID:', genreId);
    },
    
    async showAllBooks() {
      this.activeCategory = 'all';
      this.selectedNodala = null;
      this.selectedGenre = null;
      this.searchQuery = '';

      await this.fetchBooks();
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
          ? `/api/books/search/${encodeURIComponent(searchQuery)}`
          : '/api/books';
        
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
          
          (`✅ Ielādētas ${this.allBooks.length} grāmatas`);
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
          return `/${cleanPath}`;
        }
      }
      return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&h=600&fit=crop';
    },

    viewBook(isbn) {
      this.$router.push(`/admin/book/${isbn}`);
    },

    
    async loadUsersList() {
      ('Mēģinu ielādēt lietotājus...');
      ('Auth token:', this.authToken);
      ('Lietotājs no this.user:', this.user);
        
      if (!this.authToken) {
        console.error('Nav auth token');
        return;
      }
    
      try {
        const response = await fetch('/api/admin/users', {
          method: 'GET',
          headers: {
            'Authorization': 'Bearer ' + this.authToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

      ('Response status:', response.status);
    
      // Pārbaudām vai atbilde ir JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Atbilde nav JSON:', text.substring(0, 200));
        throw new Error('Serveris atgrieza HTML, nevis JSON');
      }
    
      const data = await response.json();
      ('Saņemtie dati:', data);
      
      if (data.success) {
        this.usersList = data.data;
        ('Lietotāji ielādēti:', this.usersList);
      } else {
        console.error('Kļūda ielādējot lietotājus:', data.message);
      }
        } catch (error) {
          console.error('Kļūda ielādējot lietotājus:', error);
        }
      },
    
    

    async toggleUserStatus(user) {
      try {
        const newStatus = user.status === 'aktivs' ? 'blokets' : 'aktivs';
        const response = await fetch(`/api/admin/users/${user.kodsID}/status`, {
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
        const response = await fetch(`/api/admin/books/${this.deleteBookConfirmation.bookIsbn}`, {
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
        } else if (response.status === 500 || response.status === 422) {
          
          console.error('❌ Server error on delete:', data);
          
          let errorMsg = data.message || 'Nezināma kļūda';
          if (errorMsg.includes('Kļūda:')) {
            errorMsg = errorMsg.replace('Kļūda: ', '');
          }
          this.showNotification('add', 'Kļūda dzēšot grāmatu: ' + errorMsg, false);
        } 
        else {
          this.showNotification('add', data.message || 'Kļūda dzēšot grāmatu', false);
        }
      } catch (error) {
        console.error('Kļūda dzēšot grāmatu:', error);
        this.showNotification('add', 'Servera kļūda (500)', false);
      }

    },

    async addBook() {

      this.clearValidationErrors();


      try {
        const response = await fetch('/api/admin/books', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.authToken
          },
          body: JSON.stringify({
            ...this.newBook,
            faila_pdf: this.newBook.faila_pdf ? `https://pub-6f170bacdf6a417ca301be11f05629c4.r2.dev/${this.newBook.faila_pdf}` : ''
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showNotification('add', 'Grāmata veiksmīgi pievienota', true);
          this.showAddBookForm = false;
          this.fetchBooks(); 
          
          
          this.newBook = {
            ISBN: '',
            nosaukums: '',
            autors: '',
            gads: '',
            lapu_skaits: '',
            apraksts: '',
            Zanra_ID: null,
            Nodala_ID: null,
            faila_pdf: '',
            vaku_attels: ''
          };
         } else if (response.status === 422) {

          this.handleValidationErrors(data.errors);
          this.showNotification('add', 'Lūdzu, izlabojiet atzīmētās kļūdas', false);
          
          console.error('❌ Validation errors:', data.errors || data);
          
          let errorText = 'Validācijas kļūda: ';
          if (data.errors) {
            errorText += Object.values(data.errors).flat().join(' | ');
          } else if (data.message) {
            errorText += data.message;
          }
          this.showNotification('add', errorText, false);
        } 
        else {
          this.showNotification('add', data.message || 'Kļūda pievienojot grāmatu', false);
        }
      } catch (error) {
        console.error('Kļūda pievienojot grāmatu:', error);
        this.showNotification('add', 'Servera kļūda', false);
      }
    },

    clearValidationErrors() {
      for (let field in this.validationErrors) {
        this.validationErrors[field] = [];
      }
    },


    handleValidationErrors(errors) {
      // Notīrīt vecās kļūdas
      this.clearValidationErrors();

      const fieldMapping = {
        'ISBN': 'ISBN',
        'nosaukums': 'nosaukums',
        'autors': 'autors',
        'gads': 'gads',
        'lapu_skaits': 'lapu_skaits',
        'apraksts': 'apraksts',
        'Zanra_ID': 'Zanra_ID',
        'Nodala_ID': 'Nodala_ID',
        'faila_pdf': 'faila_pdf',
        'vaku_attels': 'vaku_attels'
      };

      for (let [serverField, messages] of Object.entries(errors)) {
        const frontendField = fieldMapping[serverField] || serverField;
        if (this.validationErrors[frontendField]) {
          this.validationErrors[frontendField] = messages;
        }
      }
    }
  }
}
</script>

