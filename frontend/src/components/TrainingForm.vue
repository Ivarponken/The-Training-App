<script setup>
import { reactive, ref } from 'vue'

const form = reactive({
  when: '',
  activity: '',
  details: '',
  borg_scale: 0,
  distance: 0,
  duration: 0,
  image_path: null
})

const image = ref(null)
const preview = ref(null)

function handleImage(e) {
  const file = e.target.files[0]
  if (!file) return

  image.value = file
  preview.value = URL.createObjectURL(file)
}

async function submitWorkout() {
  // Upload image first if exists
  if (image.value) {
    const formData = new FormData()
    formData.append('image', image.value)

    try {
      const res = await fetch('http://localhost:8080/upload_image', {
        method: 'POST',
        body: formData
      })

      const data = await res.json()
      if (data.success) {
        form.image_path = data.path
      }
    } catch (err) {
      console.error('Image upload failed:', err)
    }
  }

  // Save workout
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
      form.image_path = null
      image.value = null
      preview.value = null
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

      <p>Vad för aktivitet</p>
      <input type="text" v-model="form.activity" placeholder="Skriv aktivitet här" required />

      <p>Info om vad du gjorde</p>
      <input type="text" v-model="form.details" placeholder="Skriv info här" />

      <p>Hur jobbigt var aktiviteten (1-10)</p>
      <input type="number" v-model.number="form.borg_scale" min="0" max="10" required />

      <p>Hur långt/mycket var aktiviteten</p>
      <input type="text" v-model="form.distance" placeholder="200 meter / 20 reps" />

      <p>Hur många minuter gjorde du det</p>
      <input type="number" v-model.number="form.duration" placeholder="Minuter" min="0" required />

      <p>Bild (valfritt)</p>
      <input type="file" @change="handleImage" accept="image/*" />
      <img v-if="preview" :src="preview" style="max-width: 200px; margin-top: 10px; border-radius: 8px;" />


      <button type="submit">Spara träning</button>
    </form>
  </div>
</template>
<style scoped>
.training-form {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
  background: #f0f0f0;
  border-radius: 8px;
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
  margin-top: 20px;
}
button:hover {
  background-color: rgb(173, 88, 7);
}
</style>
