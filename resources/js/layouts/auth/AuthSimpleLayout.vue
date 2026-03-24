<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh items-center justify-center overflow-hidden bg-background p-6 md:p-10"
        style="background-image: linear-gradient(to bottom, color-mix(in oklab, var(--background) 75%, transparent), color-mix(in oklab, var(--background) 65%, transparent)), url('/city_hall.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;"
    >
        <div class="w-full max-w-md rounded-2xl border border-border bg-card/95 p-6 shadow-lg backdrop-blur-sm md:p-8">
            <div class="mb-6 flex flex-col items-center gap-3 text-center">
                <Link :href="home()" class="font-semibold tracking-wide">CPDSO</Link>
                <div class="space-y-2">
                    <h1 class="text-xl font-medium">{{ title }}</h1>
                    <p class="text-sm text-muted-foreground">{{ description }}</p>
                </div>
            </div>

            <slot />
        </div>
    </div>
</template>
