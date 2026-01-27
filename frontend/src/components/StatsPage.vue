<script>
import VueApexCharts from 'vue3-apexcharts'

export default {
  name: 'StatsPage',
  components: { apexchart: VueApexCharts },

  data() {
    return {
      temperatures: {},
      loading: false,
      workoutsByDate: {},
      startDate: '',
      endDate: '',
      activities: [],
      selectedActivity: '',
      chartWidth: '100%',
      chartHeight: '60vh',
    }
  },

  computed: {
    series() {
      const dates = Object.keys(this.workoutsByDate)
      return [
        {
          name: 'Antal träningar',
          data: dates.map((d) => this.workoutsByDate[d].count),
          color: 'darkblue',
        },
        {
          name: 'Total tid (min)',
          data: dates.map((d) => this.workoutsByDate[d].totalDuration),
          color: 'green',
        },
        {
          name: 'Borg-skala',
          data: dates.map((d) => this.workoutsByDate[d].avgBorg),
          color: 'orange',
        },
      ]
    },

    chartOptions() {
      const formatShort = (dateStr) => {
        const d = new Date(dateStr)
        const dd = String(d.getDate()).padStart(2, '0')
        const mm = String(d.getMonth() + 1).padStart(2, '0')
        const yy = String(d.getFullYear()).slice(-2)
        return `${dd}/${mm}/${yy}`
      }

      return {
        title: {
          text: 'Träningsstatistik',
        },
        xaxis: {
          type: 'category',
          categories: Object.keys(this.workoutsByDate).map((d) => formatShort(d)),
        },
        yaxis: [
          {
            title: { text: 'Antal träningar' },
          },
          {
            opposite: true,
            title: { text: 'Total tid (min)' },
          },
          {
            opposite: true,
            title: { text: 'Borg-skala' },
            min: 0,
            max: 10,
          },
        ],
      }
    },
    allTime() {
      return !(this.startDate && this.endDate)
    },
  },

  async created() {
    // Hämta aktiviteter och initialt alla workouts
    await this.fetchActivities()
    await this.fetchWorkoutData()
    this.updateChartWidth()
    window.addEventListener('resize', this.updateChartWidth)
  },

  beforeUnmount() {
    window.removeEventListener('resize', this.updateChartWidth)
  },

  methods: {
    updateChartWidth() {
      this.chartWidth = '100%'
      const w = window.innerWidth
      if (w > 1200) {
        this.chartHeight = '75vh'
      } else if (w > 768) {
        this.chartHeight = '65vh'
      } else if (w > 420) {
        this.chartHeight = '55vh'
      } else {
        this.chartHeight = '72vh'
      }
    },

    async fetchWorkoutData() {
      let url = 'http://localhost:8080/workouts'
      const params = new URLSearchParams()
      if (!this.allTime && this.startDate && this.endDate) {
        params.set('start_date', this.startDate)
        params.set('end_date', this.endDate)
      }
      if (this.selectedActivity) {
        params.set('activity', this.selectedActivity)
      }
      const qs = params.toString()
      if (qs) url += `?${qs}`

      this.loading = true
      try {
        const res = await fetch(url)
        const data = await res.json()

        let days = []
        if (this.allTime) {
          // hämta och sortera enligt datum
          const set = new Set()
          data.forEach((w) => {
            const when = w.when || w.created_at || w.createdAt || w.date
            if (!when) return
            set.add(new Date(when).toISOString().slice(0, 10))
          })
          days = Array.from(set).sort()
        } else {
          // Start->slutdatum
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
    async fetchActivities() {
      try {
        const res = await fetch('http://localhost:8080/workouts/stats')
        const data = await res.json()
        this.activities = Array.isArray(data) ? data.map((r) => r.activity).filter(Boolean) : []
      } catch (err) {
        console.error('Failed to load activities', err)
      }
    },
  },
}
</script>

<template>
  <div class="stats-container">
    <h2>Träningsstatistik</h2>

    <div class="stats-content">
      <aside class="controls">
        <label>
          Start:
          <input type="date" v-model="startDate" />
        </label>
        <label>
          Slut:
          <input type="date" v-model="endDate" />
        </label>
        <label>
          Aktivitet:
          <select v-model="selectedActivity">
            <option value="">Alla aktiviteter</option>
            <option v-for="a in activities" :key="a" :value="a">{{ a }}</option>
          </select>
        </label>
        <button @click.prevent="fetchWorkoutData">Uppdatera</button>
      </aside>

      <main class="chart-area">
        <div v-if="loading" class="loading">Laddar träningsdata...</div>
        <div v-else class="chart-wrapper">
          <apexchart :width="chartWidth" type="bar" :options="chartOptions" :series="series" />
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>

  button {
  background-color: #da7618;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  width: 20%;
  cursor: pointer;
  font-size: 16px;
}

button:hover {
  background-color: rgb(173, 88, 7);
}

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

@media (max-width: 500px) {
  .stats-content {
    flex-direction: column;
  }

  .controls {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: stretch;
    width: 95%;
    margin-bottom: 1rem;
  }

  .controls label {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    width: 100%;
  }

  .controls input,
  .controls select,
  .controls button {
    width: 100%;
    box-sizing: border-box;
  }

  .controls button {
    padding: 0.6rem 0.75rem;
  }
}
</style>
 this works
