<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    allRoles: {
        type: Array,
        default: () => [],
    },
    allPermissions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

// Search & Filter State
const searchQuery = ref(props.filters?.search || '');
const activeRoleTab = ref('');

// Filtered Users
const filteredUsers = computed(() => {
    return props.users.filter((u) => {
        const matchesSearch =
            !searchQuery.value ||
            u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            u.email.toLowerCase().includes(searchQuery.value.toLowerCase());

        const matchesRole =
            !activeRoleTab.value ||
            u.roles.includes(activeRoleTab.value);

        return matchesSearch && matchesRole;
    });
});

// Role Counts
const totalCount = computed(() => props.users.length);
const adminsCount = computed(() => props.users.filter(u => u.roles.includes('Admin')).length);
const librariansCount = computed(() => props.users.filter(u => u.roles.includes('Librarian')).length);
const membersCount = computed(() => props.users.filter(u => u.roles.includes('Member')).length);

// Permission groupings & labels
const permissionConfig = {
    'view books': { label: 'View Books', group: 'Books' },
    'create books': { label: 'Add Books', group: 'Books' },
    'edit books': { label: 'Edit Books', group: 'Books' },
    'delete books': { label: 'Delete Books', group: 'Books' },
    'issue books': { label: 'Issue Books', group: 'Circulation' },
    'return books': { label: 'Return Books', group: 'Circulation' },
    'view borrowing history': { label: 'View History', group: 'Circulation' },
    'manager users': { label: 'Manage Users', group: 'Administration' },
};

// Default role permissions
const roleDefaultPermissions = {
    Admin: [
        'view books', 'create books', 'edit books', 'delete books',
        'issue books', 'return books', 'manager users', 'view borrowing history'
    ],
    Librarian: [
        'view books', 'create books', 'edit books',
        'issue books', 'return books', 'view borrowing history'
    ],
    Member: [
        'view books', 'view borrowing history'
    ],
};

// Date Formatter
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const cleanStr = typeof dateStr === 'string' ? dateStr.split('T')[0] : dateStr;
    const parts = String(cleanStr).split('-');
    if (parts.length === 3) {
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const day = parseInt(parts[2], 10);
        const d = new Date(year, month, day);
        return d.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Initials Avatar generator
const getInitials = (name) => {
    if (!name) return 'U';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Expanded permissions row state
const expandedRows = ref({});
const toggleRowExpand = (id) => {
    expandedRows.value[id] = !expandedRows.value[id];
};

// -------------------------------------------------------------
// EDIT USER & PERMISSIONS MODAL
// -------------------------------------------------------------
const editingUser = ref(null);
const showEditModal = ref(false);

const editForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'Member',
    permissions: [],
});

