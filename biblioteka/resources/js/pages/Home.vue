<template>
  <v-app>
    
    <v-app-bar 
      app 
      flat 
      height="80" 
      class="nav-bar"
      :class="{ 'scrolled': isScrolled }"
      fixed
    >
      <v-container class="d-flex align-center justify-center px-8">
        <v-btn @click="scrollToSection('hero')" variant="text" class="nav-btn">MyLibrary</v-btn>
        <v-btn @click="scrollToSection('nodalas')" variant="text" class="nav-btn ml-8">Nodaļas</v-btn>
        <v-btn @click="scrollToSection('about')" variant="text" class="nav-btn ml-8">Par biblioteku</v-btn>
      </v-container>
    </v-app-bar>

    <v-main>
      <!-- Sekcija 1: Hero -->
      <section id="hero" class="hero-section">
        
        <div class="hero-top">
          <h1 class="hero-title">MyLibrary</h1>
        </div>
        
       
        <div class="hero-bottom">
          <div 
            class="hero-image"
            :style="{ 
              backgroundImage: 'url(/images/bookshelf.jpg)',
              backgroundSize: 'cover',
              backgroundPosition: 'center',
              backgroundRepeat: 'no-repeat'
            }"
          >
            <v-btn color="#003D3A" size="x-large" class="hero-btn" @click="goToLibrary">
              Ieiet bibliotekā
            </v-btn>
          </div>
        </div>
      </section>

      <!-- Sekcija 2: Nodaļas -->
      <section id="nodalas" class="nodala-section">
        <v-container>
          <h2 class="section-title mb-12 text-center">Nodaļas MyLibrary</h2>
          <div class="d-flex justify-center flex-wrap" style="gap: 40px;">
            <!-- Karte 1 -->
            <v-card class="category-card" width="400">
              <div class="gif-container">
                <div 
                  class="gif-display"
                  :style="{ 
                    backgroundImage: 'url(/images/academikbook.gif)',
                    backgroundSize: 'contain',
                    backgroundPosition: 'center',
                    backgroundRepeat: 'no-repeat'
                  }"
                ></div>
              </div>
              <v-card-text class="text-center pt-6">
                <h3 class="category-title">Akademiskas grāmatas</h3>
                <p class="category-description mt-4">
                  Zinātniskā literatūra, mācību grāmatas un pētījumi studentiem un akadēmiķiem
                </p>
              </v-card-text>
            </v-card>

            <!-- Karte 2 -->
            <v-card class="category-card" width="400">
              <div class="gif-container">
                <div 
                  class="gif-display"
                  :style="{ 
                    backgroundImage: 'url(/images/funnybook.gif)',
                    backgroundSize: 'contain',
                    backgroundPosition: 'center',
                    backgroundRepeat: 'no-repeat'
                  }"
                ></div>
              </div>
              <v-card-text class="text-center pt-6">
                <h3 class="category-title">Grāmatas atpūtai</h3>
                <p class="category-description mt-4">
                  Romāni, fantastika, detektīvi un cita literatūra brīvā laika pavadīšanai
                </p>
              </v-card-text>
            </v-card>
          </div>
        </v-container>
      </section>

      <!-- Sekcija 3: Par biblioteku -->
      <section id="about" class="about-section">
        <v-container>
          <h2 class="section-title mb-8 text-center">Par biblioteku</h2>
          <v-card class="about-card">
            <v-card-text class="text-center pa-8">
              <p class="about-text mb-4">Laipni lūdzam tiešsaistes bibliotēkā MyLibrary!</p>
              <p class="about-text mb-4">
                Šajā bibliotēkā varat pievienot grāmatas bibliotēkai, rakstīt atsauksmes, 
                lai palīdzētu citiem lietotājiem izdarīt izvēli, un, pats galvenais, 
                jūs varēsiet lasīt grāmatas neatkarīgi no tā, vai jums ir internets vai nav, 
                jo tās var lejupielādēt PDF formātā.
              </p>
              <p class="about-text">Šim nolūkam jums būs jāreģistrējas mūsu bibliotēkā.</p>
            </v-card-text>
          </v-card>
        </v-container>
      </section>
    </v-main>
  </v-app>
</template>

<script>
import '../../css/home.css';
export default {
  name: 'HomePage',
  data() {
    return {
      isScrolled: false
    };
  },
  methods: {
    goToLibrary() {
      // novirzīt uz biblioteku lapu
      this.$router.push('/library');
    },
    scrollToSection(sectionId) {
      const element = document.getElementById(sectionId);
      if (element) {
        // Pievienojam atstarpi uz fiksēto navigāciju
        const yOffset = -80;
        const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
        window.scrollTo({ top: y, behavior: 'smooth' });
      }
    },
    handleScroll() {
      this.isScrolled = window.scrollY > 50;
    }
  },
  mounted() {
    // Ritināšanas apstrādātāja pievienošana
    window.addEventListener('scroll', this.handleScroll);
    
    
    
    // Faila esamības pārbaude
    const images = ['/images/academikbook.gif', '/images/funnybook.gif', '/images/bookshelf.jpg'];
    images.forEach(src => {
      const img = new Image();
      img.src = src;
      img.onload = () => (`✓ Attēls augšupielādēts: ${src}`);
      img.onerror = () => (`✗ Attēls nav atrasts: ${src}`);
    });
  },
  beforeUnmount() {
    // Apstrādātāja noņemšana, kad komponents ir iznīcināts
    window.removeEventListener('scroll', this.handleScroll);
  }
}
</script>