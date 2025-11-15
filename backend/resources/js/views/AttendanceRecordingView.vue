<template>
  <div class="attendance-recording-view">
    <div class="page-header">
      <h1 class="page-title">Record Attendance</h1>
      <p class="page-subtitle">Mark student attendance for today</p>
    </div>

    <!-- Class Selection -->
    <div class="selection-section">
      <div class="selection-card">
        <div class="selection-row">
          <div class="form-group">
            <label for="date" class="form-label">Date</label>
            <input
              id="date"
              v-model="attendanceData.date"
              type="date"
              class="form-input"
              :max="today"
            />
          </div>

          <div class="form-group">
            <label for="class" class="form-label">Class</label>
            <select
              id="class"
              v-model="attendanceData.class"
              class="form-select"
              @change="handleClassChange"
            >
              <option value="">Select Class</option>
              <option value="Class 10A">Class 10A</option>
              <option value="Class 10B">Class 10B</option>
              <option value="Class 10C">Class 10C</option>
              <option value="Class 9A">Class 9A</option>
              <option value="Class 9B">Class 9B</option>
            </select>
          </div>

          <div class="form-group">
            <label for="section" class="form-label">Section</label>
            <select
              id="section"
              v-model="attendanceData.section"
              class="form-select"
              @change="loadStudents"
            >
              <option value="">Select Section</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
            </select>
          </div>

          <div class="form-group">
            <button
              @click="loadStudents"
              :disabled="!attendanceData.class || !attendanceData.section || studentsLoading"
              class="load-button"
            >
              {{ studentsLoading ? 'Loading...' : 'Load Students' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="studentsForAttendance.length > 0" class="bulk-actions-section">
      <div class="bulk-actions-card">
        <h3 class="bulk-actions-title">Quick Actions</h3>
        <div class="bulk-buttons">
          <button @click="markAllAs('Present')" class="bulk-button present">
            Mark All Present
          </button>
          <button @click="markAllAs('Absent')" class="bulk-button absent">
            Mark All Absent
          </button>
          <button @click="markAllAs('Late')" class="bulk-button late">
            Mark All Late
          </button>
        </div>
      </div>

      <!-- Real-time Statistics -->
      <div class="stats-card">
        <h3 class="stats-title">Current Statistics</h3>
        <div class="stats-row">
          <div class="stat-item">
            <span class="stat-label">Total:</span>
            <span class="stat-value">{{ getCurrentStats.total }}</span>
          </div>
          <div class="stat-item present">
            <span class="stat-label">Present:</span>
            <span class="stat-value">{{ getCurrentStats.present }}</span>
          </div>
          <div class="stat-item absent">
            <span class="stat-label">Absent:</span>
            <span class="stat-value">{{ getCurrentStats.absent }}</span>
          </div>
          <div class="stat-item late">
            <span class="stat-label">Late:</span>
            <span class="stat-value">{{ getCurrentStats.late }}</span>
          </div>
          <div class="stat-item percentage">
            <span class="stat-label">Attendance:</span>
            <span class="stat-value">{{ getCurrentStats.percentage }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Students Loading -->
    <div v-if="studentsLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading students...</p>
    </div>

    <!-- Students Error -->
    <div v-else-if="studentsError" class="error-state">
      <p class="error-message">{{ studentsError }}</p>
      <button @click="loadStudents" class="retry-button">Retry</button>
    </div>

    <!-- No Students Selected -->
    <div v-else-if="studentsForAttendance.length === 0" class="empty-state">
      <span class="empty-icon">📋</span>
      <p class="empty-message">No students loaded</p>
      <p class="empty-hint">Select a class and section to load students</p>
    </div>

    <!-- Students List -->
    <div v-else class="students-section">
      <div class="students-header">
        <h2 class="students-title">
          Students ({{ studentsForAttendance.length }})
        </h2>
      </div>

      <div class="students-list">
        <div
          v-for="student in studentsForAttendance"
          :key="student.id"
          class="student-attendance-row"
        >
          <div class="student-info-compact">
            <div class="student-photo-small">
              <img
                v-if="student.photo_url"
                :src="student.photo_url"
                :alt="student.name"
                class="photo-img-small"
              />
              <div v-else class="photo-placeholder-small">
                <span class="initials-small">{{ getInitials(student.name) }}</span>
              </div>
            </div>
            <div class="student-details">
              <p class="student-name-compact">{{ student.name }}</p>
              <p class="student-id-compact">{{ student.student_id }}</p>
            </div>
          </div>

          <AttendanceMarker
            :student-id="student.id"
            :model-value="getStudentStatus(student.id)"
            @update:model-value="(status) => updateAttendanceStatus(student.id, status)"
          />
        </div>
      </div>

      <!-- Submit Button -->
      <div class="submit-section">
        <button
          @click="handleSubmit"
          :disabled="submitting || studentsForAttendance.length === 0"
          class="submit-button"
        >
          {{ submitting ? 'Submitting...' : 'Submit Attendance' }}
        </button>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <!-- Error Message -->
      <div v-if="submitError" class="error-message-box">
        {{ submitError }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useStudents } from '../composables/useStudents';
import { useAttendance } from '../composables/useAttendance';
import AttendanceMarker from '../components/AttendanceMarker.vue';

const {
  students: studentsForAttendance,
  loading: studentsLoading,
  error: studentsError,
  fetchStudents,
  setFilters,
} = useStudents();

const {
  attendanceData,
  loading: submitting,
  error: submitError,
  getCurrentStats,
  recordAttendance,
  initializeAttendanceData,
  updateAttendanceStatus,
  markAllAs,
} = useAttendance();

const today = new Date().toISOString().split('T')[0];
const successMessage = ref('');

const handleClassChange = () => {
  // Reset section when class changes
  attendanceData.value.section = '';
  studentsForAttendance.value = [];
  attendanceData.value.attendances = [];
};

const loadStudents = async () => {
  if (!attendanceData.value.class || !attendanceData.value.section) {
    return;
  }

  successMessage.value = '';
  submitError.value = null;

  try {
    // Set filters for class and section
    setFilters({
      class: attendanceData.value.class,
      section: attendanceData.value.section,
    });

    // Fetch students
    await fetchStudents(1);

    // Initialize attendance data for loaded students
    if (studentsForAttendance.value.length > 0) {
      initializeAttendanceData(studentsForAttendance.value);
    }
  } catch (err) {
    console.error('Failed to load students:', err);
  }
};

const getStudentStatus = (studentId) => {
  const attendance = attendanceData.value.attendances.find(
    (a) => a.student_id === studentId
  );
  return attendance?.status || 'Present';
};

const getInitials = (name) => {
  return name
    .split(' ')
    .map((word) => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const handleSubmit = async () => {
  successMessage.value = '';
  submitError.value = null;

  try {
    const response = await recordAttendance({
      date: attendanceData.value.date,
      class: attendanceData.value.class,
      section: attendanceData.value.section,
      attendances: attendanceData.value.attendances,
    });

    successMessage.value = `Attendance recorded successfully for ${response.data.recorded_count} students!`;

    // Scroll to top to show success message
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Clear success message after 5 seconds
    setTimeout(() => {
      successMessage.value = '';
    }, 5000);
  } catch (err) {
    console.error('Failed to submit attendance:', err);
    submitError.value = err.response?.data?.message || 'Failed to submit attendance';
  }
};

onMounted(() => {
  // Set default date to today
  attendanceData.value.date = today;
});
</script>

<style scoped>
.attendance-recording-view {
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

.selection-section {
  margin-bottom: 2rem;
}

.selection-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.selection-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  align-items: end;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-input,
.form-select {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.load-button {
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

.load-button:hover:not(:disabled) {
  background: #2563eb;
}

.load-button:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.bulk-actions-section {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 1rem;
  margin-bottom: 2rem;
}

.bulk-actions-card,
.stats-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.bulk-actions-title,
.stats-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1rem 0;
}

.bulk-buttons {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.bulk-button {
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  color: white;
}

.bulk-button.present {
  background: #10b981;
}

.bulk-button.present:hover {
  background: #059669;
}

.bulk-button.absent {
  background: #ef4444;
}

.bulk-button.absent:hover {
  background: #dc2626;
}

.bulk-button.late {
  background: #f59e0b;
}

.bulk-button.late:hover {
  background: #d97706;
}

.stats-row {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.stat-item.present .stat-value {
  color: #10b981;
}

.stat-item.absent .stat-value {
  color: #ef4444;
}

.stat-item.late .stat-value {
  color: #f59e0b;
}

.stat-item.percentage .stat-value {
  color: #3b82f6;
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
  to {
    transform: rotate(360deg);
  }
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

.students-section {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.students-header {
  margin-bottom: 1.5rem;
}

.students-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.students-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.student-attendance-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  transition: border-color 0.2s;
}

.student-attendance-row:hover {
  border-color: #d1d5db;
}

.student-info-compact {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.student-photo-small {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
}

.photo-img-small {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.photo-placeholder-small {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.initials-small {
  color: white;
  font-size: 1rem;
  font-weight: 600;
}

.student-details {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.student-name-compact {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.student-id-compact {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.submit-section {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
}

.submit-button {
  padding: 1rem 3rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-button:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.submit-button:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

.success-message {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #d1fae5;
  border: 1px solid #10b981;
  border-radius: 0.5rem;
  color: #065f46;
  text-align: center;
  font-weight: 500;
}

.error-message-box {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #fee2e2;
  border: 1px solid #ef4444;
  border-radius: 0.5rem;
  color: #991b1b;
  text-align: center;
  font-weight: 500;
}

@media (max-width: 768px) {
  .attendance-recording-view {
    padding: 1rem;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .selection-row {
    grid-template-columns: 1fr;
  }

  .bulk-actions-section {
    grid-template-columns: 1fr;
  }

  .bulk-buttons {
    flex-direction: column;
  }

  .bulk-button {
    width: 100%;
  }

  .stats-row {
    flex-direction: column;
    gap: 1rem;
  }

  .student-attendance-row {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .submit-button {
    width: 100%;
  }
}
</style>
