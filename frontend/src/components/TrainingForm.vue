<script setup>

</script>

<template>
  <div class="training-form">
    <h2>Tränings Formulär</h2>
    <p>När aktiviteten är gjord</p>
<input type="date" id="when" required="true">
    <hr />
    <p>Vad för aktivitet</p>
    <input type="text" id="activity" placeholder="Skriv aktivitet här" required="true">
    <hr />
    <p>Info om vad du gjorde</p>
    <input type="text" id="details" placeholder="Skriv info här">
    <hr />
    <p>Hur jobbigt var aktiviteten</p>
    <input type="number" id="borg_scale" min="0" max="10" placeholder="Skala 1-10 1 = väldigt lätt 10 = väldigt jobbigt" required="true">
    <hr />
    <p>Hur långt/mycket var aktiviteten</p>
    <input type="text" id="distance" placeholder="200 meter / 20 reps">
    <hr />
    <p>Hur många timmar gjorde du det</p>
    <input type="time" id="duration" required="true">
    <hr />
    <br>
    <button type="submit">Spara träning</button>
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
  background-color: rgb(173, 88, 7)
}
</style>
