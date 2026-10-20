
<script setup>
import { Head, Link, router,usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user;

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="container py-5">
        <h1 class="mb-3">Library Management Dashboard</h1>

        <p v-if="user">
            Welcome, {{ user.name }}!
        </p>

         <button @click="logout" class="btn btn-danger">
                Logout
            </button>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Books</h5>
                <p class="card-text">Browse and manage library books.</p>

                <Link href="/books" class="btn btn-primary">
                    View Books
                </Link>
            </div>
        </div>

        <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Borrowings</h5>
            <p class="card-text">Issue books to members and track returns.</p>
            <Link href="/borrowings" class="btn btn-success">
                Manage Borrowings
            </Link>
        </div>
    </div>
    </div>
</template>
