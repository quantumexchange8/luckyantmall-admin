<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddDepositProfile from "@/Pages/Settings/DepositProfile/AddDepositProfile.vue";
import DepositProfileTable from "@/Pages/Settings/DepositProfile/DepositProfileTable.vue";

const props = defineProps({
    profileCounts: Number
})
</script>

<template>
    <AuthenticatedLayout
        :title="$t('public.deposit_profile')"
    >
        <div class="flex flex-col items-center gap-5">
            <div class="flex justify-end w-full">
                <AddDepositProfile
                />
            </div>

            <!-- Deposit Profile Table-->
            <DepositProfileTable
                :profileCounts="profileCounts"
            />
        </div>
    </AuthenticatedLayout>
</template>
