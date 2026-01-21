<script setup>
import { reactive } from 'vue'

const form = reactive({
  when: '',
  activity: '',
  details: '',
  borg_scale: 0,
  distance: 0,
  duration: 0,
})

function submitWorkout() {
  fetch('http://localhost:8080/workouts', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(form),
  })
    .then((res) => res.json())
    .then((data) => {
      console.log('Workout saved:', data)
      alert('Träning sparad!')
      form.when = ''
      form.activity = ''
      form.details = ''
      form.borg_scale = 0
      form.distance = 0
      form.duration = 0
    })
    .catch((err) => console.error(err))
}
</script>


<template>
  <div class="training-form">
    <h2>Tränings Formulär</h2>

    <form @submit.prevent="submitWorkout">
      <p>När aktiviteten är gjord</p>
      <input type="date" v-model="form.when" required />
      <hr />

      <p>Vad för aktivitet</p>
      <input type="text" v-model="form.activity" placeholder="Skriv aktivitet här" required />
      <hr />

      <p>Info om vad du gjorde</p>
      <input type="text" v-model="form.details" placeholder="Skriv info här" />
      <hr />

      <p>Hur jobbigt var aktiviteten (1-10)</p>
      <input type="number" v-model.number="form.borg_scale" min="0" max="10" required />
      <hr />

      <p>Hur långt/mycket var aktiviteten</p>
      <input type="text" v-model.number="form.distance" placeholder="200 meter / 20 reps" />
      <hr />

      <p>Hur många minuter gjorde du det</p>
      <input type="number" v-model.number="form.duration" placeholder="Minuter" required />
      <hr />

      <button type="submit">Spara träning</button>
    </form>
  </div>
</template>
<style scoped>
.training-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  background: #f0f0f0;
  border-radius: 8px;
}

h2 {
  color: #da7618;
}
input {
  width: 100%;
  padding: 10px;
  border: 2px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #da7618;
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
}
button:hover {
  background-color: rgb(173, 88, 7);
}
</style>
