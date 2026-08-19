// src/services/api.js

import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000/api/v1",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    console.log("📤 API Request:", config.method.toUpperCase(), config.url);
    return config;
  },
  (error) => {
    console.error("❌ Request Error:", error);
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => {
    console.log("📥 API Response:", response.status, response.data);
    return response;
  },
  (error) => {
    console.error("❌ Response Error:", error.response || error);
    return Promise.reject(error);
  }
);

// Player API
export const playerApi = {
  getAll(params = {}) {
    return api.get("/players", { params });
  },

  getById(id) {
    return api.get(`/players/${id}`);
  },

  create(data) {
    // Use FormData for file upload
    const formData = new FormData();
    Object.keys(data).forEach((key) => {
      if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key]);
      }
    });
    return api.post("/players", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
  },

  update(id, data) {
    const formData = new FormData();
    Object.keys(data).forEach((key) => {
      if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key]);
      }
    });
    formData.append("_method", "PUT");
    return api.post(`/players/${id}`, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
  },

  delete(id) {
    return api.delete(`/players/${id}`);
  },

  getHistory(id) {
    return api.get(`/players/${id}/history`);
  },
};

// Score API
export const scoreApi = {
  update(playerId, points, reason = null, updatedBy = null) {
    return api.post(`/players/${playerId}/score`, {
      points,
      reason,
      updated_by: updatedBy,
    });
  },

  reset(playerId) {
    return api.post(`/players/${playerId}/score/reset`);
  },
};

// Team API
export const teamApi = {
  getAll() {
    return api.get("/teams");
  },

  getById(id) {
    return api.get(`/teams/${id}`);
  },

  create(data) {
    return api.post("/teams", data);
  },

  update(id, data) {
    return api.put(`/teams/${id}`, data);
  },

  delete(id) {
    return api.delete(`/teams/${id}`);
  },

  getLeaderboard(id) {
    return api.get(`/teams/${id}/leaderboard`);
  },
};

export default api;
