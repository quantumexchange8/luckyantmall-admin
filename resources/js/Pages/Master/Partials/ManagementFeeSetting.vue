<script setup>
import {ref, watch} from 'vue';
import { IconPlus, IconCircleX } from '@tabler/icons-vue';
import InputNumber from 'primevue/inputnumber';
import Button from '@/Components/Button.vue';

const rows = ref([
    {},
]);

const emit = defineEmits(['get:management_fee']);

watch(rows, (newRows) => {
    emit('get:management_fee', newRows);
}, { deep: true });

// Method to add a new row
const addRow = () => {
    rows.value.push({});
};

// Method to remove a row
const removeRow = (index) => {
    rows.value.splice(index, 1);
};
</script>

<template>
    <div class="flex flex-col items-center gap-3 self-stretch">
        <div class="flex justify-between items-center py-2 self-stretch border-b border-surface-200 bg-surface-100 dark:border-surface-700 dark:bg-surface-800">
            <div class="flex items-center px-2 w-full text-surface-950 dark:text-white text-xs font-semibold uppercase">
                {{ $t('public.days') }}
            </div>
            <div class="flex items-center px-2 w-full text-surface-950 dark:text-white text-xs font-semibold uppercase">
                {{ $t('public.fee_percentage') }} (%)
            </div>
        </div>

        <div class="flex flex-col items-center self-stretch max-h-[200px] overflow-y-auto">
            <div
                v-for="(row, index) in rows"
                :key="index"
                class="flex justify-between gap-3 my-1 items-center self-stretch h-10"
            >
                <div class="flex items-center gap-1 w-full">
                    <InputNumber
                        v-model="row.days"
                        inputId="settlement_period"
                        fluid
                        :min="0"
                        :placeholder="`0 ${$t('public.days')}`"
                        class="w-20"
                    />
                </div>
                <div class="flex items-center gap-1 w-full">
                    <InputNumber
                        v-model="row.percentage"
                        :minFractionDigits="2"
                        fluid
                        placeholder="0.00%"
                        class="w-20"
                        inputClass="w-20"
                    />
                    <Button
                        type="button"
                        variant="error-text"
                        pill
                        icon-only
                        size="sm"
                        @click="removeRow(index)"
                        :disabled="index === 0"
                    >
                        <IconCircleX size="16"/>
                    </Button>
                </div>
            </div>
        </div>

        <Button
            type="button"
            variant="primary-outlined"
            size="base"
            class="w-full"
            @click="addRow"
        >
            <IconPlus size="16"/>
            {{ $t('public.add_fee')}}
        </Button>
    </div>
</template>
