<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from 'primevue/breadcrumb';
import {h, ref, watchEffect} from "vue";
import {Link, usePage} from '@inertiajs/vue3'
import CustomerInfo from "@/Pages/Customer/Listing/Detail/CustomerInfo.vue";
import FinanceInfo from "@/Pages/Customer/Listing/Detail/Finance/FinanceInfo.vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import DeliveryAddress from "@/Pages/Customer/Listing/Detail/DeliveryAddress.vue";

const props = defineProps({
    user: Object,
    transactionsCount: Number
});

const home = ref({
    label: 'customer_listing',
    route: route('customer.listing')
});
const items = ref([
    { label: props.user.name },
]);

const userDetail = ref();

const getUserData = async () => {
    try {
        const response = await axios.get(`/customer/detail/${props.user.id_number}/getUserData`);

        userDetail.value = response.data;
    } catch (error) {
        console.error('Error get network:', error);
    }
};

getUserData();

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getUserData();
    }
});

const tabs = ref([
    {
        title: 'finance',
        content: 'Tab 1 Content',
        component: h(FinanceInfo, {
            user: props.user,
            transactionsCount: props.transactionsCount,
        }),
        value: '0'
    },
    {
        title: 'investment',
        content: 'Tab 2 Content',
        value: '1'
    },
    {
        title: 'history',
        content: 'Tab 3 Content',
        value: '2'
    },
]);
</script>

<template>
    <AuthenticatedLayout :title="$t('public.customer_listing')">
        <div class="flex flex-col gap-5">
            <Breadcrumb :home="home" :model="items">
                <template #item="{ item }">
                    <Link v-if="item.route" :href="item.route">
                        <span :class="[item.icon, 'text-color']" />
                        <span class="text-primary font-semibold hover:text-primary-600">{{ $t(`public.${item.label}`) }}</span>
                    </Link>
                    <span v-else class="text-surface-700 dark:text-surface-0">{{ item.label }} - {{ $t('public.details') }}</span>
                </template>
            </Breadcrumb>

            <!-- Customer Info -->
            <div class="flex flex-col lg:flex-row items-center w-full gap-5 self-stretch">
                <CustomerInfo
                    :userDetail="userDetail"
                />

                <DeliveryAddress
                    :idNumber="user.id_number"
                    :deliveryAddressesCount="user.delivery_addresses_count"
                />
            </div>


            <!-- Tabs -->
            <Tabs value="0">
                <TabList>
                    <Tab v-for="tab in tabs" :key="tab.title" :value="tab.value">
                        {{ $t(`public.${tab.title}`) }}
                    </Tab>
                </TabList>
                <TabPanels>
                    <TabPanel v-for="tab in tabs" :key="tab.content" :value="tab.value">
                        <component :is="tab.component" />
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </div>
    </AuthenticatedLayout>
</template>
