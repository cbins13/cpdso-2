<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{
    email: string;
    status?: string;
}>();

const code = ref<string>('');
</script>

<template>
    <AuthLayout
        title="Verify your login"
        description="Enter the 6-digit code we sent to your email before continuing."
    >
        <Head title="Email OTP verification" />

        <div
            v-if="status === 'otp-sent' || status === 'otp-resent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            A verification code was sent to {{ email }}.
        </div>

        <Form
            action="/auth/otp-challenge"
            method="post"
            class="space-y-4"
            reset-on-error
            @error="code = ''"
            #default="{ errors, processing }"
        >
            <input type="hidden" name="code" :value="code" />

            <div class="flex flex-col items-center justify-center space-y-3 text-center">
                <InputOTP
                    id="otp-login"
                    v-model="code"
                    :maxlength="6"
                    :disabled="processing"
                    autofocus
                >
                    <InputOTPGroup>
                        <InputOTPSlot
                            v-for="index in 6"
                            :key="index"
                            :index="index - 1"
                        />
                    </InputOTPGroup>
                </InputOTP>

                <InputError :message="errors.code" />
            </div>

            <Button type="submit" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Verify and continue
            </Button>
        </Form>

        <Form
            action="/auth/otp-resend"
            method="post"
            class="mt-4"
            #default="{ processing }"
        >
            <Button type="submit" variant="secondary" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Resend code
            </Button>
        </Form>
    </AuthLayout>
</template>
