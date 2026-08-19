<template>
  <transition name="modal">
    <div class="modal-overlay" @click="$emit('close')">
      <div class="history-modal" @click.stop>
        <div class="modal-header">
          <div class="player-info">
            <img :src="player.avatar_url" :alt="player.name" class="player-avatar">
            <div>
              <h3>{{ player.name }}</h3>
              <p>Score History</p>
            </div>
          </div>
          <button class="btn-close" @click="$emit('close')">×</button>
        </div>

        <div class="modal-body">
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading history...</p>
          </div>

          <div v-else-if="history.length === 0" class="empty-state">
            <span class="empty-icon">📊</span>
            <p>No score history yet</p>
          </div>

          <div v-else class="history-list">
            <div v-for="entry in history" :key="entry.id" class="history-item">
              <div class="history-icon" :class="entry.points_changed > 0 ? 'positive' : 'negative'">
                {{ entry.points_changed > 0 ? '⬆️' : '⬇️' }}
              </div>
              
              <div class="history-details">
                <div class="history-change">
                  <span class="points-badge" :class="entry.points_changed > 0 ? 'positive' : 'negative'">
                    {{ entry.points_changed > 0 ? '+' : '' }}{{ entry.points_changed }} pts
                  </span>
                  <span class="score-change">
                    {{ entry.old_score }} → {{ entry.new_score }}
                  </span>
                </div>
                
                <div class="history-meta">
                  <span class="reason">{{ entry.reason || 'Manual update' }}</span>
                  <span class="divider">•</span>
                  <span class="updated-by">{{ entry.updated_by || 'System' }}</span>
                </div>
                
                <div class="history-time">
                  {{ formatDate(entry.created_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { playerApi } from '../services/api'

const props = defineProps({
  player: {
    type: Object,
    required: true
  }
})

defineEmits(['close'])

const history = ref([])
const loading = ref(true)

const fetchHistory = async () => {
  try {
    loading.value = true
    const response = await playerApi.getHistory(props.player.id)
    history.value = response.data.data.data || response.data.data
  } catch (error) {
    console.error('Failed to fetch history:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`
  if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`
  if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`
  
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchHistory()
})
</script>

<style scoped>
.history-modal {
  background: white;
  border-radius: 20px;
  width: 600px;
  max-width: 90%;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
  padding: 2rem;
  border-bottom: 2px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.player-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #f0f0f0;
}

.player-info h3 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin: 0 0 0.25rem;
}

.player-info p {
  color: #666;
  margin: 0;
  font-size: 0.95rem;
}

.btn-close {
  width: 40px;
  height: 40px;
  background: #f0f0f0;
  border: none;
  border-radius: 10px;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-close:hover {
  background: #ff6b6b;
  color: white;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 3rem 2rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

.empty-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.history-item {
  display: flex;
  gap: 1rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
  border-radius: 12px;
  transition: all 0.3s;
}

.history-item:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.history-icon {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.history-icon.positive {
  background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
}

.history-icon.negative {
  background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
}

.history-details {
  flex: 1;
}

.history-change {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.points-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.95rem;
}

.points-badge.positive {
  background: #4ade80;
  color: white;
}

.points-badge.negative {
  background: #f87171;
  color: white;
}

.score-change {
  color: #666;
  font-size: 0.95rem;
}

.history-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.reason {
  color: #2c3e50;
  font-weight: 600;
}

.divider {
  color: #ccc;
}

.updated-by {
  color: #666;
}

.history-time {
  color: #999;
  font-size: 0.85rem;
}
</style>