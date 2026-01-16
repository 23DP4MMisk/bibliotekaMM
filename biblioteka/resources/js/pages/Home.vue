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
            <v-btn color="#003D3A" size="x-large" class="hero-btn" @click="scrollToSection('about')">
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
export default {
  name: 'HomePage',
  data() {
    return {
      isScrolled: false
    };
  },
  methods: {
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
    
    console.log('Attēlu ceļu pārbaude:');
    console.log('1. /images/academikbook.gif');
    console.log('2. /images/funnybook.gif');
    console.log('3. /images/bookshelf.jpg');
    console.log('Pilnais URL:', window.location.origin);
    
    // Faila esamības pārbaude
    const images = ['/images/academikbook.gif', '/images/funnybook.gif', '/images/bookshelf.jpg'];
    images.forEach(src => {
      const img = new Image();
      img.src = src;
      img.onload = () => console.log(`✓ Attēls augšupielādēts: ${src}`);
      img.onerror = () => console.log(`✗ Attēls nav atrasts: ${src}`);
    });
  },
  beforeUnmount() {
    // Apstrādātāja noņemšana, kad komponents ir iznīcināts
    window.removeEventListener('scroll', this.handleScroll);
  }
}
</script>

<style scoped>
/* Globals stils */
:deep(body) {
  font-family: 'Tenor Sans', sans-serif;
  background-color: #FDFBED;
  margin: 0;
  padding: 0;
}


.nav-bar {
  background-color: #FDFBED !important;
  box-shadow: none !important;
  position: fixed !important; 
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  width: 100%;
  transition: all 0.3s ease;
}


.nav-bar.scrolled {
  box-shadow: 0 2px 10px rgba(0, 61, 58, 0.1) !important;
}

.nav-btn {
  color: #003D3A !important;
  text-transform: none !important;
  font-size: 1.25rem !important;
  font-weight: 500 !important;
  letter-spacing: normal !important;
  transition: color 0.3s ease;
}

.nav-btn:hover {
  color: #002522 !important;
}

/* Sekcija 1: Hero - pievienojam atstarpi uz fiksēto navigāciju */
.hero-section {
  min-height: 100vh;
  position: relative;
  padding-top: 80px; 
}

.hero-top {
  height: 50vh;
  background-color: #FDFBED;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.hero-title {
  font-family: 'Tenor Sans', sans-serif;
  font-size: 5rem;
  font-weight: 400;
  color: #003D3A;
  line-height: 1.2;
}

.hero-bottom {
  height: 50vh;
  position: relative;
}

.hero-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 40px;
  position: relative;
}

.hero-btn {
  background-color: #003D3A !important;
  color: white !important;
  text-transform: none !important;
  font-size: 1.25rem !important;
  font-weight: 500 !important;
  padding: 15px 60px 20px  !important;
  margin: 20px !important;
  border-radius: 50px !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
  transition: transform 0.3s ease, box-shadow 0.3s ease !important;
  z-index: 10;
}

.hero-btn:hover {
  transform: translateY(-3px) !important;
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4) !important;
}

/* Sekcija 2: Nodaļas */
.nodala-section {
  background-color: #FDFBED;
  padding: 100px 0;
  min-height: 100vh;
  display: flex;
  align-items: center;
}

.section-title {
  font-family: 'Tenor Sans', sans-serif;
  font-size: 3rem;
  font-weight: 400;
  color: #003D3A;
}

.category-card {
  background-color: #FDFBED !important;
  border-radius: 20px !important;
  overflow: hidden;
  transition: transform 0.3s ease;
  border: 2px solid #003D3A !important;
}

.category-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 10px 30px rgba(0, 61, 58, 0.2) !important;
}

.gif-container {
  background-color: #003D3A;
  border-radius: 15px 15px 0 0;
  padding: 20px;
  height: 250px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.gif-display {
  width: 100%;
  height: 100%;
  border-radius: 10px;
  background-color: #002522; 
}

.category-title {
  font-family: 'Tenor Sans', sans-serif;
  font-size: 2rem;
  color: #003D3A;
  font-weight: 500;
}

.category-description {
  font-family: 'Tenor Sans', sans-serif;
  font-size: 1.1rem;
  color: #666;
  line-height: 1.6;
}

/* Sekcija 3: Par biblioteku */
.about-section {
  background-color: #FDFBED;
  padding: 100px 0;
  min-height: 100vh;
  display: flex;
  align-items: center;
}

.about-section .section-title {
  color: #003D3A;
}

.about-card {
  background-color: #FDFBED !important;
  border-radius: 20px !important;
  border: 2px solid #003D3A !important;
  max-width: 800px;
  margin: 0 auto;
}

.about-text {
  font-family: 'Tenor Sans', sans-serif;
  font-size: 1.25rem;
  color: #003D3A;
  line-height: 1.8;
}

/* Adaptacija */
@media (max-width: 960px) {
  .hero-title {
    font-size: 3rem;
  }
  
  .section-title {
    font-size: 2.5rem;
  }
  
  .gif-container {
    height: 200px;
  }
  
  .category-card {
    width: 350px !important;
  }
}

@media (max-width: 600px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .nav-btn {
    font-size: 1rem !important;
    margin: 0 4px !important;
  }
  
  .about-text {
    font-size: 1.1rem;
  }
  
  .hero-btn {
    padding: 16px 32px !important;
    font-size: 1.1rem !important;
  }
  
  .category-card {
    width: 100% !important;
    max-width: 320px;
  }
  
  .hero-section {
    padding-top: 80px; 
  }
}
</style>