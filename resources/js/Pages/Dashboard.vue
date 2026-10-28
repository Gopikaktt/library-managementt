<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    memberStats: {
        type: Object,
        default: () => null,
    },
    popularBooks: {
        type: Array,
        default: () => [],
    },
    outOfStockBooks: {
        type: Array,
        default: () => [],
    },
    lowStockBooks: {
        type: Array,
        default: () => [],
    },
    trends: {
        type: Array,
        default: () => [],
    },
    categoryDistribution: {
        type: Array,
        default: () => [],
    },
    recentBorrowings: {
        type: Array,
        default: () => [],
    },
    isStaff: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => page.props.auth?.roles ?? []);
const can = (permission) => page.props.auth?.permissions?.includes(permission) ?? false;

const isStaffUser = computed(() => {
    return props.isStaff || can('issue books') || can('create books') || roles.value.some(r => ['Admin', 'Librarian'].includes(r));
});

const isAdminUser = computed(() => {
    return roles.value.includes('Admin') || can('manager users') || can('manage users');
});

const userRole = computed(() => {
    if (roles.value.length > 0) return roles.value[0];
    return isStaffUser.value ? 'Staff' : 'Member';
});

// Canvas references
const trendsCanvas = ref(null);
const categoryCanvas = ref(null);
let trendsChartInstance = null;
let categoryChartInstance = null;

// Inventory Tab state (All Alerts vs Out of Stock vs Low Stock)
const inventoryTab = ref('out_of_stock');

onMounted(() => {
    initTrendsChart();
    initCategoryChart();
});

onBeforeUnmount(() => {
    if (trendsChartInstance) {
        trendsChartInstance.destroy();
        trendsChartInstance = null;
    }
    if (categoryChartInstance) {
        categoryChartInstance.destroy();
        categoryChartInstance = null;
    }
});

const initTrendsChart = () => {
    if (!trendsCanvas.value) return;

    const labels = props.trends.map(t => t.label);
    const borrowedData = props.trends.map(t => t.borrowed);
    const returnedData = props.trends.map(t => t.returned);

    const ctx = trendsCanvas.value.getContext('2d');

    trendsChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Books Borrowed',
                    data: borrowedData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Books Returned',
                    data: returnedData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        font: { size: 12, family: 'Inter, system-ui, sans-serif', weight: '500' },
                        color: '#475569',
                    },
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#94a3b8',
                        font: { size: 11 },
                    },
                    grid: {
                        color: '#f1f5f9',
                    },
                },
                x: {
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                    },
                    grid: {
                        display: false,
                    },
                },
            },
        },
    });
};

const initCategoryChart = () => {
    if (!categoryCanvas.value) return;

    const data = props.categoryDistribution;
    const labels = data.map(c => c.name);
    const counts = data.map(c => c.books_count);

    const palette = [
        '#6366f1', '#06b6d4', '#f59e0b', '#10b981',
        '#ec4899', '#8b5cf6', '#f97316', '#14b8a6',
    ];

    const ctx = categoryCanvas.value.getContext('2d');

    categoryChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.length ? labels : ['No Categories'],
            datasets: [
                {
                    data: counts.length ? counts : [1],
                    backgroundColor: counts.length ? palette.slice(0, labels.length) : ['#e2e8f0'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        usePointStyle: true,
                        font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                        color: '#475569',
                        padding: 12,
                    },
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 8,
                },
            },
        },
    });
};
</script>

