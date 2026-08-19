<template>
  <div class="teams-page">
    <div class="container">
      <div class="page-header">
        <div>
          <h1>🎯 Teams Management</h1>
          <p>Create, edit, and manage teams</p>
        </div>
        <button class="btn-primary" @click="openCreateModal">
          <span>➕</span> Add New Team
        </button>
      </div>

      <!-- Teams Grid -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading teams...</p>
      </div>

      <div v-else-if="teams.length === 0" class="empty-state">
        <span class="empty-icon">🎯</span>
        <h3>No Teams Found</h3>
        <p>Start by creating your first team</p>
        <button class="btn-primary" @click="openCreateModal">
          Create Team
        </button>
      </div>

      <div v-else class="teams-grid">
        <div v-for="team in teams" :key="team.id" class="team-card">
          <div class="team-header" :style="{ background: team.color }">
            <div class="team-logo">
              {{ team.logo || '🎯' }}
            </div>
          </div>

          <div class="team-body">
            <h3>{{ team.name }}</h3>
            <p v-if="team.description" class="team-description">
              {{ team.description }}
            </p>

            <div class="team-stats">
              <div class="stat">
                <span class="stat-icon">👥</span>
                <div>
                  <span class="stat-value">{{ team.players_count || 0 }}</span>
                  <span class="stat-label">Players</span>
                </div>
              </div>
              <div class="stat">
                <span class="stat-icon">⭐</span>
                <div>
                  <span class="stat-value">{{ team.total_score || 0 }}</span>
                  <span class="stat-label">Total Score</span>
                </div>
              </div>
              <div class="stat">
                <span class="stat-icon">📊</span>
                <div>
                  <span class="stat-value">{{ team.average_score || 0 }}</span>
                  <span class="stat-label">Average</span>
                </div>
              </div>
            </div>
          </div>

          <div class="team-actions">
            <router-link :to="`/teams/${team.id}`" class="btn-action" title="View Details">
              👁️
            </router-link>
            <button class="btn-action" @click="openEditModal(team)" title="Edit">
              ✏️
            </button>
            <button class="btn-action danger" @click="confirmDelete(team)" title="Delete">
              🗑️
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Team Form Modal -->
    <transition name="modal">
      <div v-if="showModal" class="modal-overlay" @click="closeModal">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <h3>{{ editingTeam ? 'Edit Team' : 'Create New Team' }}</h3>
            <button class="btn-close" @click="closeModal">×</button>
          </div>

          <form @submit.prevent="saveTeam" class="modal-form">
            <div class="form-group">
              <label>Team Name *</label>
              <input 
                v-model="formData.name" 
                type="text" 
                placeholder="Enter team name"
                required
              >
            </div>

            <div class="form-group">
              <label>Logo Emoji</label>
              <input 
                v-model="formData.logo" 
                type="text" 
                placeholder="🎯 Choose an emoji"
                maxlength="2"
              >
            </div>

            <div class="form-group">
              <label>Team Color</label>
              <div class="color-picker-wrapper">
                <input 
                  v-model="formData.color" 
                  type="color"
                  class="color-input"
                >
                <input 
                  v-model="formData.color" 
                  type="text" 
                  placeholder="#667eea"
                  pattern="^#[0-9A-Fa-f]{6}$"
                  class="color-text"
                >
              </div>
            </div>

            <div class="form-group">
              <label>Description</label>
              <textarea 
                v-model="formData.description" 
                placeholder="Enter team description (optional)"
                rows="3"
              ></textarea>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn-cancel" @click="closeModal">
                Cancel
              </button>
              <button type="submit" class="btn-primary" :disabled="submitting">
                {{ submitting ? 'Saving...' : 'Save Team' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :show="showDeleteDialog"
      title="Delete Team"
      :message="`Are you sure you want to delete ${teamToDelete?.name}? ${teamToDelete?.players_count > 0 ? 'You must remove all players first.' : 'This action cannot be undone.'}`"
      confirm-text="Delete"
      type="danger"
      @confirm="deleteTeam"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { teamApi } from '../services/api'
import ConfirmDialog from '../components/ConfirmDialog.vue'

const teams = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingTeam = ref(null)
const teamToDelete = ref(null)
const submitting = ref(false)

const formData = reactive({
  name: '',
  logo: '🎯',
  color: '#667eea',
  description: ''
})

const fetchTeams = async () => {
  try {
    loading.value = true
    const response = await teamApi.getAll()
    teams.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch teams:', error)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingTeam.value = null
  formData.name = ''
  formData.logo = '🎯'
  formData.color = '#667eea'
  formData.description = ''
  showModal.value = true
}

const openEditModal = (team) => {
  editingTeam.value = team
  formData.name = team.name
  formData.logo = team.logo || '🎯'
  formData.color = team.color
  formData.description = team.description || ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingTeam.value = null
}

const saveTeam = async () => {
  try {
    submitting.value = true
    
    const data = {
      name: formData.name,
      logo: formData.logo,
      color: formData.color,
      description: formData.description
    }
    
    if (editingTeam.value) {
      await teamApi.update(editingTeam.value.id, data)
    } else {
      await teamApi.create(data)
    }
    
    closeModal()
    await fetchTeams()
  } catch (error) {
    console.error('Failed to save team:', error)
    alert(error.response?.data?.message || 'Failed to save team. Please try again.')
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (team) => {
  teamToDelete.value = team
  showDeleteDialog.value = true
}

const deleteTeam = async () => {
  if (teamToDelete.value.players_count > 0) {
    alert('Cannot delete team with players. Please remove players first.')
    showDeleteDialog.value = false
    return
  }

  try {
    await teamApi.delete(teamToDelete.value.id)
    showDeleteDialog.value = false
    teamToDelete.value = null
    await fetchTeams()
  } catch (error) {
    console.error('Failed to delete team:', error)
    alert(error.response?.data?.message || 'Failed to delete team. Please try again.')
  }
}

onMounted(() => {
  fetchTeams()
})
</script>

<style scoped>
.teams-page {
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

.teams-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
}

.team-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  transition: all 0.3s;
}

.team-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.team-header {
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.team-logo {
  font-size: 4rem;
}

.team-body {
  padding: 1.5rem;
}

.team-body h3 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin: 0 0 0.5rem;
  font-weight: 700;
}

.team-description {
  color: #666;
  font-size: 0.95rem;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

.team-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  padding: 1rem 0;
  border-top: 2px solid #f0f0f0;
  border-bottom: 2px solid #f0f0f0;
}

.stat {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stat-icon {
  font-size: 1.5rem;
}

.stat-value {
  display: block;
  font-size: 1.25rem;
  font-weight: 800;
  color: #667eea;
  line-height: 1;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  color: #666;
}

.team-actions {
  display: flex;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
}

.btn-action {
  flex: 1;
  padding: 0.875rem;
  background: #f0f0f0;
  border: none;
  border-radius: 10px;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
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
.form-group textarea {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s;
  font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.color-picker-wrapper {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.color-input {
  width: 80px;
  height: 40px;
  border: none; padding: 0;
  background: none; cursor: pointer;
}

</style>