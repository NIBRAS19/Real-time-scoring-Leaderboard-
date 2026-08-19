<template>
  <div class="leaderboard-page">
    <div class="background-gradient"></div>
    <div class="floating-shapes">
      <div class="floating-shape shape-1"></div>
      <div class="floating-shape shape-2"></div>
      <div class="floating-shape shape-3"></div>
    </div>

    <div class="container">
      <!-- Page Header -->
      <div class="page-header">
        <div class="trophy-animation">🏆</div>
        <h2>Live Leaderboard</h2>
        <p>Real-time rankings • Updated instantly</p>
        <div class="connection-badge" :class="{ connected: isConnected }">
          <span class="status-pulse"></span>
          <span class="status-text">{{ isConnected ? 'Live' : 'Connecting...' }}</span>
        </div>
      </div>

      <!-- Notification Toast -->
      <transition name="notification-slide">
        <div v-if="notification" class="notification-toast">
          <div class="notification-icon">🎉</div>
          <div class="notification-content">
            <div class="notification-title">Score Updated!</div>
            <div class="notification-message">{{ notification }}</div>
          </div>
        </div>
      </transition>

      <!-- Main Content -->
      <div class="content-wrapper">
        <!-- Leaderboard Card -->
        <div class="leaderboard-card">
          <div class="card-header">
            <h3>Rankings</h3>
            <div class="header-actions">
              <button @click="fetchPlayers" class="refresh-btn" :disabled="loading">
                <span class="refresh-icon" :class="{ spinning: loading }">🔄</span>
              </button>
            </div>
          </div>

          <div v-if="loading" class="loading-state">
            <div class="loading-spinner"></div>
            <p>Loading rankings...</p>
          </div>
          
          <div v-else-if="error" class="error-state">
            <span class="error-icon">⚠️</span>
            <p>{{ error }}</p>
            <button @click="fetchPlayers" class="retry-btn">
              <span>↻</span> Retry
            </button>
          </div>
          
          <div v-else-if="players.length === 0" class="empty-state">
            <span class="empty-icon">🎯</span>
            <h4>No Players Yet</h4>
            <p>Start adding players to see the leaderboard!</p>
            <router-link to="/score-entry" class="add-btn">
              <span>+</span> Add Players
            </router-link>
          </div>
          
          <transition-group v-else name="player-list" tag="div" class="players-list">
            <div
              v-for="(player, index) in sortedPlayers"
              :key="player.id"
              class="player-card"
              :class="{ 
                'highlight': highlightedPlayerId === player.id,
                'rank-1': index === 0,
                'rank-2': index === 1,
                'rank-3': index === 2
              }"
            >
              <!-- Rank Badge -->
              <div class="rank-section">
                <div v-if="index < 3" class="medal-badge">
                  {{ ['🥇', '🥈', '🥉'][index] }}
                </div>
                <div v-else class="rank-number">
                  <span class="rank-hash">#</span>{{ index + 1 }}
                </div>
              </div>

              <!-- Player Info -->
              <div class="player-section">
                <div class="player-avatar">
                  {{ player.name.charAt(0).toUpperCase() }}
                </div>
                <div class="player-details">
                  <div class="player-name">{{ player.name }}</div>
                  <div class="player-meta">
                    <span class="meta-item">
                      <span class="meta-icon">🎯</span>
                      {{ player.score }} points
                    </span>
                  </div>
                </div>
              </div>

              <!-- Score Display -->
              <div class="score-section">
                <div class="score-display">
                  <span class="score-number">{{ player.score }}</span>
                  <span class="score-suffix">pts</span>
                </div>
                <div v-if="index === 0" class="leader-badge">
                  👑 Leader
                </div>
              </div>

              <!-- Animated Background -->
              <div class="card-shimmer"></div>
            </div>
          </transition-group>
        </div>

        <!-- Statistics Sidebar -->
        <div class="stats-sidebar">
          <div class="stats-card">
            <div class="stats-header">
              <h3>Statistics</h3>
              <span class="stats-badge">Live</span>
            </div>
            
            <div class="stats-grid">
              <div class="stat-item">
                <div class="stat-icon-wrapper blue">
                  <span class="stat-icon">👥</span>
                </div>
                <div class="stat-details">
                  <div class="stat-value">{{ players.length }}</div>
                  <div class="stat-label">Total Players</div>
                </div>
              </div>

              <div class="stat-item">
                <div class="stat-icon-wrapper gold">
                  <span class="stat-icon">⭐</span>
                </div>
                <div class="stat-details">
                  <div class="stat-value">{{ highestScore }}</div>
                  <div class="stat-label">Highest Score</div>
                </div>
              </div>

              <div class="stat-item">
                <div class="stat-icon-wrapper green">
                  <span class="stat-icon">📊</span>
                </div>
                <div class="stat-details">
                  <div class="stat-value">{{ averageScore }}</div>
                  <div class="stat-label">Average</div>
                </div>
              </div>

              <div class="stat-item">
                <div class="stat-icon-wrapper purple">
                  <span class="stat-icon">🔥</span>
                </div>
                <div class="stat-details">
                  <div class="stat-value">{{ updateCount }}</div>
                  <div class="stat-label">Live Updates</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Performers -->
          <div class="top-performers-card" v-if="sortedPlayers.length >= 3">
            <h3>🏅 Top 3 Performers</h3>
            <div class="podium">
              <div class="podium-item second">
                <div class="podium-rank">🥈</div>
                <div class="podium-name">{{ sortedPlayers[1]?.name }}</div>
                <div class="podium-score">{{ sortedPlayers[1]?.score }} pts</div>
                <div class="podium-bar" style="height: 60%"></div>
              </div>
              <div class="podium-item first">
                <div class="podium-rank">🥇</div>
                <div class="podium-name">{{ sortedPlayers[0]?.name }}</div>
                <div class="podium-score">{{ sortedPlayers[0]?.score }} pts</div>
                <div class="podium-bar" style="height: 100%"></div>
              </div>
              <div class="podium-item third">
                <div class="podium-rank">🥉</div>
                <div class="podium-name">{{ sortedPlayers[2]?.name }}</div>
                <div class="podium-score">{{ sortedPlayers[2]?.score }} pts</div>
                <div class="podium-bar" style="height: 40%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { playerApi } from '../services/api'
