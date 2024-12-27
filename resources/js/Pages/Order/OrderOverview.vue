<script setup>
import {
    IconUsers,
    IconUserX,
    IconTriangleFilled,
    IconTriangleInvertedFilled,
    IconUserCheck
} from "@tabler/icons-vue";
import Card from "primevue/card";
import {ref} from "vue";
import Skeleton from "primevue/skeleton";
import {generalFormat} from "@/Composables/format.js";

const props = defineProps({
    orderCounts: Number
})

const {formatAmount} = generalFormat();
const isLoading = ref(false);

const completedCount = ref(null);
const completedAmount = ref(null);
const pendingCount = ref(null);
const pendingAmount = ref(null);
const cancelCount = ref(null);
const cancelAmount = ref(null);
const refundCount = ref(null);
const refundAmount = ref(null);

const getOrderCount = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/order/getOrderOverview');

        completedCount.value = response.data.completedCount;
        completedAmount.value = response.data.completedAmount;
        
        pendingCount.value = response.data.pendingCount;
        pendingAmount.value = response.data.pendingAmount;

        cancelCount.value = response.data.cancelCount;
        cancelAmount.value = response.data.cancelAmount;

        refundCount.value = response.data.refundCount;
        refundAmount.value = response.data.refundAmount;
    } catch (error) {
        console.error('Error fetching recent counts:', error);
    } finally {
        isLoading.value = false;
    }
};

getOrderCount();

// const handleUpdateOrders = (data) => {
//     completedCount.value = data.completedCount;
//     completedAmount.value = data.completedAmount;
//     pendingCount.value = data.pendingCount;
//     pendingAmount.value = data.pendingAmount;
//     cancelCount.value = data.cancelCount;
//     cancelAmount.value = data.cancelAmount;
//     refundCount.value = data.refundCount;
//     refundAmount.value = data.refundAmount;
// };
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-4 w-full gap-3 md:gap-5">
        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex items-end gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                {{ $t("public.completed") }}
                            </div>
                            <div class="text-surface-950 dark:text-white text-xs font-medium md:text-sm">
                                <div v-if="orderCounts === 0">
                                    {{ formatAmount(0, 0) }} {{ $t('public.order_s') }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    {{ formatAmount(completedCount, 0) }} {{ $t('public.order_s') }}
                                </div>
                            </div>
                            <div class="text-surface-950 dark:text-white font-semibold md:text-lg">
                                <div v-if="orderCounts === 0">
                                    ¥ {{ formatAmount(0, 2) }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    ¥ {{ formatAmount(completedAmount, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-primary-200 dark:bg-primary-700 w-14 h-14 text-primary-600 dark:text-primary-300">
                            <IconUsers size="36" stroke-width="1.5" />
                        </div>
                    </div> -->
                </div>
            </template>
        </Card>

        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex flex-col gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                {{ $t("public.pending_payment") }}
                            </div>
                            <div class="text-surface-950 dark:text-white text-xs font-medium md:text-sm">
                                <div v-if="orderCounts === 0">
                                    {{ formatAmount(0, 0) }} {{ $t('public.order_s') }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    {{ formatAmount(pendingCount, 0) }} {{ $t('public.order_s') }}
                                </div>
                            </div>
                            <div class="text-surface-950 dark:text-white font-semibold md:text-lg">
                                <div v-if="orderCounts === 0">
                                    ¥ {{ formatAmount(0, 2) }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    ¥ {{ formatAmount(pendingAmount, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex flex-col gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                {{ $t("public.cancelled") }}
                            </div>
                            <div class="text-surface-950 dark:text-white text-xs font-medium md:text-sm">
                                <div v-if="orderCounts === 0">
                                    {{ formatAmount(0, 0) }} {{ $t('public.order_s') }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    {{ formatAmount(cancelCount, 0) }} {{ $t('public.order_s') }}
                                </div>
                            </div>
                            <div class="text-surface-950 dark:text-white font-semibold md:text-lg">
                                <div v-if="orderCounts === 0">
                                    ¥ {{ formatAmount(0, 2) }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    ¥ {{ formatAmount(cancelAmount, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex flex-col gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                {{ $t("public.return_refund") }}
                            </div>
                            <div class="text-surface-950 dark:text-white text-xs font-medium md:text-sm">
                                <div v-if="orderCounts === 0">
                                    {{ formatAmount(0, 0) }} {{ $t('public.order_s') }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    {{ formatAmount(refundCount, 0) }} {{ $t('public.order_s') }}
                                </div>
                            </div>
                            <div class="text-surface-950 dark:text-white font-semibold md:text-lg">
                                <div v-if="orderCounts === 0">
                                    ¥ {{ formatAmount(0, 2) }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    ¥ {{ formatAmount(refundAmount, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
