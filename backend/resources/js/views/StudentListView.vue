<template>
  <div class="student-list-view">
    <div class="page-header">
      <h1 class="page-title">Students</h1>
      <p class="page-subtitle">Manage student records</p>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
      <div class="search-box">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by name or student ID..."
          class="search-input"
          @input="handleSearch"
        />
        <span class="search-icon">🔍</span>
      </div>

      <div class="filter-controls">
        <select
          v-model="selectedClass"
          class="filter-select"
          @change="handleClassFilter"
        >
          <option value="">All Classes</option>
          <option value="Class 10A">Class 10A</option>
          <option value="Class 10B">Class 10B</option>
          <option value="Class 10C">Class 10C</option>
          <option value="Class 9A">Class 9A</option>
          <option value="Class 9B">Class 9B</option>
        </select>

        <button
          v-if="searchQuery || selectedClass"
          @click="clearFilters"
          class="clear-filters-button"
        >
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading students...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <p class="error-message">{{ error }}</p>
      <button @click="loadStudents" class="retry-button">Retry</button>
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasStudents" class="empty-state">
      <span class="empty-icon">👨‍🎓</span>
      <p class="empty-message">No students found</p>
      <p class="empty-hint">
        {{ searchQuery || selectedClass ? 'Try adjusting your filters' : 'No students have been added yet' }}
      </p>
    </div>

    <!-- Students Grid -->
    <div v-else class="students-content">
      <div class="students-grid">
        <StudentCard
          v-for="student in students"
          :key="student.id"
          :student="student"
          @click="handleStudentClick"
        />
      </div>

      <!-- Pagination -->
      <Pagination
        v-if="totalPages > 1"
        :current-page="currentPage"
        :total-pages="totalPages"
        :per-page="pagination.per_page"
        :total="pagination.total"
        @page-changed="handlePageChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useStudents } from '../composables/useStudents';
import StudentCard from '../components/StudentCard.vue';
import Pagination from '../components/Pagination.vue';

const {
  students,
  loading,
  error,
  pagination,
  filters,
  hasStudents,
  totalPages,
  currentPage,
  fetchStudents,
  setFilters,
  clearFilters: clearStudentFilters,
  searchStudents,
  filterByClass,
} = useStudents();

const searchQuery = ref('');
const selectedClass = ref('');

let searchTimeout = null;

const handleSearch = () => {
  // Debounce search
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    searchStudents(searchQuery.value);
  }, 500);
};

const handleClassFilter = () => {
  filterByClass(selectedClass.value);
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedClass.value = '';
  clearStudentFilters();
  loadStudents();
};

const handlePageChange = (page) => {
  fetchStudents(page);
  // Scroll to top
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleStudentClick = (student) => {
  console.log('Student clicked:', student);
  // Could navigate to student detail page or open modal
};

const loadStudents = () => {
  fetchStudents(1);
};

onMounted(() => {
  loadStudents();
});
</script>

<style scoped>
.student-list-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin: 0;
}

.filters-section {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1rem;
}

.filter-controls {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  background: white;
  cursor: pointer;
  transition: border-color 0.2s;
}

.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.clear-filters-button {
  padding: 0.75rem 1rem;
  background: #f3f4f6;
  color: #374151;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.clear-filters-button:hover {
  background: #e5e7eb;
}

.loading-state,
.error-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  color: #6b7280;
  font-size: 1rem;
}

.error-message {
  color: #dc2626;
  font-size: 1rem;
  margin-bottom: 1rem;
}

.retry-button {
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.retry-button:hover {
  background: #2563eb;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-message {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
}

.empty-hint {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.students-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.students-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .student-list-view {
    padding: 1rem;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .filters-section {
    flex-direction: column;
  }

  .search-box {
    min-width: 100%;
  }

  .filter-controls {
    width: 100%;
    flex-direction: column;
  }

  .filter-select,
  .clear-filters-button {
    width: 100%;
  }

  .students-grid {
    grid-template-columns: 1fr;
  }
}
</style>
