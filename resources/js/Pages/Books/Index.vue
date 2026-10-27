<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    books: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    selectedCategory: {
        type: [Number, null],
        default: null,
    },
    searchTerm: {
        type: String,
        default: '',
    },
});

const page = usePage();

const search = ref(props.searchTerm || '');
const categoryFilter = ref(props.selectedCategory ?? '');

const can = (permission) => {
    return page.props.auth?.permissions?.includes(permission) ?? false;
};

const isStaff = computed(() => {
    return can('create books') || can('edit books') || can('delete books');
});

const orderBook = (book) => {
    if (confirm(`Do you want to order "${book.title}"?\n\nThe loan duration is 14 days from today.`)) {
        router.post(`/books/${book.id}/order`);
    }
};

const applyFilter = () => {
    router.get('/books', {
        category_id: categoryFilter.value || undefined,
        search: search.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilter = () => {
    categoryFilter.value = '';
    search.value = '';
    router.get('/books', {}, {
        preserveState: true,
        replace: true,
    });
};

const filterByCategory = (catId) => {
    categoryFilter.value = catId;
    applyFilter();
};
</script>

<template>
    <Head title="Books Catalog" />

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
                <h2 class="h2 mb-1">{{ isStaff ? 'Books Management' : 'Library Book Catalog' }}</h2>
                <p class="text-muted mb-0">
                    {{ isStaff 
                        ? 'Manage books, monitor inventory, and organize books by category.' 
                        : 'Browse available books organized by categories and order them to borrow.' 
                    }}
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <Link
                    v-if="can('create books')"
                    href="/books/create"
                    class="btn btn-primary"
                >
                    + Add New Book
                </Link>
                <Link
                    v-if="isStaff"
                    href="/categories"
                    class="btn btn-outline-primary"
                >
                    🏷️ Manage Categories
                </Link>
                <Link
                    v-if="!isStaff"
                    href="/borrowings"
                    class="btn btn-outline-success"
                >
                    My Orders
                </Link>
                <!-- <Link href="/dashboard" class="btn btn-secondary">
                    Dashboard
                </Link> -->
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <!-- Category Dropdown Filter -->
                    <div class="col-md-5 col-lg-4">
                        <label class="form-label small text-muted mb-1 fw-semibold">
                            Filter by Category:
                        </label>
                        <select
                            v-model="categoryFilter"
                            class="form-select form-select-sm"
                            @change="applyFilter"
                        >
                            <option value="">All Categories (All Books)</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }} ({{ cat.books_count ?? 0 }} books)
                            </option>
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="col-md-5 col-lg-5">
                        <label class="form-label small text-muted mb-1 fw-semibold">
                            Search Title, Author, or ISBN:
                        </label>
                        <div class="input-group input-group-sm">
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                @keyup.enter="applyFilter"
                            >
                            <button
                                class="btn btn-primary"
                                type="button"
                                @click="applyFilter"
                            >
                                Search
                            </button>
                        </div>
                    </div>

                    <!-- Reset Action -->
                    <div class="col-md-2 col-lg-3 d-flex align-items-end pt-md-3">
                        <button
                            v-if="categoryFilter || search"
                            class="btn btn-outline-secondary btn-sm"
                            type="button"
                            @click="resetFilter"
                        >
                            ✕ Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Table -->
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>ISBN</th>
                            <th>Availability</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="book in books" :key="book.id">
                            <td>
                                <Link
                                    :href="`/books/${book.id}`"
                                    class="text-decoration-none text-dark fw-bold"
                                    title="View book details"
                                >
                                    {{ book.title }}
                                </Link>
                            </td>
                            <td>{{ book.author }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="badge bg-light text-primary border text-decoration-none border-primary-subtle"
                                    style="cursor: pointer;"
                                    title="Click to filter by this category"
                                    @click="book.category_id && filterByCategory(book.category_id)"
                                >
                                    {{ book.category?.name || book.category || 'General' }}
                                </button>
                            </td>
                            <td><small class="text-muted">{{ book.isbn }}</small></td>
                            <td>
                                <span
                                    v-if="book.available_quantity > 0"
                                    class="badge bg-success"
                                >
                                    {{ book.available_quantity }} Available
                                </span>
                                <span v-else class="badge bg-secondary">
                                    Out of Stock
                                </span>
                            </td>
                            <td class="text-end">
                                <!-- Staff Actions (View / Edit / Delete) -->
                                <div v-if="isStaff" class="btn-group btn-group-sm">
                                    <Link
                                        :href="`/books/${book.id}`"
                                        class="btn btn-outline-secondary"
                                        title="View book details"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        v-if="can('edit books')"
                                        :href="`/books/${book.id}/edit`"
                                        class="btn btn-outline-primary"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        v-if="can('delete books')"
                                        :href="`/books/${book.id}`"
                                        method="delete"
                                        as="button"
                                        type="button"
                                        class="btn btn-outline-danger"
                                        @click="confirm('Are you sure you want to delete this book?') || $event.preventDefault()"
                                    >
                                        Delete
                                    </Link>
                                </div>

                                <!-- Member Actions (View / Order Book) -->
                                <div v-else class="btn-group btn-group-sm">
                                    <Link
                                        :href="`/books/${book.id}`"
                                        class="btn btn-outline-secondary"
                                        title="View book details"
                                    >
                                        View
                                    </Link>
                                    <button
                                        v-if="book.available_quantity > 0"
                                        class="btn btn-primary"
                                        @click="orderBook(book)"
                                    >
                                        Order Book
                                    </button>
                                    <button
                                        v-else
                                        disabled
                                        class="btn btn-secondary opacity-50"
                                    >
                                        Unavailable
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="books.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <p class="mb-2 fs-5">No books match your criteria.</p>
                                <button
                                    v-if="categoryFilter || search"
                                    class="btn btn-sm btn-outline-primary me-2"
                                    @click="resetFilter"
                                >
                                    View All Books
                                </button>
                                <Link
                                    v-if="can('create books')"
                                    href="/books/create"
                                    class="btn btn-sm btn-primary"
                                >
                                    + Add New Book
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
