<script setup>
import { ref, onMounted, computed } from 'vue';
import AxiosInstance from '@/api/axios';
import Adduser from '@/components/AddUser.vue';

const axiosInstance = AxiosInstance();

// Data variables
const tableData = ref([]);
const searchQuery = ref('');
const perPageOptions = [5, 10, 15, 20];
const selectedPerPage = ref(perPageOptions[0]);
const currentPage = ref(1);
const totalPages = ref(1);
const isArray = ref(false);

// Fetch users from API with pagination
const getUsers = async (page = 1) => {
    try {
        const response = await axiosInstance.get(`/v1/auth/users?page=${page}&perPage=${selectedPerPage.value}`);

        if (response.data && response.data.dataPayload) {
            tableData.value = response.data.dataPayload.data.profiles || [];
            currentPage.value = response.data.dataPayload.currentPage || 1;
            totalPages.value = response.data.dataPayload.totalPages || 1;
        }

        isArray.value = Array.isArray(tableData.value);
    } catch (error) {
        console.error('Error fetching users:', error);
    }
};

// Handle items per page change
const updatePerPage = () => {
    getUsers(1); // Fetch data for the first page with the new perPage value
};

// Perform search (if search query is used)
const performSearch = () => {
    getUsers(1);
};

// Pagination logic
const goToPage = (page) => {
    if (page > 0 && page <= totalPages.value) {
        getUsers(page);
    }
};

// Computed data for sorting
const sortedData = computed(() => {
    return tableData.value.filter((item) => {
        return (
            item.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            item.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            item.email.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    });
});

// Fetch users on mount
onMounted(() => {
    getUsers(1);
});

const addUserModal = ref(null);
const showModal = () => {
    addUserModal.value.$refs.addUserModal.show();
};
</script>
<template>
    <b-col lg="12">
        <b-card class="h-100">
            <div>
                <h2>Users</h2>
            </div>
            <b-row class="mb-3">
                <b-col lg="12" md="12" sm="12" class="mb-3">
                    <div class="d-flex justify-content-end">
                        <b-button variant="primary" @click="showModal">
                            Add User
                        </b-button>
                    </div>
                </b-col>
                <b-col lg="12" md="12" sm="12">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <label for="itemsPerPage" class="me-2">Items per page:</label>
                            <select id="itemsPerPage" v-model="selectedPerPage" @change="updatePerPage"
                                class="form-select form-select-sm" style="width: auto;">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center">
                            <b-input-group>
                                <!-- Search Input -->
                                <b-form-input placeholder="Search..." aria-label="Search" v-model="searchQuery" />
                                <!-- Search Button -->
                                <b-input-group-append>
                                    <b-button variant="primary" @click="performSearch">
                                        Search
                                    </b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </div>
                    </div>
                </b-col>
            </b-row>
            <div class="table-responsive">
                <!-- Table Section -->
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th @click="sortTable('first_name')">FirstName <i class="fas fa-sort"></i></th>
                            <th @click="sortTable('last_name')">LastName <i class="fas fa-sort"></i></th>
                            <th @click="sortTable('mobile_number')">Contact <i class="fas fa-sort"></i></th>
                            <th @click="sortTable('email')">Email <i class="fas fa-sort"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="isArray && sortedData.length > 0">
                            <tr v-for="(item, index) in sortedData" :key="index">
                                <td>{{ item.first_name }}</td>
                                <td>{{ item.last_name }}</td>
                                <td>{{ item.mobile_number }}</td>
                                <td>{{ item.email }}</td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="5" class="text-center">No data to display</td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination -->

            </div>
            <nav aria-label="Page navigation" class="mb-n5 mt-5">
                <ul class="pagination justify-content-end">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <button class="page-link" @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1">Previous</button>
                    </li>
                    <li v-for="page in totalPages" :key="page" class="page-item"
                        :class="{ active: currentPage === page }">
                        <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <button class="page-link" @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages">Next</button>
                    </li>
                </ul>
            </nav>
        </b-card>
    </b-col>
    <Adduser ref="addUserModal" />
</template>
