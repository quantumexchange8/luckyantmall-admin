<script setup>
import Card from "primevue/card";
import Skeleton from "primevue/skeleton";
import {ref, watchEffect} from "vue";
import {generalFormat} from "@/Composables/format.js";
import {usePage} from "@inertiajs/vue3";
import WalletAdjustment from "@/Pages/Customer/Listing/Detail/Finance/WalletAdjustment.vue";
import RecentTransaction from "@/Pages/Customer/Listing/Detail/Finance/RecentTransaction.vue";

const props = defineProps({
    user: Object,
    transactionsCount: Number,
})

const wallets = ref();
const isLoading = ref(false);
const {formatAmount} = generalFormat();

const getWalletData = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/customer/detail/${props.user.id_number}/getWalletData`);

        wallets.value = response.data;
    } catch (error) {
        console.error('Error get wallets:', error);
    } finally {
        isLoading.value = false;
    }
};

getWalletData();

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getWalletData();
    }
});
</script>

<template>
    <div class="flex flex-col xl:flex-row items-start gap-5 self-stretch">
        <div class="flex flex-col gap-5 w-full xl:max-w-[640px]">
            <div
                v-if="isLoading"
                class="grid grid-cols-2 gap-3 w-full"
            >
                <Card
                    v-for="index in user.wallets_count"
                >
                    <template #content>
                        <div class="flex flex-col gap-3 items-start self-stretch">
                            <div class="flex justify-between items-center self-stretch">
                                <Skeleton width="5rem" class="my-[11px]"></Skeleton>
                            </div>

                            <div class="w-full bg-surface-100 dark:bg-surface-800 p-3 flex flex-col gap-3">
                                <span class="text-surface-700 dark:text-surface-100 text-xs font-medium">{{ $t('public.balance') }}</span>
                                <div class="md:text-lg text-primary-500 font-semibold">
                                    <Skeleton width="5rem" height="1.25rem" class="my-1"></Skeleton>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
            <div
                v-else
                class="grid grid-cols-2 gap-5 w-full"
            >
                <Card
                    v-for="wallet in wallets"
                >
                    <template #content>
                        <div class="flex flex-col gap-3 items-start self-stretch">
                            <div class="flex justify-between items-center self-stretch">
                                <span class="text-xs md:text-sm dark:text-surface-400">{{ $t(`public.${wallet.type}`) }}</span>
                                <WalletAdjustment
                                    :user="user"
                                    :wallet="wallet"
                                />
                            </div>

                            <div class="w-full bg-surface-100 dark:bg-surface-800 p-3 flex flex-col gap-3">
                                <span class="text-surface-700 dark:text-surface-100 text-xs font-medium">{{ $t('public.balance') }}</span>
                                <div class="md:text-lg text-primary-500 font-semibold">
                                    <span v-if="wallet.currency_symbol !== 'point'">{{ wallet.currency_symbol }}{{ formatAmount(wallet.balance) }}</span>
                                    <span v-else>{{ formatAmount(wallet.balance) }} {{ $t(`public.${wallet.currency_symbol}`) }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <RecentTransaction
            :idNumber="user.id_number"
            :transactionsCount="transactionsCount"
        />
    </div>
</template>
