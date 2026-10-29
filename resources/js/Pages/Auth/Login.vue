
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login – Library Management" />

    <div class="login-wrapper">
        <!-- Left branding panel -->
        <div class="brand-panel">
            <div class="brand-content">
                <div class="brand-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="48" height="48">
                        <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
                    </svg>
                </div>
                <h1 class="brand-title">Library<br>Management</h1>
                <p class="brand-subtitle">Your gateway to knowledge.<br>Manage books, borrowings & more.</p>

            </div>

            <!-- Decorative circles -->
            <div class="deco deco-1"></div>
            <div class="deco deco-2"></div>
            <div class="deco deco-3"></div>
        </div>

        <!-- Right form panel -->
        <div class="form-panel">
            <div class="form-card">
                <div class="form-header">
                    <h2>Welcome back</h2>
                    <p>Sign in to your account to continue</p>
                </div>

                <!-- Status message -->
                <div v-if="status" class="status-alert">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="login-form">
                    <!-- Email -->
                    <div class="field-group">
                        <label for="email" class="field-label">Email address</label>
                        <div class="input-wrapper" :class="{ 'has-error': form.errors.email }">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" />
                                <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" />
                            </svg>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="field-input"
                                placeholder="you@example.com"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="field-group">
                        <div class="label-row">
                            <label for="password" class="field-label">Password</label>
                            <Link v-if="canResetPassword" href="/forgot-password" class="forgot-link">Forgot password?</Link>
                        </div>
                        <div class="input-wrapper" :class="{ 'has-error': form.errors.password }">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                            </svg>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="field-input"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            />
                            <button type="button" class="toggle-pw" @click="showPassword = !showPassword" tabindex="-1">
                                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                    <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd" />
                                    <path d="M10.748 13.93l2.523 2.524a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember me -->
                    <div class="remember-row">
                        <label class="checkbox-label">
                            <input v-model="form.remember" type="checkbox" class="checkbox-input" id="remember" />
                            <span class="checkbox-custom"></span>
                            Remember me
                        </label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="submit-btn"
                        :disabled="form.processing"
                        :class="{ 'is-loading': form.processing }"
                    >
                        <span v-if="!form.processing">Sign in</span>
                        <span v-else class="spinner-wrap">
                            <svg class="spinner" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="40 20" />
                            </svg>
                            Signing in...
                        </span>
                    </button>
                </form>

                <p class="register-prompt">
                    Don't have an account?
                    <Link href="/register" class="register-link">Create one</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.login-wrapper {
    display: flex;
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
    background: #f8fafc;
}

/* ── Left brand panel ── */
.brand-panel {
    position: relative;
    width: 45%;
    background: linear-gradient(135deg, #1e3a5f 0%, #0f2849 40%, #162d4a 70%, #1a3a6e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 3rem;
}

@media (max-width: 768px) {
    .brand-panel { display: none; }
    .form-panel { width: 100%; }
}

.brand-content {
    position: relative;
    z-index: 2;
    color: #fff;
    text-align: center;
}

.brand-icon {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    backdrop-filter: blur(10px);
    color: #60a5fa;
}

.brand-title {
    font-size: 2.4rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 1rem;
    letter-spacing: -0.5px;
}

.brand-subtitle {
    font-size: 1rem;
    color: rgba(255,255,255,0.65);
    line-height: 1.6;
    margin-bottom: 2.5rem;
}

.brand-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 16px;
    padding: 1.2rem 2rem;
    backdrop-filter: blur(10px);
}

.stat { text-align: center; }
.stat-value {
    display: block;
    font-size: 1.4rem;
    font-weight: 700;
    color: #60a5fa;
}
.stat-label {
    display: block;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}
.stat-divider {
    width: 1px;
    height: 36px;
    background: rgba(255,255,255,0.15);
}

/* Decorative blobs */
.deco {
    position: absolute;
    border-radius: 50%;
    opacity: 0.08;
}
.deco-1 {
    width: 350px; height: 350px;
    background: #3b82f6;
    top: -80px; right: -80px;
}
.deco-2 {
    width: 250px; height: 250px;
    background: #60a5fa;
    bottom: -60px; left: -60px;
}
.deco-3 {
    width: 150px; height: 150px;
    background: #93c5fd;
    bottom: 30%; right: 15%;
}

/* ── Right form panel ── */
.form-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: #f8fafc;
}

.form-card {
    width: 100%;
    max-width: 420px;
}

.form-header {
    margin-bottom: 2rem;
}
.form-header h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.4rem;
    letter-spacing: -0.3px;
}
.form-header p {
    font-size: 0.95rem;
    color: #64748b;
}

/* Status alert */
.status-alert {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
}

/* Form fields */
.login-form { display: flex; flex-direction: column; gap: 1.25rem; }

.field-group { display: flex; flex-direction: column; gap: 0.4rem; }

.field-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.forgot-link {
    font-size: 0.8rem;
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}
.forgot-link:hover { color: #1d4ed8; }

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.input-wrapper:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
}
.input-wrapper.has-error {
    border-color: #ef4444;
}
.input-wrapper.has-error:focus-within {
    box-shadow: 0 0 0 3px rgba(239,68,68,0.12);
}

.input-icon {
    flex-shrink: 0;
    margin-left: 0.875rem;
    color: #94a3b8;
    pointer-events: none;
}

.field-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    padding: 0.75rem 0.875rem;
    font-size: 0.95rem;
    color: #1e293b;
    font-family: 'Inter', sans-serif;
}
.field-input::placeholder { color: #cbd5e1; }
.field-input::-ms-reveal,
.field-input::-ms-clear {
    display: none;
}

.toggle-pw {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0 0.875rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    transition: color 0.2s;
}
.toggle-pw:hover { color: #64748b; }

.field-error {
    font-size: 0.8rem;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* Remember me */
.remember-row { margin: -0.25rem 0; }

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.875rem;
    color: #475569;
    cursor: pointer;
    user-select: none;
}

.checkbox-input { display: none; }

.checkbox-custom {
    width: 18px;
    height: 18px;
    border: 1.5px solid #cbd5e1;
    border-radius: 5px;
    background: #fff;
    flex-shrink: 0;
    transition: all 0.2s;
    position: relative;
}

.checkbox-input:checked + .checkbox-custom {
    background: #2563eb;
    border-color: #2563eb;
}
.checkbox-input:checked + .checkbox-custom::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 1px;
    width: 6px;
    height: 10px;
    border: 2px solid #fff;
    border-top: none;
    border-left: none;
    transform: rotate(45deg);
}

/* Submit button */
.submit-btn {
    width: 100%;
    padding: 0.8rem;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(37,99,235,0.35);
    margin-top: 0.25rem;
}
.submit-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(37,99,235,0.45);
}
.submit-btn:active:not(:disabled) { transform: translateY(0); }
.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.spinner {
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Register prompt */
.register-prompt {
    text-align: center;
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 1.5rem;
}
.register-link {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}
.register-link:hover { color: #1d4ed8; }
</style>
