<template>
  <div class="attendance-marker">
    <label 
      v-for="status in statuses" 
      :key="status.value"
      class="status-option"
      :class="{ 'active': modelValue === status.value }"
    >
      <input
        type="radio"
        :name="`attendance-${studentId}`"
        :value="status.value"
        :checked="modelValue === status.value"
        @change="handleChange(status.value)"
        class="status-input"
      />
      <span class="status-label" :class="`status-${status.value.toLowerCase()}`">
        {{ status.label }}
      </span>
    </label>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  studentId: {
    type: Number,
    required: true
  },
  modelValue: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['update:modelValue', 'status-changed']);

const statuses = [
  { value: 'Present', label: 'Present' },
  { value: 'Absent', label: 'Absent' },
  { value: 'Late', label: 'Late' }
];

const handleChange = (value) => {
  emit('update:modelValue', value);
  emit('status-changed', { studentId: props.studentId, status: value });
};
</script>

<style scoped>
.attendance-marker {
  display: flex;
  gap: 0.5rem;
}

.status-option {
  position: relative;
  cursor: pointer;
}

.status-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.status-label {
  display: inline-block;
  padding: 0.5rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s;
  user-select: none;
}

.status-option:hover .status-label {
  border-color: #d1d5db;
}

.status-option.active .status-label {
  border-color: currentColor;
  color: white;
}

.status-present {
  color: #10b981;
}

.status-option.active .status-present {
  background: #10b981;
  border-color: #10b981;
}

.status-absent {
  color: #ef4444;
}

.status-option.active .status-absent {
  background: #ef4444;
  border-color: #ef4444;
}

.status-late {
  color: #f59e0b;
}

.status-option.active .status-late {
  background: #f59e0b;
  border-color: #f59e0b;
}
</style>
