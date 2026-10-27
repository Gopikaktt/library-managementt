<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';

defineProps({
    books: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
});

const todayDate = () => new Date().toISOString().split('T')[0];

const defaultDueDate = () => {
    const d = new Date();
    d.setDate(d.getDate() + 14); // 14 days loan period by default
    return d.toISOString().split('T')[0];
};

const form = useForm({
    user_id: '',
    book_id: '',
    borrowed_at: todayDate(),
    due_date: defaultDueDate(),
});

const submit = () => {
    form.post('/borrowings');
};
</script>

<template>
    <Head title="Issue Book" />

    <Navbar />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Issue Book to Member</h3>

                        <form @submit.prevent="submit">
                            <!-- Member Selection -->
                            <div class="mb-3">
                                <label class="form-label">Select Member</label>
                                <select v-model="form.user_id" class="form-select" required>
                                    <option value="" disabled>Choose a member...</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                                <div v-if="form.errors.user_id" class="text-danger mt-1">
                                    {{ form.errors.user_id }}
                                </div>
                            </div>

                            <!-- Book Selection -->
                            <div class="mb-3">
                                <label class="form-label">Select Book</label>
                                <select v-model="form.book_id" class="form-select" required>
                                    <option value="" disabled>Choose an available book...</option>
                                    <option v-for="book in books" :key="book.id" :value="book.id">
                                        {{ book.title }} ({{ book.available_quantity }} available)
                                    </option>
                                </select>
                                <div v-if="form.errors.book_id" class="text-danger mt-1">
                                    {{ form.errors.book_id }}
                                </div>
                            </div>

                            <!-- Dates Row -->
                            <div class="row mb-3">
                                <!-- Issue Date -->
                                <div class="col-md-6">
                                    <label class="form-label">Issue Date</label>
                                    <input
                                        v-model="form.borrowed_at"
                                        type="date"
                                        class="form-control"
                                        required
                                    />
                                    <div v-if="form.errors.borrowed_at" class="text-danger mt-1">
                                        {{ form.errors.borrowed_at }}
                                    </div>
                                </div>

                                <!-- Deadline / Due Date -->
                                <div class="col-md-6">
                                    <label class="form-label">Deadline (Due Date)</label>
                                    <input
                                        v-model="form.due_date"
                                        type="date"
                                        :min="form.borrowed_at"
                                        class="form-control"
                                        required
                                    />
                                    <div v-if="form.errors.due_date" class="text-danger mt-1">
                                        {{ form.errors.due_date }}
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info py-2 small mb-4">
                                <strong>Note:</strong> If the book is returned after the deadline, a late fine of <strong>$5.00 per day</strong> will be automatically charged.
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Issuing...' : 'Issue Book' }}
                            </button>

                            <Link href="/borrowings" class="btn btn-secondary ms-2">
                                Cancel
                            </Link>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
