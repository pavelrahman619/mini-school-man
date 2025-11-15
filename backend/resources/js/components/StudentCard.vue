<template>
  <div class="student-card" @click="$emit('click', student)">
    <div class="student-photo">
      <img 
        v-if="student.photo_url" 
        :src="student.photo_url" 
        :alt="student.name"
        class="photo-img"
      />
      <div v-else class="photo-placeholder">
        <span class="initials">{{ getInitials(student.name) }}</span>
      </div>
    </div>
    <div class="student-info">
      <h3 class="student-name">{{ student.name }}</h3>
      <p class="student-id">ID: {{ student.student_id }}</p>
      <p class="student-class">{{ student.class }} - {{ student.section }}</p>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  student: {
    type: Object,
    required: true
  }
});

defineEmits(['click']);

const getInitials = (name) => {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};
</script>

<style scoped>
.student-card {
  display: flex;
  align-items: center;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.student-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border-color: #3b82f6;
}

.student-photo {
  flex-shrink: 0;
  width: 60px;
  height: 60px;
  margin-right: 1rem;
}

.photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.photo-placeholder {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.initials {
  color: white;
  font-size: 1.25rem;
  font-weight: 600;
}

.student-info {
  flex: 1;
  min-width: 0;
}

.student-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.student-id,
.student-class {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0.125rem 0;
}
</style>
