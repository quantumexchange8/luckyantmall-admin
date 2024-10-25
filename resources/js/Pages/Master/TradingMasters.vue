<script setup>
import {
    IconSearch,
    IconCircleXFilled,
    IconUserDollar,
    IconPremiumRights,
    IconAdjustments,
    IconScanEye,
    IconAlertCircleFilled,
    IconHeartFilled,
    IconHeart,
} from '@tabler/icons-vue';
import {ref} from "vue";
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import Select from "primevue/select";
import Tag from "primevue/tag";
import Paginator from "primevue/paginator";
import Card from "primevue/card";
import Empty from "@/Components/Empty.vue";
import {generalFormat} from "@/Composables/format.js";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";

const props = defineProps({
    masterCounts: Number
});

const masters = ref([]);
const search = ref('');
const isLoading = ref(false);
const tag = ref();
const status = ref();
const currentPage = ref(1);
const rowsPerPage = ref(12);
const totalRecords = ref(0);
const {formatAmount} = generalFormat();

// Define sort options
const sortOptions = ref([
    { name: 'latest', value: 'latest' },
    { name: 'popular', value: 'popular' },
    { name: 'largest_fund', value: 'largest_fund' },
    { name: 'most_investor', value: 'most_investors' },
]);

const sortType = ref(sortOptions.value[0]);


const getResults = async (page = 1, rowsPerPage = 12) => {
    isLoading.value = true;

    try {
        let url = `/master/getMasters?page=${page}&limit=${rowsPerPage}`;

        if (sortType.value && sortType.value.value) {
            url += `&sortType=${sortType.value.value}`;
        }

        if (tag.value) {
            url += `&tag=${tag.value}`;
        }

        if (status.value) {
            url += `&status=${status.value}`;
        }

        if (search.value) {
            url += `&search=${search.value}`;
        }

        const response = await axios.get(url);
        masters.value = response.data.masters;
        totalRecords.value = response.data.totalRecords;
        currentPage.value = response.data.currentPage;
    } catch (error) {
        console.error('Error getting masters:', error);
    } finally {
        isLoading.value = false;
    }
};

// Initial call to populate data
getResults(currentPage.value, rowsPerPage.value);

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    getResults(currentPage.value, rowsPerPage.value);
};

const clearSearch = () => {
    search.value = '';
};

</script>