const openEditModal = (u) => {
    editingUser.value = u;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = u.name;
    editForm.email = u.email;
    editForm.password = '';
    editForm.role = u.roles[0] || 'Member';
    editForm.permissions = [...(u.all_permissions || [])];
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingUser.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const selectEditRole = (roleName) => {
    editForm.role = roleName;
    if (roleDefaultPermissions[roleName]) {
        editForm.permissions = [...roleDefaultPermissions[roleName]];
    }
};

const toggleEditPermission = (perm) => {
    const idx = editForm.permissions.indexOf(perm);
    if (idx > -1) {
        editForm.permissions.splice(idx, 1);
    } else {
        editForm.permissions.push(perm);
    }
};

const selectAllEditPermissions = () => {
    editForm.permissions = [...props.allPermissions];
};

const clearAllEditPermissions = () => {
    editForm.permissions = [];
};

const submitEdit = () => {
    if (!editingUser.value) return;
    editForm.put(`/users/${editingUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

// -------------------------------------------------------------
// CREATE USER MODAL
// -------------------------------------------------------------
const showCreateModal = ref(false);
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
    permissions: [],
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    createForm.name = '';
    createForm.email = '';
    createForm.password = '';
    createForm.role = '';
    createForm.permissions = [];
    showCreateModal.value = true;
    nextTick(() => {
        createForm.email = '';
        createForm.password = '';
    });
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
    createForm.clearErrors();
    createForm.role = '';
    createForm.permissions = [];
};

const selectCreateRole = (roleName) => {
    createForm.clearErrors('role');
    createForm.role = roleName;
    if (roleDefaultPermissions[roleName]) {
        createForm.permissions = [...roleDefaultPermissions[roleName]];
    }
};

const toggleCreatePermission = (perm) => {
    const idx = createForm.permissions.indexOf(perm);
    if (idx > -1) {
        createForm.permissions.splice(idx, 1);
    } else {
        createForm.permissions.push(perm);
    }
};

const selectAllCreatePermissions = () => {
    createForm.permissions = [...props.allPermissions];
};

const clearAllCreatePermissions = () => {
    createForm.permissions = [];
};

const submitCreate = () => {
    if (!createForm.role) {
        createForm.setError('role', 'Please select a primary role.');
        return;
    }
    createForm.post('/users', {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
        },
    });
};

// Lock background scrolling when a modal is open
const isAnyModalOpen = computed(() => showCreateModal.value || showEditModal.value);

watch(isAnyModalOpen, (isOpen) => {
    if (typeof document !== 'undefined') {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
});

onBeforeUnmount(() => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
});

// -------------------------------------------------------------
// DELETE USER
// -------------------------------------------------------------
const deleteForm = useForm({});
const deleteUser = (u) => {
    if (u.id === currentUser.value?.id) {
        alert('You cannot delete your own account.');
        return;
    }
    if (confirm(`Are you sure you want to delete user "${u.name}" (${u.email})?\n\nThis will remove their profile and access permissions.`)) {
        deleteForm.delete(`/users/${u.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Users & Permissions" />

    <Navbar />

    <div class="user-mgmt-container py-4">
        <!-- Flash messages -->
        <div v-if="page.props.flash?.success" class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div>{{ page.props.flash.success }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div v-if="page.props.flash?.error" class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>{{ page.props.flash.error }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="page-title mb-1">Users & Access Control</h2>
                <!-- <p class="text-muted small mb-0">
                    Manage system accounts, assign primary roles, and configure individual permissions.
                </p> -->
            </div>
            <div>
                <button
                    type="button"
                    class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 shadow-sm font-medium"
                    @click="openCreateModal"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <span>Add User</span>
                </button>
            </div>
        </div>

        <!-- Metric KPI Cards Bar -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div
                    class="kpi-card"
                    :class="{ 'kpi-card-active': activeRoleTab === '' }"
                    @click="activeRoleTab = ''"
                >
                    <div class="kpi-label">Total Users</div>
                    <div class="kpi-value text-dark">{{ totalCount }}</div>
                    <div class="kpi-sub">All registered accounts</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div
                    class="kpi-card"
                    :class="{ 'kpi-card-active': activeRoleTab === 'Admin' }"
                    @click="activeRoleTab = activeRoleTab === 'Admin' ? '' : 'Admin'"
                >
                    <div class="kpi-label d-flex align-items-center gap-1">
                        <span class="role-indicator bg-danger"></span>
                        <span>Administrators</span>
                    </div>
                    <div class="kpi-value text-danger">{{ adminsCount }}</div>
                    <div class="kpi-sub">Full system control</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div
                    class="kpi-card"
                    :class="{ 'kpi-card-active': activeRoleTab === 'Librarian' }"
                    @click="activeRoleTab = activeRoleTab === 'Librarian' ? '' : 'Librarian'"
                >
                    <div class="kpi-label d-flex align-items-center gap-1">
                        <span class="role-indicator bg-primary"></span>
                        <span>Librarians</span>
                    </div>
                    <div class="kpi-value text-primary">{{ librariansCount }}</div>
                    <div class="kpi-sub">Catalog & loan staff</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div
                    class="kpi-card"
                    :class="{ 'kpi-card-active': activeRoleTab === 'Member' }"
                    @click="activeRoleTab = activeRoleTab === 'Member' ? '' : 'Member'"
                >
                    <div class="kpi-label d-flex align-items-center gap-1">
                        <span class="role-indicator bg-success"></span>
                        <span>Members</span>
                    </div>
                    <div class="kpi-value text-success">{{ membersCount }}</div>
                    <div class="kpi-sub">Standard borrowers</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <!-- Left: Interactive Filter Pills -->
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <button
                            type="button"
                            class="filter-pill"
                            :class="{ 'filter-pill-active': activeRoleTab === '' }"
                            @click="activeRoleTab = ''"
                        >
                            All Roles <span class="pill-badge">{{ totalCount }}</span>
                        </button>
                        <button
                            type="button"
                            class="filter-pill"
                            :class="{ 'filter-pill-active': activeRoleTab === 'Admin' }"
                            @click="activeRoleTab = 'Admin'"
                        >
                            <span class="role-dot bg-danger"></span> Admin <span class="pill-badge">{{ adminsCount }}</span>
                        </button>
                        <button
                            type="button"
                            class="filter-pill"
                            :class="{ 'filter-pill-active': activeRoleTab === 'Librarian' }"
                            @click="activeRoleTab = 'Librarian'"
                        >
                            <span class="role-dot bg-primary"></span> Librarian <span class="pill-badge">{{ librariansCount }}</span>
                        </button>
                        <button
                            type="button"
                            class="filter-pill"
                            :class="{ 'filter-pill-active': activeRoleTab === 'Member' }"
                            @click="activeRoleTab = 'Member'"
                        >
                            <span class="role-dot bg-success"></span> Member <span class="pill-badge">{{ membersCount }}</span>
                        </button>
                    </div>

                    <!-- Right: Search Input -->
                    <div class="search-box-wrapper" style="min-width: 280px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                            <input
                                v-model="searchQuery"
                                type="text"
                                class="form-control border-start-0"
                                placeholder="Search by name or email..."
                            >
                            <button
                                v-if="searchQuery"
                                type="button"
                                class="btn btn-outline-secondary border-start-0"
                                @click="searchQuery = ''"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0 user-table">
                    <thead>
                        <tr>
                            <th class="ps-4" style="min-width: 220px;">User</th>
                            <th style="min-width: 130px;">Role</th>
                            <th style="min-width: 330px;">Assigned Permissions</th>
                            <th class="text-center" style="min-width: 90px;">Loans</th>
                            <th style="min-width: 120px;">Joined</th>
                            <th class="text-end pe-4" style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in filteredUsers" :key="u.id" class="user-row">
                            <!-- User Name & Email -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="user-avatar flex-shrink-0 me-3"
                                        :class="{
                                            'avatar-admin': u.roles.includes('Admin'),
                                            'avatar-librarian': u.roles.includes('Librarian'),
                                            'avatar-member': !u.roles.includes('Admin') && !u.roles.includes('Librarian')
                                        }"
                                    >
                                        {{ getInitials(u.name) }}
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="user-name">{{ u.name }}</span>
                                            <span v-if="u.id === currentUser?.id" class="badge-you">YOU</span>
                                        </div>
                                        <span class="user-email">{{ u.email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td>
                                <span
                                    v-if="u.roles.includes('Admin')"
                                    class="role-badge role-admin"
                                >
                                    <span class="role-badge-dot dot-admin"></span>
                                    <span>Admin</span>
                                </span>
                                <span
                                    v-else-if="u.roles.includes('Librarian')"
                                    class="role-badge role-librarian"
                                >
                                    <span class="role-badge-dot dot-librarian"></span>
                                    <span>Librarian</span>
                                </span>
                                <span
                                    v-else
                                    class="role-badge role-member"
                                >
                                    <span class="role-badge-dot dot-member"></span>
                                    <span>Member</span>
                                </span>
                            </td>

                            <!-- Assigned Permissions -->
                            <td>
                                <!-- Admin Full Access Indicator -->
                                <div v-if="u.all_permissions?.length === props.allPermissions.length" class="d-inline-flex align-items-center gap-1.5">
                                    <span class="perm-pill perm-pill-admin">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        All Permissions (8)
                                    </span>
                                </div>

                                <!-- Granular Permissions -->
                                <div v-else class="d-flex flex-wrap gap-1 align-items-center">
                                    <span
                                        v-for="p in (expandedRows[u.id] ? u.all_permissions : u.all_permissions.slice(0, 3))"
                                        :key="p"
                                        class="perm-pill"
                                        :class="{
                                            'perm-pill-books': permissionConfig[p]?.group === 'Books',
                                            'perm-pill-circ': permissionConfig[p]?.group === 'Circulation',
                                            'perm-pill-admin': permissionConfig[p]?.group === 'Administration'
                                        }"
                                    >
                                        {{ permissionConfig[p]?.label || p }}
                                    </span>

                                    <button
                                        v-if="u.all_permissions?.length > 3"
                                        type="button"
                                        class="btn-expand-more"
                                        @click="toggleRowExpand(u.id)"
                                    >
                                        {{ expandedRows[u.id] ? 'Show less' : `+${u.all_permissions.length - 3} more` }}
                                    </button>

                                    <span v-if="!u.all_permissions || u.all_permissions.length === 0" class="text-muted small fst-italic">
                                        No permissions
                                    </span>
                                </div>
                            </td>

                            <!-- Loans Count -->
                            <td class="text-center">
                                <span class="loans-count">{{ u.borrowings_count }}</span>
                            </td>

                            <!-- Joined Date -->
                            <td>
                                <span class="joined-date">{{ formatDate(u.created_at) }}</span>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary action-btn"
                                        title="Edit user details & permissions"
                                        @click="openEditModal(u)"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <button
                                        v-if="u.id !== currentUser?.id"
                                        type="button"
                                        class="btn btn-outline-danger action-btn"
                                        title="Delete user"
                                        :disabled="deleteForm.processing"
                                        @click="deleteUser(u)"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state-icon mb-2">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">No users found</h6>
                                <p class="text-muted small mb-3">No matching results for your current filters.</p>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    @click="searchQuery = ''; activeRoleTab = ''"
                                >
                                    Reset Filters
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    Showing <strong>{{ filteredUsers.length }}</strong> of <strong>{{ totalCount }}</strong> users
                </span>
                <span class="text-muted small">
                    Standard 3-Role Library RBAC Hierarchy
                </span>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- EDIT USER & PERMISSIONS MODAL -->
        <!-- ============================================================== -->
        <div
            v-if="showEditModal"
            class="modal-backdrop-custom"
            @click.self="closeEditModal"
        >
            <div class="modal-dialog-custom modal-lg-custom">
                <div class="modal-content-custom">
                    <!-- Modal Header -->
                    <div class="modal-header-custom">
                        <div>
                            <h5 class="modal-title-custom">Edit User & Permissions</h5>
                            <p class="modal-sub-custom mb-0">
                                Configure credentials, role assignment, and access permissions for <strong>{{ editingUser?.name }}</strong>.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Close"
                            @click="closeEditModal"
                        ></button>
                    </div>

                    <!-- Modal Body / Form -->
                    <form class="modal-form-custom" @submit.prevent="submitEdit">
                        <div class="modal-body-custom">
                            <!-- Section 1: Account Information -->
                            <div class="section-heading mb-3">
                                <span>1. Account Information</span>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-custom">Full Name <span class="text-danger">*</span></label>
                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': editForm.errors.name }"
                                        required
                                    >
                                    <div v-if="editForm.errors.name" class="invalid-feedback">
                                        {{ editForm.errors.name }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Email Address <span class="text-danger">*</span></label>
                                    <input
                                        v-model="editForm.email"
                                        type="email"
                                        class="form-control"
                                        :class="{ 'is-invalid': editForm.errors.email }"
                                        required
                                    >
                                    <div v-if="editForm.errors.email" class="invalid-feedback">
                                        {{ editForm.errors.email }}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">
                                        Password
                                        <span class="text-muted fw-normal">(Leave blank to keep existing password)</span>
                                    </label>
                                    <input
                                        v-model="editForm.password"
                                        type="password"
                                        class="form-control"
                                        :class="{ 'is-invalid': editForm.errors.password }"
                                        placeholder="Enter new 8+ character password..."
                                    >
                                    <div v-if="editForm.errors.password" class="invalid-feedback">
                                        {{ editForm.errors.password }}
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Role Selection -->
                            <div class="d-flex justify-content-between align-items-center section-heading mb-3">
                                <span>2. Primary Role Assignment</span>
                                <span class="text-muted" style="font-size: 0.75rem; text-transform: none; font-weight: normal;">
                                    Selecting a role updates default permissions automatically
                                </span>
                            </div>

                            <div class="row g-3 mb-4">
                                <div
                                    v-for="r in ['Admin', 'Librarian', 'Member']"
                                    :key="r"
                                    class="col-md-4"
                                >
                                    <div
                                        class="role-select-card"
                                        :class="{ 'role-select-card-active': editForm.role === r }"
                                        @click="selectEditRole(r)"
                                    >
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="role-select-title" :class="`role-text-${r.toLowerCase()}`">
                                                {{ r }}
                                            </span>
                                            <input
                                                :id="`edit-role-${r}`"
                                                v-model="editForm.role"
                                                type="radio"
                                                name="edit-role"
                                                :value="r"
                                                class="form-check-input mt-0"
                                            >
                                        </div>
                                        <p class="role-select-desc mb-0">
                                            <span v-if="r === 'Admin'">Full control over catalog, loans, and users.</span>
                                            <span v-else-if="r === 'Librarian'">Manage books, issues, returns, and fines.</span>
                                            <span v-else>Standard borrower: view books and active loans.</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Fine-Grained Permissions -->
                            <div class="d-flex justify-content-between align-items-center section-heading mb-3">
                                <span>3. Permissions ({{ editForm.permissions.length }}/{{ props.allPermissions.length }})</span>
                                <div class="btn-group btn-group-sm">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary py-0.5 px-2 font-medium"
                                        style="font-size: 0.75rem;"
                                        @click="selectAllEditPermissions"
                                    >
                                        Select All
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary py-0.5 px-2 font-medium"
                                        style="font-size: 0.75rem;"
                                        @click="clearAllEditPermissions"
                                    >
                                        Clear All
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary py-0.5 px-2 font-medium"
                                        style="font-size: 0.75rem;"
                                        @click="selectEditRole(editForm.role)"
                                    >
                                        Role Defaults
                                    </button>
                                </div>
                            </div>

                            <div class="permissions-container">
                                <!-- Books -->
                                <div class="perm-group-header">
                                    <span>Books Management</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div
                                        v-for="p in ['view books', 'create books', 'edit books', 'delete books']"
                                        :key="p"
                                        class="col-sm-6"
                                    >
                                        <label class="perm-item" :class="{ 'perm-item-active': editForm.permissions.includes(p) }">
                                            <input
                                                type="checkbox"
                                                class="form-check-input mt-0 me-2"
                                                :checked="editForm.permissions.includes(p)"
                                                @change="toggleEditPermission(p)"
                                            >
                                            <span class="perm-label">{{ permissionConfig[p]?.label || p }}</span>
                                            <span class="perm-code text-muted ms-auto font-monospace">{{ p }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Circulation -->
                                <div class="perm-group-header">
                                    <span>Circulation & Lending</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div
                                        v-for="p in ['issue books', 'return books', 'view borrowing history']"
                                        :key="p"
                                        class="col-sm-6"
                                    >
                                        <label class="perm-item" :class="{ 'perm-item-active': editForm.permissions.includes(p) }">
                                            <input
                                                type="checkbox"
                                                class="form-check-input mt-0 me-2"
                                                :checked="editForm.permissions.includes(p)"
                                                @change="toggleEditPermission(p)"
                                            >
                                            <span class="perm-label">{{ permissionConfig[p]?.label || p }}</span>
                                            <span class="perm-code text-muted ms-auto font-monospace">{{ p }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Administration -->
                                <div class="perm-group-header">
                                    <span>Administration & Security</span>
                                </div>
                                <div class="row g-2">
                                    <div
                                        v-for="p in ['manager users']"
                                        :key="p"
                                        class="col-sm-6"
                                    >
                                        <label class="perm-item" :class="{ 'perm-item-active': editForm.permissions.includes(p) }">
                                            <input
                                                type="checkbox"
                                                class="form-check-input mt-0 me-2"
                                                :checked="editForm.permissions.includes(p)"
                                                @change="toggleEditPermission(p)"
                                            >
                                            <span class="perm-label">{{ permissionConfig[p]?.label || p }}</span>
                                            <span class="perm-code text-muted ms-auto font-monospace">{{ p }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer-custom">
                            <button
                                type="button"
                                class="btn btn-light px-3"
                                @click="closeEditModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary px-4 font-medium"
                                :disabled="editForm.processing"
                            >
                                <span v-if="editForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- ADD USER MODAL -->
        <!-- ============================================================== -->
        <div
            v-if="showCreateModal"
            class="modal-backdrop-custom"
            @click.self="closeCreateModal"
        >
            <div class="modal-dialog-custom modal-lg-custom">
                <div class="modal-content-custom">
                    <!-- Modal Header -->
                    <div class="modal-header-custom">
                        <div>
                            <h5 class="modal-title-custom">Add New User</h5>
                            <p class="modal-sub-custom mb-0">
                                Create an account with credentials, a primary role, and permissions.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Close"
                            @click="closeCreateModal"
                        ></button>
                    </div>

                    <!-- Modal Body / Form -->
                    <form class="modal-form-custom" autocomplete="off" @submit.prevent="submitCreate">
                        <!-- Hidden inputs to absorb aggressive browser autofill -->
                        <input type="text" style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0;" tabindex="-1" autocomplete="off" />
                        <input type="password" style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0;" tabindex="-1" autocomplete="new-password" />

                        <div class="modal-body-custom">
                            <!-- Section 1: Account Information -->
                            <div class="section-heading mb-3">
                                <span>1. Account Credentials</span>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-custom">Full Name <span class="text-danger">*</span></label>
                                    <input
                                        v-model="createForm.name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': createForm.errors.name }"
                                        autocomplete="off"
                                        required
                                        autofocus
                                    >
                                    <div v-if="createForm.errors.name" class="invalid-feedback">
                                        {{ createForm.errors.name }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Email Address <span class="text-danger">*</span></label>
                                    <input
                                        v-model="createForm.email"
                                        type="email"
                                        name="new_user_email"
                                        class="form-control"
                                        :class="{ 'is-invalid': createForm.errors.email }"
                                        autocomplete="off"
                                        required
                                    >
                                    <div v-if="createForm.errors.email" class="invalid-feedback">
                                        {{ createForm.errors.email }}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Password <span class="text-danger">*</span></label>
                                    <input
                                        v-model="createForm.password"
                                        type="password"
                                        name="new_user_password"
                                        class="form-control"
                                        :class="{ 'is-invalid': createForm.errors.password }"
                                        autocomplete="new-password"
                                        required
                                    >
                                    <div v-if="createForm.errors.password" class="invalid-feedback">
                                        {{ createForm.errors.password }}
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Role Selection -->
                            <div class="section-heading mb-3">
                                <span>2. Primary Role <span class="text-danger">*</span></span>
                            </div>
                            <div class="row g-3 mb-4">
                                <div
                                    v-for="r in ['Admin', 'Librarian', 'Member']"
                                    :key="r"
                                    class="col-md-4"
                                >
                                    <div
                                        class="role-select-card"
                                        :class="{ 'role-select-card-active': createForm.role === r }"
                                        @click="selectCreateRole(r)"
                                    >
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="role-select-title" :class="`role-text-${r.toLowerCase()}`">
                                                 {{ r }}
                                            </span>
                                            <input
                                                :id="`create-role-${r}`"
                                                v-model="createForm.role"
                                                type="radio"
                                                name="create-role"
                                                :value="r"
                                                class="form-check-input mt-0"
                                            >
                                        </div>
                                        <p class="role-select-desc mb-0">
                                            <span v-if="r === 'Admin'">Full control over catalog, loans, and users.</span>
                                            <span v-else-if="r === 'Librarian'">Manage books, issues, returns, and fines.</span>
                                            <span v-else>Standard borrower: view books and active loans.</span>
                                        </p>
                                    </div>
                                </div>
                                <div v-if="createForm.errors.role" class="col-12 text-danger small mt-1">
                                    {{ createForm.errors.role }}
                                </div>
                            </div>

                            <!-- Section 3: Fine-Grained Permissions -->
                            <div class="d-flex justify-content-between align-items-center section-heading mb-3">
                                <span>3. Permissions ({{ createForm.permissions.length }}/{{ props.allPermissions.length }})</span>
                                <div class="btn-group btn-group-sm">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary py-0.5 px-2 font-medium"
                                        style="font-size: 0.75rem;"
                                        @click="selectAllCreatePermissions"
                                    >
                                        Select All
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary py-0.5 px-2 font-medium"
                                        style="font-size: 0.75rem;"
                                        @click="clearAllCreatePermissions"
                                    >
                                        Clear All
                                    </button>
                                </div>
                            </div>

                            <div class="permissions-container">
                                <div class="row g-2">
                                    <div
                                        v-for="p in props.allPermissions"
                                        :key="p"
                                        class="col-sm-6"
                                    >
                                        <label class="perm-item" :class="{ 'perm-item-active': createForm.permissions.includes(p) }">
                                            <input
                                                type="checkbox"
                                                class="form-check-input mt-0 me-2"
                                                :checked="createForm.permissions.includes(p)"
                                                @change="toggleCreatePermission(p)"
                                            >
                                            <span class="perm-label">{{ permissionConfig[p]?.label || p }}</span>
                                            <span class="perm-code text-muted ms-auto font-monospace">{{ p }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer-custom">
                            <button
                                type="button"
                                class="btn btn-light px-3"
                                @click="closeCreateModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary px-4 font-medium"
                                :disabled="createForm.processing"
                            >
                                <span v-if="createForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                                Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.user-mgmt-container {
    max-width: 1200px;
    margin: 0 auto;
    padding-left: 1rem;
    padding-right: 1rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
}

/* KPI Summary Cards */
.kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 1.15rem;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}
.kpi-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.kpi-card-active {
    border-color: #3b82f6 !important;
    background: #f8fafc;
    box-shadow: 0 0 0 1px #3b82f6;
}
.kpi-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.25rem;
}
.kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0.15rem;
}
.kpi-sub {
    font-size: 0.72rem;
    color: #94a3b8;
}
.role-indicator {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

/* Filter Pills */
.filter-pill {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    cursor: pointer;
    transition: all 0.12s ease-in-out;
}
.filter-pill:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}
.filter-pill-active {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
}
.filter-pill-active .pill-badge {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}
.pill-badge {
    background: #f1f5f9;
    color: #475569;
    padding: 0.1rem 0.45rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 600;
}
.role-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}

/* Table Styles */
.user-table {
    border-collapse: separate;
    border-spacing: 0;
}
.user-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding-top: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #e2e8f0;
}
.user-row {
    transition: background-color 0.1s ease;
}
.user-row:hover {
    background-color: #f8fafc;
}
.user-row td {
    padding-top: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
}

/* Avatar */
.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}
.avatar-admin {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.avatar-librarian {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.avatar-member {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}

.user-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #0f172a;
}
.user-email {
    font-size: 0.78rem;
    color: #64748b;
    display: block;
}
.badge-you {
    font-size: 0.65rem;
    font-weight: 700;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 0.1rem 0.35rem;
    letter-spacing: 0.04em;
}

/* Standard Role Badges */
.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
}
.role-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.role-admin {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.dot-admin {
    background: #dc2626;
}
.role-librarian {
    background: #f0f9ff;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.dot-librarian {
    background: #0284c7;
}
.role-member {
    background: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
}
.dot-member {
    background: #10b981;
}

/* Permission Pills */
.perm-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: 500;
    background: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
}
.perm-pill-books {
    background: #f8fafc;
    color: #1e40af;
    border-color: #dbeafe;
}
.perm-pill-circ {
    background: #f8fafc;
    color: #065f46;
    border-color: #a7f3d0;
}
.perm-pill-admin {
    background: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
}

.btn-expand-more {
    background: none;
    border: none;
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.1rem 0.35rem;
    cursor: pointer;
    border-radius: 4px;
}
.btn-expand-more:hover {
    color: #0f172a;
    background: #f1f5f9;
}

.loans-count {
    display: inline-block;
    font-weight: 600;
    color: #0f172a;
    font-size: 0.85rem;
}
.joined-date {
    font-size: 0.8rem;
    color: #64748b;
}

.action-btn {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.6rem;
    border-radius: 5px;
}

/* Modal Custom Styles */
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 1.25rem 1rem;
    overflow-y: auto;
    overscroll-behavior: contain;
}
.modal-dialog-custom {
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    margin: auto;
}
.modal-lg-custom {
    max-width: 760px;
}
.modal-content-custom {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
}
.modal-form-custom {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
}
.modal-header-custom {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-shrink: 0;
}
.modal-title-custom {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.15rem;
}
.modal-sub-custom {
    font-size: 0.8rem;
    color: #64748b;
}
.modal-body-custom {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1 1 auto;
    min-height: 0;
    overscroll-behavior: contain;
}
.modal-body-custom::-webkit-scrollbar {
    width: 6px;
}
.modal-body-custom::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.modal-body-custom::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.modal-body-custom::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.modal-footer-custom {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
    flex-shrink: 0;
}

.section-heading {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 0.4rem;
}

.form-label-custom {
    font-size: 0.82rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.3rem;
}

/* Role Select Cards in Modal */
.role-select-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.85rem;
    cursor: pointer;
    transition: all 0.12s ease;
    height: 100%;
}
.role-select-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.role-select-card-active {
    border-color: #3b82f6 !important;
    background: #eff6ff !important;
    box-shadow: 0 0 0 1px #3b82f6;
}
.role-select-title {
    font-size: 0.85rem;
    font-weight: 700;
}
.role-select-desc {
    font-size: 0.72rem;
    color: #64748b;
    line-height: 1.35;
}
.role-text-admin {
    color: #dc2626;
}
.role-text-librarian {
    color: #0284c7;
}
.role-text-member {
    color: #10b981;
}

/* Permissions Selection Grid */
.permissions-container {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
}
.perm-group-header {
    font-size: 0.72rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.45rem;
}
.perm-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.45rem 0.65rem;
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-bottom: 0;
    transition: all 0.1s ease;
}
.perm-item:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.perm-item-active {
    border-color: #93c5fd;
    background: #eff6ff;
}
.perm-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #1e293b;
}
.perm-code {
    font-size: 0.65rem;
}
</style>
