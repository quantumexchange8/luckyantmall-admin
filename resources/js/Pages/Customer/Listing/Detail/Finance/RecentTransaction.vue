<script setup>
import Card from "primevue/card";
import Skeleton from "primevue/skeleton";
import {computed, ref} from "vue";
import {generalFormat} from "@/Composables/format.js";
import Divider from "primevue/divider";
import DataView from "primevue/dataview";
import Tag from "primevue/tag";
import Empty from "@/Components/Empty.vue";
import ScrollPanel from "primevue/scrollpanel";
import dayjs from "dayjs";

const props = defineProps({
    idNumber: String,
    transactionsCount: Number,
})

const isLoading = ref(false);
const totalDeposit = ref(0);
const totalWithdrawal = ref(0);
const transactionHistory = ref([]);
const { formatAmount } = generalFormat();

const getRecentTransactionData = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/customer/detail/${props.idNumber}/getRecentTransactionData`);

        totalDeposit.value = response.data.total_deposit;
        totalWithdrawal.value = response.data.total_withdrawal;
        transactionHistory.value = response.data.transaction;
    } catch (error) {
        console.error('Error get info:', error);
    } finally {
        isLoading.value = false;
    }
};

getRecentTransactionData();

const overviewData = computed(() =>  [
    {
        label: 'total_deposit',
        value: totalDeposit.value,
    },
    {
        label: 'total_withdrawal',
        value: totalWithdrawal.value,
    },
]);

const getSeverity = (status) => {
    switch (status) {
        case 'fail':
            return 'danger';

        case 'success':
            return 'success';

        default:
            return null;
    }
}
</script>

<template>
    <div class="flex flex-col gap-5 self-stretch w-full">
        <div class="flex flex-col sm:flex-row gap-5 self-stretch">
            <Card
                v-for="(overview, index) in overviewData"
                class="w-full"
            >
                <template #content>
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <div class="dark:text-surface-400 text-xs">
                            {{ $t(`public.${overview.label}`) }}
                        </div>
                        <div v-if="isLoading">
                            <Skeleton width="5rem" height="1.5rem" class="my-0.5" />
                        </div>
                        <div
                            v-else
                            class="md:text-lg text-surface-950 dark:text-white font-semibold truncate"
                        >
                            ¥{{ formatAmount(overview.value) }}
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <Card class="w-full">
            <template #content>
                <div class="flex flex-col items-start gap-3">
                    <div class="flex items-center w-full">
                        <span class="text-surface-950 dark:text-white font-semibold my-1.5">{{ $t('public.recent_transaction') }}</span>
                    </div>
                    <!-- loading -->
                    <div
                        v-if="transactionsCount > 0"
                        class="w-full"
                    >
                        <div
                            v-if="isLoading"
                            class="w-full self-stretch"
                        >
                            <ScrollPanel style="width: 100%; max-height: 165px;">
                                <div class="flex flex-col">
                                    <div v-for="(address, index) in transactionsCount" :key="index">
                                        <div
                                            class="flex justify-between items-center py-3 gap-4"
                                            :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }"
                                        >
                                            <div class="flex flex-col gap-1">
                                                <Skeleton width="5rem" height="1.25rem"></Skeleton>
                                                <Skeleton width="10rem" height="1.25rem"></Skeleton>
                                                <Skeleton width="8rem"></Skeleton>
                                            </div>
                                            <div class="text-surface-950 dark:text-white font-bold pr-3">
                                                <Skeleton width="6rem" height="1.25rem"></Skeleton>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </ScrollPanel>
                        </div>

                        <DataView
                            v-else
                            :value="transactionHistory"
                            class="w-full self-stretch"
                        >
                            <template #list="slotProps">
                                <ScrollPanel style="width: 100%; height: 165px;">
                                    <div class="flex flex-col">
                                        <div v-for="(item, index) in slotProps.items" :key="index">
                                            <div
                                                class="flex justify-between items-center py-3 gap-4"
                                                :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }"
                                            >
                                                <div class="flex flex-col">
                                                    <div class="w-auto">
                                                        <Tag
                                                            :value="$t(`public.${item.transaction_type}`)"
                                                            :severity="getSeverity(item.status)"
                                                        />
                                                    </div>
                                                    <div class="text-surface-950 dark:text-white text-sm font-semibold">
                                                        {{ item.transaction_number }}
                                                    </div>
                                                    <div class="text-xs text-surface-500">
                                                        {{ dayjs(item.approval_at).format('YYYY/MM/DD HH:mm:ss') }}
                                                    </div>
                                                </div>
                                                <div class="text-surface-950 dark:text-white font-bold pr-3">
                                                    ¥{{ formatAmount(item.amount) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </ScrollPanel>
                            </template>
                        </DataView>
                    </div>

                    <div
                        v-else
                        class="flex flex-col justify-center items-center gap-5 self-stretch h-[165px]"
                    >
                        <Empty
                        />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
