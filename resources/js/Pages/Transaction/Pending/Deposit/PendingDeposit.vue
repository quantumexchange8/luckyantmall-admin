<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PendingDepositOverview from "@/Pages/Transaction/Pending/Deposit/PendingDepositOverview.vue";
import PendingDepositTable from "@/Pages/Transaction/Pending/Deposit/PendingDepositTable.vue";

defineProps({
    pendingDepositCounts: Number
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.pending_deposit')">
        <div class="flex flex-col items-center gap-5">
            <PendingDepositOverview
                :pendingDepositCounts="pendingDepositCounts"
            />

            <!-- Pending Deposit Table -->
            <PendingDepositTable
                :pendingDepositCounts="pendingDepositCounts"
            />
        </div>
    </AuthenticatedLayout>
</template>
