<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref} from "vue";
import DepositHistoryOverview from "@/Pages/Transaction/History/Deposit/DepositHistoryOverview.vue";
import DepositHistoryTable from "@/Pages/Transaction/History/Deposit/DepositHistoryTable.vue";

defineProps({
    depositHistoryCount: Number
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.deposit_history')">
        <div class="flex flex-col items-center gap-5">
            <DepositHistoryOverview
                :depositHistoryCount="depositHistoryCount"
            />

            <!-- Deposit History Table -->
            <DepositHistoryTable
                :depositHistoryCount="depositHistoryCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
