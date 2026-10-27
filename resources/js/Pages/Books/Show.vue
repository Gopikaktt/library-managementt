<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    book: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const can = (permission) => {
    return page.props.auth?.permissions?.includes(permission) ?? false;
};

const isStaff = computed(() => {
    return can('create books') || can('edit books') || can('delete books') || can('issue books');
});

const borrowedCount = computed(() => {
    return Math.max(0, (props.book.quantity || 0) - (props.book.available_quantity || 0));
});

const availablePercent = computed(() => {
    if (!props.book.quantity || props.book.quantity <= 0) return 0;
    return Math.round((props.book.available_quantity / props.book.quantity) * 100);
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/books');
    }
};

const orderBook = () => {
    if (confirm(`Do you want to order "${props.book.title}"?\n\nThe loan duration is 14 days from today.`)) {
        router.post(`/books/${props.book.id}/order`);
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Book: ${props.book.title}`" />

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
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span
                        v-if="props.book.category?.name || props.book.category"
                        class="badge bg-light text-primary border border-primary-subtle"
                    >
                        {{ props.book.category?.name || props.book.category }}
                    </span>
                    <span
                        v-if="props.book.available_quantity > 0"
                        class="badge bg-success"
                    >
                        {{ props.book.available_quantity }} Available
                    </span>
                    <span v-else class="badge bg-secondary">
                        Out of Stock
                    </span>
                </div>
                <h1 class="h2 mb-1 fw-bold">{{ props.book.title }}</h1>
                <p class="text-muted mb-0">by <span class="fw-medium text-dark">{{ props.book.author }}</span></p>
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

        <div class="row g-4">
            <!-- Left Column: Details & Inventory -->
            <div class="col-lg-8">
                <!-- Book Information Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Book Specifications
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="text-muted small">Book Title</div>
                                <div class="fw-semibold text-dark fs-6">{{ props.book.title }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Author</div>
                                <div class="fw-semibold text-dark fs-6">{{ props.book.author }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Category</div>
                                <div>
                                    <Link
                                        :href="`/books?category_id=${props.book.category_id}`"
                                        class="badge bg-light text-primary border text-decoration-none border-primary-subtle"
                                    >
                                        {{ props.book.category?.name || props.book.category || 'General' }}
                                    </Link>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">ISBN</div>
                                <div class="font-monospace fw-medium text-dark">{{ props.book.isbn }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Price</div>
                                <div class="fw-bold text-success fs-5">
                                    ${{ Number(props.book.price || 0).toFixed(2) }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Current Availability Status</div>
                                <div>
                                    <span
                                        v-if="props.book.available_quantity > 0"
                                        class="text-success fw-semibold"
                                    >
                                        ● In Stock ({{ props.book.available_quantity }} ready for issue)
                                    </span>
                                    <span v-else class="text-danger fw-semibold">
                                        ● Currently Out of Stock
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory & Stock Status -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Inventory & Availability
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row text-center mb-3 g-2">
                            <div class="col-4">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="text-muted small">Total Copies</div>
                                    <div class="fs-4 fw-bold text-dark">{{ props.book.quantity }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="text-muted small">Available Copies</div>
                                    <div class="fs-4 fw-bold text-success">{{ props.book.available_quantity }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="text-muted small">Borrowed Copies</div>
                                    <div class="fs-4 fw-bold text-primary">{{ borrowedCount }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Availability Bar -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Availability Ratio</span>
                                <span>{{ availablePercent }}% Available</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div
                                    class="progress-bar bg-success"
                                    role="progressbar"
                                    :style="{ width: `${availablePercent}%` }"
                                    :aria-valuenow="availablePercent"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Borrowing History (Staff Only) -->
                <div v-if="isStaff && props.book.borrowings?.length" class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Recent Borrowing History
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Member</th>
                                    <th>Borrowed Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="b in props.book.borrowings" :key="b.id">
                                    <td>
                                        <div class="fw-semibold">{{ b.user?.name || 'Unknown' }}</div>
                                        <div class="text-muted small">{{ b.user?.email }}</div>
                                    </td>
                                    <td>{{ formatDate(b.borrowed_at) }}</td>
                                    <td>{{ formatDate(b.due_date) }}</td>
                                    <td>
                                        <span
                                            v-if="b.status === 'borrowed'"
                                            class="badge bg-warning text-dark"
                                        >
                                            Active Loan
                                        </span>
                                        <span
                                            v-else-if="b.status === 'returned'"
                                            class="badge bg-success"
                                        >
                                            Returned
                                        </span>
                                        <span
                                            v-else-if="b.status === 'overdue'"
                                            class="badge bg-danger"
                                        >
                                            Overdue
                                        </span>
                                        <span v-else class="badge bg-secondary">
                                            {{ b.status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <Link :href="`/borrowings/${b.id}`" class="btn btn-sm btn-outline-info">
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions & Metadata -->
            <div class="col-lg-4">
                <!-- Quick Actions Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Actions
                        </h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column gap-2">
                        <!-- Staff Actions -->
                        <template v-if="isStaff">
                            <Link
                                v-if="can('edit books')"
                                :href="`/books/${props.book.id}/edit`"
                                class="btn btn-primary w-100"
                            >
                                Edit Book Details
                            </Link>

                            <Link
                                v-if="can('issue books') && props.book.available_quantity > 0"
                                href="/borrowings/create"
                                class="btn btn-outline-primary w-100"
                            >
                                Issue This Book
                            </Link>

                            <Link
                                v-if="can('delete books')"
                                :href="`/books/${props.book.id}`"
                                method="delete"
                                as="button"
                                type="button"
                                class="btn btn-outline-danger w-100"
                                @click="confirm('Are you sure you want to delete this book?') || $event.preventDefault()"
                            >
                                Delete Book
                            </Link>
                        </template>

                        <!-- Member Actions -->
                        <template v-else>
                            <button
                                v-if="props.book.available_quantity > 0"
                                class="btn btn-primary w-100 py-2 fw-semibold"
                                @click="orderBook"
                            >
                                Order Book (Borrow)
                            </button>
                            <div v-else class="alert alert-warning mb-0 text-center py-2 small">
                                This book is currently out of stock. Please check back later.
                            </div>
                        </template>

                        <button
                            type="button"
                            class="btn btn-light w-100 mt-2"
                            @click="goBack"
                        >
                            Back to Catalog
                        </button>
                    </div>
                </div>

                <!-- Catalog Metadata Card -->
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-3 small text-muted">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Catalog ID:</span>
                            <span class="fw-semibold text-dark">#{{ props.book.id }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Added On:</span>
                            <span class="text-dark">{{ formatDate(props.book.created_at) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span>Last Updated:</span>
                            <span class="text-dark">{{ formatDate(props.book.updated_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
