<script setup>
defineProps({
    value: {
        type: String,
    },
    invalid: Boolean
});
</script>

<template>
    <label
        class="block font-medium text-xs"
        :class="{
            'text-gray-700 dark:text-gray-400': !invalid,
            'text-error-500 dark:text-error-400': invalid
        }"
    >
        <div v-if="value">
            {{ value }}<span class="text-error-500 text-xs font-medium"> *</span>
        </div>
        <div v-else>
            <slot />
        </div>
    </label>
</template>
