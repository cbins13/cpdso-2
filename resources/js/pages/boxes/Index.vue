<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Box = {
    id: number;
    name: string;
    items_count: number;
    deleted_at: string | null;
};

defineProps<{
    boxes: Box[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Boxes',
        href: '/boxes',
    },
];

const createForm = useForm({
    name: '',
});

function createBox() {
    createForm.post('/boxes', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Boxes" />

        <div class="space-y-6 p-4 md:p-6">
            <Heading
                title="Boxes"
                description="Create and manage your boxes"
            />

            <form @submit.prevent="createBox" class="space-y-3 rounded-md border border-border p-4">
                <div class="grid gap-2">
                    <Label for="name">Box name</Label>
                    <Input
                        id="name"
                        v-model="createForm.name"
                        placeholder="Vegetables"
                        required
                    />
                    <p v-if="createForm.errors.name" class="text-sm text-destructive">
                        {{ createForm.errors.name }}
                    </p>
                </div>

                <Button :disabled="createForm.processing">Create box</Button>
            </form>

            <div class="space-y-3">
                <div
                    v-for="box in boxes"
                    :key="box.id"
                    class="flex items-center justify-between rounded-md border border-border p-4"
                >
                    <div>
                        <p class="font-medium">{{ box.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ box.items_count }} item(s)</p>
                    </div>

                    <Link :href="`/boxes/${box.id}`" class="inline-flex items-center rounded-md border border-border px-3 py-2 text-sm hover:bg-muted">
                        Open
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
