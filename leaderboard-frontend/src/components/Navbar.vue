<template>
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-brand">
        <router-link to="/" class="logo-wrapper">
          <div class="logo-icon">🏆</div>
          <div class="logo-text">
            <h1>Leaderboard</h1>
            <span class="tagline">Real-time scoring</span>
          </div>
        </router-link>
      </div>
      
      <div class="nav-links">
        <router-link to="/" class="nav-link" :class="{ active: $route.path === '/' }">
          <span class="link-icon">📊</span>
          <span class="link-text">Leaderboard</span>
        </router-link>
        
        <router-link to="/score-entry" class="nav-link" :class="{ active: $route.path === '/score-entry' }">
          <span class="link-icon">✏️</span>
          <span class="link-text">Update Scores</span>
        </router-link>
        
        <router-link to="/players" class="nav-link" :class="{ active: $route.path === '/players' }">
          <span class="link-icon">👥</span>
          <span class="link-text">Players</span>
        </router-link>
        
        <router-link to="/teams" class="nav-link" :class="{ active: $route.path === '/teams' }">
          <span class="link-icon">🎯</span>
          <span class="link-text">Teams</span>
        </router-link>
      </div>

      <button class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen">
        <span class="hamburger-icon">☰</span>
      </button>
    </div>

    <transition name="slide">
      <div v-if="mobileMenuOpen" class="mobile-menu">
        <router-link to="/" class="mobile-link" @click="mobileMenuOpen = false">
          <span class="link-icon">📊</span> Leaderboard
        </router-link>
        <router-link to="/score-entry" class="mobile-link" @click="mobileMenuOpen = false">
          <span class="link-icon">✏️</span> Update Scores
        </router-link>
        <router-link to="/players" class="mobile-link" @click="mobileMenuOpen = false">
          <span class="link-icon">👥</span> Players
        </router-link>
        <router-link to="/teams" class="mobile-link" @click="mobileMenuOpen = false">
          <span class="link-icon">🎯</span> Teams
        </router-link>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
const mobileMenuOpen = ref(false)
</script>

<style scoped>
.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  color: white;
}

.logo-icon {
  font-size: 2.5rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

.logo-text h1 {
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.5px;
  line-height: 1;
}

.tagline {
  font-size: 0.75rem;
  opacity: 0.9;
  font-weight: 400;
}

.nav-links {
  display: flex;
  gap: 0.5rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border-radius: 10px;
  text-decoration: none;
  color: white;
  font-weight: 600;
  transition: all 0.3s;
  background: rgba(255, 255, 255, 0.1);
  font-size: 0.95rem;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.nav-link.active {
  background: white;
  color: #667eea;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.link-icon {
  font-size: 1.2rem;
}

.mobile-menu-btn {
  display: none;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1.5rem;
}

.mobile-menu {
  display: none;
}

@media (max-width: 968px) {
  .nav-links {
    display: none;
  }

  .mobile-menu-btn {
    display: block;
  }

  .mobile-menu {
    display: flex;
    flex-direction: column;
    background: rgba(0, 0, 0, 0.2);
    padding: 1rem;
    gap: 0.5rem;
  }

  .mobile-link {
    padding: 1rem;
    color: white;
    text-decoration: none;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
  }

  .mobile-link:hover {
    background: rgba(255, 255, 255, 0.1);
  }
}

.slide-enter-active, .slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from, .slide-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>