<template>
    <Head title="Library Dashboard" />

    <!-- Top Navigation Bar -->
    <Navbar />

    <div class="dashboard-wrapper py-4">
        <div class="container-xl">
            <!-- Flash Alerts -->
            <div v-if="page.props.flash?.success" class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ page.props.flash.success }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div v-if="page.props.flash?.error" class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ page.props.flash.error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Header Section -->
            <div class="dashboard-header mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-slate-900 mb-0">Library Dashboard</h1>
                    <span
                        class="badge px-2.5 py-1 rounded-pill"
                        :class="{
                            'bg-danger': userRole === 'Admin',
                            'bg-primary': userRole === 'Librarian',
                            'bg-success': userRole === 'Member'
                        }"
                    >
                        {{ userRole }}
                    </span>
                </div>
                <p class="text-muted small mb-0 mt-1">
                    Welcome back, <strong>{{ user?.name }}</strong>. Here is your library's real-time overview & operations center.
                </p>
            </div>

            <!-- KPI METRIC CARDS -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-card p-3 rounded-4 shadow-sm bg-white border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="stat-label text-muted small fw-semibold text-uppercase">Total Catalog</span>
                            <div class="stat-icon bg-blue-light text-primary rounded-3">📖</div>
                        </div>
                        <div class="stat-value h3 fw-bold mb-1 text-slate-900">{{ props.stats.total_books ?? 0 }}</div>
                        <div class="stat-desc text-muted small">
                            {{ props.stats.total_copies ?? 0 }} total copies in library
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="stat-card p-3 rounded-4 shadow-sm bg-white border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="stat-label text-muted small fw-semibold text-uppercase">Active Loans</span>
                            <div class="stat-icon bg-emerald-light text-success rounded-3">🔄</div>
                        </div>
                        <div class="stat-value h3 fw-bold mb-1 text-slate-900">{{ props.stats.active_borrowings ?? 0 }}</div>
                        <div class="stat-desc text-muted small">
                            {{ props.stats.returned_count ?? 0 }} books returned
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="stat-card p-3 rounded-4 shadow-sm bg-white border" :class="{ 'border-danger-subtle': props.stats.overdue_borrowings > 0 }">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="stat-label text-muted small fw-semibold text-uppercase">Overdue Books</span>
                            <div class="stat-icon bg-rose-light text-danger rounded-3">⚠️</div>
                        </div>
                        <div class="stat-value h3 fw-bold mb-1" :class="props.stats.overdue_borrowings > 0 ? 'text-danger' : 'text-slate-900'">
                            {{ props.stats.overdue_borrowings ?? 0 }}
                        </div>
                        <div class="stat-desc text-muted small">
                            Require immediate return
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="stat-card p-3 rounded-4 shadow-sm bg-white border" :class="{ 'border-warning-subtle': props.stats.out_of_stock_count > 0 }">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="stat-label text-muted small fw-semibold text-uppercase">Out of Stock</span>
                            <div class="stat-icon bg-amber-light text-warning rounded-3">📦</div>
                        </div>
                        <div class="stat-value h3 fw-bold mb-1" :class="props.stats.out_of_stock_count > 0 ? 'text-danger' : 'text-slate-900'">
                            {{ props.stats.out_of_stock_count ?? 0 }}
                        </div>
                        <div class="stat-desc text-muted small">
                            <span v-if="props.stats.low_stock_count > 0" class="text-warning fw-medium">
                                +{{ props.stats.low_stock_count }} low on stock
                            </span>
                            <span v-else>Available for restock</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Personal Stats Banner (For Members) -->
            <div v-if="props.memberStats && !isStaffUser" class="card border-0 bg-primary-subtle text-primary-emphasis p-3 rounded-4 shadow-sm mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-2">👤</div>
                        <div>
                            <h5 class="fw-bold mb-0">Your Personal Borrowing Summary</h5>
                            <span class="small text-muted">Track your active book loans and return schedule</span>
                        </div>
                    </div>
                    <div class="d-flex gap-4">
                        <div class="text-center">
                            <div class="h4 fw-bold mb-0">{{ props.memberStats.my_active_loans }}</div>
                            <span class="small text-muted">Active Books</span>
                        </div>
                        <div class="text-center">
                            <div class="h4 fw-bold mb-0" :class="props.memberStats.my_overdue_loans > 0 ? 'text-danger' : ''">
                                {{ props.memberStats.my_overdue_loans }}
                            </div>
                            <span class="small text-muted">Overdue</span>
                        </div>
                        <div class="text-center">
                            <div class="h4 fw-bold mb-0">{{ props.memberStats.my_total_borrowed }}</div>
                            <span class="small text-muted">Total Orders</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRAPHS SECTION -->
            <div class="row g-4 mb-4">
                <!-- Monthly Trends Graph -->
                <div class="col-lg-8">
                    <div class="card h-100 shadow-sm border rounded-4 bg-white">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-slate-900 mb-0">Borrowing & Return Trends</h5>
                                <span class="text-muted small">Monthly circulation activity across the last 6 months</span>
                            </div>
                            <span class="badge bg-light text-secondary border px-2 py-1 small">Monthly</span>
                        </div>
                        <div class="card-body px-4 pb-4 pt-2">
                            <div style="height: 280px; position: relative;">
                                <canvas ref="trendsCanvas"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Distribution Graph -->
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm border rounded-4 bg-white">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-slate-900 mb-0">Books by Category</h5>
                                <span class="text-muted small">Catalog distribution</span>
                            </div>
                            <span class="badge bg-light text-secondary border px-2 py-1 small">{{ props.categoryDistribution.length }} Categories</span>
                        </div>
                        <div class="card-body px-4 pb-3 pt-2 d-flex flex-column align-items-center justify-content-center">
                            <div style="height: 250px; width: 100%; position: relative;">
                                <canvas ref="categoryCanvas"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TWO-COLUMN SECTION: MOST POPULAR BOOKS & OUT OF STOCK BOOKS -->
            <div class="row g-4 mb-4">
                <!-- MOST POPULAR BOOKS -->
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border rounded-4 bg-white">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-slate-900 mb-0">
                                    <span class="me-1">🔥</span> Most Popular Books
                                </h5>
                                <span class="text-muted small">Highest circulating books borrowed by members</span>
                            </div>
                            <Link href="/books" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold">
                                View Catalog &rarr;
                            </Link>
                        </div>
                        <div class="card-body px-4 pb-3 pt-2">
                            <div v-if="props.popularBooks.length === 0" class="text-center py-4 text-muted small">
                                No borrowing history recorded yet.
                            </div>
                            <div v-else class="list-group list-group-flush">
                                <div
                                    v-for="(book, idx) in props.popularBooks"
                                    :key="book.id"
                                    class="list-group-item px-0 py-2.5 d-flex align-items-center justify-content-between border-bottom"
                                >
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Rank Badge -->
                                        <div
                                            class="rank-badge rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                            :class="{
                                                'rank-gold': idx === 0,
                                                'rank-silver': idx === 1,
                                                'rank-bronze': idx === 2,
                                                'rank-default': idx > 2
                                            }"
                                        >
                                            #{{ idx + 1 }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-slate-900 text-truncate" style="max-width: 240px;">
                                                {{ book.title }}
                                            </div>
                                            <div class="text-muted small">
                                                by {{ book.author }} &bull;
                                                <span class="badge bg-light text-secondary border px-1.5 py-0.5">
                                                    {{ book.category_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end d-flex align-items-center gap-3">
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary fw-semibold px-2.5 py-1">
                                                {{ book.borrowings_count }} {{ book.borrowings_count === 1 ? 'loan' : 'loans' }}
                                            </span>
                                            <div class="mt-1">
                                                <span
                                                    v-if="book.is_out_of_stock"
                                                    class="badge bg-danger-subtle text-danger"
                                                    style="font-size: 0.7rem;"
                                                >
                                                    Out of Stock
                                                </span>
                                                <span
                                                    v-else
                                                    class="text-muted"
                                                    style="font-size: 0.72rem;"
                                                >
                                                    {{ book.available_quantity }} / {{ book.quantity }} available
                                                </span>
                                            </div>
                                        </div>

                                        <Link :href="`/books/${book.id}`" class="btn btn-outline-secondary btn-sm px-2 py-1">
                                            View
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OUT OF STOCK & INVENTORY ALERTS -->
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border rounded-4 bg-white">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-slate-900 mb-0">
                                    <span class="me-1">📦</span> Stock & Replenishment Alerts
                                </h5>
                                <span class="text-muted small">Books with zero or low remaining inventory</span>
                            </div>

                            <!-- Tabs toggle -->
                            <div class="btn-group btn-group-sm">
                                <button
                                    type="button"
                                    class="btn"
                                    :class="inventoryTab === 'out_of_stock' ? 'btn-danger' : 'btn-outline-secondary'"
                                    @click="inventoryTab = 'out_of_stock'"
                                >
                                    Out of Stock ({{ props.outOfStockBooks.length }})
                                </button>
                                <button
                                    type="button"
                                    class="btn"
                                    :class="inventoryTab === 'low_stock' ? 'btn-warning text-dark' : 'btn-outline-secondary'"
                                    @click="inventoryTab = 'low_stock'"
                                >
                                    Low Stock ({{ props.lowStockBooks.length }})
                                </button>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-3 pt-2">
                            <!-- OUT OF STOCK VIEW -->
                            <div v-if="inventoryTab === 'out_of_stock'">
                                <div v-if="props.outOfStockBooks.length === 0" class="text-center py-4 text-success small">
                                    <div class="fs-4 mb-1">🎉</div>
                                    <strong>All books currently in stock!</strong>
                                    <p class="text-muted mb-0">No books have zero available quantity.</p>
                                </div>
                                <div v-else class="list-group list-group-flush">
                                    <div
                                        v-for="book in props.outOfStockBooks"
                                        :key="book.id"
                                        class="list-group-item px-0 py-2.5 d-flex align-items-center justify-content-between border-bottom"
                                    >
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="p-2 bg-danger-subtle text-danger rounded-3 fs-6">
                                                🚫
                                            </div>
                                            <div>
                                                <div class="fw-bold text-slate-900 text-truncate" style="max-width: 250px;">
                                                    {{ book.title }}
                                                </div>
                                                <div class="text-muted small">
                                                    by {{ book.author }} &bull; ISBN: {{ book.isbn }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-danger px-2 py-1">
                                                0 / {{ book.quantity }} Available
                                            </span>
                                            <Link
                                                v-if="isStaffUser"
                                                :href="`/books/${book.id}/edit`"
                                                class="btn btn-outline-danger btn-sm py-1 px-2.5 fw-medium"
                                                style="font-size: 0.75rem;"
                                            >
                                                Restock
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- LOW STOCK VIEW -->
                            <div v-else>
                                <div v-if="props.lowStockBooks.length === 0" class="text-center py-4 text-muted small">
                                    <div class="fs-4 mb-1">👍</div>
                                    <strong>No low-stock items.</strong>
                                    <p class="text-muted mb-0">No books currently have only 1-2 copies left.</p>
                                </div>
                                <div v-else class="list-group list-group-flush">
                                    <div
                                        v-for="book in props.lowStockBooks"
                                        :key="book.id"
                                        class="list-group-item px-0 py-2.5 d-flex align-items-center justify-content-between border-bottom"
                                    >
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 fs-6">
                                                ⚠️
                                            </div>
                                            <div>
                                                <div class="fw-bold text-slate-900 text-truncate" style="max-width: 250px;">
                                                    {{ book.title }}
                                                </div>
                                                <div class="text-muted small">
                                                    by {{ book.author }} &bull; ISBN: {{ book.isbn }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-warning text-dark px-2 py-1">
                                                {{ book.available_quantity }} / {{ book.quantity }} Left
                                            </span>
                                            <Link
                                                v-if="isStaffUser"
                                                :href="`/books/${book.id}/edit`"
                                                class="btn btn-outline-warning btn-sm py-1 px-2.5 fw-medium text-dark"
                                                style="font-size: 0.75rem;"
                                            >
                                                Edit
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECENT BORROWING ACTIVITY TABLE -->
            <div class="card shadow-sm border rounded-4 bg-white mb-4">
                <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-slate-900 mb-0">Recent Borrowing Activity</h5>
                        <span class="text-muted small">Latest issued loans, returns, and order records</span>
                    </div>
                    <Link href="/borrowings" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1">
                        View All Records &rarr;
                    </Link>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                    <th class="ps-4 py-3">Book Title</th>
                                    <th class="py-3">Borrower</th>
                                    <th class="py-3">Borrowed On</th>
                                    <th class="py-3">Due Date</th>
                                    <th class="py-3">Status</th>
                                    <th class="text-end pe-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="props.recentBorrowings.length === 0">
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        No recent borrowing records found.
                                    </td>
                                </tr>
                                <tr v-for="b in props.recentBorrowings" :key="b.id">
                                    <td class="ps-4 fw-semibold text-slate-900">
                                        <Link :href="`/books/${b.book_id}`" class="text-decoration-none text-slate-900 hover-primary">
                                            {{ b.book_title }}
                                        </Link>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-slate-900" style="font-size: 0.85rem;">{{ b.user_name }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ b.user_email }}</div>
                                    </td>
                                    <td class="text-muted small">{{ b.borrowed_at }}</td>
                                    <td class="small" :class="b.is_overdue ? 'text-danger fw-bold' : 'text-muted'">
                                        {{ b.due_date }}
                                        <span v-if="b.is_overdue" class="badge bg-danger-subtle text-danger ms-1" style="font-size: 0.65rem;">
                                            OVERDUE
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            v-if="b.status === 'returned'"
                                            class="badge bg-success-subtle text-success px-2 py-1"
                                        >
                                            Returned
                                        </span>
                                        <span
                                            v-else-if="b.is_overdue"
                                            class="badge bg-danger-subtle text-danger px-2 py-1"
                                        >
                                            Overdue Loan
                                        </span>
                                        <span
                                            v-else
                                            class="badge bg-primary-subtle text-primary px-2 py-1"
                                        >
                                            Active Loan
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <Link :href="`/borrowings/${b.id}`" class="btn btn-light btn-sm py-1 px-2.5 font-medium border" style="font-size: 0.75rem;">
                                            Details
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dashboard-wrapper {
    background-color: #f8fafc;
    min-height: calc(100vh - 65px);
}

.text-slate-900 {
    color: #0f172a;
}

.hover-primary:hover {
    color: #3b82f6 !important;
}

/* Stat Cards */
.stat-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}

.bg-blue-light {
    background-color: #eff6ff;
}
.bg-emerald-light {
    background-color: #ecfdf5;
}
.bg-rose-light {
    background-color: #fff1f2;
}
.bg-amber-light {
    background-color: #fffbeb;
}

/* Rank Badges */
.rank-badge {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
    flex-shrink: 0;
}
.rank-gold {
    background-color: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}
.rank-silver {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.rank-bronze {
    background-color: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
}
.rank-default {
    background-color: #f8fafc;
    color: #94a3b8;
    border: 1px solid #f1f5f9;
}
</style>
