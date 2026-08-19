<template>
  <div class="leaderboard">
    <h1>🏆 Real-Time Leaderboard</h1>
    
    <!-- Notification Toast -->
    <Transition name="slide-down">
      <div v-if="notification" class="notification">
        {{ notification }}
      </div>
    </Transition>

    <!-- Player List -->
    <div class="players-container">
      <div v-if="loading" class="loading">Loading players...</div>
      <div v-else-if="players.length === 0" class="empty">No players yet</div>
      <TransitionGroup v-else name="list" tag="div" class="players-list">
        <div
          v-for="(player, index) in sortedPlayers"
          :key="player.id"
          class="player-card"
          :class="{ 'highlight': highlightedPlayerId === player.id }"
        >
          <div class="rank">{{ index + 1 }}</div>
          <div class="player-info">
            <div class="name">{{ player.name }}</div>
            <div class="score">{{ player.score }} pts</div>
          </div>
          <div class="medal" v-if="index < 3">
            {{ ['🥇', '🥈', '🥉'][index] }}
          </div>
        </div>
      </TransitionGroup>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { playerApi } from '../services/api'
import echo from '../plugins/echo'

const players = ref([])
const loading = ref(true)
const notification = ref('')
const highlightedPlayerId = ref(null)

const sortedPlayers = computed(() => {
  return [...players.value].sort((a, b) => {
    if (b.score !== a.score) return b.score - a.score
    return a.name.localeCompare(b.name)
  })
})

const fetchPlayers = async () => {
  try {
    loading.value = true
    const response = await playerApi.getAll()
    players.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch players:', error)
  } finally {
    loading.value = false
  }
}

const updatePlayerScore = (updatedPlayer) => {
  const index = players.value.findIndex(p => p.id === updatedPlayer.id)
  if (index !== -1) {
    players.value[index] = { ...players.value[index], ...updatedPlayer }
  }
}

const showNotification = (message) => {
  notification.value = message
  setTimeout(() => {
    notification.value = ''
  }, 3000)
}

const highlightPlayer = (playerId) => {
  highlightedPlayerId.value = playerId
  setTimeout(() => {
    highlightedPlayerId.value = null
  }, 2000)
}

onMounted(() => {
  fetchPlayers()

  // Listen for real-time score updates
  echo.channel('leaderboard')
    .listen('.score.updated', (data) => {
      console.log('Score updated:', data)
      
      updatePlayerScore(data.player)
      
      const points = data.points_added > 0 ? `+${data.points_added}` : data.points_added
      showNotification(`${data.player.name} ${points} points!`)
      highlightPlayer(data.player.id)
    })
})

onUnmounted(() => {
  echo.leaveChannel('leaderboard')
})
</script>

<style scoped>
.leaderboard {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

h1 {
  text-align: center;
  margin-bottom: 2rem;
  color: #2c3e50;
}

.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  background: #4caf50;
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  z-index: 1000;
  font-weight: 500;
}

.players-container {
  min-height: 400px;
}

.loading, .empty {
  text-align: center;
  padding: 3rem;
  color: #666;
  font-size: 1.1rem;
}

.players-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.player-card {
  display: flex;
  align-items: center;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.player-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.player-card.highlight {
  animation: pulse 0.5s ease-in-out;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.rank {
  font-size: 1.5rem;
  font-weight: bold;
  color: #667eea;
  min-width: 50px;
}

.player-card.highlight .rank {
  color: white;
}

.player-info {
  flex: 1;
}

.name {
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.score {
  font-size: 0.9rem;
  opacity: 0.8;
}

.medal {
  font-size: 2rem;
}

/* Animations */
@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.02); }
}

.slide-down-enter-active, .slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}

.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}

.list-move, .list-enter-active, .list-leave-active {
  transition: all 0.5s ease;
}

.list-enter-from, .list-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

.list-leave-active {
  position: absolute;
}
</style>