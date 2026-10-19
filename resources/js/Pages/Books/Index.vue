<script setup>
import { Head, Link,usePage } from '@inertiajs/vue3';

defineProps({
    books: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const can = (permission) => {
    return page.props.auth?.permissions?.includes(permission) ?? false;
};
</script>

<template>
    <Head title="Books" />

    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Library Books</h1>

            <Link
                v-if="can('create books')"
                href="/books/create"
                class="rounded bg-blue-600 px-4 py-2 text-white"
            >
                Add Book
            </Link>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-3 text-left">Title</th>
                        <th class="border p-3 text-left">Author</th>
                        <th class="border p-3 text-left">ISBN</th>
                        <th class="border p-3 text-left">Quantity</th>
                        <th class="border p-3 text-left">Available</th>
                        <th class="border p-3 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="book in books" :key="book.id">
                        <td class="border p-3">{{ book.title }}</td>
                        <td class="border p-3">{{ book.author }}</td>
                        <td class="border p-3">{{ book.isbn }}</td>
                        <td class="border p-3">{{ book.quantity }}</td>
                        <td class="border p-3">{{ book.available_quantity }}</td>
                        <td class="border p-3">
                            <Link
                                v-if="can('edit books')"
                                :href="`/books/${book.id}/edit`"
                                class="mr-3 text-blue-600"
                            >
                                Edit
                            </Link>

                            <Link
                                v-if="can('delete books')"
                                :href="`/books/${book.id}`"
                                method="delete"
                                as="button"
                                type="button"
                                class="text-red-600"
                                @click="confirm('Are you sure you want to delete this book?') || $event.preventDefault()"
                            >
                                Delete
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="books.length === 0">
                        <td colspan="6" class="p-4 text-center">
                            No books found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
