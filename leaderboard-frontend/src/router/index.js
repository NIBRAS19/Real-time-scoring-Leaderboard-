import { createRouter, createWebHistory } from 'vue-router'
import LeaderboardPage from '../views/LeaderboardPage.vue'
import ScoreEntryPage from '../views/ScoreEntryPage.vue'
import PlayersPage from '../views/PlayersPage.vue'
import TeamsPage from '../views/TeamsPage.vue'
import PlayerDetailPage from '../views/PlayerDetailPage.vue'
import TeamDetailPage from '../views/TeamDetailPage.vue'

const routes = [
  {
    path: '/',
    name: 'Leaderboard',
    component: LeaderboardPage,
    meta: { title: 'Leaderboard' }
  },
  {
    path: '/score-entry',
    name: 'ScoreEntry',
    component: ScoreEntryPage,
    meta: { title: 'Update Scores' }
  },
  {
    path: '/players',
    name: 'Players',
    component: PlayersPage,
    meta: { title: 'Manage Players' }
  },
  {
    path: '/players/:id',
    name: 'PlayerDetail',
    component: PlayerDetailPage,
    meta: { title: 'Player Details' }
  },
  {
    path: '/teams',
    name: 'Teams',
    component: TeamsPage,
    meta: { title: 'Manage Teams' }
  },
  {
    path: '/teams/:id',
    name: 'TeamDetail',
    component: TeamDetailPage,
    meta: { title: 'Team Details' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  document.title = to.meta.title ? `${to.meta.title} - Leaderboard` : 'Leaderboard'
  next()
})

export default router