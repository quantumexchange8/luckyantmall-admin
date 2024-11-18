<script setup>
import {ref} from "vue";
import {generalFormat} from "@/Composables/format.js";
import Skeleton from "primevue/skeleton";
import Divider from "primevue/divider";
import DataView from "primevue/dataview";
import ScrollPanel from "primevue/scrollpanel";
import Tag from "primevue/tag";
import Empty from "@/Components/Empty.vue";
import {useLangObserver} from "@/Composables/localeObserver.js";
import Card from "primevue/card";

const props = defineProps({
    idNumber: String,
    deliveryAddressesCount: Number,
});

const deliveryAddresses = ref();
const isLoading = ref(false);
const {locale} = useLangObserver();

const getDeliveryAddresses = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/customer/detail/${props.idNumber}/getDeliveryAddresses`);

        deliveryAddresses.value = response.data;
    } catch (error) {
        console.error('Error get info:', error);
    } finally {
        isLoading.value = false;
    }
};

getDeliveryAddresses();
</script>

<template>
    <Card class="w-full">
        <template #content>
            <div class="flex flex-col items-start gap-3">
                <div class="flex items-center w-full">
                    <span class="text-surface-950 dark:text-white font-semibold my-1.5">{{ $t('public.delivery_addresses') }}</span>
                </div>
                <!-- loading -->
                <div
                    v-if="deliveryAddressesCount > 0"
                    class="w-full"
                >
                    <div
                        v-if="isLoading"
                        class="w-full self-stretch"
                    >
                        <ScrollPanel style="width: 100%; max-height: 292px;">
                            <div class="flex flex-col">
                                <div v-for="(address, index) in deliveryAddressesCount" :key="index">
                                    <div
                                        class="flex flex-col self-stretch w-full pr-3 pb-3 gap-4"
                                    >
                                        <div class="flex flex-col">
                                            <div class="px-3 flex justify-between items-center text-surface-950 dark:text-white text-sm min-h-[50px] font-semibold bg-surface-100 dark:bg-surface-800">
                                                <div class="flex gap-2 items-center">
                                                    <Skeleton width="10rem"></Skeleton>
                                                    <Divider layout="vertical" />
                                                    <Skeleton width="5rem"></Skeleton>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-2 w-full p-3">
                                                <div class="text-sm text-surface-950 dark:text-white">
                                                    <Skeleton width="12rem"></Skeleton>
                                                </div>
                                                <div class="flex gap-2 items-center divide-x divide-surface-500 text-sm text-surface-500">
                                                    <Skeleton width="8rem" class="mb-1"></Skeleton>
                                                    <Skeleton width="5rem" class="mb-1"></Skeleton>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </ScrollPanel>
                    </div>

                    <DataView
                        v-else
                        :value="deliveryAddresses"
                        class="w-full self-stretch"
                    >
                        <template #list="slotProps">
                            <ScrollPanel style="width: 100%; max-height: 292px;">
                                <div class="flex flex-col">
                                    <div v-for="(address, index) in slotProps.items" :key="index">
                                        <div
                                            class="flex flex-col self-stretch w-full pr-3 pb-3 gap-4"
                                        >
                                            <div class="flex flex-col">
                                                <div class="px-3 flex justify-between items-center text-surface-950 dark:text-white text-sm min-h-[50px] font-semibold bg-surface-100 dark:bg-surface-800">
                                                    <div class="flex gap-2 items-center">
                                                        <span>{{ address.receiver_name}}</span>
                                                        <Divider layout="vertical" />
                                                        <span class="text-surface-400">{{ address.receiver_phone}}</span>
                                                        <Tag v-if="address.default_address" :value="$t('public.default')" severity="info" />
                                                    </div>
                                                </div>
                                                <div class="flex flex-col gap-1 w-full p-3">
                                                    <div class="text-sm text-surface-950 dark:text-white">
                                                        {{ address.address }}
                                                    </div>
                                                    <div class="flex gap-2 items-center divide-x divide-surface-500 text-sm text-surface-500">
                                                        <span>{{ address.postal_code}}</span>
                                                        <span class="pl-2">{{ locale !== 'en' ? address.country.translations[locale] : address.country.name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </ScrollPanel>
                        </template>
                    </DataView>
                </div>

                <div
                    v-else
                    class="flex flex-col justify-center items-center gap-5 self-stretch"
                >
                    <Empty
                        :title="$t('public.no_address_added')"
                        :message="$t('public.no_address_added_caption')"
                    />
                </div>
            </div>
        </template>
    </Card>
</template>
