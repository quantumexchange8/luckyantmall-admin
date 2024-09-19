<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref} from "vue";
import {
    IconTriangleFilled,
    IconTriangleInvertedFilled
} from "@tabler/icons-vue"
import {transactionFormat} from "@/Composables/format.js";
import Groups from "@/Pages/Group/Groups.vue";

const props = defineProps({
    groupCount: Number
})

const dataItems = ref([
    {
        title: 'total_deposit',
        total: ref(1000),
        comparison: ref(5),
    },
    {
        title: 'total_withdrawal',
        total: ref(0),
        comparison: ref(5),
    }
]);
const {formatAmount} = transactionFormat();
</script>

<template>
    <AuthenticatedLayout :title="$t('public.group')">
        <div class="flex flex-col items-center gap-5">
            <div class="flex flex-col md:flex-row items-center gap-5 self-stretch">
                <div class="w-full flex flex-col gap-5 md:max-w-[262px] xl:max-w-[358px] 2xl:max-w-[560px]">
                    <!-- Total overview -->
                    <div
                        v-for="(item, index) in dataItems"
                        :key="index"
                        class="p-5 rounded-2xl bg-white w-full shadow flex flex-col items-center gap-3"
                    >
                        <span class="self-stretch text-gray-500 text-sm">{{ $t(item.title) }} (¥)</span>
                        <div class="flex w-full items-end gap-5">
                        <span class="text-gray-950 text-lg font-semibold md:text-xxl">
                            {{ item.total ? formatAmount(item.total) : formatAmount(0) }}
                        </span>
                            <div class="flex items-center pb-1.5 gap-2">
                                <div v-if="item.total" class="flex items-center gap-2">
                                    <div
                                        class="flex items-center gap-2"
                                        :class="{
                                        'text-green': item.comparison > 0,
                                        'text-pink': item.comparison < 0
                                    }"
                                    >
                                        <IconTriangleFilled v-if="item.comparison > 0" size="12" stroke-width="1.25" />
                                        <IconTriangleInvertedFilled v-if="item.comparison < 0" size="12" stroke-width="1.25" />
                                        <span class="text-xs font-medium md:text-sm">
                                        {{ `${formatAmount(item.comparison)}%` }}
                                    </span>
                                    </div>
                                    <span class="text-gray-400 text-xs md:text-sm">{{ $t('public.vs_last_month') }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-xs md:text-sm">{{ $t('public.data_not_available') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 3 groups -->
                <div class="w-full flex flex-col items-center self-stretch p-5 gap-5 md:gap-8 rounded-2xl bg-white shadow">
                    <span class="self-stretch text-gray-500 text-sm">{{ $t('public.top_3_groups') }}</span>
                </div>
            </div>

            <Groups
                :groupCount="groupCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
