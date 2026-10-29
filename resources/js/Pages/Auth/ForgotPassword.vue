
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <Head title="Forgot Password – Library Management" />

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
                <p class="brand-subtitle">No worries, we'll send you<br>a reset link to your email.</p>
            </div>
            <div class="deco deco-1"></div>
            <div class="deco deco-2"></div>
            <div class="deco deco-3"></div>
        </div>

        <!-- Right form panel -->
        <div class="form-panel">
            <div class="form-card">
                <div class="form-header">
                    <div class="lock-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2>Forgot password?</h2>
                    <p>Enter your email and we'll send you a reset link</p>
                </div>

                <!-- Success status -->
                <div v-if="status" class="status-alert">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="fp-form">
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

                    <button
                        type="submit"
                        class="submit-btn"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">Send reset link</span>
                        <span v-else class="spinner-wrap">
                            <svg class="spinner" viewBox="0 0 24 24" fill="none" width="20" height="20">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="40 20" />
                            </svg>
                            Sending...
                        </span>
                    </button>
                </form>

                <p class="back-prompt">
                    <Link href="/login" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="15" height="15">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                        </svg>
                        Back to sign in
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.login-wrapper {
    display: flex;
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
    background: #f8fafc;
}

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
    .form-panel  { width: 100%; }
}

.brand-content { position: relative; z-index: 2; color: #fff; text-align: center; }

.brand-icon {
    width: 80px; height: 80px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.5rem;
    backdrop-filter: blur(10px);
    color: #60a5fa;
}

.brand-title {
    font-size: 2.4rem; font-weight: 700;
    line-height: 1.2; margin-bottom: 1rem; letter-spacing: -0.5px;
}
.brand-subtitle { font-size: 1rem; color: rgba(255,255,255,0.65); line-height: 1.6; }

.deco { position: absolute; border-radius: 50%; opacity: 0.08; }
.deco-1 { width: 350px; height: 350px; background: #3b82f6; top: -80px; right: -80px; }
.deco-2 { width: 250px; height: 250px; background: #60a5fa; bottom: -60px; left: -60px; }
.deco-3 { width: 150px; height: 150px; background: #93c5fd; bottom: 30%; right: 15%; }

.form-panel {
    flex: 1; display: flex; align-items: center;
    justify-content: center; padding: 2rem; background: #f8fafc;
}
.form-card { width: 100%; max-width: 420px; }

.lock-icon {
    width: 56px; height: 56px;
    background: #eff6ff;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #2563eb;
    margin-bottom: 1.25rem;
}

.form-header { margin-bottom: 1.75rem; }
.form-header h2 { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem; letter-spacing: -0.3px; }
.form-header p  { font-size: 0.95rem; color: #64748b; }

.status-alert {
    display: flex; align-items: center; gap: 0.5rem;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    color: #166534; border-radius: 10px;
    padding: 0.75rem 1rem; font-size: 0.875rem; margin-bottom: 1.25rem;
}

.fp-form { display: flex; flex-direction: column; gap: 1.25rem; }
.field-group { display: flex; flex-direction: column; gap: 0.4rem; }
.field-label { font-size: 0.875rem; font-weight: 500; color: #374151; }

.input-wrapper {
    position: relative; display: flex; align-items: center;
    background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.input-wrapper:focus-within { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
.input-wrapper.has-error { border-color: #ef4444; }

.input-icon { flex-shrink: 0; margin-left: 0.875rem; color: #94a3b8; pointer-events: none; }

.field-input {
    flex: 1; border: none; outline: none; background: transparent;
    padding: 0.75rem 0.875rem; font-size: 0.95rem; color: #1e293b;
    font-family: 'Inter', sans-serif;
}
.field-input::placeholder { color: #cbd5e1; }
.field-error { font-size: 0.8rem; color: #ef4444; }

.submit-btn {
    width: 100%; padding: 0.8rem;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff; border: none; border-radius: 10px;
    font-size: 0.95rem; font-weight: 600; font-family: 'Inter', sans-serif;
    cursor: pointer; transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(37,99,235,0.35);
}
.submit-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.45); }
.submit-btn:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner-wrap { display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.spinner { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.back-prompt { text-align: center; margin-top: 1.5rem; }
.back-link {
    display: inline-flex; align-items: center; gap: 0.35rem;
    color: #2563eb; font-size: 0.875rem; font-weight: 500;
    text-decoration: none; transition: color 0.2s;
}
.back-link:hover { color: #1d4ed8; }
</style>
