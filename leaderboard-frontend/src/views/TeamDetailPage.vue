<script setup lang="jsx">
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const team = ref(null)
const players = ref([])
const loading = ref(true)

const teamId = window.location.pathname.split('/').pop()

const fetchTeam = async () => {
  try {
    const response = await axios.get(`/api/v1/teams/${teamId}`)
    team.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch team:', error)
  }
}

const fetchTeamPlayers = async () => {
  try {
    loading.value = true
    const response = await axios.get(`/api/v1/teams/${teamId}/leaderboard`)
    players.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch team players:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTeam()
  fetchTeamPlayers()
})

watch(() => teamId, () => {
  fetchTeam()
  fetchTeamPlayers()
})

const styles = {
  container: { padding: '2rem', maxWidth: '900px', margin: 'auto' },
  teamHeader: {
    textAlign: 'center',
    padding: '2rem',
    borderRadius: '12px',
    color: 'white',
    marginBottom: '2rem'
  },
  teamLogo: { fontSize: '3rem', marginBottom: '.5rem' },
  teamTitle: { fontSize: '2rem', fontWeight: 'bold', margin: '.3rem 0' },
  teamDesc: { fontSize: '1rem', opacity: 0.9 },
  teamStats: {
    display: 'flex',
    justifyContent: 'space-around',
    gap: '1rem',
    marginBottom: '2rem',
    flexWrap: 'wrap'
  },
  statCard: {
    background: '#f3f4f6',
    borderRadius: '10px',
    padding: '1rem',
    flex: '1',
    minWidth: '120px',
    textAlign: 'center'
  },
  statIcon: { fontSize: '1.5rem' },
  statValue: { fontSize: '1.4rem', fontWeight: 'bold' },
  statLabel: { color: '#6b7280', fontSize: '.9rem' },
  playersSection: { marginTop: '2rem' },
  sectionTitle: { fontSize: '1.4rem', fontWeight: 'bold', marginBottom: '1rem' },
  playersList: {
    display: 'flex',
    flexDirection: 'column',
    gap: '.8rem'
  },
  playerRow: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    background: '#f9fafb',
    padding: '0.8rem 1rem',
    borderRadius: '8px'
  },
  rank: { fontWeight: 'bold', width: '40px', textAlign: 'center' },
  smallAvatar: { width: '50px', height: '50px', borderRadius: '50%' },
  playerInfo: { flex: 1, marginLeft: '1rem' },
  playerName: { fontSize: '1rem', fontWeight: '500' },
  playerScore: { fontWeight: 'bold' },
  loading: { textAlign: 'center', marginTop: '2rem' },
  empty: { textAlign: 'center', color: '#9ca3af', fontStyle: 'italic' }
}
</script>

<template>
  <div v-if="!team" :style="styles.loading">Loading...</div>

  <div v-else :style="styles.container">
    <div :style="{ ...styles.teamHeader, background: team.color || '#6366f1' }">
      <div :style="styles.teamLogo">{{ team.logo || '🎯' }}</div>
      <h1 :style="styles.teamTitle">{{ team.name }}</h1>
      <p v-if="team.description" :style="styles.teamDesc">{{ team.description }}</p>
    </div>

    <div :style="styles.teamStats">
      <div :style="styles.statCard">
        <div :style="styles.statIcon">👥</div>
        <div :style="styles.statValue">{{ team.players_count || 0 }}</div>
        <div :style="styles.statLabel">Players</div>
      </div>
      <div :style="styles.statCard">
        <div :style="styles.statIcon">⭐</div>
        <div :style="styles.statValue">{{ team.total_score || 0 }}</div>
        <div :style="styles.statLabel">Total Score</div>
      </div>
      <div :style="styles.statCard">
        <div :style="styles.statIcon">📊</div>
        <div :style="styles.statValue">{{ team.average_score || 0 }}</div>
        <div :style="styles.statLabel">Average</div>
      </div>
    </div>

    <div :style="styles.playersSection">
      <h2 :style="styles.sectionTitle">Team Players</h2>
      <div v-if="loading" :style="styles.loading">Loading players...</div>
      <div v-else-if="players.length === 0" :style="styles.empty">
        No players in this team yet
      </div>
      <div v-else :style="styles.playersList">
        <div v-for="(player, index) in players" :key="player.id" :style="styles.playerRow">
          <div :style="styles.rank">#{{ index + 1 }}</div>
          <img :src="player.avatar_url" :alt="player.name" :style="styles.smallAvatar" />
          <div :style="styles.playerInfo">
            <div :style="styles.playerName">{{ player.name }}</div>
          </div>
          <div :style="styles.playerScore">{{ player.score }} pts</div>
        </div>
      </div>
    </div>
  </div>
</template>
