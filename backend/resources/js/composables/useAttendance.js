import { ref, computed } from 'vue';
import { attendanceService } from '../services/attendanceService';

export function useAttendance() {
    const attendanceRecords = ref([]);
    const todayStats = ref(null);
    const monthlyReport = ref(null);
    const loading = ref(false);
    const error = ref(null);

    // Attendance form data
    const attendanceData = ref({
        date: new Date().toISOString().split('T')[0],
        class: '',
        section: '',
        attendances: [],
    });

    /**
     * Record bulk attendance
     * @param {Object} data - Attendance data
     */
    const recordAttendance = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await attendanceService.recordBulk(data);
            return response;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to record attendance';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Fetch today's attendance statistics
     */
    const fetchTodayStats = async () => {
        loading.value = true;
        error.value = null;
        try {
            const data = await attendanceService.getTodayStats();
            todayStats.value = data.data;
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch today\'s statistics';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Fetch monthly attendance report
     * @param {number} month - Month (1-12)
     * @param {number} year - Year
     * @param {string} classId - Class identifier
     */
    const fetchMonthlyReport = async (month, year, classId) => {
        loading.value = true;
        error.value = null;
        try {
            const data = await attendanceService.getMonthlyReport(month, year, classId);
            monthlyReport.value = data.data;
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch monthly report';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Fetch attendance records with filters
     * @param {Object} params - Filter parameters
     */
    const fetchAttendance = async (params = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const data = await attendanceService.getAttendance(params);
            attendanceRecords.value = data.data;
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch attendance';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Fetch student attendance report
     * @param {number} studentId - Student ID
     * @param {string} startDate - Start date
     * @param {string} endDate - End date
     */
    const fetchStudentReport = async (studentId, startDate, endDate) => {
        loading.value = true;
        error.value = null;
        try {
            const data = await attendanceService.getStudentReport(studentId, startDate, endDate);
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch student report';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Initialize attendance data for students
     * @param {Array} students - Array of student objects
     */
    const initializeAttendanceData = (students) => {
        attendanceData.value.attendances = students.map(student => ({
            student_id: student.id,
            status: 'Present',
            note: '',
        }));
    };

    /**
     * Update attendance status for a student
     * @param {number} studentId - Student ID
     * @param {string} status - Attendance status (Present/Absent/Late)
     */
    const updateAttendanceStatus = (studentId, status) => {
        const attendance = attendanceData.value.attendances.find(
            a => a.student_id === studentId
        );
        if (attendance) {
            attendance.status = status;
        }
    };

    /**
     * Mark all students with the same status
     * @param {string} status - Attendance status
     */
    const markAllAs = (status) => {
        attendanceData.value.attendances.forEach(attendance => {
            attendance.status = status;
        });
    };

    /**
     * Calculate attendance percentage
     * @param {Array} attendances - Array of attendance records
     */
    const calculatePercentage = (attendances) => {
        if (!attendances || attendances.length === 0) return 0;
        const presentCount = attendances.filter(a => a.status === 'Present').length;
        return ((presentCount / attendances.length) * 100).toFixed(2);
    };

    /**
     * Get attendance statistics from current data
     */
    const getCurrentStats = computed(() => {
        const attendances = attendanceData.value.attendances;
        if (!attendances || attendances.length === 0) {
            return { present: 0, absent: 0, late: 0, total: 0, percentage: 0 };
        }

        const present = attendances.filter(a => a.status === 'Present').length;
        const absent = attendances.filter(a => a.status === 'Absent').length;
        const late = attendances.filter(a => a.status === 'Late').length;
        const total = attendances.length;
        const percentage = ((present / total) * 100).toFixed(2);

        return { present, absent, late, total, percentage };
    });

    return {
        attendanceRecords,
        todayStats,
        monthlyReport,
        attendanceData,
        loading,
        error,
        getCurrentStats,
        recordAttendance,
        fetchTodayStats,
        fetchMonthlyReport,
        fetchAttendance,
        fetchStudentReport,
        initializeAttendanceData,
        updateAttendanceStatus,
        markAllAs,
        calculatePercentage,
    };
}
