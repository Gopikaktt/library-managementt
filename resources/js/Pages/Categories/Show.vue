<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const can = (permission) => {
    return page.props.auth?.permissions?.includes(permission) ?? false;
};

const isStaff = computed(() => {
    return can('create books') || can('edit books') || can('delete books');
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/categories');
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
    <Head :title="`Category: ${props.category.name}`" />

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
                    🏷️
                </span>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary fs-6">
                            {{ props.category.books_count }} {{ props.category.books_count === 1 ? 'Book' : 'Books' }}
                        </span>
                    </div>
                    <h1 class="h2 mb-1 fw-bold">{{ props.category.name }}</h1>
                    <p class="text-muted mb-0">Library Book Category</p>
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

        <div class="row g-4">
            <!-- Left: Description and Books list -->
            <div class="col-lg-8">
                <!-- Description Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            About this Category
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-0 fs-6 text-dark" v-if="props.category.description">
                            {{ props.category.description }}
                        </p>
                        <p class="mb-0 text-muted fst-italic" v-else>
                            No detailed description provided for this category.
                        </p>
                    </div>
                </div>

                <!-- Books in this Category Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Books in this Category ({{ props.category.books?.length || props.category.books_count || 0 }})
                        </h5>
                        <Link
                            v-if="props.category.books_count > 0"
                            :href="`/books?category_id=${props.category.id}`"
                            class="btn btn-sm btn-outline-primary"
                        >
                            Open in Books Catalog &rarr;
                        </Link>
                    </div>

                    <div v-if="props.category.books && props.category.books.length > 0" class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
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
                                <tr v-for="b in props.category.books" :key="b.id">
                                    <td>
                                        <Link :href="`/books/${b.id}`" class="text-decoration-none text-dark fw-bold">
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
                                        <Link :href="`/books/${b.id}`" class="btn btn-sm btn-outline-info">
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-5 text-muted">
                        <p class="mb-0 fs-6">No books are currently assigned to this category.</p>
                    </div>
                </div>
            </div>

            <!-- Right: Actions & Metadata -->
            <div class="col-lg-4">
                <!-- Actions Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-semibold text-secondary">
                            Actions
                        </h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column gap-2">
                        <Link
                            v-if="props.category.books_count > 0"
                            :href="`/books?category_id=${props.category.id}`"
                            class="btn btn-primary w-100"
                        >
                            View Books in Catalog ({{ props.category.books_count }})
                        </Link>
                        <Link
                            href="/categories"
                            class="btn btn-outline-secondary w-100"
                        >
                            Back to All Categories
                        </Link>
                        <button
                            type="button"
                            class="btn btn-light w-100"
                            @click="goBack"
                        >
                            Close & Go Back
                        </button>
                    </div>
                </div>

                <!-- Metadata Card -->
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-3 small text-muted">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Category ID:</span>
                            <span class="fw-semibold text-dark">#{{ props.category.id }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Total Books:</span>
                            <span class="fw-semibold text-dark">{{ props.category.books_count }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Created At:</span>
                            <span class="text-dark">{{ formatDate(props.category.created_at) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span>Last Updated:</span>
                            <span class="text-dark">{{ formatDate(props.category.updated_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
