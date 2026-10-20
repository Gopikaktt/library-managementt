
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
};

</script>

<template>
    <Head title="Register" />

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Library Management</h2>
                        <h4 class="mb-3">Create Account</h4>

                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <div v-if="form.errors.name" class="text-danger mt-1">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    required
                                    autocomplete="username"
                                />
                                <div v-if="form.errors.email" class="text-danger mt-1">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="form-control"
                                    required
                                    autocomplete="new-password"
                                />
                                <div v-if="form.errors.password" class="text-danger mt-1">
                                    {{ form.errors.password }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="form-control"
                                    required
                                    autocomplete="new-password"
                                />
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Registering...' : 'Register' }}
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            Already registered?
                            <Link href="/login">Login</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
