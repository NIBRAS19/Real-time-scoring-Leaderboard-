<template>
  <transition name="modal">
    <div v-if="show" class="modal-overlay" @click="onCancel">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <div class="modal-icon" :class="type">
            {{ iconMap[type] }}
          </div>
          <h3>{{ title }}</h3>
        </div>
        
        <div class="modal-body">
          <p>{{ message }}</p>
        </div>
        
        <div class="modal-footer">
          <button class="btn-cancel" @click="onCancel">
            Cancel
          </button>
          <button class="btn-confirm" :class="type" @click="onConfirm">
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['warning', 'danger', 'info'].includes(value)
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const iconMap = {
  warning: '⚠️',
  danger: '🗑️',
  info: 'ℹ️'
}

const onConfirm = () => {
  emit('confirm')
}

const onCancel = () => {
  emit('cancel')
}
</script>

<style scoped>
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
  max-width: 500px;
  width: 90%;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  padding: 2rem 2rem 1rem;
  text-align: center;
}

.modal-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.modal-header h3 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin: 0;
}

.modal-body {
  padding: 0 2rem 2rem;
  text-align: center;
}

.modal-body p {
  font-size: 1.1rem;
  color: #666;
  line-height: 1.6;
  margin: 0;
}

.modal-footer {
  padding: 1.5rem 2rem;
  display: flex;
  gap: 1rem;
  border-top: 2px solid #f0f0f0;
}

.btn-cancel, .btn-confirm {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-cancel {
  background: #f0f0f0;
  color: #666;
}

.btn-cancel:hover {
  background: #e0e0e0;
}

.btn-confirm {
  color: white;
}

.btn-confirm.warning {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.btn-confirm.danger {
  background: linear-gradient(135deg, #ff6b6b 0%, #c92a2a 100%);
}

.btn-confirm.info {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
</style>
