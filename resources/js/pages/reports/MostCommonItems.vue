<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type MostCommonItem = {
    item_name: string;
    frequency: number;
};

const props = defineProps<{
    reportData: MostCommonItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reports',
        href: '#',
    },
    {
        title: 'Most Common Items',
        href: '/reports/most-common-items',
    },
];

const maxFrequency = computed(() => {
    if (props.reportData.length === 0) return 0;
    return Math.max(...props.reportData.map(r => r.frequency));
});

const totalOccurrences = computed(() => {
    return props.reportData.reduce((sum, row) => sum + row.frequency, 0);
});

const uniqueItems = computed(() => {
    return props.reportData.length;
});

const getBarWidth = (frequency: number): number => {
    if (maxFrequency.value === 0) return 0;
    return (frequency / maxFrequency.value) * 100;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Most Common Items" />

        <div class="space-y-6 p-4 md:p-6">
            <div class="flex items-center justify-between gap-4">
                <Heading
                    title="Most Common Items"
                    description="Items ranked by frequency across all boxes (top 50)"
                />
                <Link href="/reports/items-by-box" class="text-sm underline underline-offset-4">
                    ← View Items by Box & Owner
                </Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-md border border-border bg-card p-4">
                    <p class="text-sm font-medium text-muted-foreground">Unique Items</p>
                    <p class="mt-1 text-2xl font-bold">{{ uniqueItems }}</p>
                </div>
                <div class="rounded-md border border-border bg-card p-4">
                    <p class="text-sm font-medium text-muted-foreground">Total Occurrences</p>
                    <p class="mt-1 text-2xl font-bold">{{ totalOccurrences }}</p>
                </div>
                <div class="rounded-md border border-border bg-card p-4">
                    <p class="text-sm font-medium text-muted-foreground">Most Frequent</p>
                    <p class="mt-1 text-2xl font-bold">{{ maxFrequency }}</p>
                </div>
            </div>

            <!-- Data Table with Progress Bars -->
            <div class="overflow-hidden rounded-md border border-border">
                <div class="bg-muted px-4 py-3">
                    <div class="grid grid-cols-3 gap-4 text-sm font-semibold">
                        <div>Item Name</div>
                        <div class="text-right">Frequency</div>
                        <div>Distribution</div>
                    </div>
                </div>

                <div v-if="reportData.length === 0" class="px-4 py-8 text-center">
                    <p class="text-sm text-muted-foreground">No items found. Create items in your boxes to see the report.</p>
                </div>

                <div v-else class="divide-y divide-border">
                    <div
                        v-for="(item, index) in reportData"
                        :key="`item-${index}`"
                        class="px-4 py-3 hover:bg-muted/50 transition-colors"
                    >
                        <div class="grid grid-cols-3 gap-4 items-center">
                            <div>
                                <p class="font-medium capitalize">{{ item.item_name }}</p>
                                <p class="text-xs text-muted-foreground">Rank: #{{ index + 1 }}</p>
                            </div>
                            <div class="text-right font-semibold">{{ item.frequency }}</div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-muted rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-primary transition-all duration-300"
                                        :style="{ width: `${getBarWidth(item.frequency)}%` }"
                                    ></div>
                                </div>
                                <span class="text-xs text-muted-foreground min-w-fit">
                                    {{ ((item.frequency / totalOccurrences) * 100).toFixed(1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
