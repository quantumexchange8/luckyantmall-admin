<script setup>
import Button from "@/Components/Button.vue";
import Dialog from "primevue/dialog";
import { ref } from "vue";

const visible = ref(false);
</script>

<template>
    <Button
        type="button"
        variant="primary-flat"
        @click="visible = true"
    >
        {{ $t('public.create_group') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_post')"
        class="dialog-xs md:dialog-md"
    >
    asdasd
    </Dialog>
</template>
