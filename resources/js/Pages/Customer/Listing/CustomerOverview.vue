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
    customerCounts: Number
})

const {formatAmount} = generalFormat();
const isLoading = ref(false);
const currentJoinUser = ref(0);
const lastMonthJoinUserComparison = ref(0);
const verifiedUser = ref(0);
const unverifiedUser = ref(0);

const getHighestDeposit = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/customer/getCustomerOverview');
        currentJoinUser.value = response.data.currentJoinUser;
        lastMonthJoinUserComparison.value = response.data.lastMonthJoinUserComparison;
        verifiedUser.value = response.data.verifiedUser;
        unverifiedUser.value = response.data.unverifiedUser;
    } catch (error) {
        console.error('Error fetching recent approvals:', error);
    } finally {
        isLoading.value = false;
    }
};

getHighestDeposit();
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 w-full gap-3 md:gap-5">
        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex items-end gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                {{ $t("public.all_customer") }}
                            </div>
                            <div class="text-surface-950 dark:text-white text-xl font-semibold md:text-xxl flex items-end gap-2">
                                <div v-if="customerCounts === 0">
                                    {{ formatAmount(0, 0) }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    {{ formatAmount(currentJoinUser, 0) }}
                                </div>

                                <div class="flex items-center gap-2 pb-1">
                                    <div v-if="customerCounts > 0" class="flex items-center gap-2">
                                        <div
                                            class="flex items-center gap-2"
                                            :class="
                                        {
                                            'text-green': lastMonthJoinUserComparison > 0,
                                            'text-pink': lastMonthJoinUserComparison < 0
                                        }"
                                        >
                                            <IconTriangleFilled v-if="lastMonthJoinUserComparison > 0" size="12" stroke-width="1.25" />
                                            <IconTriangleInvertedFilled v-if="lastMonthJoinUserComparison < 0" size="12" stroke-width="1.25" />
                                            <span class="text-xs font-medium md:text-sm">{{ formatAmount(lastMonthJoinUserComparison, 0) }} {{ $t('public.pax') }}</span>
                                        </div>
                                        <span class="text-gray-400 dark:text-surface-600 text-xs md:text-sm">{{ $t('public.compare_last_month') }}</span>
                                    </div>
                                    <span v-else class="text-gray-400 dark:text-surface-600 text-xs md:text-sm">{{ $t('public.data_not_available') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-primary-200 dark:bg-primary-700 w-14 h-14 text-primary-600 dark:text-primary-300">
                            <IconUsers size="36" stroke-width="1.5" />
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
                                {{ $t("public.verified") }}
                            </div>
                            <div class="text-surface-950 dark:text-white text-xl font-semibold md:text-xxl">
                                <div v-if="customerCounts === 0">
                                    {{ formatAmount(0, 0) }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    {{ formatAmount(verifiedUser, 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center rounded-full bg-success-100 dark:bg-success-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-success-200 dark:bg-success-700 w-14 h-14 text-success-600 dark:text-success-300">
                            <IconUserCheck size="36" stroke-width="1.5" />
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
                                {{ $t("public.verified") }}
                            </div>
                            <div class="text-surface-950 dark:text-white text-xl font-semibold md:text-xxl">
                                <div v-if="customerCounts === 0">
                                    {{ formatAmount(0, 0) }}
                                </div>
                                <div v-else-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>
                                <div v-else>
                                    {{ formatAmount(unverifiedUser, 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center rounded-full bg-error-100 dark:bg-error-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-error-200 dark:bg-error-700 w-14 h-14 text-error-600 dark:text-error-200">
                            <IconUserX size="36" stroke-width="1.5" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
