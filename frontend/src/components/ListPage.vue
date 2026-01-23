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
          <th>Åtgärd</th>
        </tr>
      </thead>
      <tbody>
    <tr v-for="item in filteredItems" :key="item.id">
    <td data-label="Datum">{{ item.when || '-' }}</td>
    <td data-label="Aktivitet">{{ item.activity || '-' }}</td>
    <td data-label="Detaljer">{{ item.details || '-' }}</td>
    <td data-label="Borg-skala">
      <span v-if="borgText(item.borg_scale)">
        {{ borgText(item.borg_scale) }}
      </span>
      <span v-else>-</span>
    </td>
    <td data-label="Distans">{{ item.distance || '-' }}</td>
    <td data-label="Tid (min)">{{ item.duration || '-' }}</td>
    <td data-label="Åtgärd">
      <button class="delete-btn" @click="confirmDelete(item.id)">Ta bort</button>
    </td>
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
    async confirmDelete(id) {
      if (!confirm('Vill du verkligen ta bort denna träning?')) return;
      await this.deleteItem(id);
    },
    async deleteItem(id) {
      try {
        const res = await fetch(`http://localhost:8080/workouts/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error('Delete failed');
        this.items = this.items.filter(i => i.id !== id);
      } catch (err) {
        console.error('Failed to delete item', err);
        alert('Kunde inte ta bort träningen');
      }
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
  word-break: break-word;
  white-space: normal;
  max-width: 200px;
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
/* Delete button */
.delete-btn {
  background: #e11d48;
  color: #fff;
  padding: 6px 10px;
  font-size: 14px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
}
.delete-btn:hover { background: #be123c; }
@media (max-width: 700px) {
  .lista {
    padding: 5px;
  }
  .date-filter {
    flex-direction: column;
    gap: 8px;
  }
  table, thead, tbody, th, td, tr {
    display: block;
    width: 100%;
  }
  thead {
    display: none;
  }
  tr {
    margin-bottom: 16px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    padding: 8px 0;
  }
  td {
    border: none;
    position: relative;
    padding-left: 50%;
    min-height: 32px;
    max-width: none;
    word-break: break-word;
  }
  td:before {
    position: absolute;
    left: 8px;
    top: 12px;
    width: 45%;
    white-space: pre-wrap;
    font-weight: bold;
    color: #da7618;
    content: attr(data-label);
  }
}
</style>
