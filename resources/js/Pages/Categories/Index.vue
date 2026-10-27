<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const can = (permission) => {
    return page.props.auth?.permissions?.includes(permission) ?? false;
};

const isStaff = computed(() => {
    return can('create books') || can('edit books') || can('delete books');
});

// Create Category Modal & Form
const showCreateModal = ref(false);

const createForm = useForm({
    name: '',
    description: '',
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
    createForm.clearErrors();
};

const submitCreate = () => {
    createForm.post('/categories', {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
        },
    });
};

// View Category State
const viewingCategory = ref(null);
const viewCategory = (cat) => {
    viewingCategory.value = cat;
};
const startEditFromView = (cat) => {
    viewingCategory.value = null;
    startEdit(cat);
};

// Edit Category State & Form
const editingCategory = ref(null);
const editForm = useForm({
    name: '',
    description: '',
});

const startEdit = (cat) => {
    editingCategory.value = cat;
    editForm.name = cat.name;
    editForm.description = cat.description || '';
};

const cancelEdit = () => {
    editingCategory.value = null;
    editForm.reset();
};

const submitEdit = () => {
    if (!editingCategory.value) return;
    editForm.put(`/categories/${editingCategory.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            cancelEdit();
        },
    });
};

// Delete Category Form
const deleteForm = useForm({});
const deleteCategory = (cat) => {
    const warning = cat.books_count > 0 
        ? `\n\nNote: There are ${cat.books_count} book(s) associated with this category. They will become unassigned.`
        : '';
    if (confirm(`Are you sure you want to delete the category "${cat.name}"?${warning}`)) {
        deleteForm.delete(`/categories/${cat.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Category Management" />

    <Navbar />

    <div class="container py-4">
        <!-- Flash messages -->
        <div v-if="page.props.flash?.success" class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ page.props.flash.success }}
        </div>
        <div v-if="page.props.flash?.error" class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ page.props.flash.error }}
        </div>

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h2 mb-1">Book Categories</h1>
                <p class="text-muted mb-0">
                    Organize your library catalog into categories so books can be easily found and categorized.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button
                    v-if="can('create books')"
                    type="button"
                    class="btn btn-primary"
                    @click="openCreateModal"
                >
                    + Add Category
                </button>
                <!-- <Link href="/books" class="btn btn-outline-secondary">
                    &larr; Book Catalog
                </Link> -->
            </div>
        </div>

        <!-- Categories List Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    Available Categories
                    <span class="badge bg-primary-subtle text-primary ms-2 fs-6">
                        {{ categories.length }}
                    </span>
                </h5>
                <button
                    v-if="can('create books')"
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="openCreateModal"
                >
                    + Add Category
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Category Name</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 15%;">Books</th>
                            <th class="text-end" style="width: 22%;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="cat in categories" :key="cat.id">
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="p-2 bg-light rounded-circle me-2 text-primary fs-6">
                                        🏷️
                                    </span>
                                    <button
                                        type="button"
                                        class="btn btn-link text-decoration-none text-dark fw-bold p-0 text-start"
                                        title="Click to view category details"
                                        @click="viewCategory(cat)"
                                    >
                                        {{ cat.name }}
                                    </button>
                                </div>
                            </td>
                            <td>
                                <span v-if="cat.description" class="text-secondary small">
                                    {{ cat.description }}
                                </span>
                                <span v-else class="text-muted fst-italic small">
                                    No description provided
                                </span>
                            </td>
                            <td class="text-center">
                                <Link
                                    :href="`/books?category_id=${cat.id}`"
                                    class="badge bg-info-subtle text-dark border text-decoration-none px-2 py-1"
                                    title="Click to view books in this category"
                                >
                                    {{ cat.books_count }} {{ cat.books_count === 1 ? 'Book' : 'Books' }}
                                </Link>
                            </td>
                            <td class="text-end">
                                <div v-if="isStaff" class="btn-group btn-group-sm">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        title="View category details"
                                        @click="viewCategory(cat)"
                                    >
                                        View
                                    </button>
                                    <button
                                        v-if="can('edit books')"
                                        type="button"
                                        class="btn btn-outline-primary"
                                        @click="startEdit(cat)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="can('delete books')"
                                        type="button"
                                        class="btn btn-outline-danger"
                                        :disabled="deleteForm.processing"
                                        @click="deleteCategory(cat)"
                                    >
                                        Delete
                                    </button>
                                </div>
                                <div v-else>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="View category details"
                                        @click="viewCategory(cat)"
                                    >
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="categories.length === 0">
                            <td colspan="4" class="text-center py-5 text-muted">
                                <p class="mb-2">No categories found.</p>
                                <button
                                    v-if="can('create books')"
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    @click="openCreateModal"
                                >
                                    + Add First Category
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Category Modal -->
        <div
            v-if="showCreateModal"
            class="modal fade show d-block"
            tabindex="-1"
            style="background: rgba(0, 0, 0, 0.5);"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-semibold">Add New Category</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="closeCreateModal"
                        ></button>
                    </div>
                    <form @submit.prevent="submitCreate">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Category Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    v-model="createForm.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': createForm.errors.name }"
                                    required
                                    autofocus
                                >
                                <div v-if="createForm.errors.name" class="invalid-feedback">
                                    {{ createForm.errors.name }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Description <small class="text-muted fw-normal">(Optional)</small>
                                </label>
                                <textarea
                                    v-model="createForm.description"
                                    class="form-control"
                                    :class="{ 'is-invalid': createForm.errors.description }"
                                    rows="3"
                                ></textarea>
                                <div v-if="createForm.errors.description" class="invalid-feedback">
                                    {{ createForm.errors.description }}
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-light"
                                @click="closeCreateModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="createForm.processing"
                            >
                                {{ createForm.processing ? 'Saving...' : 'Add Category' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div
            v-if="editingCategory"
            class="modal fade show d-block"
            tabindex="-1"
            style="background: rgba(0, 0, 0, 0.5);"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Category</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="cancelEdit"
                        ></button>
                    </div>
                    <form @submit.prevent="submitEdit">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Category Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    v-model="editForm.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': editForm.errors.name }"
                                    required
                                    autofocus
                                >
                                <div v-if="editForm.errors.name" class="invalid-feedback">
                                    {{ editForm.errors.name }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Description (Optional)
                                </label>
                                <textarea
                                    v-model="editForm.description"
                                    class="form-control"
                                    :class="{ 'is-invalid': editForm.errors.description }"
                                    rows="3"
                                ></textarea>
                                <div v-if="editForm.errors.description" class="invalid-feedback">
                                    {{ editForm.errors.description }}
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-light"
                                @click="cancelEdit"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="editForm.processing"
                            >
                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Category Modal -->
        <div
            v-if="viewingCategory"
            class="modal fade show d-block"
            tabindex="-1"
            style="background: rgba(0, 0, 0, 0.5);"
        >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="p-2 bg-light rounded-circle text-primary fs-5">🏷️</span>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ viewingCategory.name }}</h5>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ viewingCategory.books_count }} {{ viewingCategory.books_count === 1 ? 'Book' : 'Books' }}
                                </span>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            @click="viewingCategory = null"
                        ></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Description Section -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Description</label>
                            <p class="mb-0 text-dark bg-light p-3 rounded-3" v-if="viewingCategory.description">
                                {{ viewingCategory.description }}
                            </p>
                            <p class="mb-0 text-muted fst-italic bg-light p-3 rounded-3" v-else>
                                No description provided for this category.
                            </p>
                        </div>

                        <!-- Books in this Category -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold text-secondary small text-uppercase mb-0">
                                    Books in this Category ({{ viewingCategory.books?.length || viewingCategory.books_count || 0 }})
                                </label>
                                <Link
                                    v-if="viewingCategory.books_count > 0"
                                    :href="`/books?category_id=${viewingCategory.id}`"
                                    class="small text-decoration-none"
                                >
                                    Browse in Catalog &rarr;
                                </Link>
                            </div>

                            <div v-if="viewingCategory.books && viewingCategory.books.length > 0" class="table-responsive border rounded-3">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>ISBN</th>
                                            <th class="text-center">Availability</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="b in viewingCategory.books" :key="b.id">
                                            <td>
                                                <Link :href="`/books/${b.id}`" class="text-decoration-none text-dark fw-semibold">
                                                    {{ b.title }}
                                                </Link>
                                            </td>
                                            <td class="text-muted small">{{ b.author }}</td>
                                            <td><small class="font-monospace text-muted">{{ b.isbn }}</small></td>
                                            <td class="text-center">
                                                <span v-if="b.available_quantity > 0" class="badge bg-success">
                                                    {{ b.available_quantity }} Available
                                                </span>
                                                <span v-else class="badge bg-secondary">
                                                    Out of Stock
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <Link :href="`/books/${b.id}`" class="btn btn-xs btn-outline-info py-0 px-2" style="font-size: 0.75rem;">
                                                    View
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-4 bg-light rounded-3 text-muted">
                                <p class="mb-0">No books currently assigned to this category.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <Link
                            v-if="viewingCategory.books_count > 0"
                            :href="`/books?category_id=${viewingCategory.id}`"
                            class="btn btn-outline-primary me-auto"
                        >
                            View Filtered Books ({{ viewingCategory.books_count }})
                        </Link>
                        <button
                            v-if="can('edit books')"
                            type="button"
                            class="btn btn-primary"
                            @click="startEditFromView(viewingCategory)"
                        >
                            Edit Category
                        </button>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="viewingCategory = null"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
