<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="CPDSO Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border">
                <h1 class="text-xl font-semibold">CPDSO dashboard</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                    No operational records are available yet. Data-driven panels will appear here once users complete onboarding and verification activities.
                </p>
            </section>

            <section class="grid gap-4 md:grid-cols-2">
                <article class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                    <h2 class="text-sm font-semibold">Identity verification state</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        There are no pending verification events at this time.
                    </p>
                </article>
                <article class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                    <h2 class="text-sm font-semibold">Security posture</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Security metrics and policy summaries will populate after initial activity is recorded.
                    </p>
                </article>
            </section>
        </div>
    </AppLayout>
</template>
