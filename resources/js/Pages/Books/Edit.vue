<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    book: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    title: props.book.title,
    author: props.book.author,
    isbn: props.book.isbn,
    category: props.book.category,
    quantity: props.book.quantity,
    price: props.book.price,
});

const submit = () => {
    form.put(`/books/${props.book.id}`);
};
</script>

<template>
    <Head title="Edit Book" />

    <div class="container mt-5">
        <h1>Edit Book</h1>

        <form @submit.prevent="submit">
            <div class="mb-3">
                <label class="form-label">Book Title</label>
                <input v-model="form.title" type="text" class="form-control">
                <div v-if="form.errors.title" class="text-danger">
                    {{ form.errors.title }}
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Author</label>
                <input v-model="form.author" type="text" class="form-control">
                <div v-if="form.errors.author" class="text-danger">
                    {{ form.errors.author }}
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">ISBN</label>
                <input v-model="form.isbn" type="text" class="form-control">
                <div v-if="form.errors.isbn" class="text-danger">
                    {{ form.errors.isbn }}
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <input v-model="form.category" type="text" class="form-control">
                <div v-if="form.errors.category" class="text-danger">
                    {{ form.errors.category }}
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input v-model.number="form.quantity" type="number" min="1" class="form-control">
                <div v-if="form.errors.quantity" class="text-danger">
                    {{ form.errors.quantity }}
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Price</label>
                <input v-model.number="form.price" type="number" min="0" step="0.01" class="form-control">
                <div v-if="form.errors.price" class="text-danger">
                    {{ form.errors.price }}
                </div>
            </div>

            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Updating...' : 'Update Book' }}
            </button>

            <Link href="/books" class="btn btn-secondary ms-2">
                Cancel
            </Link>
        </form>
    </div>
</template>
