<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';

defineProps({
    borrowings: {
        type: Array,
        default: () => [],
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
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const dueDate = new Date(dueDateStr);
    dueDate.setHours(0, 0, 0, 0);
    const diffTime = today.getTime() - dueDate.getTime();
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
};
</script>

<template>
    <Head title="Borrowings" />

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Borrowing Records</h2>
                <p class="text-muted mb-0">Track book issues, deadlines, and overdue fines ($5.00/day).</p>
            </div>
            <div>
                <Link
                    v-if="can('issue books')"
                    href="/borrowings/create"
                    class="btn btn-primary"
                >
                    Issue Book
                </Link>
                <Link href="/dashboard" class="btn btn-secondary ms-2">
                    Dashboard
                </Link>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Member</th>
                            <th>Book Title</th>
                            <th>Issue Date</th>
                            <th>Deadline Date</th>
                            <th>Returned Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in borrowings" :key="item.id">
                            <td>
                                <strong>{{ item.user?.name }}</strong>
                                <br />
                                <small class="text-muted">{{ item.user?.email }}</small>
                            </td>
                            <td>{{ item.book?.title }}</td>
                            <td>{{ item.borrowed_at }}</td>
                            <td>
                                <strong>{{ item.due_date }}</strong>
                            </td>
                            <td>{{ item.returned_at ?? '-' }}</td>
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
                            <td>
                                <button
                                    v-if="item.status !== 'returned' && can('return books')"
                                    @click="returnBook(item)"
                                    class="btn btn-sm btn-outline-success"
                                >
                                    Return Book
                                </button>
                                <span v-else class="text-muted small">Completed</span>
                            </td>
                        </tr>

                        <tr v-if="borrowings.length === 0">
                            <td colspan="8" class="text-center py-4 text-muted">
                                No borrowing records found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
