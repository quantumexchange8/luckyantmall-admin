<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import dayjs from "dayjs";
import {IconSun, IconMoon} from "@tabler/icons-vue";
import { toggleDarkMode, isDark } from '@/Composables'
import Button from "@/Components/Button.vue"
</script>

<template>
    <div class="flex flex-col min-h-screen bg-surface-0 dark:bg-surface-950">
        <div class="flex py-3 px-5 md:px-10 justify-end items-center">
            <Button
                type="button"
                variant="gray-text"
                icon-only
                pill
                @click="() => { toggleDarkMode() }"
            >
                <IconSun v-if="!isDark" size="20" stroke-width="1.5" />
                <IconMoon v-if="isDark" size="20" color="white" stroke-width="1.5" />
            </Button>
        </div>
        <div class="flex flex-1 flex-col justify-center items-center pb-12 md:px-8 xs:gap-y-[60px]">
            <div class="w-full flex md:flex-1 justify-center">
                <div class="w-full max-w-xs md:max-w-none md:w-[360px] flex flex-col justify-center items-center mx-5">
                    <slot />
                </div>
            </div>
            <div class="text-center text-gray-500 text-xs mt-auto">© {{ dayjs().year() }} Luckyant Mall. All rights reserved.</div>
        </div>
    </div>
</template>
