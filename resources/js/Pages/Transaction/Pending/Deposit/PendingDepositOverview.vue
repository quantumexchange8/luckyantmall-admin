<script setup>
import Card from "primevue/card";
import Timeline from 'primevue/timeline';
import {onMounted, ref, watchEffect} from "vue";
import ScrollPanel from 'primevue/scrollpanel';
import dayjs from "dayjs";
import Tag from "primevue/tag";
import Skeleton from "primevue/skeleton";
import {generalFormat} from "@/Composables/format.js";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    pendingDepositCounts: Number,
    totalPendingAmount: Number,
})

const isLoading = ref(false);
const recentApprovals = ref([]);
const pendingDeposits = ref(null);
const {formatAmount} = generalFormat();

const getRecentApprovals = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/transaction/pending/getRecentApprovals');
        recentApprovals.value = response.data.recentApprovals;
        pendingDeposits.value = response.data.pendingDeposits;
    } catch (error) {
        console.error('Error fetching recent approvals:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    getRecentApprovals();
});

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

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getRecentApprovals();
    }
});
</script>

<template>
    <div class="flex flex-col lg:flex-row gap-3 md:gap-5 w-full">
        <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-3 md:gap-5 lg:min-w-80 xl:min-w-96">
            <Card>
                <template #content>
                    <div class="flex flex-col items-start gap-3">
                        <div class="font-medium text-surface-500">
                            {{ $t("public.total_pending_deposit") }}
                        </div>
                        <div class="text-xl lowercase">
                            <div v-if="pendingDepositCounts === 0">
                                {{ pendingDepositCounts }} {{ $t('public.deposit') }}
                            </div>
                            <div v-else-if="isLoading">
                                <Skeleton width="5rem" height="2rem"></Skeleton>
                            </div>
                            <div v-else>
                                {{ pendingDeposits }} {{ $t('public.deposit') }}
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
            <Card>
                <template #content>
                    <div class="flex flex-col items-start gap-3">
                        <div class="font-medium text-surface-500">
                            {{ $t("public.total_pending_amount") }}
                        </div>
                        <div class="text-xl">
                            ¥{{ formatAmount(totalPendingAmount ?? 0) }}
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <Card class="w-full">
            <template #content>
                <div class="flex flex-col items-start gap-3 md:gap-5">
                    <span class="text-surface-700 dark:text-surface-300 font-semibold text-sm">{{ $t('public.recent_approval') }}</span>
                    <ScrollPanel style="width: 100%; height: 150px">
                        <Timeline :value="recentApprovals">
                            <template #opposite="slotProps">
                                <small class="text-surface-500 dark:text-surface-400">{{ dayjs(slotProps.item.approval_at).format('YY/MM/DD HH:mm:ss') }}</small>
                            </template>
                            <template #content="slotProps">
                                <div class="flex gap-2 items-center">
                                    <span>{{ slotProps.item.transaction_number }}</span>
                                    <Tag
                                        :value="$t(`public.${slotProps.item.status}`)"
                                        :severity="getSeverity(slotProps.item.status)"
                                    />
                                </div>
                            </template>
                        </Timeline>
                    </ScrollPanel>
                </div>
            </template>
        </Card>
    </div>
</template>
