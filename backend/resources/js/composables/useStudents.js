import { ref, computed } from 'vue';
import { studentService } from '../services/studentService';

export function useStudents() {
    const students = ref([]);
    const student = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    });

    // Filters
    const filters = ref({
        search: '',
        class: '',
        section: '',
    });

    /**
     * Fetch students with filters and pagination
     * @param {number} page - Page number
     */
    const fetchStudents = async (page = 1) => {
        loading.value = true;
        error.value = null;
        try {
            const params = {
                page,
                ...filters.value,
            };
            const data = await studentService.getAll(params);
            students.value = data.data;
            pagination.value = {
                current_page: data.current_page,
                last_page: data.last_page,
                per_page: data.per_page,
                total: data.total,
            };
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch students';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Fetch single student by ID
     * @param {number} id - Student ID
     */
    const fetchStudent = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const data = await studentService.getById(id);
            student.value = data.data;
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch student';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Create new student
     * @param {FormData|Object} studentData - Student data
     */
    const createStudent = async (studentData) => {
        loading.value = true;
        error.value = null;
        try {
            const data = await studentService.create(studentData);
            await fetchStudents(pagination.value.current_page);
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create student';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Update existing student
     * @param {number} id - Student ID
     * @param {FormData|Object} studentData - Updated student data
     */
    const updateStudent = async (id, studentData) => {
        loading.value = true;
        error.value = null;
        try {
            const data = await studentService.update(id, studentData);
            await fetchStudents(pagination.value.current_page);
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update student';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Delete student
     * @param {number} id - Student ID
     */
    const deleteStudent = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const data = await studentService.delete(id);
            await fetchStudents(pagination.value.current_page);
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete student';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Set filter values
     * @param {Object} newFilters - Filter values
     */
    const setFilters = (newFilters) => {
        filters.value = { ...filters.value, ...newFilters };
    };

    /**
     * Clear all filters
     */
    const clearFilters = () => {
        filters.value = {
            search: '',
            class: '',
            section: '',
        };
    };

    /**
     * Search students by name or student_id
     * @param {string} query - Search query
     */
    const searchStudents = async (query) => {
        filters.value.search = query;
        await fetchStudents(1);
    };

    /**
     * Filter students by class
     * @param {string} className - Class name
     */
    const filterByClass = async (className) => {
        filters.value.class = className;
        await fetchStudents(1);
    };

    // Computed properties
    const hasStudents = computed(() => students.value.length > 0);
    const totalPages = computed(() => pagination.value.last_page);
    const currentPage = computed(() => pagination.value.current_page);

    return {
        students,
        student,
        loading,
        error,
        pagination,
        filters,
        hasStudents,
        totalPages,
        currentPage,
        fetchStudents,
        fetchStudent,
        createStudent,
        updateStudent,
        deleteStudent,
        setFilters,
        clearFilters,
        searchStudents,
        filterByClass,
    };
}
