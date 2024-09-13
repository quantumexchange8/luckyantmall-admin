<script setup>
import { Link } from '@inertiajs/vue3'
import { sidebarState } from '@/Composables'
import {IconCircle} from "@tabler/icons-vue";
import Badge from 'primevue/badge';

const props = defineProps({
    href: {
        type: String,
        required: false,
    },
    active: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    external: {
        type: Boolean,
        default: false,
    },
    pendingCounts: Number
})

const Tag = !props.external ? Link : 'a'
</script>

<template>
    <component
        :is="Tag"
        v-if="href"
        :href="href"
        :class="[
            'p-2.5 flex gap-3 items-center rounded-md transition-colors w-full',
            {
                'text-gray-700 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-100 hover:bg-primary-50 dark:hover:bg-primary-800':
                    !active,
                'text-white bg-primary-500 dark:bg-primary-600 hover:bg-primary-600 dark:hover:bg-primary-700 border border-primary-500 hover:border-primary-600 dark:border-primary-600 dark:hover:border-primary-700':
                    active,
            },
        ]"
    >
        <div class="max-w-5">
            <slot name="icon">
                <IconCircle
                    size="20"
                />
            </slot>
        </div>

        <div class="flex items-center gap-2 w-full">
            <span
                class="text-sm font-medium w-full"
                v-show="sidebarState.isOpen || sidebarState.isHovered"
            >
                {{ title }}
            </span>
            <Badge
                v-if="pendingCounts > 0 && (sidebarState.isOpen || sidebarState.isHovered)"
                :value="pendingCounts"
                severity="info"
            ></Badge>
        </div>
    </component>
    <button
        v-else
        type="button"
        :class="[
            'p-3 flex gap-3 items-center rounded-lg transition-colors w-full',
            {
                'text-gray-950 hover:text-primary-500 hover:bg-primary-50':
                    !active,
                'text-white bg-primary-500 hover:bg-primary-600':
                    active,
            },
        ]"
    >
        <slot name="icon">
            <IconCircle
                size="20"
            />
        </slot>

        <span
            class="text-sm font-medium"
            v-show="sidebarState.isOpen || sidebarState.isHovered"
        >
            {{ title }}
        </span>
        <slot name="arrow" />
    </button>
</template>
