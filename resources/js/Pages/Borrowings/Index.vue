<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';

defineProps({
    borrowings: {
        type: Array,
        default: () => [],
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

const returnBook = (item) => {
    const daysLate = calculateOverdueDays(item.due_date);
    let message = 'Are you sure you want to mark this book as returned?';
    if (daysLate > 0) {
        message += `\n\n⚠️ NOTE: This book is ${daysLate} day(s) overdue!\nA fine of $${(daysLate * 5).toFixed(2)} ($5/day) will be recorded.`;
    }

    if (confirm(message)) {
        router.post(`/borrowings/${item.id}/return`);
    }
};

const calculateOverdueDays = (dueDateStr) => {
    if (!dueDateStr) return 0;
    const cleanDue = String(dueDateStr).split('T')[0];
    const dueParts = cleanDue.split('-');
    const dueDate = new Date(parseInt(dueParts[0], 10), parseInt(dueParts[1], 10) - 1, parseInt(dueParts[2], 10));
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    dueDate.setHours(0, 0, 0, 0);
    const diffTime = today.getTime() - dueDate.getTime();
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
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
</script>

<template>
    <Head :title="isStaff ? 'Borrowing Records' : 'My Orders'" />

    <Navbar />

    <div class="container py-4">
        <!-- Flash messages -->
        <div v-if="page.props.flash?.success" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ page.props.flash.success }}
        </div>
        <div v-if="page.props.flash?.error" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ page.props.flash.error }}
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ isStaff ? 'Borrowing Records' : 'My Orders & Borrowings' }}</h2>
                <p class="text-muted mb-0">
                    {{ isStaff 
                        ? 'Track book issues, deadlines, and overdue fines ($5.00/day).' 
                        : 'Review your active book loans, deadlines, and past order history.' 
                    }}
                </p>
            </div>
            <div>
                <Link
                    v-if="can('issue books')"
                    href="/borrowings/create"
                    class="btn btn-primary"
                >
                    Issue Book
                </Link>
                <Link
                    v-if="!isStaff"
                    href="/books"
                    class="btn btn-primary"
                >
                    + Order Books
                </Link>
                <!-- <Link href="/dashboard" class="btn btn-secondary ms-2">
                    Dashboard
                </Link> -->
            </div>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th v-if="isStaff">Member</th>
                            <th>Book Title</th>
                            <th>{{ isStaff ? 'Issue Date' : 'Order Date' }}</th>
                            <th>Return Deadline</th>
                            <th>Returned Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                            <th class="text-end">{{ isStaff ? 'Action' : 'Order Status' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in borrowings" :key="item.id">
                            <td v-if="isStaff">
                                <strong>{{ item.user?.name }}</strong>
                                <br />
                                <small class="text-muted">{{ item.user?.email }}</small>
                            </td>
                            <td>
                                <Link
                                    :href="`/borrowings/${item.id}`"
                                    class="text-decoration-none text-dark fw-bold"
                                    title="View borrowing details"
                                >
                                    {{ item.book?.title }}
                                </Link>
                                <br v-if="item.book?.isbn" />
                                <small v-if="item.book?.isbn" class="text-muted">ISBN: {{ item.book.isbn }}</small>
                            </td>
                            <td>{{ formatDate(item.borrowed_at) }}</td>
                            <td>
                                <strong>{{ formatDate(item.due_date) }}</strong>
                            </td>
                            <td>{{ item.returned_at ? formatDate(item.returned_at) : '-' }}</td>
                            <td>
                                <span
                                    v-if="item.status === 'returned'"
                                    class="badge bg-success"
                                >
                                    Returned
                                </span>
                                <span
                                    v-else-if="calculateOverdueDays(item.due_date) > 0"
                                    class="badge bg-danger"
                                >
                                    Overdue ({{ calculateOverdueDays(item.due_date) }}d)
                                </span>
                                <span v-else class="badge bg-warning text-dark">
                                    Borrowed
                                </span>
                            </td>
                            <td>
                                <span v-if="item.status === 'returned'">
                                    ${{ Number(item.fine).toFixed(2) }}
                                </span>
                                <span
                                    v-else-if="calculateOverdueDays(item.due_date) > 0"
                                    class="text-danger fw-bold"
                                >
                                    ${{ (calculateOverdueDays(item.due_date) * 5).toFixed(2) }}
                                    <small class="d-block text-muted">(accruing)</small>
                                </span>
                                <span v-else class="text-muted">
                                    $0.00
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1 justify-content-end">
                                    <Link
                                        :href="`/borrowings/${item.id}`"
                                        class="btn btn-sm btn-outline-info"
                                        title="View Record"
                                    >
                                        View
                                    </Link>

                                    <!-- Staff Action: Return Book -->
                                    <template v-if="isStaff">
                                        <button
                                            v-if="item.status !== 'returned' && can('return books')"
                                            @click="returnBook(item)"
                                            class="btn btn-sm btn-outline-success"
                                        >
                                            Return Book
                                        </button>
                                        <span v-else class="text-muted small ms-1">Completed</span>
                                    </template>

                                    <!-- Member Action: Status Indicator -->
                                    <template v-else>
                                        <span
                                            v-if="item.status === 'returned'"
                                            class="badge bg-light text-success border"
                                        >
                                            ✓ Completed
                                        </span>
                                        <span
                                            v-else-if="calculateOverdueDays(item.due_date) > 0"
                                            class="badge bg-danger-subtle text-danger"
                                        >
                                            Return to Desk
                                        </span>
                                        <span
                                            v-else
                                            class="badge bg-primary-subtle text-primary"
                                        >
                                            Active Loan
                                        </span>
                                    </template>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="borrowings.length === 0">
                            <td :colspan="isStaff ? 8 : 7" class="text-center py-5 text-muted">
                                <p class="mb-2">No borrowing records found.</p>
                                <Link v-if="!isStaff" href="/books" class="btn btn-sm btn-primary">
                                    Browse Books to Place an Order
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