import echo from '../plugins/echo'

const players = ref([])
const loading = ref(true)
const error = ref(null)
const notification = ref('')
const highlightedPlayerId = ref(null)
const isConnected = ref(false)
const updateCount = ref(0)

const sortedPlayers = computed(() => {
  return [...players.value].sort((a, b) => {
    if (b.score !== a.score) return b.score - a.score
    return a.name.localeCompare(b.name)
  })
})

const highestScore = computed(() => {
  if (players.value.length === 0) return 0
  return Math.max(...players.value.map(p => p.score))
})

const averageScore = computed(() => {
  if (players.value.length === 0) return 0
  const total = players.value.reduce((sum, p) => sum + p.score, 0)
  return Math.round(total / players.value.length)
})

const fetchPlayers = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await playerApi.getAll()
    players.value = response.data.data
    console.log('✅ Leaderboard loaded:', players.value.length, 'players')
  } catch (err) {
    console.error('❌ Failed to fetch leaderboard:', err)
    error.value = 'Failed to load leaderboard'
  } finally {
    loading.value = false
  }
}

const updatePlayerScore = (updatedPlayer) => {
  const index = players.value.findIndex(p => p.id === updatedPlayer.id)
  if (index !== -1) {
    players.value[index] = { ...players.value[index], ...updatedPlayer }
  } else {
    players.value.push(updatedPlayer)
  }
}

const showNotification = (message) => {
  notification.value = message
  setTimeout(() => {
    notification.value = ''
  }, 4000)
}

const highlightPlayer = (playerId) => {
  highlightedPlayerId.value = playerId
  setTimeout(() => {
    highlightedPlayerId.value = null
  }, 2500)
}

