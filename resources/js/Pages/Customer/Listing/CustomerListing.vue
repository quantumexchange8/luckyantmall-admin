<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CustomerTable from "@/Pages/Customer/Listing/CustomerTable.vue";
import AddCustomer from "@/Pages/Customer/Listing/AddCustomer.vue";
import CustomerOverview from "@/Pages/Customer/Listing/CustomerOverview.vue";

const props = defineProps({
    customerCounts: Number
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.customer_listing')">
        <div class="flex flex-col items-center gap-5">
            <div class="flex justify-end w-full">
                <AddCustomer />
            </div>

            <!-- Data Overview -->
            <CustomerOverview
                :customerCounts="customerCounts"
            />

            <!-- Customer Table-->
            <CustomerTable
                :customerCounts="customerCounts"
            />
        </div>
    </AuthenticatedLayout>
</template>
