<script setup lang="jsx">
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const player = ref(null)
const history = ref([])
const loading = ref(true)

const playerId = window.location.pathname.split('/').pop()

const fetchPlayer = async () => {
  try {
    const response = await axios.get(`/api/v1/players/${playerId}`)
    player.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch player:', error)
  }
}

const fetchHistory = async () => {
  try {
    loading.value = true
    const response = await axios.get(`/api/v1/players/${playerId}/history`)
    history.value = response.data.data.data || response.data.data
  } catch (error) {
    console.error('Failed to fetch history:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPlayer()
  fetchHistory()
})

watch(() => playerId, () => {
  fetchPlayer()
  fetchHistory()
})

const styles = {
  container: { padding: '2rem', maxWidth: '800px', margin: 'auto' },
  detailHeader: { display: 'flex', alignItems: 'center', gap: '1rem' },
  largeAvatar: { width: '120px', height: '120px', borderRadius: '50%' },
  title: { fontSize: '1.8rem', fontWeight: 'bold', marginBottom: '.5rem' },
  teamBadge: { background: '#e0e7ff', padding: '4px 8px', borderRadius: '6px', display: 'inline-block' },
  scoreDisplay: { marginTop: '0.5rem', fontSize: '1rem' },
  scoreLabel: { color: '#6b7280', marginRight: '4px' },
  scoreValue: { fontWeight: 'bold', color: '#111827' },
  historySection: { marginTop: '2rem' },
  sectionTitle: { fontSize: '1.4rem', fontWeight: 'bold', marginBottom: '1rem' },
  historyList: { display: 'flex', flexDirection: 'column', gap: '.8rem' },
  historyItem: { display: 'flex', gap: '1rem', background: '#f9fafb', padding: '1rem', borderRadius: '10px' },
  historyIcon: { width: '40px', height: '40px', borderRadius: '50%', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' },
  historyContent: { flex: 1 },
  historyPoints: { fontWeight: 'bold', fontSize: '1rem' },
  historyMeta: { fontSize: '.9rem', color: '#6b7280' },
  historyTime: { fontSize: '.8rem', color: '#9ca3af', marginTop: '4px' },
  loading: { textAlign: 'center', marginTop: '2rem' },
  empty: { textAlign: 'center', color: '#9ca3af', fontStyle: 'italic' }
}
</script>

<template>
  <div v-if="!player" :style="styles.loading">Loading...</div>
  <div v-else :style="styles.container">
    <div :style="styles.detailHeader">
      <img :src="player.avatar_url" :alt="player.name" :style="styles.largeAvatar" />
      <div>
        <h1 :style="styles.title">{{ player.name }}</h1>
        <div v-if="player.team" :style="styles.teamBadge">
          {{ player.team.logo }} {{ player.team.name }}
        </div>
        <div :style="styles.scoreDisplay">
          <span :style="styles.scoreLabel">Current Score:</span>
          <span :style="styles.scoreValue">{{ player.score }}</span>
        </div>
      </div>
    </div>

    <div :style="styles.historySection">
      <h2 :style="styles.sectionTitle">Score History</h2>
      <div v-if="loading" :style="styles.loading">Loading history...</div>
      <div v-else-if="history.length === 0" :style="styles.empty">No score history yet</div>
      <div v-else :style="styles.historyList">
        <div v-for="entry in history" :key="entry.id" :style="styles.historyItem">
          <div
            :style="{
              ...styles.historyIcon,
              background: entry.points_changed > 0 ? '#4ade80' : '#f87171'
            }"
          >
            {{ entry.points_changed > 0 ? '⬆️' : '⬇️' }}
          </div>
          <div :style="styles.historyContent">
            <div :style="styles.historyPoints">
              {{ entry.points_changed > 0 ? '+' : '' }}{{ entry.points_changed }} pts
            </div>
            <div :style="styles.historyMeta">
              {{ entry.reason || 'Manual update' }} • {{ entry.updated_by || 'System' }}
            </div>
            <div :style="styles.historyTime">
              {{ new Date(entry.created_at).toLocaleString() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
