<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';

defineProps<{
    status?: string;
}>();

const code = ref<string>('');
</script>

<template>
    <AuthLayout
        title="Verify CPDSO account"
        description="Enter the 6-digit verification code sent to your registered email address."
    >
        <Head title="Email verification" />

        <div
            v-if="status === 'otp-sent' || status === 'otp-resent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            A verification OTP has been sent to your email address.
        </div>

        <Form
            action="/email/verify-otp"
            method="post"
            class="space-y-4"
            reset-on-error
            @error="code = ''"
            v-slot="{ errors, processing }"
        >
            <input type="hidden" name="code" :value="code" />

            <div
                class="flex flex-col items-center justify-center space-y-3 text-center"
            >
                <InputOTP
                    id="otp-verify-email"
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

            <Button class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Verify account
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Log out
            </TextLink>
        </Form>

        <Form
            action="/email/verify-otp/resend"
            method="post"
            class="mt-4 text-center"
            v-slot="{ processing: resendProcessing }"
        >
            <Button :disabled="resendProcessing" variant="secondary">
                <Spinner v-if="resendProcessing" />
                Resend OTP
            </Button>
        </Form>
    </AuthLayout>
</template>
