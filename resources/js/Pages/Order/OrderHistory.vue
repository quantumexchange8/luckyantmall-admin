<script setup>
import {h, ref, watch} from "vue";
import {transactionFormat} from "@/Composables/index.js";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import OrderHistoryTable from "@/Pages/Order/OrderHistoryTable.vue";

// const props = defineProps({
//     transactionTypes: Array,
// })

// const totalAmount = ref(null);
// const successAmount = ref(null);
// const rejectedAmount = ref(null);

// const {formatAmount} = transactionFormat();

const tabs = ref([
    {
        title: 'all',
        type: 'All',
        value: '0',
        component: h(OrderHistoryTable)
    },
    {
        title: 'processing',
        type: 'Processing',
        value: '1'
        // component: h(WithdrawalTable)
    },
    {
        title: 'shipping',
        type: 'Shipping',
        value: '2'
        // component: h(TransferTable)
    },
    {
        title: 'completed',
        type: 'Completed',
        value: '3'
    },
    {
        title: 'pending_payment',
        type: 'PendingPayment',
        value: '4'
    },
    {
        title: 'cancelled',
        type: 'Cancelled',
        value: '5'
    },
    {
        title: 'return_refund',
        type: 'ReturnRefund',
        value: '6'
    },
]);

const selectedType = ref('all');
const activeIndex = ref('0');

// Watch for changes in selectedType and update the activeIndex accordingly
watch(activeIndex, (newIndex) => {
    const activeTab = tabs.value.find(tab => tab.value === newIndex);
    if (activeTab) {
        selectedType.value = activeTab.type;
    }
});

// const handleUpdateTotals = (data) => {
//     totalAmount.value = data.totalAmount;
//     successAmount.value = data.successAmount;
//     rejectedAmount.value = data.rejectedAmount;
// };
</script>

<template>
    <div class="flex flex-col items-center w-full gap-5">
        <Tabs v-model:value="activeIndex" class="w-full">
            <TabList>
                <Tab
                    v-for="tab in tabs"
                    :key="tab.title"
                    :value="tab.value"
                >
                    {{ $t(`public.${tab.title}`) }}
                </Tab>
            </TabList>
        </Tabs>

        <component
            :is="tabs[activeIndex]?.component"
            :selectedType="selectedType"
            
        />
        {{ tabs[activeIndex]?.title }}
    </div>
</template>
