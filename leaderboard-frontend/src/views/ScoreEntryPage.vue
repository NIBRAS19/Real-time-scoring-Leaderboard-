<template>
  <div class="score-entry-page">
    <div class="background-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>

    <div class="container">
      <div class="page-header">
        <div class="header-icon">✨</div>
        <h2>Update Player Scores</h2>
        <p>Select a player and adjust their score. Changes broadcast instantly!</p>
      </div>

      <div class="content-grid">
        <!-- Main Form Card -->
        <div class="score-entry-card">
          <div class="card-header">
            <h3>Score Manager</h3>
            <div class="live-indicator">
              <span class="pulse-dot"></span>
              <span>Live</span>
            </div>
          </div>

          <!-- Success/Error Message -->
          <transition name="fade-slide">
            <div v-if="message" class="message" :class="messageType">
              <span class="message-icon">{{ messageType === 'success' ? '✅' : '❌' }}</span>
              <span class="message-text">{{ message }}</span>
            </div>
          </transition>

          <!-- Score Update Form -->
          <form @submit.prevent="submitScore" class="score-form">
            <div class="form-group">
              <label for="player">
                <span class="label-icon">👤</span>
                <span>Select Player</span>
              </label>
              <div class="select-wrapper">
                <select 
                  id="player" 
                  v-model="selectedPlayerId" 
                  required
                  :disabled="submitting || loadingPlayers"
                >
                  <option value="">Choose a player...</option>
                  <option 
                    v-for="player in sortedPlayers" 
                    :key="player.id" 
                    :value="player.id"
                  >
                    {{ player.name }} - {{ player.score }} pts
                  </option>
                </select>
                <span class="select-arrow">▼</span>
              </div>
            </div>

            <div class="form-group">
              <label for="points">
                <span class="label-icon">🎯</span>
                <span>Points</span>
              </label>
              <div class="points-input-wrapper">
                <button 
                  type="button"
                  class="points-btn decrease"
                  @click="points -= 1"
                  :disabled="submitting"
                >
                  −
                </button>
                <input 
                  id="points"
                  v-model.number="points" 
                  type="number" 
                  placeholder="0"
                  required
                  :disabled="submitting"
                  class="points-input"
                >
                <button 
                  type="button"
                  class="points-btn increase"
                  @click="points += 1"
                  :disabled="submitting"
                >
                  +
                </button>
              </div>
              <small class="help-text">Use + for addition, − for subtraction</small>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
              <div class="quick-label">Quick Add</div>
              <div class="quick-buttons">
                <button 
                  v-for="amount in [5, 10, 25, 50, 100]" 
                  :key="amount"
                  type="button"
                  @click="setPoints(amount)"
                  class="quick-btn positive"
                  :disabled="submitting"
                >
                  +{{ amount }}
                </button>
              </div>
              <div class="quick-label">Quick Subtract</div>
              <div class="quick-buttons">
                <button 
                  v-for="amount in [5, 10, 25]" 
                  :key="amount"
                  type="button"
                  @click="setPoints(-amount)"
                  class="quick-btn negative"
                  :disabled="submitting"
                >
                  −{{ amount }}
                </button>
              </div>
            </div>

            <div class="form-actions">
              <button 
                type="submit" 
                class="btn-primary"
                :disabled="submitting || !selectedPlayerId || points === 0"
              >
                <span v-if="submitting" class="btn-content">
                  <span class="spinner-small"></span>
                  Updating...
                </span>
                <span v-else class="btn-content">
                  <span>🚀</span>
                  Update Score
                </span>
              </button>
              
              <button 
                type="button" 
                class="btn-secondary"
                @click="resetForm"
                :disabled="submitting"
              >
                <span>🔄</span>
                Reset
              </button>
            </div>
          </form>
        </div>

        <!-- Players Preview Card -->
        <div class="players-preview-card">
          <div class="card-header">
            <h3>Current Players</h3>
            <span class="player-count">{{ players.length }} total</span>
          </div>
          
          <div v-if="loadingPlayers" class="loading-state">
            <div class="spinner"></div>
            <p>Loading...</p>
          </div>
          
          <div v-else-if="players.length === 0" class="empty-state">
            <span class="empty-icon">🎯</span>
            <p>No players found</p>
          </div>
          
          <div v-else class="players-grid">
            <div 
              v-for="(player, index) in sortedPlayers" 
              :key="player.id"
              class="player-preview-card"
              :class="{ 
                selected: selectedPlayerId === player.id,
                'top-rank': index < 3
              }"
              @click="selectPlayer(player.id)"
            >
              <div class="player-rank">{{ index + 1 }}</div>
              <div class="player-details">
                <div class="player-name">{{ player.name }}</div>
                <div class="player-score">
                  <span class="score-value">{{ player.score }}</span>
                  <span class="score-label">pts</span>
                </div>
              </div>
              <div v-if="index < 3" class="rank-badge">
                {{ ['🥇', '🥈', '🥉'][index] }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { playerApi, scoreApi } from '../services/api'

const players = ref([])
const selectedPlayerId = ref('')
const points = ref(0)
const submitting = ref(false)
const loadingPlayers = ref(true)
const message = ref('')
const messageType = ref('success')

const sortedPlayers = computed(() => {
  return [...players.value].sort((a, b) => {
    if (b.score !== a.score) return b.score - a.score
    return a.name.localeCompare(b.name)
  })
})

const fetchPlayers = async () => {
  try {
    loadingPlayers.value = true
    const response = await playerApi.getAll()
    players.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch players:', error)
    showMessage('Failed to load players', 'error')
  } finally {
    loadingPlayers.value = false
  }
}

const submitScore = async () => {
  if (!selectedPlayerId.value || points.value === 0) return

  try {
    submitting.value = true
    message.value = ''

    await scoreApi.update(selectedPlayerId.value, points.value)

    const playerName = players.value.find(p => p.id === selectedPlayerId.value)?.name
    const pointsText = points.value > 0 ? `+${points.value}` : points.value
    
    showMessage(`${playerName} ${pointsText} points! Updated successfully`, 'success')
    
    points.value = 0
    await fetchPlayers()
  } catch (error) {
    showMessage(error.response?.data?.message || 'Failed to update score', 'error')
  } finally {
    submitting.value = false
  }
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 5000)
}

