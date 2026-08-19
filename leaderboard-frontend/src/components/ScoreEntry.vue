<template>
  <div class="score-entry">
    <h2>Update Score</h2>
    
    <form @submit.prevent="submitScore">
      <div class="form-group">
        <label for="player">Select Player</label>
        <select 
          id="player" 
          v-model="selectedPlayerId" 
          required
        >
          <option value="">-- Choose a player --</option>
          <option 
            v-for="player in players" 
            :key="player.id" 
            :value="player.id"
          >
            {{ player.name }} ({{ player.score }} pts)
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="points">Points to Add/Subtract</label>
        <input 
          id="points"
          v-model.number="points" 
          type="number" 
          placeholder="Enter points (can be negative)"
          required
        >
      </div>

      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Updating...' : 'Update Score' }}
      </button>
    </form>

    <div v-if="message" class="message" :class="messageType">
      {{ message }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { playerApi, scoreApi } from '../services/api'

const players = ref([])
const selectedPlayerId = ref('')
const points = ref(0)
const submitting = ref(false)
const message = ref('')
const messageType = ref('success')

const fetchPlayers = async () => {
  try {
    const response = await playerApi.getAll()
    players.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch players:', error)
  }
}

const submitScore = async () => {
  if (!selectedPlayerId.value || points.value === 0) return

  try {
    submitting.value = true
    message.value = ''

    await scoreApi.update(selectedPlayerId.value, points.value)

    messageType.value = 'success'
    message.value = 'Score updated successfully!'
    
    points.value = 0
    fetchPlayers() // Refresh player list
  } catch (error) {
    messageType.value = 'error'
    message.value = error.response?.data?.message || 'Failed to update score'
  } finally {
    submitting.value = false
    setTimeout(() => {
      message.value = ''
    }, 3000)
  }
}

onMounted(() => {
  fetchPlayers()
})
</script>

<style scoped>
.score-entry {
  max-width: 500px;
  margin: 0 auto;
  padding: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

h2 {
  margin-bottom: 1.5rem;
  color: #2c3e50;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
}

select, input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

select:focus, input:focus {
  outline: none;
  border-color: #667eea;
}

button {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

button:hover:not(:disabled) {
  transform: translateY(-2px);
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.message {
  margin-top: 1rem;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  font-weight: 500;
}

.message.success {
  background: #d4edda;
  color: #155724;
}

.message.error {
  background: #f8d7da;
  color: #721c24;
}
</style>