onMounted(() => {
  fetchPlayers()

  echo.connector.pusher.connection.bind('connected', () => {
    isConnected.value = true
  })

  echo.connector.pusher.connection.bind('disconnected', () => {
    isConnected.value = false
  })

  const channel = echo.channel('leaderboard')
  
  channel.listen('.score.updated', (data) => {
    console.log('🎉 Score update:', data)
    
    updateCount.value++
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
.leaderboard-page {
  min-height: calc(100vh - 80px);
  padding: 3rem 1rem;
  position: relative;
  overflow: hidden;
}

.background-gradient {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea15 0%, #764ba215 50%, #f093fb15 100%);
  z-index: 0;
}

.floating-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
  z-index: 0;
}

.floating-shape {
  position: absolute;
  border-radius: 50%;
  opacity: 0.08;
}

.shape-1 {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  top: 10%;
  right: 10%;
  animation: float-1 15s infinite ease-in-out;
}

.shape-2 {
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg, #f093fb, #f5576c);
  bottom: 20%;
  left: 15%;
  animation: float-2 20s infinite ease-in-out;
}

.shape-3 {
  width: 150px;
  height: 150px;
  background: linear-gradient(135deg, #4facfe, #00f2fe);
  top: 60%;
  right: 20%;
  animation: float-3 18s infinite ease-in-out;
}

@keyframes float-1 {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  50% { transform: translate(30px, -30px) rotate(180deg); }
}

@keyframes float-2 {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  50% { transform: translate(-25px, 25px) rotate(-180deg); }
}

@keyframes float-3 {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  50% { transform: translate(20px, 20px) rotate(90deg); }
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

.trophy-animation {
  font-size: 5rem;
  margin-bottom: 1rem;
  animation: trophy-bounce 2s infinite;
  display: inline-block;
}

@keyframes trophy-bounce {
  0%, 100% { transform: translateY(0) rotate(-5deg); }
  50% { transform: translateY(-15px) rotate(5deg); }
}

.page-header h2 {
  font-size: 3rem;
  font-weight: 900;
  margin-bottom: 0.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -1px;
}

.page-header p {
  color: #666;
  font-size: 1.2rem;
  margin-bottom: 1.5rem;
}

.connection-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  background: white;
  border-radius: 25px;
  font-weight: 700;
  color: #999;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transition: all 0.3s;
}

.connection-badge.connected {
  color: #16a34a;
  box-shadow: 0 4px 20px rgba(22, 163, 74, 0.2);
}

.status-pulse {
  width: 12px;
  height: 12px;
  background: #ccc;
  border-radius: 50%;
  animation: pulse-gray 2s infinite;
}

.connection-badge.connected .status-pulse {
  background: #4ade80;
  animation: pulse-connect 2s infinite;
}

@keyframes pulse-connect {
  0%, 100% { transform: scale(1); opacity: 1; box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
  50% { transform: scale(1.2); opacity: 0.8; box-shadow: 0 0 0 6px rgba(74, 222, 128, 0); }
}

@keyframes pulse-gray {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

.notification-toast {
  position: fixed;
  top: 110px;
  right: 20px;
  background: white;
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  z-index: 2000;
  display: flex;
  align-items: center;
  gap: 1rem;
  min-width: 300px;
  max-width: 400px;
  border-left: 4px solid #667eea;
}

.notification-icon {
  font-size: 2rem;
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.25rem;
  font-size: 1.05rem;
}

.notification-message {
  color: #666;
  font-size: 0.95rem;
}

.content-wrapper {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
  align-items: start;
}

.leaderboard-card, .stats-card, .top-performers-card {
  background: white;
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
  transition: all 0.3s;
}

.leaderboard-card:hover {
  box-shadow: 0 15px 50px rgba(0,0,0,0.12);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 3px solid #f0f0f0;
}

.card-header h3 {
  font-size: 1.75rem;
  font-weight: 800;
  color: #2c3e50;
}

.refresh-btn {
  width: 45px;
  height: 45px;
  border: 2px solid #e0e0e0;
  background: white;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.refresh-btn:hover:not(:disabled) {
  border-color: #667eea;
  background: #667eea;
}

.refresh-btn:hover:not(:disabled) .refresh-icon {
  filter: brightness(0) invert(1);
}

.refresh-icon {
  font-size: 1.25rem;
  transition: transform 0.3s;
}

.refresh-icon.spinning {
  animation: spin 1s linear infinite;
}

.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #666;
}

.loading-spinner {
  width: 60px;
  height: 60px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1.5rem;
}

.error-icon, .empty-icon {
  font-size: 4rem;
  display: block;
  margin-bottom: 1.5rem;
}

.empty-state h4 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.retry-btn, .add-btn {
  margin-top: 1.5rem;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
  font-size: 1rem;
}

.retry-btn:hover, .add-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.players-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.player-card {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1.75rem;
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
  border-radius: 20px;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid transparent;
  position: relative;
  overflow: hidden;
}

.player-card:hover {
  transform: translateX(8px) scale(1.02);
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
  border-color: #667eea;
}

.player-card.rank-1 {
  background: linear-gradient(135deg, #ffd70015 0%, #ffed4e15 100%);
  border-color: #ffd700;
}

.player-card.rank-2 {
  background: linear-gradient(135deg, #c0c0c015 0%, #e8e8e815 100%);
  border-color: #c0c0c0;
}

.player-card.rank-3 {
  background: linear-gradient(135deg, #cd7f3215 0%, #f4a46015 100%);
  border-color: #cd7f32;
}

.player-card.highlight {
  animation: highlight-pulse 0.6s ease-in-out;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  transform: scale(1.05);
  box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
}

@keyframes highlight-pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.card-shimmer {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s;
}

.player-card:hover .card-shimmer {
  left: 100%;
}

.rank-section {
  min-width: 70px;
  text-align: center;
}

.medal-badge {
  font-size: 3.5rem;
  animation: medal-float 3s infinite ease-in-out;
}

@keyframes medal-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

.rank-number {
  font-size: 2rem;
  font-weight: 900;
  color: #667eea;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.rank-hash {
  font-size: 1rem;
  opacity: 0.5;
}

.player-card.highlight .rank-number {
  color: white;
}

.player-section {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.player-avatar {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  font-weight: 800;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.player-card.highlight .player-avatar {
  background: white;
  color: #667eea;
}

.player-details {
  flex: 1;
}

.player-name {
  font-size: 1.5rem;
  font-weight: 800;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  letter-spacing: -0.5px;
}

.player-card.highlight .player-name {
  color: white;
}

.player-meta {
  display: flex;
  gap: 1rem;
  color: #666;
  font-size: 0.95rem;
}

.player-card.highlight .player-meta {
  color: rgba(255, 255, 255, 0.9);
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.meta-icon {
  font-size: 1.1rem;
}

.score-section {
  text-align: right;
}

.score-display {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.score-number {
  font-size: 2.5rem;
  font-weight: 900;
  color: #667eea;
  line-height: 1;
}

.player-card.highlight .score-number {
  color: white;
}

.score-suffix {
  font-size: 1rem;
  color: #999;
  font-weight: 600;
}

.player-card.highlight .score-suffix {
  color: rgba(255, 255, 255, 0.8);
}

.leader-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  color: #b8860b;
  border-radius: 20px;
  font-weight: 800;
  font-size: 0.875rem;
  box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
}

.stats-sidebar {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  position: sticky;
  top: 100px;
}

.stats-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.stats-header h3 {
  font-size: 1.5rem;
  font-weight: 800;
  color: #2c3e50;
}

.stats-badge {
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
  color: white;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 700;
  animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.stats-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
  border-radius: 16px;
  transition: all 0.3s;
}

.stat-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.stat-icon-wrapper {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-icon-wrapper.blue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon-wrapper.gold {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
}

.stat-icon-wrapper.green {
  background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
}

.stat-icon-wrapper.purple {
  background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
}

.stat-details {
  flex: 1;
}

.stat-value {
  font-size: 2rem;
  font-weight: 900;
  color: #2c3e50;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  color: #666;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.top-performers-card {
  margin-top: 0;
}

.top-performers-card h3 {
  font-size: 1.25rem;
  font-weight: 800;
  color: #2c3e50;
  margin-bottom: 2rem;
  text-align: center;
}

.podium {
  display: flex;
  align-items: flex-end;
  justify-content: center;
  gap: 1rem;
  min-height: 250px;
}

.podium-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.podium-rank {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  animation: medal-float 3s infinite ease-in-out;
}

.podium-item.first .podium-rank {
  font-size: 3rem;
  animation-delay: 0s;
}

.podium-item.second .podium-rank {
  animation-delay: 0.2s;
}

.podium-item.third .podium-rank {
  animation-delay: 0.4s;
}

.podium-name {
  font-weight: 800;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
  text-align: center;
  word-break: break-word;
}

.podium-score {
  font-size: 0.875rem;
  color: #667eea;
  font-weight: 700;
  margin-bottom: 1rem;
}

.podium-bar {
  width: 100%;
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px 12px 0 0;
  position: relative;
  box-shadow: 0 -4px 20px rgba(102, 126, 234, 0.3);
  transition: height 0.5s ease;
}

.podium-item.first .podium-bar {
  background: linear-gradient(180deg, #ffd700 0%, #ffed4e 100%);
  box-shadow: 0 -4px 20px rgba(255, 215, 0, 0.3);
}

.podium-item.second .podium-bar {
  background: linear-gradient(180deg, #c0c0c0 0%, #e8e8e8 100%);
  box-shadow: 0 -4px 20px rgba(192, 192, 192, 0.3);
}

.podium-item.third .podium-bar {
  background: linear-gradient(180deg, #cd7f32 0%, #f4a460 100%);
  box-shadow: 0 -4px 20px rgba(205, 127, 50, 0.3);
}

/* Animations */
.notification-slide-enter-active, .notification-slide-leave-active {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.notification-slide-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.notification-slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.player-list-move, .player-list-enter-active, .player-list-leave-active {
  transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.player-list-enter-from {
  opacity: 0;
  transform: translateX(-50px) scale(0.95);
}

.player-list-leave-to {
  opacity: 0;
  transform: translateX(50px) scale(0.95);
}

.player-list-leave-active {
  position: absolute;
  width: calc(100% - 3.5rem);
}

/* Responsive Design */
@media (max-width: 1200px) {
  .content-wrapper {
    grid-template-columns: 1fr 350px;
  }
}

@media (max-width: 1024px) {
  .content-wrapper {
    grid-template-columns: 1fr;
  }
  
  .stats-sidebar {
    position: static;
    grid-template-columns: repeat(2, 1fr);
    display: grid;
  }
  
  .top-performers-card {
    grid-column: 1 / -1;
  }
}

@media (max-width: 768px) {
  .leaderboard-page {
    padding: 2rem 1rem;
  }

  .page-header h2 {
    font-size: 2rem;
  }

  .trophy-animation {
    font-size: 3.5rem;
  }

  .leaderboard-card, .stats-card, .top-performers-card {
    padding: 1.5rem;
  }

  .player-card {
    padding: 1.25rem;
    gap: 1rem;
  }

  .rank-section {
    min-width: 50px;
  }

  .medal-badge {
    font-size: 2.5rem;
  }

  .rank-number {
    font-size: 1.5rem;
  }

  .player-avatar {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
  }

  .player-name {
    font-size: 1.2rem;
  }

  .score-number {
    font-size: 2rem;
  }

  .notification-toast {
    left: 10px;
    right: 10px;
    min-width: auto;
    max-width: none;
  }

  .stats-sidebar {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .podium {
    min-height: 200px;
  }

  .podium-rank {
    font-size: 2rem;
  }

  .podium-item.first .podium-rank {
    font-size: 2.5rem;
  }
}

@media (max-width: 480px) {
  .player-card {
    flex-wrap: wrap;
  }

  .score-section {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>