const resetForm = () => {
  selectedPlayerId.value = ''
  points.value = 0
  message.value = ''
}

const setPoints = (amount) => {
  points.value = amount
}

const selectPlayer = (playerId) => {
  selectedPlayerId.value = playerId
}

onMounted(() => {
  fetchPlayers()
})
</script>

<style scoped>
.score-entry-page {
  min-height: calc(100vh - 80px);
  padding: 3rem 1rem;
  position: relative;
  overflow: hidden;
}

.background-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
  z-index: 0;
}

.shape {
  position: absolute;
  border-radius: 50%;
  opacity: 0.05;
  animation: float-shapes 20s infinite ease-in-out;
}

.shape-1 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  top: -200px;
  right: -100px;
  animation-delay: 0s;
}

.shape-2 {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  bottom: -150px;
  left: -100px;
  animation-delay: 5s;
}

.shape-3 {
  width: 250px;
  height: 250px;
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  top: 50%;
  left: 50%;
  animation-delay: 10s;
}

@keyframes float-shapes {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  25% { transform: translate(30px, -30px) rotate(90deg); }
  50% { transform: translate(-20px, 20px) rotate(180deg); }
  75% { transform: translate(20px, 30px) rotate(270deg); }
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.header-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.page-header h2 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 0.75rem;
  font-weight: 800;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-header p {
  color: #666;
  font-size: 1.15rem;
  max-width: 600px;
  margin: 0 auto;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  align-items: start;
}

.score-entry-card, .players-preview-card {
  background: white;
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
  transition: all 0.3s;
}

.score-entry-card:hover, .players-preview-card:hover {
  box-shadow: 0 15px 50px rgba(0,0,0,0.12);
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.card-header h3 {
  font-size: 1.5rem;
  color: #2c3e50;
  font-weight: 700;
}

.live-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
}

.pulse-dot {
  width: 8px;
  height: 8px;
  background: #4ade80;
  border-radius: 50%;
  animation: pulse-animation 2s infinite;
}

@keyframes pulse-animation {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.5); opacity: 0.7; }
}

.player-count {
  padding: 0.5rem 1rem;
  background: #f0f0f0;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  color: #667eea;
}

.message {
  padding: 1.25rem 1.5rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  font-weight: 600;
  border: 2px solid;
}

.message.success {
  background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
  color: #155724;
  border-color: #c3e6cb;
}

