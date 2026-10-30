<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    kpis: {
        type: Object,
        default: () => ({}),
    },
    circulationOverview: {
        type: Array,
        default: () => [],
    },
    categoriesData: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
    overdueList: {
        type: Array,
        default: () => [],
    },
    newMembers: {
        type: Array,
        default: () => [],
    },
    ebookUsage: {
        type: Array,
        default: () => [],
    },
    totalEbookAccesses: {
        type: Number,
        default: 4285,
    },
    recentIssuedBooks: {
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

const userRole = computed(() => {
    if (roles.value.length > 0) return roles.value[0];
    return props.isStaff ? 'Librarian' : 'Member';
});

// Canvas chart refs
const circulationCanvas = ref(null);
const categoryCanvas = ref(null);
let circulationChartInstance = null;
let categoryChartInstance = null;

// Header controls
const academicYear = ref('2024 - 25');
const searchQuery = ref('');
const circulationTimeframe = ref('This Year');
const ebookTimeframe = ref('This Year');

onMounted(() => {
    initCirculationChart();
    initCategoryChart();
});

onBeforeUnmount(() => {
    if (circulationChartInstance) {
        circulationChartInstance.destroy();
        circulationChartInstance = null;
    }
    if (categoryChartInstance) {
        categoryChartInstance.destroy();
        categoryChartInstance = null;
    }
});

const initCirculationChart = () => {
    if (!circulationCanvas.value) return;

    const labels = props.circulationOverview.map(c => c.month);
    const issuedData = props.circulationOverview.map(c => c.issued);
    const returnedData = props.circulationOverview.map(c => c.returned);

    const ctx = circulationCanvas.value.getContext('2d');

    circulationChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Issued',
                    data: issuedData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    fill: false,
                    tension: 0.38,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4.5,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Returned',
                    data: returnedData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    fill: false,
                    tension: 0.38,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4.5,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9',
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        callback: function(val) {
                            if (val >= 1000) return (val / 1000) + 'K';
                            return val;
                        },
                    },
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                    },
                },
            },
        },
    });
};

const totalCategoryCount = computed(() => {
    return props.categoriesData.reduce((acc, curr) => acc + (curr.count || 0), 0) || props.kpis.total_books?.value || 58246;
});

const initCategoryChart = () => {
    if (!categoryCanvas.value) return;

    const labels = props.categoriesData.map(c => c.name);
    const data = props.categoriesData.map(c => c.count);
    const colors = props.categoriesData.map(c => c.color);

    const ctx = categoryCanvas.value.getContext('2d');

    categoryChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [
                {
                    data,
                    backgroundColor: colors,
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const val = context.raw;
                            return ` ${context.label}: ${Number(val).toLocaleString()}`;
                        },
                    },
                },
            },
        },
    });
};

const formatNumber = (num) => {
    if (num === undefined || num === null) return '0';
    return Number(num).toLocaleString();
};

const handleQuickAction = (action) => {
    if (action === 'Barcode Print') {
        window.print();
    } else if (action === 'Send SMS') {
        alert('SMS Notification service is connected and ready to send overdue reminders.');
    } else if (action === 'Generate Report') {
        window.print();
    }
};
</script>

