import api from './api';

export const studentService = {
    /**
     * Get all students with optional filters
     * @param {Object} params - { page, search, class, section }
     * @returns {Promise}
     */
    async getAll(params = {}) {
        const response = await api.get('/students', { params });
        return response.data;
    },

    /**
     * Get single student by ID
     * @param {number} id - Student ID
     * @returns {Promise}
     */
    async getById(id) {
        const response = await api.get(`/students/${id}`);
        return response.data;
    },

    /**
     * Create new student
     * @param {FormData|Object} data - Student data
     * @returns {Promise}
     */
    async create(data) {
        const config = data instanceof FormData 
            ? { headers: { 'Content-Type': 'multipart/form-data' } }
            : {};
        const response = await api.post('/students', data, config);
        return response.data;
    },

    /**
     * Update existing student
     * @param {number} id - Student ID
     * @param {FormData|Object} data - Updated student data
     * @returns {Promise}
     */
    async update(id, data) {
        const config = data instanceof FormData 
            ? { headers: { 'Content-Type': 'multipart/form-data' } }
            : {};
        const response = await api.post(`/students/${id}`, data, config);
        return response.data;
    },

    /**
     * Delete student
     * @param {number} id - Student ID
     * @returns {Promise}
     */
    async delete(id) {
        const response = await api.delete(`/students/${id}`);
        return response.data;
    },
};
