<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => page.props.auth?.roles ?? []);
const permissions = computed(() => page.props.auth?.permissions ?? []);

const userRole = computed(() => {
    if (roles.value.length > 0) return roles.value[0];
    return 'Member';
});

// Profile update form
const form = useForm({
    name: user.value?.name || '',
    email: user.value?.email || '',
});

const updateProfile = () => {
    form.patch('/profile');
};

// Password change form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put('/password');
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="My Profile" />

    <!-- Top Navigation Bar -->
    <Navbar />

    <div class="container py-4">
        <!-- Flash messages -->
        <div v-if="page.props.flash?.success" class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ page.props.flash.success }}
        </div>
        <div v-if="page.props.flash?.error" class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ page.props.flash.error }}
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h1 class="h2 mb-1">User Profile</h1>
                <p class="text-muted mb-0">View your account details and update your personal information or password.</p>
            </div>
            <Link href="/dashboard" class="btn btn-outline-secondary">
                &larr; Back to Dashboard
            </Link>
        </div>

        <div class="row g-4">
            <!-- Left Column: User View / Overview Card -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 5rem;">
                    <div class="card-body p-4 text-center">
                        <!-- Avatar / Initial -->
                        <div
                            class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-3 shadow-sm"
                            style="width: 80px; height: 80px; font-size: 2rem; background: linear-gradient(135deg, #0d6efd, #0dcaf0);"
                        >
                            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                        </div>

                        <h4 class="mb-1 fw-bold">{{ user?.name }}</h4>
                        <p class="text-muted small mb-2">{{ user?.email }}</p>

                        <!-- Role Badge -->
                        <div>
                            <span
                                class="badge fs-6 px-3 py-2"
                                :class="{
                                    'bg-danger': userRole === 'Admin',
                                    'bg-info text-dark': userRole === 'Librarian',
                                    'bg-success': userRole === 'Member'
                                }"
                            >
                                {{ userRole }}
                            </span>
                        </div>
                    </div>

                    <!-- Details List -->
                    <ul class="list-group list-group-flush border-top border-bottom small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="text-muted">Account ID</span>
                            <span class="fw-semibold">#{{ user?.id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="text-muted">Member Since</span>
                            <span class="fw-semibold">{{ formatDate(user?.created_at) }}</span>
                        </li>
                    </ul>

                    <!-- Assigned Permissions -->
                    <div v-if="permissions.length > 0" class="card-body p-4">
                        <h6 class="fw-semibold text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Assigned Privileges
                        </h6>
                        <div class="d-flex flex-wrap gap-1">
                            <span
                                v-for="perm in permissions"
                                :key="perm"
                                class="badge bg-light text-dark border small"
                            >
                                ✓ {{ perm }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Edit Forms -->
            <div class="col-lg-8">
                <!-- Card 1: Edit Profile Information -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">
                            ✏️ Edit Profile Information
                        </h5>
                        <!-- <small class="text-muted">
                            Update your account's display name and primary email address.
                        </small> -->
                    </div>

                    <div class="card-body p-4">
                        <form @submit.prevent="updateProfile">
                            <!-- Name -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.name }"
                                    required
                                >
                                <div v-if="form.errors.name" class="invalid-feedback">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.email }"
                                    required
                                >
                                <div v-if="form.errors.email" class="invalid-feedback">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <Link href="/dashboard" class="btn btn-light px-3">
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="btn btn-primary px-4"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save Profile Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card 2: Update Password -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">
                            🔒 Change Password
                        </h5>
                        <!-- <small class="text-muted">
                            Ensure your account is using a long, secure password.
                        </small> -->
                    </div>

                    <div class="card-body p-4">
                        <form @submit.prevent="updatePassword">
                            <!-- Current Password -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Password</label>
                                <input
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': passwordForm.errors.current_password }"
                                    autocomplete="current-password"
                                    required
                                >
                                <div v-if="passwordForm.errors.current_password" class="invalid-feedback">
                                    {{ passwordForm.errors.current_password }}
                                </div>
                            </div>

                            <!-- New Password -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">New Password</label>
                                <input
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': passwordForm.errors.password }"
                                    autocomplete="new-password"
                                    required
                                >
                                <div v-if="passwordForm.errors.password" class="invalid-feedback">
                                    {{ passwordForm.errors.password }}
                                </div>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': passwordForm.errors.password_confirmation }"
                                    autocomplete="new-password"
                                    required
                                >
                                <div v-if="passwordForm.errors.password_confirmation" class="invalid-feedback">
                                    {{ passwordForm.errors.password_confirmation }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <Link href="/dashboard" class="btn btn-light px-3">
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="btn btn-primary px-4"
                                    :disabled="passwordForm.processing"
                                >
                                    {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