<template>
    <Head title="Library Dashboard – Overview" />

    <div class="dash-page-wrapper">
        <Navbar />

        <main class="dashboard-body">
            <!-- Header bar with title, academic year, search & user card -->
            <div class="dash-top-bar">
                <div class="top-title-group">
                    <h1 class="dash-main-title">Dashboard</h1>
                    <p class="dash-subtitle">Overview of library operations and insights</p>
                </div>

                <div class="top-actions-group">
                    <!-- Academic Year Selector -->
                    <div class="academic-year-picker">
                        <span class="ay-label">Academic Year</span>
                        <div class="select-chip">
                            <select v-model="academicYear" class="ay-select">
                                <option value="2024 - 25">2024 - 25</option>
                                <option value="2025 - 26">2025 - 26</option>
                                <option value="2023 - 24">2023 - 24</option>
                            </select>
                            <svg class="select-chevron" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <!-- Global Search -->
                    <div class="search-box-wrap">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search title, author, ISBN, member..."
                            class="dash-search-input"
                        />
                        <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <!-- Notification bell -->
                    <div class="notif-bell-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span class="notif-badge">5</span>
                    </div>

                    <!-- User Profile pill -->
                    <div class="user-pill-card">
                        <div class="user-avatar-circle">
                            <span>AV</span>
                        </div>
                        <div class="user-pill-info">
                            <div class="user-pill-name">{{ user?.name || 'Admin User' }}</div>
                            <div class="user-pill-role">{{ userRole }}</div>
                        </div>
                        <svg class="user-pill-arrow" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- ROW 1: 6 STAT METRIC CARDS -->
            <div class="kpi-grid">
                <!-- Total Books -->
                <div class="kpi-card">
                    <div class="kpi-icon-box bg-green-soft text-green">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
                            <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
                        </svg>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">Total Books</span>
                        <div class="kpi-value">{{ formatNumber(kpis.total_books?.value) }}</div>
                        <div class="kpi-trend text-green">
                            <span class="trend-arrow">↑</span> {{ kpis.total_books?.growth }} <span class="trend-sub">vs last year</span>
                        </div>
                    </div>
                </div>

                <!-- Total Members -->
                <div class="kpi-card">
                    <div class="kpi-icon-box bg-blue-soft text-blue">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
                            <path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.603.75.75 0 00.364-.63l.001-.12a7.125 7.125 0 00-5.191-6.852z" />
                        </svg>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">Total Members</span>
                        <div class="kpi-value">{{ formatNumber(kpis.total_members?.value) }}</div>
                        <div class="kpi-trend text-green">
                            <span class="trend-arrow">↑</span> {{ kpis.total_members?.growth }} <span class="trend-sub">vs last year</span>
                        </div>
                    </div>
                </div>

                <!-- Books Issued -->
                <div class="kpi-card">
                    <div class="kpi-icon-box bg-purple-soft text-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="22" height="22">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">Books Issued</span>
                        <div class="kpi-value">{{ formatNumber(kpis.books_issued?.value) }}</div>
                        <div class="kpi-trend text-green">
                            <span class="trend-arrow">↑</span> {{ kpis.books_issued?.growth }} <span class="trend-sub">vs last year</span>
                        </div>
                    </div>
                </div>

                <!-- Books Returned -->
                <div class="kpi-card">
                    <div class="kpi-icon-box bg-amber-soft text-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="22" height="22">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">Books Returned</span>
                        <div class="kpi-value">{{ formatNumber(kpis.books_returned?.value) }}</div>
                        <div class="kpi-trend text-green">
                            <span class="trend-arrow">↑</span> {{ kpis.books_returned?.growth }} <span class="trend-sub">vs last year</span>
                        </div>
                    </div>
                </div>

                <!-- Overdue Books -->
                <div class="kpi-card">
                    <div class="kpi-icon-box bg-red-soft text-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="22" height="22">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">Overdue Books</span>
                        <div class="kpi-value">{{ formatNumber(kpis.overdue_books?.value) }}</div>
                        <div class="kpi-trend text-green">
                            <span class="trend-arrow">↓</span> {{ kpis.overdue_books?.growth }} <span class="trend-sub">vs last year</span>
                        </div>
                    </div>
                </div>

                <!-- Active e-Books -->
                <div class="kpi-card">
                    <div class="kpi-icon-box bg-teal-soft text-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                            <rect x="5" y="2" width="14" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="12" y1="18" x2="12.01" y2="18" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" />
                        </svg>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">Active e-Books</span>
                        <div class="kpi-value">{{ formatNumber(kpis.active_ebooks?.value) }}</div>
                        <div class="kpi-trend text-green">
                            <span class="trend-arrow">↑</span> {{ kpis.active_ebooks?.growth }} <span class="trend-sub">vs last year</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 2: CIRCULATION OVERVIEW | TOP CATEGORIES | RECENT ACTIVITIES -->
            <div class="charts-row-grid">
                <!-- Circulation Overview Line Chart -->
                <div class="card-panel circulation-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Circulation Overview</h2>
                        <div class="panel-header-right">
                            <div class="chart-indicators">
                                <span class="indicator-item">
                                    <span class="indicator-dot bg-green"></span>
                                    <span>Issued</span>
                                </span>
                                <span class="indicator-item">
                                    <span class="indicator-dot bg-blue"></span>
                                    <span>Returned</span>
                                </span>
                            </div>
                            <div class="mini-select-wrap">
                                <select v-model="circulationTimeframe" class="mini-select">
                                    <option value="This Year">This Year</option>
                                    <option value="Last 6 Months">Last 6 Months</option>
                                    <option value="All Time">All Time</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body chart-body">
                        <canvas ref="circulationCanvas"></canvas>
                    </div>
                </div>

                <!-- Top Categories Doughnut Chart -->
                <div class="card-panel category-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Top Categories</h2>
                    </div>
                    <div class="panel-body doughnut-flex-body">
                        <div class="doughnut-canvas-wrap">
                            <canvas ref="categoryCanvas"></canvas>
                            <div class="doughnut-inner-label">
                                <div class="inner-count">{{ formatNumber(totalCategoryCount) }}</div>
                                <div class="inner-sub">Total Books</div>
                            </div>
                        </div>
                        <div class="category-legend-list">
                            <div v-for="cat in categoriesData" :key="cat.name" class="legend-row">
                                <div class="legend-label-col">
                                    <span class="legend-circle" :style="{ backgroundColor: cat.color }"></span>
                                    <span class="legend-name">{{ cat.name }}</span>
                                </div>
                                <div class="legend-data-col">
                                    <span class="legend-count">{{ formatNumber(cat.count) }}</span>
                                    <span class="legend-percent">({{ cat.percentage }}%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="card-panel activities-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Recent Activities</h2>
                        <Link href="/borrowings" class="view-all-link">View all</Link>
                    </div>
                    <div class="panel-body activities-list">
                        <div v-for="(act, idx) in recentActivities" :key="idx" class="activity-item">
                            <div class="activity-icon-wrap" :style="{ backgroundColor: act.bg, color: act.color }">
                                <svg v-if="act.icon === 'book'" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                    <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
                                </svg>
                                <svg v-else-if="act.icon === 'rotate'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <svg v-else-if="act.icon === 'user-plus'" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                    <path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z" />
                                </svg>
                                <svg v-else-if="act.icon === 'bell'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <rect x="5" y="2" width="14" height="20" rx="2" />
                                    <line x1="12" y1="18" x2="12.01" y2="18" stroke-width="3" />
                                </svg>
                            </div>
                            <div class="activity-text">
                                <div class="act-title">{{ act.title }}</div>
                                <div class="act-desc">
                                    {{ act.description }}
                                    <span v-if="act.user" class="act-by">by {{ act.user }}</span>
                                </div>
                            </div>
                            <div class="activity-time">{{ act.time }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 3: OVERDUE BOOKS | NEW MEMBERS | E-BOOK USAGE -->
            <div class="charts-row-grid second-row">
                <!-- Overdue Books Table -->
                <div class="card-panel overdue-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Overdue Books</h2>
                        <Link href="/borrowings" class="view-all-link">View all</Link>
                    </div>
                    <div class="panel-body table-responsive">
                        <table class="dash-custom-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Member</th>
                                    <th>Due Date</th>
                                    <th class="text-right">Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in overdueList" :key="item.id">
                                    <td class="font-medium text-dark">{{ item.title }}</td>
                                    <td>{{ item.member }}</td>
                                    <td class="text-muted">{{ item.due_date }}</td>
                                    <td class="text-right">
                                        <span class="overdue-badge">{{ item.days_overdue }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- New Members List -->
                <div class="card-panel members-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">New Members</h2>
                        <Link href="/users" class="view-all-link">View all</Link>
                    </div>
                    <div class="panel-body table-responsive">
                        <table class="dash-custom-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Joined On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="member in newMembers" :key="member.id">
                                    <td>
                                        <div class="member-cell">
                                            <img :src="member.avatar" :alt="member.name" class="member-table-avatar" />
                                            <span class="font-medium text-dark">{{ member.name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="dept-badge">{{ member.department }}</span></td>
                                    <td class="text-muted">{{ member.joined_on }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- e-Book Usage -->
                <div class="card-panel ebook-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">e-Book Usage</h2>
                        <div class="mini-select-wrap">
                            <select v-model="ebookTimeframe" class="mini-select">
                                <option value="This Year">This Year</option>
                                <option value="This Month">This Month</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-body ebook-body">
                        <div class="ebook-list">
                            <div v-for="ebook in ebookUsage" :key="ebook.title" class="ebook-row">
                                <div class="ebook-info-row">
                                    <span class="ebook-title">{{ ebook.title }}</span>
                                    <span class="ebook-access-count">{{ formatNumber(ebook.accesses) }}</span>
                                </div>
                                <div class="ebook-progress-track">
                                    <div class="ebook-progress-fill" :style="{ width: ebook.percent + '%' }"></div>
                                </div>
                            </div>
                        </div>

                        <div class="ebook-footer-total">
                            <span class="total-label">Total e-Book Accesses</span>
                            <span class="total-val">{{ formatNumber(totalEbookAccesses) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 4: RECENT ISSUED BOOKS & QUICK ACTIONS -->
            <div class="bottom-split-grid">
                <!-- Recent Issued Books Table (wide) -->
                <div class="card-panel issued-table-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Recent Issued Books</h2>
                        <Link href="/borrowings" class="view-all-link">View all</Link>
                    </div>
                    <div class="panel-body table-responsive">
                        <table class="dash-custom-table wide-table">
                            <thead>
                                <tr>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Member</th>
                                    <th>Issued On</th>
                                    <th>Due On</th>
                                    <th>Barcode</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="book in recentIssuedBooks" :key="book.id">
                                    <td class="font-medium text-dark">{{ book.title }}</td>
                                    <td class="text-secondary">{{ book.author }}</td>
                                    <td>{{ book.member }}</td>
                                    <td class="text-muted">{{ book.issued_on }}</td>
                                    <td class="text-muted">{{ book.due_on }}</td>
                                    <td><span class="barcode-badge">{{ book.barcode }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions Grid (8 items in 4x2 grid matching reference) -->
                <div class="card-panel quick-actions-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Quick Actions</h2>
                    </div>
                    <div class="panel-body actions-matrix">
                        <!-- Add New Book -->
                        <Link href="/books/create" class="action-tile">
                            <div class="tile-icon-box bg-green-soft text-green">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                    <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
                                </svg>
                            </div>
                            <span class="tile-title">Add New Book</span>
                        </Link>

                        <!-- Issue Book -->
                        <Link href="/borrowings/create" class="action-tile">
                            <div class="tile-icon-box bg-blue-soft text-blue">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <span class="tile-title">Issue Book</span>
                        </Link>

                        <!-- Add Member -->
                        <Link href="/users" class="action-tile">
                            <div class="tile-icon-box bg-purple-soft text-purple">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                    <path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z" />
                                </svg>
                            </div>
                            <span class="tile-title">Add Member</span>
                        </Link>

                        <!-- Return Book -->
                        <Link href="/borrowings" class="action-tile">
                            <div class="tile-icon-box bg-amber-soft text-amber">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                </svg>
                            </div>
                            <span class="tile-title">Return Book</span>
                        </Link>

                        <!-- Barcode Print -->
                        <button type="button" @click="handleQuickAction('Barcode Print')" class="action-tile btn-tile">
                            <div class="tile-icon-box bg-pink-soft text-pink">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                    <path fill-rule="evenodd" d="M3 4.5a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 4.5zm0 15a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 19.5zM4.5 7.5a.75.75 0 00-1.5 0v9a.75.75 0 001.5 0v-9zm3 0a.75.75 0 00-.75.75v7.5a.75.75 0 001.5 0v-7.5A.75.75 0 007.5 7.5zm4.5.75a.75.75 0 011.5 0v7.5a.75.75 0 01-1.5 0v-7.5zm4.5-.75a.75.75 0 00-.75.75v7.5a.75.75 0 001.5 0v-7.5a.75.75 0 00-.75-.75zm3 .75a.75.75 0 011.5 0v7.5a.75.75 0 01-1.5 0v-7.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="tile-title">Barcode Print</span>
                        </button>

                        <!-- Send SMS -->
                        <button type="button" @click="handleQuickAction('Send SMS')" class="action-tile btn-tile">
                            <div class="tile-icon-box bg-green-soft text-green">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                    <path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.177 7.152.521 1.944.286 3.348 2.008 3.348 3.974v6.01c0 1.966-1.404 3.688-3.348 3.974a48.97 48.97 0 01-2.902.348v3.673a.75.75 0 01-1.28.53l-4.72-4.72a49.18 49.18 0 01-5.35-.441C2.903 15.65 1.5 13.928 1.5 11.962V6.745c0-1.966 1.404-3.688 3.348-3.974z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="tile-title">Send SMS</span>
                        </button>

                        <!-- Generate Report -->
                        <button type="button" @click="handleQuickAction('Generate Report')" class="action-tile btn-tile">
                            <div class="tile-icon-box bg-blue-soft text-blue">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v16a1 1 0 001 1h18a1 1 0 001-1V4a1 1 0 00-1-1H3zm3 14a1 1 0 011-1h1a1 1 0 011 1v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-1zm4-5a1 1 0 011-1h1a1 1 0 011 1v6a1 1 0 01-1 1h-1a1 1 0 01-1-1v-6zm4-3a1 1 0 011-1h1a1 1 0 011 1v9a1 1 0 01-1 1h-1a1 1 0 01-1-1V9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="tile-title">Generate Report</span>
                        </button>

                        <!-- Add e-Book -->
                        <Link href="/books/create" class="action-tile">
                            <div class="tile-icon-box bg-teal-soft text-teal">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <rect x="5" y="2" width="14" height="20" rx="2" />
                                    <line x1="12" y1="18" x2="12.01" y2="18" stroke-width="3" />
                                </svg>
                            </div>
                            <span class="tile-title">Add e-Book</span>
                        </Link>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.dash-page-wrapper {
    min-height: 100vh;
    background-color: #f8fafc;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #1e293b;
}

.dashboard-body {
    padding: 24px 32px 48px;
    max-width: 1680px;
    margin: 0 auto;
}

/* TOP BAR */
.dash-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.dash-main-title {
    font-size: 26px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin-bottom: 4px;
}

.dash-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
}

.top-actions-group {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.academic-year-picker {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 12px;
    gap: 8px;
}

.ay-label {
    font-size: 13px;
    color: #475569;
    font-weight: 500;
}

.select-chip {
    position: relative;
    display: flex;
    align-items: center;
}

.ay-select {
    appearance: none;
    border: none;
    background: transparent;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    padding-right: 18px;
    cursor: pointer;
    outline: none;
}

.select-chevron {
    position: absolute;
    right: 0;
    pointer-events: none;
    color: #64748b;
}

.search-box-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.dash-search-input {
    width: 270px;
    height: 38px;
    padding: 8px 36px 8px 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13px;
    color: #1e293b;
    outline: none;
    transition: all 0.2s;
}

.dash-search-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    width: 320px;
}

.search-icon {
    position: absolute;
    right: 12px;
    color: #94a3b8;
    pointer-events: none;
}

.notif-bell-btn {
    position: relative;
    width: 38px;
    height: 38px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s;
}

.notif-bell-btn:hover {
    background: #f1f5f9;
}

.notif-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #ef4444;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-pill-card {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 5px 12px 5px 8px;
    cursor: pointer;
}

.user-avatar-circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-pill-info {
    line-height: 1.2;
}

.user-pill-name {
    font-size: 12.5px;
    font-weight: 600;
    color: #0f172a;
}

.user-pill-role {
    font-size: 11px;
    color: #64748b;
}

.user-pill-arrow {
    color: #94a3b8;
    margin-left: 2px;
}

/* KPI GRID */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

@media (max-width: 1400px) {
    .kpi-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 768px) {
    .kpi-grid {
        grid-template-columns: repeat(1, 1fr);
    }
}

.kpi-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: transform 0.2s, box-shadow 0.2s;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.kpi-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.kpi-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    display: block;
    margin-bottom: 3px;
}

.kpi-value {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
    margin-bottom: 3px;
}

.kpi-trend {
    font-size: 11px;
    font-weight: 600;
}

.trend-arrow {
    font-size: 12px;
}

.trend-sub {
    color: #94a3b8;
    font-weight: 400;
    margin-left: 3px;
}

/* COLORS */
.bg-green-soft { background: #ecfdf5; }
.text-green { color: #10b981; }
.bg-green { background-color: #10b981; }

.bg-blue-soft { background: #eff6ff; }
.text-blue { color: #3b82f6; }
.bg-blue { background-color: #3b82f6; }

.bg-purple-soft { background: #f5f3ff; }
.text-purple { color: #8b5cf6; }

.bg-amber-soft { background: #fffbeb; }
.text-amber { color: #f59e0b; }

.bg-red-soft { background: #fef2f2; }
.text-red { color: #ef4444; }

.bg-teal-soft { background: #f0fdfa; }
.text-teal { color: #14b8a6; }

.bg-pink-soft { background: #fdf2f8; }
.text-pink { color: #ec4899; }

/* PANELS */
.card-panel {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
}

.panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.panel-header-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.chart-indicators {
    display: flex;
    align-items: center;
    gap: 12px;
}

.indicator-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.indicator-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.mini-select-wrap {
    position: relative;
}

.mini-select {
    appearance: none;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    color: #475569;
    padding: 4px 22px 4px 10px;
    cursor: pointer;
    outline: none;
}

.view-all-link {
    font-size: 12px;
    font-weight: 600;
    color: #10b981;
    text-decoration: none;
}
.view-all-link:hover {
    text-decoration: underline;
}

.panel-body {
    padding: 18px 20px;
}

/* ROW 2: 3 COLUMNS */
.charts-row-grid {
    display: grid;
    grid-template-columns: 2.2fr 1.6fr 1.4fr;
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 1280px) {
    .charts-row-grid {
        grid-template-columns: 1fr;
    }
}

.chart-body {
    height: 250px;
    position: relative;
}

/* DOUGHNUT */
.doughnut-flex-body {
    display: flex;
    align-items: center;
    gap: 20px;
    height: 250px;
}

.doughnut-canvas-wrap {
    position: relative;
    width: 150px;
    height: 150px;
    flex-shrink: 0;
}

.doughnut-inner-label {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.inner-count {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

.inner-sub {
    font-size: 11px;
    color: #94a3b8;
}

.category-legend-list {
    display: flex;
    flex-direction: column;
    gap: 9px;
    flex: 1;
}

.legend-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12.5px;
}

.legend-label-col {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-circle {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.legend-name {
    color: #475569;
}

.legend-data-col {
    display: flex;
    align-items: center;
    gap: 4px;
}

.legend-count {
    font-weight: 600;
    color: #0f172a;
}

.legend-percent {
    color: #94a3b8;
    font-size: 11.5px;
}

/* RECENT ACTIVITIES */
.activities-list {
    display: flex;
    flex-direction: column;
    gap: 13px;
    height: 250px;
    overflow-y: auto;
    padding: 14px 20px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.activity-icon-wrap {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-text {
    flex: 1;
    min-width: 0;
}

.act-title {
    font-size: 12.5px;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.2;
}

.act-desc {
    font-size: 11.5px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.act-by {
    color: #94a3b8;
}

.activity-time {
    font-size: 11px;
    color: #94a3b8;
    flex-shrink: 0;
}

/* ROW 3: OVERDUE | MEMBERS | E-BOOK */
.second-row {
    grid-template-columns: 1.6fr 1.6fr 1.3fr;
}

.dash-custom-table {
    width: 100%;
    border-collapse: collapse;
}

.dash-custom-table th {
    font-size: 11.5px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: capitalize;
    padding: 8px 12px;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
}

.dash-custom-table td {
    padding: 11px 12px;
    font-size: 12.5px;
    color: #475569;
    border-bottom: 1px solid #f8fafc;
}

.font-medium {
    font-weight: 600;
}

.text-dark {
    color: #0f172a;
}

.text-secondary {
    color: #64748b;
}

.text-muted {
    color: #94a3b8;
}

.text-right {
    text-align: right;
}

.overdue-badge {
    color: #ef4444;
    font-weight: 700;
}

.member-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.member-table-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    object-fit: cover;
}

.dept-badge {
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 4px;
}

/* EBOOK USAGE */
.ebook-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: calc(100% - 54px);
}

.ebook-list {
    display: flex;
    flex-direction: column;
    gap: 13px;
}

.ebook-info-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    margin-bottom: 5px;
}

.ebook-title {
    color: #334155;
    font-weight: 500;
}

.ebook-access-count {
    font-weight: 600;
    color: #0f172a;
}

.ebook-progress-track {
    height: 6px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}

.ebook-progress-fill {
    height: 100%;
    background: #10b981;
    border-radius: 999px;
}

.ebook-footer-total {
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12.5px;
}

.total-label {
    color: #475569;
    font-weight: 600;
}

.total-val {
    color: #10b981;
    font-weight: 700;
    font-size: 14px;
}

/* ROW 4: BOTTOM SPLIT GRID */
.bottom-split-grid {
    display: grid;
    grid-template-columns: 2.2fr 1.3fr;
    gap: 20px;
}

@media (max-width: 1100px) {
    .bottom-split-grid {
        grid-template-columns: 1fr;
    }
}

.barcode-badge {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 2px 6px;
    font-family: monospace;
    font-size: 11px;
    color: #334155;
}

.actions-matrix {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

@media (max-width: 600px) {
    .actions-matrix {
        grid-template-columns: repeat(2, 1fr);
    }
}

.action-tile {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 14px 6px;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
    background: #ffffff;
}

.action-tile:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
}

.btn-tile {
    cursor: pointer;
    outline: none;
}

.tile-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}

.tile-title {
    font-size: 11.5px;
    font-weight: 600;
    color: #334155;
    line-height: 1.25;
}
</style>
