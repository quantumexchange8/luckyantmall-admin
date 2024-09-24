<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref} from "vue";
import Card from "primevue/card";
import {IconTriangleFilled, IconTriangleInvertedFilled} from "@tabler/icons-vue";
import CustomerTable from "@/Pages/Customer/Listing/CustomerTable.vue";
import AddCustomer from "@/Pages/Customer/Listing/AddCustomer.vue";

const props = defineProps({
    customerCounts: Number
})

const dataItems = ref([
    {
        title: 'all_customer',
        total: ref(1000),
        comparison: ref(5),
    },
    {
        title: 'verified',
        total: ref(0),
        comparison: ref(5),
    },
    {
        title: 'unverified',
        total: ref(0),
        comparison: ref(5),
    }
]);
</script>

<template>
    <AuthenticatedLayout :title="$t('public.customer_listing')">
        <div class="flex flex-col items-center gap-5">
            <div class="flex justify-end w-full">
                <AddCustomer />
            </div>

            <!-- data overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 w-full gap-3 md:gap-5">
                <Card
                    v-for="(item, index) in dataItems"
                    :key="index"
                >
                    <template #content>
                        <div class="flex flex-col items-center gap-3">
                            <span class="self-stretch text-gray-500 dark:text-gray-400 text-sm">{{ $t(`public.${item.title}`) }} (¥)</span>
                            <div class="flex w-full items-end gap-5">
                                <span class="text-lg font-semibold md:text-xxl">{{ item.total }}</span>
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
                                            <span class="text-xs font-medium md:text-sm">{{ item.comparison }}</span>
                                        </div>
                                        <span class="text-gray-400 dark:text-gray-500 text-xs md:text-sm">{{ $t('public.vs_last_month') }}</span>
                                    </div>
                                    <span v-else class="text-gray-400 dark:text-gray-500 text-xs md:text-sm">{{ $t('public.data_not_available') }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Customer Table-->
            <CustomerTable
                :customerCounts="customerCounts"
            />
        </div>
    </AuthenticatedLayout>
</template>
