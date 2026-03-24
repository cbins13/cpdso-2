<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { destroy, index, store } from '@/routes/api-tokens';
import type { BreadcrumbItem } from '@/types';

type ApiToken = {
    id: number;
    name: string;
    created_at: string;
    last_used_at: string | null;
};

type Props = {
    tokens: ApiToken[];
    newToken?: string | null;
};

withDefaults(defineProps<Props>(), {
    newToken: null,
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'API tokens',
        href: index(),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="API tokens" />

        <h1 class="sr-only">API tokens</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Create API token"
                    description="Generate a new personal access token for API authentication"
                />

                <div
                    v-if="newToken"
                    class="rounded-md border border-amber-300 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950"
                    role="status"
                    aria-live="polite"
                >
                    <p class="text-sm text-amber-900 dark:text-amber-200">
                        Copy this token now. It will not be shown again.
                    </p>
                    <code class="mt-2 block overflow-x-auto rounded bg-amber-100 px-3 py-2 font-mono text-xs text-amber-950 dark:bg-amber-900 dark:text-amber-100">{{ newToken }}</code>
                </div>

                <Form
                    v-bind="store.form()"
                    class="space-y-4"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Token name</Label>
                        <Input
                            id="name"
                            name="name"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                            placeholder="Integration token"
                            :aria-invalid="errors.name ? 'true' : 'false'"
                            :aria-describedby="errors.name ? 'token_name_error' : undefined"
                        />
                        <InputError
                            id="token_name_error"
                            :message="errors.name"
                            role="alert"
                            live="assertive"
                        />
                    </div>

                    <Button :disabled="processing">Create token</Button>
                </Form>
            </div>

            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Active tokens"
                    description="Revoke tokens you no longer use"
                />

                <p v-if="tokens.length === 0" class="text-sm text-muted-foreground">
                    No API tokens found.
                </p>

                <div v-else class="space-y-3">
                    <div
                        v-for="token in tokens"
                        :key="token.id"
                        class="flex items-center justify-between rounded-md border border-border p-4"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ token.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                Created {{ token.created_at }} · Last used {{ token.last_used_at ?? 'never' }}
                            </p>
                        </div>

                        <Form v-bind="destroy.form(token.id)">
                            <Button type="submit" variant="destructive" size="sm">Revoke</Button>
                        </Form>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
