<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import OrderOverview from "@/Pages/Order/OrderOverview.vue";
import OrderHistory from "@/Pages/Order/OrderHistory.vue";

const props = defineProps({
    orderCounts: Number
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.order_listing')">
        <div class="flex flex-col items-center gap-5">
            <!-- Data Overview -->
            <OrderOverview
                :orderCounts="orderCounts"
            />

            <!-- Order Table-->
            <OrderHistory
            />
        </div>
    </AuthenticatedLayout>
</template>
