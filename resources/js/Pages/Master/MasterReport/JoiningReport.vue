<script setup>
import dayjs from "dayjs";
import DataTable from "primevue/datatable";
import {IconAdjustments, IconCircleXFilled, IconDownload, IconSearch} from "@tabler/icons-vue";
import InputText from "primevue/inputtext";
import ProgressSpinner from "primevue/progressspinner";
import Column from "primevue/column";
import Empty from "@/Components/Empty.vue";
import Tag from "primevue/tag";
import Button from "@/Components/Button.vue";
import {onMounted, ref} from "vue";
import {generalFormat} from "@/Composables/format.js";
import {FilterMatchMode} from "@primevue/core/api";
import ColumnGroup from "primevue/columngroup";
import Row from "primevue/row";

const props = defineProps({
    master: Object
})

const accounts = ref([]);
const isLoading = ref(false);
const {formatAmount, formatRgbaColor} = generalFormat();

const getResults = async (filterDate = null) => {
    isLoading.value = true;

    try {
        let url = `/master/getJoiningAccountsData?master_meta_login=${props.master.meta_login}`;

        // if (filterDate) {
        //     const [startDate, endDate] = filterDate;
        //     url += `&startDate=${dayjs(startDate).format('YYYY-MM-DD')}&endDate=${dayjs(endDate).format('YYYY-MM-DD')}`;
        // }

        const response = await axios.get(url);
        accounts.value = response.data.accounts;
    } catch (error) {
        console.error('Error changing locale:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    getResults();
});

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
    upline_id: {value: null, matchMode: FilterMatchMode.EQUALS},
    group_id: {value: null, matchMode: FilterMatchMode.EQUALS},
    role: {value: null, matchMode: FilterMatchMode.EQUALS},
    status: {value: null, matchMode: FilterMatchMode.EQUALS},
});
</script>

<template>
    <div
        class="w-full"
    >
        <DataTable
            v-model:filters="filters"
            :value="accounts"
            :paginator="accounts?.length > 0 && master.ongoing_subscriptions_count > 0"
            removableSort
            scrollable
            scrollHeight="400px"
            dataKey="id"
            :rows="10"
            :rowsPerPageOptions="[10, 20, 50, 100]"
            tableStyle="md:min-width: 50rem"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
            :currentPageReportTemplate="$t('public.paginator_caption')"
            :globalFilterFields="['user.name', 'user.email', 'meta_login']"
            ref="dt"
            :loading="isLoading"
        >
            <template #header>
                <div class="flex flex-col md:flex-row gap-3 items-center self-stretch md:pb-5">
                    <div class="relative w-full md:w-60">
                        <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                            <IconSearch size="20" stroke-width="1.5"/>
                        </div>
                        <InputText
                            v-model="filters['global'].value"
                            :placeholder="$t('public.keyword_search')"
                            class="font-normal pl-12 w-full md:w-60"
                        />
                        <div
                            v-if="filters['global'].value !== null"
                            class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                            @click="clearFilterGlobal"
                        >
                            <IconCircleXFilled size="16"/>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 w-full gap-3">
                        <Button
                            type="button"
                            variant="gray-outlined"
                            class="flex gap-3 items-center justify-center w-full md:w-[130px]"
                        >
                            <IconAdjustments size="16" stroke-width="1.5"/>
                            <div class="text-sm font-medium">
                                {{ $t('public.filter') }}
                            </div>
                        </Button>
                        <div class="w-full flex justify-end">
                            <Button
                                type="button"
                                variant="primary-outlined"
                                @click="exportCSV($event)"
                                class="w-full md:w-auto"
                            >
                                {{ $t('public.export') }}
                                <IconDownload size="16" stroke-width="1.5"/>
                            </Button>
                        </div>
                    </div>
                </div>
            </template>
            <template #empty>
                <Empty
                    :title="$t('public.empty_member_title')"
                    :message="$t('public.empty_member_message')"
                />
            </template>
            <template #loading>
                <div class="flex flex-col gap-2 items-center justify-center">
                    <ProgressSpinner
                        strokeWidth="4"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $t('public.loading_users_caption') }}</span>
                </div>
            </template>
            <template v-if="accounts?.length > 0 && master.ongoing_subscriptions_count > 0">
                <Column
                    field="approval_at"
                    sortable
                    :header="$t('public.join_date')"
                    class="hidden md:table-cell min-w-[136px]"
                >
                    <template #body="slotProps">
                        {{ dayjs(slotProps.data.approval_at).format('YYYY/MM/DD') }}
                    </template>
                </Column>
                <Column
                    field="user.name"
                    sortable
                    :header="$t('public.name')"
                    class="hidden md:table-cell"
                >
                    <template #body="slotProps">
                        <div class="flex flex-col items-start">
                            <div class="font-medium max-w-[80px] lg:max-w-[160px] truncate">
                                {{ slotProps.data.user.name }}
                            </div>
                            <div class="text-surface-500 text-xs max-w-[80px] lg:max-w-[160px] truncate">
                                {{ slotProps.data.user.email }}
                            </div>
                        </div>
                    </template>
                </Column>
                <Column
                    field="leader"
                    :header="$t('public.leader')"
                    class="hidden md:table-cell"
                >
                    <template #body="slotProps">
                        <div class="flex flex-col items-start">
                            <div class="text-xs max-w-[80px] lg:max-w-[160px] truncate">
                                {{ slotProps.data.user.group.group.group_leader.name }}
                            </div>
                            <Tag
                                class="flex items-center gap-1 rounded"
                                :style="{ background: formatRgbaColor(slotProps.data.user.group.group.color, 0.1) }"
                            >
                                <div
                                    class="w-1 h-1 grow-0 shrink-0 rounded-full"
                                    :style="{ backgroundColor: `#${slotProps.data.user.group.group.color}` }"
                                ></div>
                                <div
                                    class="text-xxs font-semibold text-nowrap"
                                    :style="{ color: `#${slotProps.data.user.group.group.color}` }"
                                >
                                    {{ slotProps.data.user.group.group.name }}
                                </div>
                            </Tag>
                        </div>
                    </template>
                </Column>
                <Column
                    field="meta_login"
                    :header="$t('public.account')"
                    class="hidden md:table-cell">
                    <template #body="slotProps"
                    >
                        {{ slotProps.data.meta_login }}
                    </template>
                </Column>
                <Column
                    field="subscription_amount"
                    sortable
                    :header="$t('public.capital') + '&nbsp;($)'"
                    class="hidden md:table-cell"
                >
                    <template #body="slotProps">
                        {{ formatAmount(slotProps.data.subscription_amount) }}
                    </template>
                </Column>
                <ColumnGroup type="footer">
                    <Row>
                        <Column class="hidden md:table-cell" :footer="$t('public.total') + ' ($):'" :colspan="4" footerStyle="text-align:right" />
                        <Column class="hidden md:table-cell" :footer="formatAmount(master.ongoing_subscriptions_sum_subscription_amount)" />
                    </Row>
                </ColumnGroup>
            </template>
        </DataTable>
    </div>
</template>
