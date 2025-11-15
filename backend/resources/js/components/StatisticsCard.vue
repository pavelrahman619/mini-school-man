<template>
  <div class="statistics-card" :class="`card-${color}`">
    <div class="card-icon">
      <span class="icon">{{ icon }}</span>
    </div>
    <div class="card-content">
      <h3 class="card-title">{{ title }}</h3>
      <p class="card-value">{{ formattedValue }}</p>
      <p v-if="subtitle" class="card-subtitle">{{ subtitle }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  icon: {
    type: String,
    default: '📊'
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'red', 'yellow', 'purple'].includes(value)
  },
  subtitle: {
    type: String,
    default: null
  },
  format: {
    type: String,
    default: 'number',
    validator: (value) => ['number', 'percentage', 'text'].includes(value)
  }
});

const formattedValue = computed(() => {
  if (props.format === 'percentage') {
    return `${props.value}%`;
  }
  if (props.format === 'number' && typeof props.value === 'number') {
    return props.value.toLocaleString();
  }
  return props.value;
});
</script>

<style scoped>
.statistics-card {
  display: flex;
  align-items: center;
  padding: 1.5rem;
  border-radius: 0.75rem;
  background: white;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  transition: transform 0.2s;
}

.statistics-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.card-icon {
  flex-shrink: 0;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.75rem;
  margin-right: 1rem;
}

.icon {
  font-size: 2rem;
}

.card-blue .card-icon {
  background: #dbeafe;
}

.card-green .card-icon {
  background: #d1fae5;
}

.card-red .card-icon {
  background: #fee2e2;
}

.card-yellow .card-icon {
  background: #fef3c7;
}

.card-purple .card-icon {
  background: #ede9fe;
}

.card-content {
  flex: 1;
  min-width: 0;
}

.card-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin: 0 0 0.25rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.card-value {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.card-blue .card-value {
  color: #2563eb;
}

.card-green .card-value {
  color: #059669;
}

.card-red .card-value {
  color: #dc2626;
}

.card-yellow .card-value {
  color: #d97706;
}

.card-purple .card-value {
  color: #7c3aed;
}

.card-subtitle {
  font-size: 0.75rem;
  color: #9ca3af;
  margin: 0.25rem 0 0 0;
}
</style>
