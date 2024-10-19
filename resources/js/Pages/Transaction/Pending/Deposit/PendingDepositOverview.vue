<script setup>
import Card from "primevue/card";
import Timeline from 'primevue/timeline';
import {onMounted, ref} from "vue";
import ScrollPanel from 'primevue/scrollpanel';
import dayjs from "dayjs";

const props = defineProps({
    pendingDepositCounts: Number
})

const isLoading = ref(false);
const recentApprovals = ref([]);
const pendingDeposits = ref(null);

const getRecentApprovals = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/transaction/pending_deposit/getRecentApprovals');
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

const events = ref([
    { status: 'Ordered', date: '15/10/2020 10:30', icon: 'pi pi-shopping-cart', color: '#9C27B0'},
    { status: 'Processing', date: '15/10/2020 14:00', icon: 'pi pi-cog', color: '#673AB7' },
    { status: 'Shipped', date: '15/10/2020 16:15', icon: 'pi pi-shopping-cart', color: '#FF9800' },
    { status: 'Delivered', date: '16/10/2020 10:00', icon: 'pi pi-check', color: '#607D8B' },
    { status: 'Delivered', date: '16/10/2020 10:00', icon: 'pi pi-check', color: '#607D8B' },
    { status: 'Delivered', date: '16/10/2020 10:00', icon: 'pi pi-check', color: '#607D8B' }
]);
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-5 w-full">
        <div class="grid gap-3 md:gap-5">
            <Card>
                <template #content>
                    <div class="flex flex-col items-start gap-3">
                        <div class="font-medium text-surface-500">
                            {{ $t("public.total_pending_deposit") }}
                        </div>
                        <div class="text-xl">
                            {{ pendingDepositCounts }} {{ $t('public.deposit') }}
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
                            ¥{{ 99999 }}
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <Card>
            <template #content>
                <div class="flex flex-col items-start gap-3 md:gap-5">
                    <span class="text-surface-300 font-semibold text-sm">{{ $t('public.recent_approval') }}</span>
                    <ScrollPanel style="width: 100%; height: 150px">
                        <Timeline :value="recentApprovals">
                            <template #opposite="slotProps">
                                <small class="text-surface-500 dark:text-surface-400">{{ dayjs(slotProps.item.approval_at).format('YY/MM/DD HH:mm:ss') }}</small>
                            </template>
                            <template #content="slotProps">
                                {{slotProps.item.status}}
                            </template>
                        </Timeline>
                    </ScrollPanel>
                </div>
            </template>
        </Card>
    </div>
</template>
