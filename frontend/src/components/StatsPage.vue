<script>
import VueApexCharts from 'vue3-apexcharts'

export default {
  name: 'StatsPage',
  components: { apexchart: VueApexCharts },

  data() {
    return {
      temperatures: {},
      loading: false,
      // keyed by YYYY-MM-DD -> { count, totalDuration, sumBorg, avgBorg }
      workoutsByDate: {},
      // range controls
      startDate: '',
      endDate: '',
      allTime: true,
      chartWidth: '100%', // Dynamisk bredd
    }
  },

  computed: {
    series() {
      // Order of series maps to yaxis entries in chartOptions
      const dates = Object.keys(this.workoutsByDate)
      return [
        {
          name: 'Total workouts',
          data: dates.map((d) => this.workoutsByDate[d].count),
          color: '#1f77b4',
        },
        {
          name: 'Total duration (min)',
          data: dates.map((d) => this.workoutsByDate[d].totalDuration),
          color: '#ff7f0e',
        },
        {
          name: 'Avg Borg',
          data: dates.map((d) => this.workoutsByDate[d].avgBorg),
          color: '#2ca02c',
        },
      ]
    },

    chartOptions() {
      const isMobile = window.innerWidth <= 768

      return {
        chart: {
          zoom: { enabled: false },
          toolbar: { show: !isMobile }, // Dölj toolbar på mobil
        },
        title: {
          text: 'Träningsstatistik (sista 10 dagarna)',
          style: {
            fontSize: isMobile ? '16px' : '18px',
          },
        },
        xaxis: {
          categories: Object.keys(this.workoutsByDate).map((date) => {
            const d = new Date(date)
            const day = d.getDate()
            const months = [
              'jan',
              'feb',
              'mar',
              'apr',
              'maj',
              'jun',
              'jul',
              'aug',
              'sep',
              'okt',
              'nov',
              'dec',
            ]
            const month = months[d.getMonth()]
            return `${day} ${month}`
          }),
          labels: {
            rotate: -45,
            rotateAlways: isMobile, // Rotera alltid på mobil
            style: {
              fontSize: isMobile ? '10px' : '12px',
            },
          },
        },
        yaxis: [
          {
            title: { text: 'Antal träningar', style: { fontSize: isMobile ? '12px' : '14px' } },
            labels: { style: { fontSize: isMobile ? '10px' : '12px' } },
          },
          {
            opposite: true,
            title: { text: 'Total tid (min)', style: { fontSize: isMobile ? '12px' : '14px' } },
            labels: { style: { fontSize: isMobile ? '10px' : '12px' } },
          },
          {
            opposite: true,
            title: { text: 'Genomsnittlig Borg', style: { fontSize: isMobile ? '12px' : '14px' } },
            labels: { style: { fontSize: isMobile ? '10px' : '12px' } },
            min: 0,
            max: 10,
          },
        ],
        legend: {
          fontSize: isMobile ? '12px' : '14px',
        },
        stroke: {
          width: isMobile ? 2 : 3, // Tunnare linjer på mobil
        },
      }
    },
  },

  async created() {
    // initial load: all time
    await this.fetchWorkoutData()
    this.updateChartWidth()
    window.addEventListener('resize', this.updateChartWidth)
  },

  beforeUnmount() {
    window.removeEventListener('resize', this.updateChartWidth)
  },

  methods: {
    updateChartWidth() {
      // Sätt bredd baserat på skärmstorlek
      if (window.innerWidth <= 768) {
        this.chartWidth = '100%'
      } else {
        this.chartWidth = '700'
      }
    },

    async fetchWorkoutData() {
      // Build URL with optional date range
      let url = 'http://localhost:8080/workouts'
      if (!this.allTime && this.startDate && this.endDate) {
        const params = new URLSearchParams({ start_date: this.startDate, end_date: this.endDate })
        url += `?${params.toString()}`
      }

      this.loading = true
      try {
        const res = await fetch(url)
        const data = await res.json()

        // Determine date keys in order
        let days = []
        if (this.allTime) {
          // collect unique dates present in data, sorted ascending
          const set = new Set()
          data.forEach((w) => {
            const when = w.when || w.created_at || w.createdAt || w.date
            if (!when) return
            set.add(new Date(when).toISOString().slice(0, 10))
          })
          days = Array.from(set).sort()
        } else {
          // build full range from startDate to endDate
          const s = new Date(this.startDate)
          const e = new Date(this.endDate)
          for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
            days.push(new Date(d).toISOString().slice(0, 10))
          }
        }

        const map = {}
        days.forEach((day) => {
          map[day] = { count: 0, totalDuration: 0, sumBorg: 0, avgBorg: 0 }
        })

        data.forEach((w) => {
          const when = w.when || w.created_at || w.createdAt || w.date
          if (!when) return
          const dateKey = new Date(when).toISOString().slice(0, 10)
          if (!map[dateKey]) return

          map[dateKey].count += 1
          map[dateKey].totalDuration += Number(w.duration) || 0
          map[dateKey].sumBorg += Number(w.borg_scale) || 0
        })

        Object.keys(map).forEach((k) => {
          const e = map[k]
          e.avgBorg = e.count > 0 ? +(e.sumBorg / e.count).toFixed(2) : 0
        })

        // maintain insertion order by creating a new object from days
        const ordered = {}
        days.forEach((d) => {
          ordered[d] = map[d]
        })
        this.workoutsByDate = ordered
      } catch (err) {
        console.error('Fetch error:', err)
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

<template>
  <div class="stats-container">
    <h2>Träningsstatistik</h2>
    <div class="controls">
      <label>
        Start:
        <input type="date" v-model="startDate" :disabled="allTime" />
      </label>
      <label>
        Slut:
        <input type="date" v-model="endDate" :disabled="allTime" />
      </label>
      <label>
        <input type="checkbox" v-model="allTime" /> All tid
      </label>
      <button @click.prevent="fetchWorkoutData">Uppdatera</button>
    </div>
    <div v-if="loading" class="loading">Laddar träningsdata...</div>
    <div v-else class="chart-wrapper">
      <apexchart :width="chartWidth" type="line" :options="chartOptions" :series="series" />
    </div>
  </div>
</template>

<style scoped>
.stats-container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.chart-wrapper {
  width: 100%;
  overflow-x: auto;
}

.controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  margin-bottom: 0.75rem;
}

.controls label {
  display: flex;
  gap: 0.25rem;
  align-items: center;
}

.loading {
  padding: 2rem;
  font-size: 1.2rem;
}

@media (max-width: 768px) {
  .temp-container {
    padding: 0 0.5rem;
  }

  h2 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
  }
}
</style>