.message.error {
  background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
  color: #721c24;
  border-color: #f5c6cb;
}

.message-icon {
  font-size: 1.5rem;
}

.score-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 700;
  color: #2c3e50;
  font-size: 1.05rem;
}

.label-icon {
  font-size: 1.5rem;
}

.select-wrapper {
  position: relative;
}

select {
  width: 100%;
  padding: 1rem 3rem 1rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 14px;
  font-size: 1rem;
  transition: all 0.3s;
  background: white;
  appearance: none;
  cursor: pointer;
  font-weight: 500;
}

select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.select-arrow {
  position: absolute;
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #667eea;
  font-size: 0.875rem;
}

.points-input-wrapper {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.points-btn {
  width: 50px;
  height: 50px;
  border: 2px solid #667eea;
  background: white;
  color: #667eea;
  border-radius: 12px;
  font-size: 1.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.points-btn:hover:not(:disabled) {
  background: #667eea;
  color: white;
  transform: scale(1.1);
}

.points-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.points-input {
  flex: 1;
  padding: 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 14px;
  font-size: 1.5rem;
  text-align: center;
  font-weight: 700;
  color: #667eea;
  transition: all 0.3s;
}

.points-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.help-text {
  font-size: 0.875rem;
  color: #666;
  font-weight: 400;
}

.quick-actions {
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 16px;
}

.quick-label {
  font-size: 0.875rem;
  font-weight: 700;
  color: #666;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.quick-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.quick-buttons:last-child {
  margin-bottom: 0;
}

.quick-btn {
  padding: 0.75rem 1.25rem;
  border: 2px solid;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 0.95rem;
}

.quick-btn.positive {
  background: white;
  border-color: #4ade80;
  color: #16a34a;
}

.quick-btn.positive:hover:not(:disabled) {
  background: #4ade80;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(74, 222, 128, 0.3);
}

.quick-btn.negative {
  background: white;
  border-color: #f87171;
  color: #dc2626;
}

.quick-btn.negative:hover:not(:disabled) {
  background: #f87171;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(248, 113, 113, 0.3);
}

.quick-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.form-actions {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-primary, .btn-secondary {
  padding: 1.25rem 2rem;
  border: none;
  border-radius: 14px;
  font-size: 1.05rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: #f5f5f5;
  color: #666;
}

.btn-secondary:hover:not(:disabled) {
  background: #e0e0e0;
  transform: translateY(-2px);
}

.btn-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.players-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-height: 600px;
  overflow-y: auto;
  padding-right: 0.5rem;
}

.players-grid::-webkit-scrollbar {
  width: 6px;
}

.players-grid::-webkit-scrollbar-track {
  background: #f0f0f0;
  border-radius: 10px;
}

.players-grid::-webkit-scrollbar-thumb {
  background: #667eea;
  border-radius: 10px;
}

.player-preview-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  border: 2px solid #e0e0e0;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s;
  background: white;
}

.player-preview-card:hover {
  border-color: #667eea;
  transform: translateX(4px);
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
}

.player-preview-card.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.player-preview-card.top-rank {
  border-color: #ffd700;
  background: linear-gradient(135deg, #fff5e6 0%, #ffe4b3 100%);
}

.player-preview-card.top-rank.selected {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.player-rank {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.1rem;
}

.player-preview-card.selected .player-rank {
  background: white;
  color: #667eea;
}

.player-details {
  flex: 1;
}

.player-name {
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
  color: #2c3e50;
}

.player-preview-card.selected .player-name {
  color: white;
}

.player-score {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
  color: #666;
}

.player-preview-card.selected .player-score {
  color: rgba(255, 255, 255, 0.9);
}

.score-value {
  font-weight: 700;
  font-size: 1.1rem;
  color: #667eea;
}

.player-preview-card.selected .score-value {
  color: white;
}

.score-label {
  font-size: 0.875rem;
}

.rank-badge {
  font-size: 2rem;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 3rem 2rem;
  color: #666;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

.empty-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .score-entry-page {
    padding: 2rem 1rem;
  }

  .page-header h2 {
    font-size: 2rem;
  }

  .score-entry-card, .players-preview-card {
    padding: 1.5rem;
  }

  .form-actions {
    grid-template-columns: 1fr;
  }

  .quick-buttons {
    gap: 0.5rem;
  }

  .quick-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }
}
</style>