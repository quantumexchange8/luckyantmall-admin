<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PendingDepositOverview from "@/Pages/Transaction/Pending/Deposit/PendingDepositOverview.vue";
import PendingDepositTable from "@/Pages/Transaction/Pending/Deposit/PendingDepositTable.vue";
import {ref} from "vue";

defineProps({
    pendingDepositCounts: Number
});

const totalPendingAmount = ref(null);
</script>

<template>
    <AuthenticatedLayout :title="$t('public.pending_deposit')">
        <div class="flex flex-col items-center gap-5">
            <PendingDepositOverview
                :pendingDepositCounts="pendingDepositCounts"
                @totalPendingAmount="totalPendingAmount"
            />

            <!-- Pending Deposit Table -->
            <PendingDepositTable
                :pendingDepositCounts="pendingDepositCounts"
                @update:total-pending-amount="totalPendingAmount = $event"
            />
        </div>
    </AuthenticatedLayout>
</template>
