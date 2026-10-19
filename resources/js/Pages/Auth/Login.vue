
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login');
};
</script>

<template>
    <Head title="Login" />

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Library Management</h2>
                        <h4 class="mb-3">Login</h4>

                        <div v-if="status" class="alert alert-success">
                            {{ status }}
                        </div>

                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    required
                                    autofocus
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
                                    autocomplete="current-password"
                                />
                                <div v-if="form.errors.password" class="text-danger mt-1">
                                    {{ form.errors.password }}
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="form-check-input"
                                    id="remember"
                                />
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Logging in...' : 'Login' }}
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <Link href="/register">Create an account</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
