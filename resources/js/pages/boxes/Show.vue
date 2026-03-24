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
};

type Item = {
    id: number;
    name: string;
    deleted_at: string | null;
};

const props = defineProps<{
    box: Box;
    items: Item[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Boxes',
        href: '/boxes',
    },
    {
        title: props.box.name,
        href: `/boxes/${props.box.id}`,
    },
];

const itemForm = useForm({
    name: '',
});

function createItem() {
    itemForm.post(`/boxes/${props.box.id}/items`, {
        preserveScroll: true,
        onSuccess: () => itemForm.reset(),
    });
}

function deleteItem(itemId: number) {
    itemForm.delete(`/items/${itemId}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="box.name" />

        <div class="space-y-6 p-4 md:p-6">
            <div class="flex items-center justify-between gap-4">
                <Heading :title="box.name" description="Manage items inside this box" />
                <Link href="/boxes" class="text-sm text-muted-foreground underline underline-offset-4">Back to boxes</Link>
            </div>

            <form @submit.prevent="createItem" class="space-y-3 rounded-md border border-border p-4">
                <div class="grid gap-2">
                    <Label for="item_name">Item name</Label>
                    <Input
                        id="item_name"
                        v-model="itemForm.name"
                        placeholder="Carrot"
                        required
                    />
                    <p v-if="itemForm.errors.name" class="text-sm text-destructive">
                        {{ itemForm.errors.name }}
                    </p>
                </div>

                <Button :disabled="itemForm.processing">Add item</Button>
            </form>

            <div class="space-y-3">
                <p v-if="items.length === 0" class="text-sm text-muted-foreground">
                    No items yet.
                </p>

                <div
                    v-for="item in items"
                    :key="item.id"
                    class="flex items-center justify-between rounded-md border border-border p-4"
                >
                    <p class="font-medium">{{ item.name }}</p>

                    <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        @click="deleteItem(item.id)"
                    >
                        Delete
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