<template>
    <div class="w-full flex flex-col items-center gap-5">
        <!-- toolbar -->
        <div class="flex flex-col md:flex-row gap-3 items-center self-stretch">
            <div class="relative w-full md:w-60">
                <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                    <IconSearch size="20" stroke-width="1.25" />
                </div>
                <InputText v-model="search" :placeholder="$t('public.keyword_search')" class="font-normal pl-12 w-full md:w-60" />
                <div
                    v-if="search"
                    class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                    @click="clearSearch"
                >
                    <IconCircleXFilled size="16" />
                </div>
            </div>
            <div class="w-full flex justify-between items-center self-stretch gap-3">
                <Button
                    variant="gray-outlined"
                    @click="toggle"
                    size="base"
                    class="flex gap-3 items-center justify-center w-full md:w-[130px]"
                >
                    <IconAdjustments size="20" color="#0C111D" stroke-width="1.25" />
                    <div class="text-sm text-gray-950 font-medium">
                        {{ $t('public.filter') }}
                    </div>
                </Button>
                <Select
                    v-model="sortType"
                    :options="sortOptions"
                    optionLabel="name"
                    class="w-full md:w-40"
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <div>{{ $t('public.' + slotProps.value.name) }}</div>
                            </div>
                        </div>
                    </template>
                    <template #option="slotProps">
                        {{ $t('public.' + slotProps.option.name) }}
                    </template>
                </Select>
            </div>
        </div>

        <div v-if="masterCounts === 0 && !masters.length">
            <Empty
                :title="$t('public.no_groups_created_yet')"
                :message="$t('public.no_groups_created_yet_caption')"
            />
        </div>

        <div v-else class="w-full">
            <div v-if="isLoading">
                q
            </div>

            <div v-else-if="!masters.length">
                w
            </div>

            <div v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 3xl:grid-cols-4 gap-5 self-stretch">
                    <Card
                        v-for="(master, index) in masters"
                        :key="index"
                    >
                        <template #content>
                            <div class="flex flex-col items-center gap-4 self-stretch">
                                <!-- Profile Section -->
                                <div class="w-full flex items-center gap-4 self-stretch">
                                    <div class="w-[42px] h-[42px] shrink-0 grow-0 rounded-full overflow-hidden">
                                        <div v-if="master.master_profile_photo">
                                            <img :src="master.master_profile_photo" alt="Profile Photo" />
                                        </div>
                                        <div v-else>
                                            <DefaultProfilePhoto />
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-start max-w-[100px] xxs:max-w-[124px] xs:max-w-full 3xl:max-w-[134px]">
                                        <div class="self-stretch truncate text-surface-950 dark:text-white font-bold">
                                            {{ master.master_name }}
                                        </div>
                                        <div class="self-stretch truncate text-gray-500 text-sm">
                                            {{ master.meta_login }}
                                        </div>
                                    </div>
                                    <div class="flex gap-3 items-center w-full justify-end">
                                        <!--                                <Action-->
                                        <!--                                    :master="master"-->
                                        <!--                                    :groupsOptions="groupsOptions"-->
                                        <!--                                />-->
                                    </div>
                                </div>

                                <!-- StatusBadge Section -->
                                <div class="flex items-center gap-2 self-stretch">
                                    <Tag severity="primary">
                                        $ {{ formatAmount(master.min_investment) }}
                                    </Tag>
                                    <Tag severity="secondary">
                                        <div v-if="master.join_period !== 0">
                                            {{ master.join_period }}
                                            {{ $t(`public.${master.join_period_type}`) }}
                                        </div>
                                        <div v-else>
                                            {{ $t('public.lock_free') }}
                                        </div>
                                    </Tag>
                                    <Tag severity="secondary">
                                        {{ formatAmount(master.sharing_profit, 0) + '%&nbsp;' + $t('public.profit') }}
                                    </Tag>
                                </div>

                                <!-- Performance Section -->
                                <div class="py-2 flex justify-center items-center gap-2 self-stretch border-y border-solid border-surface-200 dark:border-surface-700">
                                    <div class="w-full flex flex-col items-center">
                                        <div class="self-stretch text-surface-950 dark:text-white text-center font-semibold">
                                            {{ formatAmount(master.total_gain) }}%
                                        </div>
                                        <div class="self-stretch text-surface-500 text-center text-xs">
                                            {{ $t('public.total_gain') }}
                                        </div>
                                    </div>
                                    <div class="w-full flex flex-col items-center">
                                        <div class="self-stretch text-surface-950 dark:text-white text-center font-semibold">
                                            {{ formatAmount(master.monthly_gain) }}%
                                        </div>
                                        <div class="self-stretch text-surface-500 text-center text-xs">
                                            {{ $t('public.monthly_gain') }}
                                        </div>
                                    </div>
                                    <div class="w-full flex flex-col items-center">
                                        <div class="self-stretch text-center font-semibold">
                                            <div
                                                v-if="master.latest_profit !== 0"
                                                :class="(master.latest_profit < 0) ? 'text-error-500' : 'text-success-500'"
                                            >
                                                ${{ formatAmount(master.latest_profit) }}
                                            </div>
                                            <div
                                                v-else
                                                class="text-surface-950 dark:text-white"
                                            >
                                                -
                                            </div>
                                        </div>
                                        <div class="self-stretch text-surface-500 text-center text-xs">
                                            {{ $t('public.total_pnl') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Details Section -->
                                <div class="flex items-end justify-between self-stretch">
                                    <div class="flex flex-col items-center gap-1 self-stretch">
                                        <div class="py-1 flex items-center gap-3 self-stretch">
                                            <IconUserDollar size="20" stroke-width="1.25" />
                                            <div class="w-full text-surface-950 dark:text-white text-sm font-medium">
                                                {{ master.ongoing_subscriptions_count }} {{ $t('public.investors') }}
                                            </div>
                                        </div>
                                        <div class="py-1 flex items-center gap-3 self-stretch">
                                            <IconPremiumRights size="20" stroke-width="1.25" />
                                            <div class="w-full text-surface-950 dark:text-white text-sm font-medium">
                                                <span class="text-primary-500">$ {{ formatAmount(master.ongoing_subscriptions_sum_subscription_amount ?? 0) }}</span> {{ $t('public.fund_capital') }}
                                            </div>
                                        </div>
                                        <div class="py-1 flex items-center gap-3 self-stretch">
                                            <IconScanEye size="20" stroke-width="1.25" />
                                            <div class="w-full text-surface-950 dark:text-white text-sm font-medium max-w-[128px] xxs:max-w-[180px] xs:max-w-[220px] sm:max-w-full md:max-w-[180px] xl:max-w-md 3xl:max-w-[180px] truncate">
                                                {{ master.visibility_type === 'public' ? $t(`public.${master.visibility_type}`) : master.group_names }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <Paginator
                    :first="(currentPage - 1) * rowsPerPage"
                    :rows="rowsPerPage"
                    :totalRecords="totalRecords"
                    @page="onPageChange"
                />
            </div>
        </div>
    </div>
</template>
