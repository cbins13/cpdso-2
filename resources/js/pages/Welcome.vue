<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="CPDSO" />

    <div
        class="min-h-screen bg-background text-foreground"
        style="background-image: linear-gradient(to bottom, color-mix(in oklab, var(--background) 85%, transparent), color-mix(in oklab, var(--background) 65%, transparent)), url('/city_hall.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;"
    >
        <header class="border-b border-border">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4 lg:px-8">
                <div>
                    <h1 class="text-lg font-semibold">CPDSO</h1>
                </div>

                <nav class="flex items-center gap-3" aria-label="Authentication navigation">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex items-center rounded-md border border-border px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
                    >
                        Open dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-flex items-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted"
                        >
                            Sign in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90"
                        >
                            Create account
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-6 py-8 lg:px-8 lg:py-12">
            <section
                class="relative overflow-hidden rounded-2xl border border-border bg-card/90 p-8 md:p-10"
            >
                <h2 class="max-w-3xl text-2xl leading-tight font-semibold md:text-3xl">
                    Welcome to the City Planning, Development, and Sustainabililty Office
                </h2>
            </section>
        </main>
    </div>
</template>
