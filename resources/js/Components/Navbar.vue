<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => page.props.auth?.roles ?? []);
const can = (permission) => page.props.auth?.permissions?.includes(permission) ?? false;

const isStaff = computed(() => {
    return can('create books') || can('issue books') || roles.value.some(r => ['Admin', 'Librarian'].includes(r));
});

const isAdmin = computed(() => {
    return roles.value.includes('Admin') || can('manager users') || can('manage users');
});

const userRole = computed(() => {
    if (roles.value.length > 0) return roles.value[0];
    return isStaff.value ? 'Staff' : 'Member';
});

// Mobile menu toggle state
const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Check if current route is active
const isActive = (path) => {
    const currentUrl = page.url;
    if (path === '/dashboard') {
        return currentUrl === '/dashboard';
    }
    return currentUrl.startsWith(path);
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <!-- Brand / Logo -->
            <Link href="/dashboard" class="navbar-brand d-flex align-items-center fw-bold text-primary">
                <span class="fs-4 me-2">📚</span>
                <span>LibraryMS</span>
            </Link>

            <!-- Mobile Hamburger Toggler -->
            <button
                class="navbar-toggler border-0"
                type="button"
                aria-label="Toggle navigation"
                @click="toggleMobileMenu"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div
                class="collapse navbar-collapse"
                :class="{ 'show': isMobileMenuOpen }"
            >
                <!-- Main Navigation Links -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <Link
                            href="/dashboard"
                            class="nav-link px-3"
                            :class="{ 'active fw-bold text-primary': isActive('/dashboard') }"
                        >
                            📊 Dashboard
                        </Link>
                    </li>

                    <!-- Books -->
                    <li class="nav-item">
                        <Link
                            href="/books"
                            class="nav-link px-3"
                            :class="{ 'active fw-bold text-primary': isActive('/books') }"
                        >
                            📖 Books
                        </Link>
                    </li>

                    <!-- Categories -->
                    <li class="nav-item">
                        <Link
                            href="/categories"
                            class="nav-link px-3"
                            :class="{ 'active fw-bold text-primary': isActive('/categories') }"
                        >
                            🏷️ Categories
                        </Link>
                    </li>

                    <!-- Borrowings -->
                    <li class="nav-item">
                        <Link
                            href="/borrowings"
                            class="nav-link px-3"
                            :class="{ 'active fw-bold text-primary': isActive('/borrowings') }"
                        >
                            📋 Borrowings
                        </Link>
                    </li>

                    <!-- Users (Admin Only) -->
                    <li v-if="isAdmin" class="nav-item">
                        <Link
                            href="/users"
                            class="nav-link px-3"
                            :class="{ 'active fw-bold text-primary': isActive('/users') }"
                        >
                            👥 Users
                        </Link>
                    </li>

                    <!-- Profile (Mobile only item) -->
                    <li class="nav-item d-lg-none">
                        <Link
                            href="/profile"
                            class="nav-link px-3"
                            :class="{ 'active fw-bold text-primary': isActive('/profile') }"
                        >
                            👤 My Profile ({{ user?.name }})
                        </Link>
                    </li>
                </ul>

                <!-- Right Side: User Profile & Logout -->
                <div class="d-flex align-items-center gap-2 pt-2 pt-lg-0 border-top border-lg-0">
                    <!-- User Greeting & Profile Link (Clickable) -->
                    <Link
                        v-if="user"
                        href="/profile"
                        class="nav-profile-link text-decoration-none d-flex align-items-center py-1 px-2 rounded me-1"
                        :class="{ 'active-profile bg-light border': isActive('/profile') }"
                        title="Click to view and edit your profile"
                    >
                        <div
                            class="rounded-circle text-white d-inline-flex align-items-center justify-content-center me-2 flex-shrink-0 shadow-sm"
                            style="width: 34px; height: 34px; font-size: 0.9rem; font-weight: bold; background: linear-gradient(135deg, #0d6efd, #0dcaf0);"
                        >
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>

                        <div class="text-start me-2 d-none d-sm-block">
                            <span class="small text-muted d-block" style="font-size: 0.7rem; line-height: 1;">User Profile</span>
                            <strong class="text-dark">{{ user.name }}</strong>
                        </div>

                        <span
                            class="badge"
                            :class="{
                                'bg-danger': userRole === 'Admin',
                                'bg-info text-dark': userRole === 'Librarian',
                                'bg-success': userRole === 'Member'
                            }"
                        >
                            {{ userRole }}
                        </span>
                    </Link>

                    <!-- Logout Button -->
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm px-3"
                        @click="logout"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.nav-profile-link {
    transition: background-color 0.15s ease-in-out;
}
.nav-profile-link:hover {
    background-color: rgba(13, 110, 253, 0.08);
}
.navbar-nav .nav-link {
    transition: color 0.15s ease-in-out;
}
.navbar-nav .nav-link:hover {
    color: var(--bs-primary);
}
.navbar-nav .nav-link.active {
    border-bottom: 2px solid var(--bs-primary);
}
@media (max-width: 991.98px) {
    .navbar-nav .nav-link.active {
        border-bottom: none;
        border-left: 3px solid var(--bs-primary);
        padding-left: 0.75rem !important;
    }
}
</style>
