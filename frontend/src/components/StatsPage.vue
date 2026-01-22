<script setup>
import { reactive, ref, watch } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const apexcharts = VueApexCharts

const form = reactive({
  start_date: '',
  end_date: '',
})

const statsData = reactive([])

const chartLabels = ref([])
const chartSeries = ref([])

watch(
  statsData,
  () => {
    chartLabels.value = statsData.map(stat => stat.activity)

    chartSeries.value = [
      {
        name: 'Total Workouts',
        data: statsData.map(stat => Number(stat.total_workouts) || 0),
      },
      {
        name: 'Total Duration (min)',
        data: statsData.map(stat => Number(stat.total_duration) || 0),
      },
      {
        name: 'Avg Borg',
        data: statsData.map(stat => Number(stat.avg_borg) || 0),
      },
    ]
  },
  { deep: true }
)

function getStats() {
  const url = `http://localhost:8080/workouts/stats?start_date=${form.start_date}&end_date=${form.end_date}`

  fetch(url)
    .then((res) => res.json())
    .then((data) => {
      statsData.splice(0, statsData.length, ...data)
    })
    .catch((err) => console.error(err))
}

const chartOptions = reactive({
  chart: { type: 'line' },
  xaxis: { categories: chartLabels.value },
  title: { text: 'Statistik per workout:' },
  plotOptions: { bar: { horizontal: false } },
  dataLabels: { enabled: true },
})

</script>
<template>
  <div class="container">
    <h1>Träningsstatistik</h1>

    <div class="form-card">
      <form @submit.prevent="getStats">
        <div class="form-group">
          <label for="start-date">Startdatum</label>
          <input id="start-date" type="date" v-model="form.start_date" required />
        </div>

        <div class="form-group">
          <label for="end-date">Slutdatum</label>
          <input id="end-date" type="date" v-model="form.end_date" required />
        </div>

        <button type="submit" class="submit-btn">Hämta statistik</button>
      </form>
    </div>

    <div v-if="statsData.length" class="chart-wrapper">
      <apexcharts
        type="line"
        height="350"
        :options="{ ...chartOptions, xaxis: { categories: chartLabels } }"
        :series="[{ name: 'Total Workouts', data: chartSeries }]"
      />
    </div>
  </div>
</template>
