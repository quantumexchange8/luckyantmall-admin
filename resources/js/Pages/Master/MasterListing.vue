<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MasterOverview from "@/Pages/Master/MasterOverview.vue";
import AddMaster from "@/Pages/Master/AddMaster.vue";
import TradingMasters from "@/Pages/Master/TradingMasters.vue";

defineProps({
    masterCounts: Number
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.master')">
        <div class="flex flex-col items-center gap-5">
            <!-- Add Master -->
            <div class="flex justify-end items-center self-stretch">
                <AddMaster />
            </div>

            <MasterOverview />

            <TradingMasters
                :masterCounts="masterCounts"
            />
        </div>
    </AuthenticatedLayout>
</template>
