<template>
  <v-container fluid class="pa-8">
    
    <div class="d-flex justify-space-between align-center mb-6">
      <div class="d-flex align-center">
        <v-btn icon color="#003D3A" @click="$router.push('/admin')" class="mr-3">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <h1 class="text-h4 font-weight-bold" style="color: #003D3A;">Analītika un Grafiki</h1>
      </div>
      
      <v-btn color="#003D3A" @click="fetchData" :loading="loading" rounded>
        <v-icon left>mdi-refresh</v-icon> Atjaunināt
      </v-btn>
    </div>

    
    <v-alert v-if="errorMsg" type="error" class="mb-6">{{ errorMsg }}</v-alert>

    
    <v-row v-if="!loading">
      
      <v-col cols="12" md="8">
        <v-card elevation="2" class="pa-4 rounded-xl h-100">
           <div class="d-flex justify-space-between align-center mb-4">
   
            <v-card-title class="text-subtitle-1 font-weight-bold pa-0">
              Lejupielādes (Pēdējās {{ selectedPeriod }} dienas)
            </v-card-title>
            
            
            <v-select
              v-model="selectedPeriod"
              :items="periodOptions"
              item-title="title"
              item-value="value"
              density="compact"
              variant="outlined"
              hide-details
              style="max-width: 200px;"
              @update:model-value="fetchData"
            ></v-select>
          </div>

          <apexchart 
            v-if="downloadSeries.length > 0 && downloadSeries[0].data.length > 0"
            type="area" 
            height="350" 
            :options="downloadOptions" 
            :series="downloadSeries"
          ></apexchart>
          
          <div v-else class="d-flex flex-column align-center justify-center" style="height: 350px;">
            <v-icon size="64" color="grey-lighten-1">mdi-download-off</v-icon>
            <p class="mt-4 text-grey">Nav datu par lejupielādēm šajā periodā.</p>
          </div>
        </v-card>
      </v-col>

      
      <v-col cols="12" md="4">
        <v-card elevation="2" class="pa-4 rounded-xl h-100">
          <v-card-title class="text-subtitle-1 font-weight-bold">Populārākie žanri</v-card-title>
          <apexchart 
            type="donut" 
            height="350" 
            :options="genreOptions" 
            :series="genreSeries"
          ></apexchart>
        </v-card>
      </v-col>

      
      <v-col cols="12">
        <v-card elevation="2" class="pa-4 rounded-xl mt-4">
          <v-card-title class="text-subtitle-1 font-weight-bold">Jaunu lietotāju reģistrācija</v-card-title>
          <apexchart 
            type="bar" 
            height="300" 
            :options="userOptions" 
            :series="userSeries"
          ></apexchart>
        </v-card>
      </v-col>
    </v-row>

    
    <div v-else class="text-center py-12">
      <v-progress-circular indeterminate color="#003D3A" size="64"></v-progress-circular>
      <p class="mt-4">Ielādē grafikus...</p>
    </div>
  </v-container>
</template>

<script>
import VueApexCharts from "vue3-apexcharts";

export default {
  name: "AdminStatistics",
  components: {
    apexchart: VueApexCharts,
  },
  data() {
    return {
      loading: true,
      errorMsg: null,
      downloadSeries: [],
      selectedPeriod: 30, 
      periodOptions: [
        { title: 'Pēdējās 7 dienas', value: 7 },
        { title: 'Pēdējās 14 dienas', value: 14 },
        { title: 'Pēdējās 30 dienas', value: 30 },
        { title: 'Pēdējās 90 dienas', value: 90 },
      ],
      downloadOptions: {
        chart: { id: "downloads-chart", toolbar: { show: false } },
        colors: ["#003D3A"],
        stroke: { curve: "smooth" },
        xaxis: { categories: [] },
        dataLabels: { enabled: false }
      },
      genreSeries: [],
      genreOptions: {
        labels: [],
        colors: ["#003D3A", "#1976d2", "#2e7d32", "#f57c00", "#c62828"],
        legend: { position: "bottom" }
      },
      userSeries: [],
      userOptions: {
        chart: { id: "users-chart", toolbar: { show: false } },
        colors: ["#1976d2"],
        xaxis: { categories: [] },
        plotOptions: { bar: { borderRadius: 4, columnWidth: "50%" } }
      }
    };
  },
  async mounted() {
    await this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      this.errorMsg = null;
      
      try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`/api/admin/charts-data?days=${this.selectedPeriod}`, {
          headers: { 
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json' 
          }
        });

        if (!response.ok) {
          throw new Error(`Servera kļūda: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
          
          this.downloadOptions.xaxis.categories = data.downloads.map(item => item.date);
          this.downloadSeries = [{ name: 'Lejupielādes', data: data.downloads.map(item => item.count) }];

          this.genreOptions.labels = data.genres.map(item => item.genre);
          this.genreSeries = data.genres.map(item => item.count);

          this.userOptions.xaxis.categories = data.users.map(item => item.month);
          this.userSeries = [{ name: 'Lietotāji', data: data.users.map(item => item.count) }];
        } else {
          this.errorMsg = "Neizdevās ielādēt datus.";
        }
      } catch (error) {
        console.error("Kļūda ielādējot statistikas datus:", error);
        this.errorMsg = "Kļūda savienojumā ar serveri: " + error.message;
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>