<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
         title: 'Welcome back',
        description: 'Sign in to manage your envelopes and spending.',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <div
    v-if="status"
    class="mb-5 rounded-xl bg-[#e6f0eb] px-4 py-3 text-center text-sm font-medium text-[#356854]"
>
    {{ status }}
</div>

<Form
    v-bind="store.form()"
    :reset-on-success="['password']"
    v-slot="{ errors, processing }"
    class="space-y-5"
>
    <div class="space-y-2">
        <Label
            for="email"
            class="text-sm font-medium text-[#243b34]"
        >
            Email address
        </Label>

        <Input
            id="email"
            type="email"
            name="email"
            required
            autofocus
            :tabindex="1"
            autocomplete="email"
            placeholder="you@example.com"
            class="h-11 rounded-xl"
        />

        <InputError :message="errors.email" />
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <Label
                for="password"
                class="text-sm font-medium text-[#243b34]"
            >
                Password
            </Label>

            <TextLink
                v-if="canResetPassword"
                :href="request()"
                class="text-sm text-[#477b67] hover:text-[#315b4b]"
                :tabindex="5"
            >
                Forgot password?
            </TextLink>
        </div>

        <PasswordInput
            id="password"
            name="password"
            required
            :tabindex="2"
            autocomplete="current-password"
            placeholder="Enter your password"
            class="h-11 rounded-xl"
        />

        <InputError :message="errors.password" />
    </div>

    <Label
        for="remember"
        class="flex cursor-pointer items-center gap-3 text-sm text-[#63736d]"
    >
        <Checkbox
            id="remember"
            name="remember"
            :tabindex="3"
        />

        <span>Keep me signed in</span>
    </Label>

    <Button
        type="submit"
        class="h-11 w-full rounded-xl bg-[#477b67] font-semibold text-white hover:bg-[#386451]"
        :tabindex="4"
        :disabled="processing"
        data-test="login-button"
    >
        <Spinner v-if="processing" />

        <span>
            {{ processing ? 'Signing in...' : 'Log in' }}
        </span>
    </Button>
</Form>

<div
    class="mt-6 border-t border-[#edf0ed] pt-5 text-center text-sm text-[#71807a]"
>
    Accounts are created by invitation.<br>
    Ask Bob if you need access.
</div>
</template>
