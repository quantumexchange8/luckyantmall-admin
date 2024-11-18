<script setup>
import Card from "primevue/card";
import Skeleton from "primevue/skeleton";
import {generalFormat} from "@/Composables/format.js";
import {onMounted, ref} from "vue";
import {
    IconCurrencyDollar,
    IconCurrencyDollarOff,
    IconTriangleFilled,
    IconTriangleInvertedFilled
} from "@tabler/icons-vue";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";

const props = defineProps({
    depositHistoryCount: Number,
})

const {formatAmount} = generalFormat();
const isLoading = ref(false);
const topThreeUser = ref([]);
const currentSuccessDeposit = ref(0);
const lastMonthSuccessDepositComparison = ref(0);
const currentFailDeposit = ref(0);

const getHighestDeposit = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/transaction/history/getHighestDeposit');
        topThreeUser.value = response.data.topThreeUser;
        currentSuccessDeposit.value = response.data.currentSuccessDeposit;
        lastMonthSuccessDepositComparison.value = response.data.lastMonthSuccessDepositComparison;
        currentFailDeposit.value = response.data.currentFailDeposit;
    } catch (error) {
        console.error('Error fetching recent approvals:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    getHighestDeposit();
});

function calculatePercentage(fund) {
    if (!currentSuccessDeposit.value || !fund) {
        return 0;
    }
    return ((fund / currentSuccessDeposit.value) * 100).toFixed(2);
}

</script>

<template>
    <div class="flex flex-col lg:flex-row gap-3 md:gap-5 w-full">
        <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-3 md:gap-5 lg:min-w-80 xl:min-w-[400px]">
            <Card>
                <template #content>
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col gap-1">
                            <div class="flex flex-col items-start gap-2">
                                <div class="text-surface-500 text-sm">
                                    {{ $t("public.success_amount") }}
                                </div>
                                <div class="text-surface-950 dark:text-white text-xl font-semibold md:text-xxl">
                                    <div v-if="depositHistoryCount === 0">
                                        ¥{{ formatAmount(0) }}
                                    </div>
                                    <div v-else-if="isLoading">
                                        <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                    </div>
                                    <div v-else>
                                        ¥{{ formatAmount(currentSuccessDeposit) }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div v-if="currentSuccessDeposit" class="flex items-center gap-2">
                                    <div
                                        class="flex items-center gap-2"
                                        :class="
                                        {
                                            'text-green': lastMonthSuccessDepositComparison > 0,
                                            'text-pink': lastMonthSuccessDepositComparison < 0
                                        }"
                                    >
                                        <IconTriangleFilled v-if="lastMonthSuccessDepositComparison > 0" size="12" stroke-width="1.25" />
                                        <IconTriangleInvertedFilled v-if="lastMonthSuccessDepositComparison < 0" size="12" stroke-width="1.25" />
                                        <span class="text-xs font-medium md:text-sm">  {{ `${formatAmount(lastMonthSuccessDepositComparison)}%` }}</span>
                                    </div>
                                    <span class="text-gray-400 dark:text-surface-600 text-xs md:text-sm">{{ $t('public.compare_last_month') }}</span>
                                </div>
                                <span v-else class="text-gray-400 dark:text-surface-600 text-xs md:text-sm">{{ $t('public.data_not_available') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-center rounded-full bg-success-100 dark:bg-success-900/40 w-[72px] h-[72px]">
                            <div class="flex items-center justify-center rounded-full bg-success-200 dark:bg-success-700 w-14 h-14 text-success-600 dark:text-success-300">
                                <IconCurrencyDollar size="36" stroke-width="1.5" />
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
                                    {{ $t("public.fail_amount") }}
                                </div>
                                <div class="text-surface-950 dark:text-white text-xl font-semibold md:text-xxl">
                                    <div v-if="depositHistoryCount === 0">
                                        ¥{{ formatAmount(0) }}
                                    </div>
                                    <div v-else-if="isLoading">
                                        <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                    </div>
                                    <div v-else>
                                        ¥{{ formatAmount(currentFailDeposit) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-center rounded-full bg-error-100 dark:bg-error-900/40 w-[72px] h-[72px]">
                            <div class="flex items-center justify-center rounded-full bg-error-200 dark:bg-error-700 w-14 h-14 text-error-600 dark:text-error-200">
                                <IconCurrencyDollarOff size="36" stroke-width="1.5" />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <Card class="w-full">
            <template #content>
                <div class="flex flex-col items-start gap-5">
                    <div class="flex flex-col gap-2 items-start self-stretch md:flex-shrink-0">
                        <div class="flex justify-center items-center">
                            <span class="text-surface-500 text-sm">{{ $t('public.top_three_user_deposit') + `(¥)` }}</span>
                        </div>
                        <div class="flex items-end gap-5">
                            <span class="text-surface-950 dark:text-white text-xl font-semibold md:text-xxl">{{ currentSuccessDeposit ? formatAmount(currentSuccessDeposit) : formatAmount(0) }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 items-center self-stretch w-full">
                        <div v-for="index in 3" :key="index" class="flex items-center py-2 gap-3 md:gap-4 w-full">
                            <div class="w-full flex items-center min-w-[140px] md:min-w-[180px] md:max-w-[240px] gap-3 md:gap-4">
                                <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                    <DefaultProfilePhoto />
                                </div>
                                <span class="truncate w-full max-w-[180px] md:max-w-[200px] text-surface-950 dark:text-white text-sm font-medium md:text-base">
                                    {{ topThreeUser[index - 1]?.user.name || '------' }}
                                </span>
                            </div>
                            <div class="w-full max-w-[52px] xs:max-w-sm sm:max-w-md md:max-w-full h-1 bg-gray-100 dark:bg-surface-700 rounded-full relative">
                                <div
                                    class="absolute top-0 left-0 h-full rounded-full bg-primary-500 transition-all duration-700 ease-in-out"
                                    :style="{ width: `${calculatePercentage(topThreeUser[index - 1]?.total_deposit)}%` }"
                                />
                            </div>
                            <span class="truncate text-surface-950 dark:text-white text-right text-sm font-medium md:text-base w-full max-w-[88px] md:max-w-[110px]">
                                {{ topThreeUser[index - 1]?.total_deposit ? formatAmount(topThreeUser[index - 1].total_deposit) : formatAmount(0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
