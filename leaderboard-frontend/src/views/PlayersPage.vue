<template>
  <div class="players-page">
    <div class="container">
      <div class="page-header">
        <div>
          <h1>👥 Players Management</h1>
          <p>Create, edit, and manage all players</p>
        </div>
        <button class="btn-primary" @click="openCreateModal">
          <span>➕</span> Add New Player
        </button>
      </div>

      <!-- Search and Filter -->
      <div class="filters-section">
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search players..."
            @input="fetchPlayers"
          >
        </div>
        
        <select v-model="filterTeam" @change="fetchPlayers" class="team-filter">
          <option value="">All Teams</option>
          <option v-for="team in teams" :key="team.id" :value="team.id">
            {{ team.name }}
          </option>
        </select>
      </div>

      <!-- Players Grid -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading players...</p>
      </div>

      <div v-else-if="players.length === 0" class="empty-state">
        <span class="empty-icon">🎯</span>
        <h3>No Players Found</h3>
        <p>Start by adding your first player</p>
        <button class="btn-primary" @click="openCreateModal">
          Add Player
        </button>
      </div>

      <div v-else class="players-grid">
        <div v-for="player in players" :key="player.id" class="player-card">
          <div class="player-avatar-section">
            <img 
              :src="player.avatar_url" 
              :alt="player.name"
              class="player-avatar"
            >
            <div v-if="player.team" class="team-badge" :style="{ background: player.team.color }">
              {{ player.team.name }}
            </div>
          </div>

          <div class="player-info">
            <h3>{{ player.name }}</h3>
            <div class="player-stats">
              <div class="stat">
                <span class="stat-label">Score</span>
                <span class="stat-value">{{ player.score }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">History</span>
                <span class="stat-value">{{ player.history_count || 0 }}</span>
              </div>
            </div>
          </div>

          <div class="player-actions">
            <button class="btn-action" @click="viewHistory(player)" title="View History">
              📊
            </button>
            <button class="btn-action" @click="openEditModal(player)" title="Edit">
              ✏️
            </button>
            <button class="btn-action danger" @click="confirmDelete(player)" title="Delete">
              🗑️
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Player Form Modal -->
    <transition name="modal">
      <div v-if="showModal" class="modal-overlay" @click="closeModal">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <h3>{{ editingPlayer ? 'Edit Player' : 'Create New Player' }}</h3>
            <button class="btn-close" @click="closeModal">×</button>
          </div>

          <form @submit.prevent="savePlayer" class="modal-form">
            <div class="form-group">
              <label>Name *</label>
              <input 
                v-model="formData.name" 
                type="text" 
                placeholder="Enter player name"
                required
              >
            </div>

            <div class="form-group">
              <label>Avatar</label>
              <div class="avatar-upload">
                <img 
                  :src="previewAvatar || (editingPlayer ? editingPlayer.avatar_url : '/default-avatar.png')" 
                  alt="Avatar preview"
                  class="avatar-preview"
                >
                <input 
                  type="file" 
                  ref="avatarInput"
                  accept="image/*"
                  @change="handleAvatarChange"
                  style="display: none"
                >
                <button type="button" class="btn-upload" @click="$refs.avatarInput.click()">
                  📷 Choose Avatar
                </button>
              </div>
            </div>

            <div class="form-group">
              <label>Team</label>
              <select v-model="formData.team_id">
                <option :value="null">No Team</option>
                <option v-for="team in teams" :key="team.id" :value="team.id">
                  {{ team.name }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Initial Score</label>
              <input 
                v-model.number="formData.score" 
                type="number" 
                min="0"
                placeholder="0"
              >
            </div>

            <div class="modal-footer">
              <button type="button" class="btn-cancel" @click="closeModal">
                Cancel
              </button>
              <button type="submit" class="btn-primary" :disabled="submitting">
                {{ submitting ? 'Saving...' : 'Save Player' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Score History Modal -->
    <ScoreHistoryModal 
      v-if="showHistoryModal"
      :player="selectedPlayer"
      @close="showHistoryModal = false"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :show="showDeleteDialog"
      title="Delete Player"
      :message="`Are you sure you want to delete ${playerToDelete?.name}? This action cannot be undone.`"
      confirm-text="Delete"
      type="danger"
      @confirm="deletePlayer"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { playerApi, teamApi } from '../services/api'
import ConfirmDialog from '../components/ConfirmDialog.vue'
import ScoreHistoryModal from '../components/ScoreHistoryModal.vue'

const players = ref([])
const teams = ref([])
const loading = ref(true)
const searchQuery = ref('')
const filterTeam = ref('')
const showModal = ref(false)
const showHistoryModal = ref(false)
const showDeleteDialog = ref(false)
const editingPlayer = ref(null)
const selectedPlayer = ref(null)
const playerToDelete = ref(null)
const submitting = ref(false)
const previewAvatar = ref(null)
const avatarFile = ref(null)

const formData = reactive({
  name: '',
  team_id: null,
  score: 0
})

const fetchPlayers = async () => {
  try {
    loading.value = true
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (filterTeam.value) params.team_id = filterTeam.value
    
    const response = await playerApi.getAll(params)
    players.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch players:', error)
  } finally {
    loading.value = false
  }
}

const fetchTeams = async () => {
  try {
    const response = await teamApi.getAll()
    teams.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch teams:', error)
  }
}

const openCreateModal = () => {
  editingPlayer.value = null
  formData.name = ''
  formData.team_id = null
  formData.score = 0
  previewAvatar.value = null
  avatarFile.value = null
  showModal.value = true
}

const openEditModal = (player) => {
  editingPlayer.value = player
  formData.name = player.name
  formData.team_id = player.team_id
  formData.score = player.score
  previewAvatar.value = null
  avatarFile.value = null
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingPlayer.value = null
}

const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    avatarFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      previewAvatar.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const savePlayer = async () => {
  try {
    submitting.value = true
    
    const data = {
      name: formData.name,
      team_id: formData.team_id,
      score: formData.score
    }
    
    if (avatarFile.value) {
      data.avatar = avatarFile.value
    }
    
    if (editingPlayer.value) {
      await playerApi.update(editingPlayer.value.id, data)
    } else {
      await playerApi.create(data)
    }
    
    closeModal()
    await fetchPlayers()
  } catch (error) {
    console.error('Failed to save player:', error)
    alert('Failed to save player. Please try again.')
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (player) => {
  playerToDelete.value = player
  showDeleteDialog.value = true
}

const deletePlayer = async () => {
  try {
    await playerApi.delete(playerToDelete.value.id)
    showDeleteDialog.value = false
    playerToDelete.value = null
    await fetchPlayers()
  } catch (error) {
    console.error('Failed to delete player:', error)
    alert('Failed to delete player. Please try again.')
  }
}

const viewHistory = (player) => {
  selectedPlayer.value = player
  showHistoryModal.value = true
}

onMounted(() => {
  fetchPlayers()
  fetchTeams()
})
</script>

<style scoped>
.players-page {
  min-height: calc(100vh - 80px);
  padding: 2rem 1rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.container {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin: 0 0 0.5rem;
  font-weight: 800;
}

.page-header p {
  color: #666;
  font-size: 1.1rem;
  margin: 0;
}

.btn-primary {
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
  font-size: 1rem;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.filters-section {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.search-box {
  flex: 1;
  position: relative;
}

.search-icon {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.25rem;
}

.search-box input {
  width: 100%;
  padding: 1rem 1rem 1rem 3.5rem;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s;
}

.search-box input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.team-filter {
  min-width: 200px;
  padding: 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.team-filter:focus {
  outline: none;
  border-color: #667eea;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
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

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 4rem;
  display: block;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.players-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.player-card {
  background: white;
  border-radius: 20px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  transition: all 0.3s;
}

.player-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.player-avatar-section {
  text-align: center;
  margin-bottom: 1rem;
  position: relative;
}

.player-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #f0f0f0;
  margin-bottom: 0.5rem;
}

.team-badge {
  display: inline-block;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  color: white;
  font-size: 0.85rem;
  font-weight: 600;
}

.player-info {
  text-align: center;
  margin-bottom: 1rem;
}

.player-info h3 {
  font-size: 1.3rem;
  color: #2c3e50;
  margin-bottom: 1rem;
  font-weight: 700;
}

.player-stats {
  display: flex;
  justify-content: center;
  gap: 2rem;
}

.stat {
  text-align: center;
}

.stat-label {
  display: block;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 800;
  color: #667eea;
}

.player-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  padding-top: 1rem;
  border-top: 2px solid #f0f0f0;
}

.btn-action {
  padding: 0.75rem 1rem;
  background: #f0f0f0;
  border: none;
  border-radius: 10px;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-action:hover {
  background: #667eea;
  transform: translateY(-2px);
}

.btn-action.danger:hover {
  background: #ff6b6b;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(4px);
}

.modal-container {
  background: white;
  border-radius: 20px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
  padding: 2rem;
  border-bottom: 2px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin: 0;
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

.modal-form {
  padding: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 700;
  color: #2c3e50;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.avatar-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.avatar-preview {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #f0f0f0;
}

.btn-upload {
  padding: 0.75rem 1.5rem;
  background: #f0f0f0;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-upload:hover {
  background: #667eea;
  color: white;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 2px solid #f0f0f0;
}

.btn-cancel {
  flex: 1;
  padding: 1rem;
  background: #f0f0f0;
  color: #666;
  border: none;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-cancel:hover {
  background: #e0e0e0;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .filters-section {
    flex-direction: column;
  }

  .team-filter {
    width: 100%;
  }

  .players-grid {
    grid-template-columns: 1fr;
  }
}
</style>

