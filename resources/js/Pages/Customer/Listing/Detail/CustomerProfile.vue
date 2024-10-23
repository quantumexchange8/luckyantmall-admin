<script setup>
import Card from "primevue/card";
import Tag from "primevue/tag";
import Skeleton from "primevue/skeleton";
import {ref, watchEffect} from "vue";
import {generalFormat} from "@/Composables/format.js";
import EditContactInfo from "@/Pages/Customer/Listing/Detail/EditContactInfo.vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    idNumber: String
})

const customerInfo = ref();
const { formatAmount } = generalFormat();

const getCustomerContactInfo = async () => {
    try {
        const response = await axios.get(`/customer/detail/${props.idNumber}/getCustomerContactInfo`);

        customerInfo.value = response.data;
    } catch (error) {
        console.error('Error get info:', error);
    }
};

getCustomerContactInfo();

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getCustomerContactInfo();
    }
});

const getSeverity = (status) => {
    switch (status) {
        case 'unverified':
            return 'danger';

        case 'verified':
            return 'success';

        case 'pending':
            return 'info';

        case 'renewal':
            return null;
    }
}
</script>

<template>
    <div class="flex flex-col md:flex-row gap-5 items-center self-stretch">
        <Card class="w-full">
            <template #content>
                <div class="flex flex-col items-start gap-3">
                    <div class="flex justify-between items-center w-full">
                        <span class="text-surface-950 dark:text-white font-semibold">{{ $t('public.contact_information') }}</span>
                        <EditContactInfo
                            :customerInfo="customerInfo"
                        />
                    </div>

                    <!-- loading -->
                    <div
                        v-if="!customerInfo"
                        class="flex flex-col justify-center items-center gap-5 self-stretch"
                    >
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <!-- Email -->
                            <div class="flex flex-col justify-center items-start gap-2 w-full">
                                <div class="text-surface-500 text-xs w-full">
                                    {{ $t('public.email_address') }}
                                </div>
                                <Skeleton width="10rem" class="mb-1"></Skeleton>
                            </div>

                            <!-- Phone -->
                            <div class="flex flex-col justify-center items-start gap-2 w-full">
                                <div class="text-surface-500 text-xs w-full">
                                    {{ $t('public.phone_number') }}
                                </div>
                                <Skeleton width="10rem" class="mb-1"></Skeleton>
                            </div>
                        </div>

                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <!-- Country -->
                            <div class="flex flex-col justify-center items-start gap-2 w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.country') }}
                                </div>
                                <Skeleton width="10rem" class="mb-1"></Skeleton>
                            </div>

                            <!-- Referral Code -->
                            <div class="flex flex-col justify-center items-start gap-2 w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.referral_code') }}
                                </div>
                                <Skeleton width="10rem" class="mb-1"></Skeleton>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-col justify-center items-center gap-5 self-stretch"
                    >
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <!-- Email -->
                            <div class="flex flex-col justify-center items-start w-full">
                                <div class="flex gap-1 items-center w-full">
                                    <span class="text-xs text-surface-500">{{ $t('public.email_address') }}</span>
                                    <Tag
                                        :value="$t(`public.${customerInfo.email_verified_at ? 'verified' : 'unverified'}`)"
                                        :severity="getSeverity(customerInfo.email_verified_at ? 'verified' : 'unverified')"
                                    />
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ customerInfo.email }}
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex flex-col justify-center items-start gap-1 w-full">
                                <div class="text-surface-500 text-xs w-full mb-1">
                                    {{ $t('public.phone_number') }}
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ customerInfo.dial_code }} {{ customerInfo.phone }}
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <!-- Country -->
                            <div class="flex flex-col justify-center items-start gap-1 w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.country') }}
                                </div>
                                <div class="flex gap-1 items-center">
                                    <span>{{ customerInfo.country.emoji}}</span>
                                    <span class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">{{ customerInfo.country.name}}</span>
                                </div>
                            </div>

                            <!-- Referral Code -->
                            <div class="flex flex-col justify-center items-start gap-1 w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.referral_code') }}
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ customerInfo.referral_code }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #content>
            </template>
        </Card>
    </div>
</template>
