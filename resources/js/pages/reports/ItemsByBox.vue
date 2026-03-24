<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type BoxReport = {
    user_id: number;
    user_name: string;
    box_id: number;
    box_name: string;
    item_count: number;
};

const props = defineProps<{
    reportData: BoxReport[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reports',
        href: '#',
    },
    {
        title: 'Items by Box & Owner',
        href: '/reports/items-by-box',
    },
];

const groupedByUser = computed(() => {
    const grouped: Record<number, { name: string; boxes: BoxReport[] }> = {};
    
    for (const row of props.reportData) {
        if (!grouped[row.user_id]) {
            grouped[row.user_id] = {
                name: row.user_name,
                boxes: [],
            };
        }
        grouped[row.user_id].boxes.push(row);
    }
    
    return grouped;
});

const totalItems = computed(() => {
    return props.reportData.reduce((sum, row) => sum + row.item_count, 0);
});

const totalBoxes = computed(() => {
    const uniqueBoxes = new Set(props.reportData.map(row => row.box_id));
    return uniqueBoxes.size;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Items by Box & Owner" />

        <div class="space-y-6 p-4 md:p-6">
            <div class="flex items-center justify-between gap-4">
                <Heading
                    title="Items by Box & Owner"
                    description="View the count of items in each box, organized by owner"
                />
                <Link href="/reports/most-common-items" class="text-sm underline underline-offset-4">
                    View Most Common Items →
                </Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-md border border-border bg-card p-4">
                    <p class="text-sm font-medium text-muted-foreground">Total Items</p>
                    <p class="mt-1 text-2xl font-bold">{{ totalItems }}</p>
                </div>
                <div class="rounded-md border border-border bg-card p-4">
                    <p class="text-sm font-medium text-muted-foreground">Total Boxes</p>
                    <p class="mt-1 text-2xl font-bold">{{ totalBoxes }}</p>
                </div>
            </div>

            <!-- Data Table -->
            <div class="space-y-4">
                <div v-if="Object.keys(groupedByUser).length === 0" class="rounded-md border border-border p-6 text-center">
                    <p class="text-sm text-muted-foreground">No data available. Create boxes and items to see the report.</p>
                </div>

                <div
                    v-for="(userGroup, userId) in groupedByUser"
                    :key="`user-${userId}`"
                    class="overflow-hidden rounded-md border border-border"
                >
                    <div class="bg-muted px-4 py-3">
                        <h3 class="font-semibold">{{ userGroup.name }}</h3>
                    </div>

                    <div class="divide-y divide-border">
                        <div
                            v-for="box in userGroup.boxes"
                            :key="`box-${box.box_id}`"
                            class="flex items-center justify-between px-4 py-3"
                        >
                            <div>
                                <p class="font-medium">{{ box.box_name }}</p>
                                <p class="text-xs text-muted-foreground">Box ID: {{ box.box_id }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-primary/10 px-3 py-1 text-sm font-semibold">
                                    {{ box.item_count }}
                                </span>
                                <span class="text-xs text-muted-foreground">item{{ box.item_count !== 1 ? 's' : '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
