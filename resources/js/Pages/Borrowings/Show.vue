<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    borrowing: {
        type: Object,
        required: true,
    },
    isStaff: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const can = (permission) => {
    return page.props.auth?.permissions?.includes(permission) ?? false;
};

// Safe date formatter ignoring timezone shifts
const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    const cleanStr = typeof dateStr === 'string' ? dateStr.split('T')[0] : dateStr;
    const parts = String(cleanStr).split('-');
    if (parts.length === 3) {
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const day = parseInt(parts[2], 10);
        const d = new Date(year, month, day);
        return d.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Days overdue calculation
const overdueDays = computed(() => {
    if (!props.borrowing.due_date) return 0;
    const cleanDue = String(props.borrowing.due_date).split('T')[0];
    const dueParts = cleanDue.split('-');
    const dueDate = new Date(parseInt(dueParts[0], 10), parseInt(dueParts[1], 10) - 1, parseInt(dueParts[2], 10));

    let compareDate = new Date();
    if (props.borrowing.returned_at) {
        const cleanRet = String(props.borrowing.returned_at).split('T')[0];
        const retParts = cleanRet.split('-');
        compareDate = new Date(parseInt(retParts[0], 10), parseInt(retParts[1], 10) - 1, parseInt(retParts[2], 10));
    }
    compareDate.setHours(0, 0, 0, 0);
    dueDate.setHours(0, 0, 0, 0);

    const diffTime = compareDate.getTime() - dueDate.getTime();
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
});

// Days remaining until deadline
const daysRemaining = computed(() => {
    if (!props.borrowing.due_date || props.borrowing.status === 'returned') return 0;
    const cleanDue = String(props.borrowing.due_date).split('T')[0];
    const dueParts = cleanDue.split('-');
    const dueDate = new Date(parseInt(dueParts[0], 10), parseInt(dueParts[1], 10) - 1, parseInt(dueParts[2], 10));
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    dueDate.setHours(0, 0, 0, 0);

    const diffTime = dueDate.getTime() - today.getTime();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
});

// Calculated active fine
const calculatedFine = computed(() => {
    if (props.borrowing.status === 'returned') {
        return Number(props.borrowing.fine || 0).toFixed(2);
    }
    return (overdueDays.value * 5).toFixed(2);
});

const isOverdue = computed(() => {
    return props.borrowing.status !== 'returned' && overdueDays.value > 0;
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/borrowings');
    }
};

const returnBook = () => {
    let message = `Are you sure you want to mark "${props.borrowing.book?.title}" as returned?`;
    if (overdueDays.value > 0) {
        message += `\n\n⚠️ NOTE: This book is ${overdueDays.value} day(s) overdue!\nA fine of $${calculatedFine.value} ($5.00/day) will be recorded.`;
    }

    if (confirm(message)) {
        router.post(`/borrowings/${props.borrowing.id}/return`);
    }
};
</script>

<template>
    <Head :title="`Borrowing Record #${props.borrowing.id}`" />

    <Navbar />

    <div class="container py-4" style="max-width: 960px;">
        <!-- Flash messages -->
        <div v-if="page.props.flash?.success" class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ page.props.flash.success }}
        </div>
        <div v-if="page.props.flash?.error" class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ page.props.flash.error }}
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="p-3 bg-light rounded-circle text-primary fs-3 shadow-sm">
                    📋
                </span>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-secondary-subtle text-secondary fs-6">
                            Record #{{ props.borrowing.id }}
                        </span>
                        <!-- Status Badge -->
                        <span
                            v-if="props.borrowing.status === 'returned'"
                            class="badge bg-success fs-6"
                        >
                            ✓ Returned
                        </span>
                        <span
                            v-else-if="isOverdue"
                            class="badge bg-danger fs-6"
                        >
                            ⚠️ Overdue ({{ overdueDays }}d)
                        </span>
                        <span
                            v-else
                            class="badge bg-warning text-dark fs-6"
                        >
                            📖 Active Loan
                        </span>
                    </div>
                    <h1 class="h2 mb-1 fw-bold">
                        {{ isStaff ? 'Borrowing Record' : 'Order Details' }}
                    </h1>
                    <p class="text-muted mb-0">
                        Book loan for <span class="fw-semibold text-dark">{{ props.borrowing.book?.title }}</span>
                    </p>
                </div>
            </div>

            <!-- Close icon button returning to back page -->
            <button
                type="button"
                class="btn-close"
                style="font-size: 1.15rem; cursor: pointer;"
                aria-label="Close"
                title="Close and return to back page"
                @click="goBack"
            ></button>
        </div>

        <!-- Status Summary Highlight Banner -->
        <div
            v-if="props.borrowing.status === 'returned'"
            class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-3"
        >
            <div class="fs-2">🎉</div>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1 fw-bold">Book Successfully Returned</h5>
                <p class="mb-0 small">
                    This book was returned to the library on <strong>{{ formatDate(props.borrowing.returned_at) }}</strong>.
                    <span v-if="Number(props.borrowing.fine) > 0" class="text-danger fw-semibold ms-1">
                        Recorded late fine: ${{ Number(props.borrowing.fine).toFixed(2) }}.
                    </span>
                    <span v-else class="text-success fw-semibold ms-1">
                        Returned on time with no overdue fines.
                    </span>
                </p>
            </div>
        </div>

        <div
            v-else-if="isOverdue"
            class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-3"
        >
            <div class="fs-2">⚠️</div>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1 fw-bold">Loan Overdue by {{ overdueDays }} Day(s)</h5>
                <p class="mb-0 small">
                    The return deadline was <strong>{{ formatDate(props.borrowing.due_date) }}</strong>.
                    An overdue fine of <strong>$5.00/day</strong> is being charged.
                    Current accrued fine: <strong class="fs-6">${{ calculatedFine }}</strong>.
                </p>
            </div>
        </div>

        <div
            v-else
            class="alert alert-primary border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-3"
        >
            <div class="fs-2">📖</div>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1 fw-bold">Active Book Loan</h5>
                <p class="mb-0 small">
                    <span v-if="daysRemaining > 0">
                        This book is currently on loan with <strong>{{ daysRemaining }} day(s) remaining</strong> before the deadline.
                    </span>
                    <span v-else>
                        This book is due <strong>today</strong>!
                    </span>
                    Please return it on or before <strong>{{ formatDate(props.borrowing.due_date) }}</strong> to avoid fines.
                </p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Loan Timeline, Book Details, Member Details -->
            <div class="col-lg-8">
                <!-- Loan Timeline & Status Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Loan Schedule & Timeline
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="text-muted small">Issue / Order Date</div>
                                <div class="fw-semibold text-dark fs-6">
                                    {{ formatDate(props.borrowing.borrowed_at) }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Return Deadline (Due Date)</div>
                                <div class="fw-bold text-dark fs-6" :class="{ 'text-danger': isOverdue }">
                                    {{ formatDate(props.borrowing.due_date) }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Returned Date</div>
                                <div>
                                    <span v-if="props.borrowing.returned_at" class="fw-semibold text-success">
                                        {{ formatDate(props.borrowing.returned_at) }}
                                    </span>
                                    <span v-else class="text-muted fst-italic">
                                        Not returned yet
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Loan Status</div>
                                <div>
                                    <span
                                        v-if="props.borrowing.status === 'returned'"
                                        class="badge bg-success"
                                    >
                                        Returned
                                    </span>
                                    <span
                                        v-else-if="isOverdue"
                                        class="badge bg-danger"
                                    >
                                        Overdue ({{ overdueDays }} days)
                                    </span>
                                    <span
                                        v-else
                                        class="badge bg-warning text-dark"
                                    >
                                        Active Loan
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Overdue Fine Amount</div>
                                <div class="fs-5 fw-bold" :class="Number(calculatedFine) > 0 ? 'text-danger' : 'text-success'">
                                    ${{ calculatedFine }}
                                    <small v-if="isOverdue" class="fs-6 text-muted fw-normal ms-1">
                                        ($5.00/day)
                                    </small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Standard Loan Duration</div>
                                <div class="text-dark fw-medium">
                                    14 Days
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Book Details Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Book Specifications
                        </h5>
                        <Link
                            v-if="props.borrowing.book_id"
                            :href="`/books/${props.borrowing.book_id}`"
                            class="btn btn-sm btn-outline-primary"
                        >
                            View in Catalog &rarr;
                        </Link>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="text-muted small">Book Title</div>
                                <div class="fw-bold fs-6">
                                    <Link
                                        :href="`/books/${props.borrowing.book?.id}`"
                                        class="text-decoration-none text-dark"
                                    >
                                        {{ props.borrowing.book?.title }}
                                    </Link>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Author</div>
                                <div class="fw-semibold text-dark">
                                    {{ props.borrowing.book?.author || 'Unknown' }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Category</div>
                                <div>
                                    <span
                                        v-if="props.borrowing.book?.category?.name || props.borrowing.book?.category"
                                        class="badge bg-light text-primary border border-primary-subtle"
                                    >
                                        {{ props.borrowing.book?.category?.name || props.borrowing.book?.category }}
                                    </span>
                                    <span v-else class="text-muted small">General</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">ISBN</div>
                                <div class="font-monospace text-dark">
                                    {{ props.borrowing.book?.isbn || 'N/A' }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Original Price</div>
                                <div class="fw-semibold text-success">
                                    ${{ Number(props.borrowing.book?.price || 0).toFixed(2) }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Current Library Stock</div>
                                <div>
                                    <span
                                        v-if="props.borrowing.book?.available_quantity > 0"
                                        class="badge bg-success"
                                    >
                                        {{ props.borrowing.book?.available_quantity }} Available
                                    </span>
                                    <span v-else class="badge bg-secondary">
                                        0 Available (Checked Out)
                                    </span>
                                    <small class="text-muted ms-2">
                                        Total copies: {{ props.borrowing.book?.quantity }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Borrower & Loan Metadata -->
            <div class="col-lg-4">
                <!-- Borrower / Member Information Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Borrower Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <div class="text-muted small">Member Name</div>
                            <div class="fw-bold text-dark fs-6">
                                {{ props.borrowing.user?.name || 'Unknown' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Email Address</div>
                            <div class="text-dark">
                                <a :href="`mailto:${props.borrowing.user?.email}`" class="text-decoration-none">
                                    {{ props.borrowing.user?.email }}
                                </a>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div>
                                <div class="text-muted small">Member ID</div>
                                <div class="fw-semibold text-secondary font-monospace">
                                    #{{ props.borrowing.user?.id }}
                                </div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">
                                Library Member
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Record Metadata Card -->
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-3 small text-muted">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Record ID:</span>
                            <span class="fw-semibold text-dark">#{{ props.borrowing.id }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Record Status:</span>
                            <span class="fw-semibold text-dark text-capitalize">{{ props.borrowing.status }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Fine Policy:</span>
                            <span class="text-dark">$5.00 / day overdue</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Created At:</span>
                            <span class="text-dark">{{ formatDate(props.borrowing.created_at) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span>Last Updated:</span>
                            <span class="text-dark">{{ formatDate(props.borrowing.updated_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
