<script setup lang="ts">
defineProps<{
    id?: string;
    message?: string;
    role?: string;
    live?: 'off' | 'polite' | 'assertive';
}>();
</script>

<template>
    <div v-show="message" :id="id" :role="role" :aria-live="live">
        <p class="text-sm text-red-600 dark:text-red-500">
            {{ message }}
        </p>
    </div>
</template>
