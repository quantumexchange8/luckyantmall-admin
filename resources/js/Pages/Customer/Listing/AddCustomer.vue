<script setup>
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import {
    IconUserPlus,
} from "@tabler/icons-vue"

const visible = ref(false);
</script>

<template>
    <Button
        type="button"
        variant="primary-flat"
        class="w-full md:w-auto"
        @click="visible = true"
    >
        <IconUserPlus size="16" />
        {{ $t('public.add_customer') }}
    </Button>
</template>
