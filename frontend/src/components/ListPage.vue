<template>
  <div class="lista">
    <h2>Lista från databasen</h2>
    <div class="date-filter">
      <label>
        Startdatum:
        <input type="date" v-model="startDate">
      </label>
      <label>
        Slutdatum:
        <input type="date" v-model="endDate">
      </label>
      <label>
        <button @click="generatePDF">Ladda ner som PDF</button>
      </label>
    </div>

    <table>
      <thead>
        <tr>
          <th>Datum</th>
          <th>Aktivitet</th>
          <th>Detaljer</th>
          <th>Borg-skala</th>
          <th>Distans</th>
          <th>Tid (min)</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="item in filteredItems" :key="item.id">
          <td>{{ item.when || '-' }}</td>
          <td>{{ item.activity || '-' }}</td>
          <td>{{ item.details || '-' }}</td>
          <td>
            <span v-if="borgText(item.borg_scale)">
              {{ borgText(item.borg_scale) }}
            </span>
            <span v-else>-</span>
          </td>
          <td>{{ item.distance || '-' }}</td>
          <td>{{ item.duration || '-' }}</td>
        </tr>
      </tbody>
    </table>

  </div>
</template>

<script>
import { jsPDF } from "jspdf";
import autoTable from "jspdf-autotable";
export default {
  data() {
    return {
      items: [],
      startDate: '',
      endDate: '',
      borgMap: {
        0: "0 - Rest",
        1: "1 - Really Easy",
        2: "2 - Easy",
        3: "3 - Moderate",
        4: "4 - Sort of Hard",
        5: "5 - Hard",
        6: "6 - Hard",
        7: "7 - Really Hard",
        8: "8 - Really Hard",
        9: "9 - Really, Really, Hard",
        10: "10 - Maximal Effort"
      }
    }
  },
  computed: {
    filteredItems() {
      return this.items.filter(item => {
        const itemDate = item.when;
        const afterStart = !this.startDate || itemDate >= this.startDate;
        const beforeEnd = !this.endDate || itemDate <= this.endDate;
        return afterStart && beforeEnd;
      });
    }
  },
  methods: {
    borgText(val) {
      return this.borgMap[val] || null;
    },
    generatePDF() {
  const doc = new jsPDF();
  const columns = [
    "Datum",
    "Aktivitet",
    "Detaljer",
    "Borg-skala",
    "Distans",
    "Tid (min)"
  ];
  const rows = this.filteredItems.map(item => [
    item.when || "-",
    item.activity || "-",
    item.details || "-",
    this.borgText(item.borg_scale) || "-",
    item.distance || "-",
    item.duration || "-"
  ]);
  doc.text("Lista från databasen", 14, 16);
  autoTable(doc, {
    head: [columns],
    body: rows,
    startY: 20
  });
  doc.save("lista.pdf");
},
  },
  mounted() {
    fetch('http://localhost:8080/workouts')
      .then(response => response.json())
      .then(data => {
        this.items = data;
      })
      .catch(error => {
        console.error('Fel vid hämtning:', error);
      });
  }
}
</script>

<style scoped>
.lista {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
  background: #f0f0f0;
  border-radius: 8px;
}
.date-filter {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
}
.date-filter label {
  font-weight: 500;
}
table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}
th, td {
  padding: 12px 8px;
  text-align: left;
  border-bottom: 1px solid #da7618;
}
th {
  background: #ffffff;
  font-weight: 600;
}
tr:last-child td {
  border-bottom: none;
}
button {
  background-color: #da7618;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  width: 100%;
  cursor: pointer;
  font-size: 16px;
  margin-top: 25px;
}
button:hover {
  background-color: rgb(173, 88, 7);
}
</style>
