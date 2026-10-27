<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => page.props.auth?.roles ?? []);
const can = (permission) => page.props.auth?.permissions?.includes(permission) ?? false;

const isStaff = computed(() => {
    return can('issue books') || can('create books') || roles.value.some(r => ['Admin', 'Librarian'].includes(r));
});

const isAdmin = computed(() => {
    return roles.value.includes('Admin') || can('manager users') || can('manage users');
});

const userRole = computed(() => {
    if (roles.value.length > 0) return roles.value[0];
    return isStaff.value ? 'Staff' : 'Member';
});
</script>

<template>
    <Head title="Dashboard" />

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

        <div class="mb-4 pb-3 border-bottom">
            <h1 class="h2 mb-1">Library Dashboard</h1>
            <p v-if="user" class="text-muted mb-0">
                Welcome, <strong>{{ user.name }}</strong>
                <span
                    class="badge ms-2"
                    :class="{
                        'bg-danger': userRole === 'Admin',
                        'bg-info text-dark': userRole === 'Librarian',
                        'bg-success': userRole === 'Member'
                    }"
                >
                    {{ userRole }}
                </span>
            </p>
        </div>

        <!-- Role-specific cards -->
        <div class="row g-4">
            <!-- Books Card -->
            <div :class="isStaff ? 'col-lg-4 col-md-6' : 'col-md-6'">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 bg-primary-subtle text-primary rounded-3 me-3 fs-5">
                                📖
                            </div>
                            <h5 class="card-title mb-0">
                                {{ isStaff ? 'Book Management' : 'Book Catalog' }}
                            </h5>
                        </div>
                        <p class="card-text text-muted flex-grow-1">
                            {{ isStaff 
                                ? 'Add new books, select categories from dropdown, update stock, and edit details.' 
                                : 'Explore available books filtered by category and place an order to borrow.' 
                            }}
                        </p>
                        <Link href="/books" class="btn btn-primary mt-2">
                            {{ isStaff ? 'Manage Books' : 'Browse & Order Books' }} &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Categories Card (Staff Only) -->
            <div v-if="isStaff" class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 me-3 fs-5">
                                🏷️
                            </div>
                            <h5 class="card-title mb-0">
                                Categories
                            </h5>
                        </div>
                        <p class="card-text text-muted flex-grow-1">
                            Add, organize, and manage book categories so books can be selected from the category dropdown.
                        </p>
                        <Link href="/categories" class="btn btn-warning mt-2">
                            Manage Categories &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Borrowings / Orders Card -->
            <div :class="isStaff ? 'col-lg-4 col-md-6' : 'col-md-6'">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 bg-success-subtle text-success rounded-3 me-3 fs-5">
                                📋
                            </div>
                            <h5 class="card-title mb-0">
                                {{ isStaff ? 'Borrowing Records' : 'My Orders & Borrowings' }}
                            </h5>
                        </div>
                        <p class="card-text text-muted flex-grow-1">
                            {{ isStaff 
                                ? 'Issue books to members, monitor loan deadlines, process returns, and track overdue fines.' 
                                : 'Check your active book orders, due dates, return statuses, and borrowing history.' 
                            }}
                        </p>
                        <Link href="/borrowings" class="btn btn-success mt-2">
                            {{ isStaff ? 'Manage Borrowings' : 'View My Orders' }} &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Users & Permissions Card (Admin Only) -->
            <div v-if="isAdmin" class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 bg-danger-subtle text-danger rounded-3 me-3 fs-5">
                                👥
                            </div>
                            <h5 class="card-title mb-0">
                                Users & Permissions
                            </h5>
                        </div>
                        <p class="card-text text-muted flex-grow-1">
                            View registered users and their permissions, assign roles, and grant or customize system permissions.
                        </p>
                        <Link href="/users" class="btn btn-outline-danger mt-2">
                            Manage Users &rarr;
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
