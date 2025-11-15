import api from './api';

export const attendanceService = {
    /**
     * Record bulk attendance for multiple students
     * @param {Object} data - { date, class, section, attendances: [{ student_id, status, note }] }
     * @returns {Promise}
     */
    async recordBulk(data) {
        const response = await api.post('/attendance/bulk', data);
        return response.data;
    },

    /**
     * Get today's attendance statistics
     * @returns {Promise}
     */
    async getTodayStats() {
        const response = await api.get('/attendance/today');
        return response.data;
    },

    /**
     * Get monthly attendance report
     * @param {number} month - Month (1-12)
     * @param {number} year - Year
     * @param {string} classId - Class identifier
     * @returns {Promise}
     */
    async getMonthlyReport(month, year, classId) {
        const response = await api.get('/attendance/monthly-report', {
            params: { month, year, class: classId }
        });
        return response.data;
    },

    /**
     * Get attendance records with filters
     * @param {Object} params - { date, class, section, status, page }
     * @returns {Promise}
     */
    async getAttendance(params = {}) {
        const response = await api.get('/attendance', { params });
        return response.data;
    },

    /**
     * Get individual student attendance report
     * @param {number} studentId - Student ID
     * @param {string} startDate - Start date (YYYY-MM-DD)
     * @param {string} endDate - End date (YYYY-MM-DD)
     * @returns {Promise}
     */
    async getStudentReport(studentId, startDate, endDate) {
        const response = await api.get(`/attendance/student/${studentId}`, {
            params: { startDate, endDate }
        });
        return response.data;
    },
};
