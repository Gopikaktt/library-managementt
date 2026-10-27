<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    title: '',
    author: '',
    isbn: '',
    category_id: '',
    quantity: 1,
    price: 0,
});

const showNewCategoryModal = ref(false);
const newCategoryForm = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post('/books');
};

const createCategory = () => {
    newCategoryForm.post('/categories', {
        preserveScroll: true,
        onSuccess: () => {
            // Find newly added category in updated props or reset
            newCategoryForm.reset();
            showNewCategoryModal.value = false;
        },
    });
};

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/books');
    }
};
</script>

<template>
    <Head title="Add Book" />

    <Navbar />

    <div class="container py-4" style="max-width: 720px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">Add New Book</h1>
                <p class="text-muted mb-0">Enter the details of the book to add it to the library catalog.</p>
            </div>
            <button
                type="button"
                class="btn-close"
                style="font-size: 1.1rem; cursor: pointer;"
                aria-label="Close"
                title="Close and return to back page"
                @click="goBack"
            ></button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form @submit.prevent="submit">
                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Book Title <span class="text-danger">*</span></label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.title }"
                        >
                        <div v-if="form.errors.title" class="invalid-feedback">
                            {{ form.errors.title }}
                        </div>
                    </div>

                    <!-- Author -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Author <span class="text-danger">*</span></label>
                        <input
                            v-model="form.author"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.author }"
                        >
                        <div v-if="form.errors.author" class="invalid-feedback">
                            {{ form.errors.author }}
                        </div>
                    </div>

                    <!-- ISBN -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ISBN <span class="text-danger">*</span></label>
                        <input
                            v-model="form.isbn"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.isbn }"
                        >
                        <div v-if="form.errors.isbn" class="invalid-feedback">
                            {{ form.errors.isbn }}
                        </div>
                    </div>

                    <!-- Category Dropdown -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">
                                Category <span class="text-danger">*</span>
                            </label>
                            <div>
                                <button
                                    type="button"
                                    class="btn btn-link btn-sm p-0 text-decoration-none me-3"
                                    @click="showNewCategoryModal = true"
                                >
                                    + Add New Category
                                </button>
                                <Link
                                    href="/categories"
                                    class="small text-muted text-decoration-none"
                                >
                                    Manage Categories &rarr;
                                </Link>
                            </div>
                        </div>

                        <select
                            v-model="form.category_id"
                            class="form-select"
                            :class="{ 'is-invalid': form.errors.category_id }"
                        >
                            <option value="" disabled>-- Select a Category --</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.category_id" class="invalid-feedback">
                            {{ form.errors.category_id }}
                        </div>
                        <div v-if="categories.length === 0" class="form-text text-warning">
                            No categories available yet. Please add a category first.
                        </div>
                    </div>

                    <!-- Quantity & Price Row -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Quantity / Stock <span class="text-danger">*</span></label>
                            <input
                                v-model.number="form.quantity"
                                type="number"
                                min="1"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.quantity }"
                            >
                            <div v-if="form.errors.quantity" class="invalid-feedback">
                                {{ form.errors.quantity }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Price ($) <span class="text-danger">*</span></label>
                            <input
                                v-model.number="form.price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.price }"
                            >
                            <div v-if="form.errors.price" class="invalid-feedback">
                                {{ form.errors.price }}
                            </div>
                        </div>
                    </div>

                    <!-- Submit & Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button
                            type="button"
                            class="btn btn-light px-4"
                            @click="goBack"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary px-4"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Book' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Add Category Modal -->
        <div
            v-if="showNewCategoryModal"
            class="modal fade show d-block"
            tabindex="-1"
            style="background: rgba(0, 0, 0, 0.5);"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Category</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="showNewCategoryModal = false"
                        ></button>
                    </div>
                    <form @submit.prevent="createCategory">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                <input
                                    v-model="newCategoryForm.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': newCategoryForm.errors.name }"
                                    required
                                    autofocus
                                >
                                <div v-if="newCategoryForm.errors.name" class="invalid-feedback">
                                    {{ newCategoryForm.errors.name }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description (Optional)</label>
                                <textarea
                                    v-model="newCategoryForm.description"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-light"
                                @click="showNewCategoryModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="newCategoryForm.processing"
                            >
                                {{ newCategoryForm.processing ? 'Creating...' : 'Create Category' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
