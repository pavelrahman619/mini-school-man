<template>
  <div class="dashboard-view">
    <div class="dashboard-header">
      <h1 class="page-title">Dashboard</h1>
      <p class="page-subtitle">Today's Attendance Overview</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading dashboard data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <p class="error-message">{{ error }}</p>
      <button @click="loadDashboardData" class="retry-button">Retry</button>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="dashboard-content">
      <!-- Today's Statistics -->
      <div class="stats-grid">
        <StatisticsCard
          title="Total Students"
          :value="todayStats?.total || 0"
          icon="👥"
          color="blue"
        />
        <StatisticsCard
          title="Present"
          :value="todayStats?.present || 0"
          icon="✅"
          color="green"
          :subtitle="`${todayStats?.present_percentage || 0}% of total`"
        />
        <StatisticsCard
          title="Absent"
          :value="todayStats?.absent || 0"
          icon="❌"
          color="red"
          :subtitle="`${todayStats?.absent_percentage || 0}% of total`"
        />
        <StatisticsCard
          title="Late"
          :value="todayStats?.late || 0"
          icon="⏰"
          color="yellow"
          :subtitle="`${todayStats?.late_percentage || 0}% of total`"
        />
      </div>

      <!-- Monthly Attendance Chart -->
      <div class="chart-section">
        <AttendanceChart
          :data="chartData"
          :title="`Monthly Attendance - ${currentMonthName} ${currentYear}`"
          subtitle="Daily attendance breakdown for the current month"
        />
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
          <router-link to="/attendance" class="action-card">
            <span class="action-icon">📝</span>
            <span class="action-label">Record Attendance</span>
          </router-link>
          <router-link to="/students" class="action-card">
            <span class="action-icon">👨‍🎓</span>
            <span class="action-label">View Students</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAttendance } from '../composables/useAttendance';
import StatisticsCard from '../components/StatisticsCard.vue';
import AttendanceChart from '../components/AttendanceChart.vue';

const { todayStats, monthlyReport, loading, error, fetchTodayStats, fetchMonthlyReport } = useAttendance();

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;

const currentMonthName = computed(() => {
  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  return monthNames[currentMonth - 1];
});

// Transform monthly report data for chart
const chartData = computed(() => {
  if (!monthlyReport.value || !monthlyReport.value.daily_stats) {
    return { labels: [], present: [], absent: [], late: [] };
  }

  const dailyStats = monthlyReport.value.daily_stats;
  const labels = [];
  const present = [];
  const absent = [];
  const late = [];

  // Sort by date and extract data
  Object.keys(dailyStats)
    .sort()
    .forEach(date => {
      const day = new Date(date).getDate();
      labels.push(`Day ${day}`);
      present.push(dailyStats[date].present || 0);
      absent.push(dailyStats[date].absent || 0);
      late.push(dailyStats[date].late || 0);
    });

  return { labels, present, absent, late };
});

const loadDashboardData = async () => {
  try {
    // Fetch today's statistics
    await fetchTodayStats();
    
    // Fetch monthly report for current month (all classes)
    await fetchMonthlyReport(currentMonth, currentYear, '');
  } catch (err) {
    console.error('Failed to load dashboard data:', err);
  }
};

onMounted(() => {
  loadDashboardData();
});
</script>

<style scoped>
.dashboard-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
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

.loading-state,
.error-state {
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

.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.chart-section {
  margin-top: 1rem;
}

.quick-actions {
  margin-top: 1rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1rem 0;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 0.75rem;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
}

.action-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.action-icon {
  font-size: 3rem;
  margin-bottom: 0.75rem;
}

.action-label {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

@media (max-width: 768px) {
  .dashboard-view {
    padding: 1rem;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
