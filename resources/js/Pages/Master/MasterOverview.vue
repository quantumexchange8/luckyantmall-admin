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
const topThreeMaster = ref([]);
const currentAssets = ref(0);
const lastMonthAssetComparison = ref(0);
const currentInvestors = ref(0);
const lastMonthInvestorComparison = ref(0);

const getHighestDeposit = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/master/getMasterOverview');
        topThreeMaster.value = response.data.topThreeMaster;
        currentAssets.value = response.data.currentAssets;
        lastMonthAssetComparison.value = response.data.lastMonthAssetComparison;
        currentInvestors.value = response.data.currentInvestors;
        lastMonthInvestorComparison.value = response.data.lastMonthInvestorComparison;
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
    if (!currentInvestors.value || !fund) {
        return 0;
    }
    return ((fund / currentInvestors.value) * 100).toFixed(2);
}

</script>

<template>
    <div class="flex flex-col md:flex-row gap-3 md:gap-5 w-full">
        <Card class="w-full md:min-w-[450px] xl:min-w-[610px] 2xl:min-w-[892px]">
            <template #content>
                <div class="flex flex-col items-start gap-5">
                    <div class="flex flex-col gap-2 items-start self-stretch md:flex-shrink-0">
                        <div class="flex justify-center items-center">
                            <span class="text-surface-500 text-sm">{{ $t('public.current_active_fund') + `($)` }}</span>
                        </div>
                        <div class="flex items-end gap-5">
                            <span class="text-surface-950 dark:text-white text-xl font-semibold md:text-xxl">{{ currentInvestors ? formatAmount(currentInvestors) : formatAmount(0) }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 items-center self-stretch w-full">
                        <div v-for="index in 3" :key="index" class="flex items-center py-2 gap-3 md:gap-4 w-full">
                            <div class="w-full flex items-center min-w-[140px] md:min-w-[180px] md:max-w-[240px] gap-3 md:gap-4">
                                <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                    <DefaultProfilePhoto />
                                </div>
                                <span class="truncate w-full max-w-[180px] md:max-w-[200px] text-surface-950 dark:text-white text-sm font-medium md:text-base">
                                    {{ topThreeMaster[index - 1]?.trading_master.master_name || '------' }}
                                </span>
                            </div>
                            <div class="w-full max-w-[52px] xs:max-w-sm sm:max-w-md md:max-w-full h-1 bg-gray-100 dark:bg-surface-700 rounded-full relative">
                                <div
                                    class="absolute top-0 left-0 h-full rounded-full bg-primary-500 transition-all duration-700 ease-in-out"
                                    :style="{ width: `${calculatePercentage(topThreeMaster[index - 1]?.total_investment)}%` }"
                                />
                            </div>
                            <span class="truncate text-surface-950 dark:text-white text-right text-sm font-medium md:text-base w-full max-w-[88px] md:max-w-[110px]">
                                {{ topThreeMaster[index - 1]?.total_investment ? formatAmount(topThreeMaster[index - 1].total_investment) : formatAmount(0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <div class="grid grid-cols-1 gap-3 md:gap-5 w-full md:max-w-[340px] xl:max-w-[358px] 2xl:max-w-[528px]">
            <Card>
                <template #content>

                </template>
            </Card>
            <Card>
                <template #content>

                </template>
            </Card>
        </div>
    </div>
</template>
