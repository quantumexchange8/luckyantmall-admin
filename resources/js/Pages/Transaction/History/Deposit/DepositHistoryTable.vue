<script setup>
import Card from "primevue/card";
import dayjs from "dayjs";
import ProgressSpinner from "primevue/progressspinner";
import Empty from "@/Components/Empty.vue";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {IconAdjustments, IconCircleXFilled, IconDownload, IconSearch} from "@tabler/icons-vue";
import Tag from "primevue/tag";
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import {onMounted, ref, watchEffect} from "vue";
import {generalFormat} from "@/Composables/format.js";
import {FilterMatchMode} from "@primevue/core/api";
import {usePage} from "@inertiajs/vue3";
import Image from "primevue/image";

const props = defineProps({
    depositHistoryCount: Number
});

const depositHistories = ref([]);
const isLoading = ref(false);
const {formatRgbaColor} = generalFormat();
const emit = defineEmits(['update:totalPendingAmount']);

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/transaction/history/getDepositHistoryData');
        depositHistories.value = response.data.depositHistories;
        emit('update:totalPendingAmount', response.data.totalPendingAmount);
    } catch (error) {
        console.error('Error fetching pending deposits:', error);
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

const getSeverity = (status) => {
    switch (status) {
        case 'fail':
            return 'danger';

        case 'success':
            return 'success';

        default:
            return null;
    }
}

// dialog
const visible = ref(false);
const data = ref({});
const openDialog = (rowData) => {
    visible.value = true;
    data.value = rowData;
};

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getResults();
    }
});
</script>

<template>
    <Card class="w-full">
        <template #content>
            <div v-if="depositHistoryCount === 0">
                <Empty
                    :title="$t('public.empty_deposit_title')"
                    :message="$t('public.empty_deposit_message')"
                />
            </div>

            <div v-else>
                <DataTable
                    v-model:filters="filters"
                    :value="depositHistories"
                    :paginator="depositHistories?.length > 0"
                    removableSort
                    dataKey="id"
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    tableStyle="md:min-width: 50rem"
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    :currentPageReportTemplate="$t('public.paginator_caption')"
                    :globalFilterFields="['user.name', 'user.email', 'transaction_number']"
                    ref="dt"
                    :loading="isLoading"
                    selectionMode="single"
                    @row-click="(event) => openDialog(event.data)"
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
                            :title="$t('public.empty_deposit_title')"
                            :message="$t('public.empty_deposit_message')"
                        />
                    </template>
                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $t('public.loading_transactions_caption') }}</span>
                        </div>
                    </template>
                    <template v-if="depositHistories?.length > 0">
                        <Column
                            field="approval_at"
                            sortable
                            class="hidden md:table-cell min-w-[120px]"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.date') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ dayjs(slotProps.data.approval_at).format('YYYY/MM/DD HH:mm:ss') }}
                            </template>
                        </Column>
                        <Column
                            field="user.name"
                            sortable
                            frozen
                            :header="$t('public.name')"
                            class="hidden md:table-cell min-w-[220px]"
                        >
                            <template #body="slotProps">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full overflow-hidden grow-0 shrink-0">
                                        <template v-if="slotProps.data.profile_photo">
                                            <img :src="slotProps.data.profile_photo" alt="profile_photo">
                                        </template>
                                        <template v-else>
                                            <DefaultProfilePhoto/>
                                        </template>
                                    </div>
                                    <div class="flex flex-col items-start">
                                        <div class="font-medium max-w-[150px] truncate">
                                            {{ slotProps.data.user.name }}
                                        </div>
                                        <div class="text-gray-500 text-xs max-w-[150px] truncate">
                                            @{{ slotProps.data.user.username }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="user.email"
                            sortable
                            style="width: 15%"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.email') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ slotProps.data.user.email }}
                            </template>
                        </Column>
                        <Column
                            field="leader"
                            sortable
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.leader') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ slotProps.data.user.group.group.group_leader.name }}
                            </template>
                        </Column>
                        <Column
                            field="transaction_number"
                            sortable
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.txn_no') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ slotProps.data.transaction_number }}
                            </template>
                        </Column>
                        <Column
                            field="amount"
                            sortable
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.amount') }}</span>
                            </template>
                            <template #body="slotProps">
                                ¥{{ slotProps.data.amount }}
                            </template>
                        </Column>
                        <Column
                            field="status"
                            sortable
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.status') }}</span>
                            </template>
                            <template #body="slotProps">
                                <Tag
                                    :value="$t(`public.${slotProps.data.status}`)"
                                    :severity="getSeverity(slotProps.data.status)"
                                />
                            </template>
                        </Column>
                        <Column class="md:hidden">
                            <template #body="slotProps">
                                <div class="flex flex-col items-start gap-1 self-stretch">
                                    <div class="flex items-center gap-2 self-stretch w-full">
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                                <template v-if="slotProps.data.profile_photo">
                                                    <img :src="slotProps.data.profile_photo"
                                                         alt="profile_photo">
                                                </template>
                                                <template v-else>
                                                    <DefaultProfilePhoto/>
                                                </template>
                                            </div>
                                            <div class="flex flex-col items-start">
                                                <div
                                                    class="font-medium max-w-[120px] xxs:max-w-[140px] min-[390px]:max-w-[180px] xs:max-w-[220px] truncate">
                                                    {{ slotProps.data.name }}
                                                </div>
                                                <div
                                                    class="text-gray-500 text-xs max-w-[120px] xxs:max-w-[140px] min-[390px]:max-w-[180px] xs:max-w-[220px] truncate">
                                                    {{ slotProps.data.email }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-end">

                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 h-[26px]">
                                        <!--                                        <StatusBadge :value="slotProps.data.role">-->
                                        <!--                                            {{ $t(`public.${slotProps.data.role}`) }}-->
                                        <!--                                        </StatusBadge>-->
                                        <div class="flex items-center justify-center">
                                            <div
                                                v-if="slotProps.data.group_id"
                                                class="flex items-center gap-2 rounded justify-center py-1 px-2"
                                                :style="{ backgroundColor: formatRgbaColor(slotProps.data.group_color, 0.1) }"
                                            >
                                                <div
                                                    class="w-1.5 h-1.5 grow-0 shrink-0 rounded-full"
                                                    :style="{ backgroundColor: `#${slotProps.data.group_color}` }"
                                                ></div>
                                                <div
                                                    class="text-xs font-semibold"
                                                    :style="{ color: `#${slotProps.data.group_color}` }"
                                                >
                                                    {{ slotProps.data.group_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>

    <Dialog v-model:visible="visible" modal :header="$t('public.deposit_details')" class="dialog-xs md:dialog-md">
        <div class="flex flex-col justify-center items-start pb-4 gap-3 self-stretch border-b border-gray-200 dark:border-surface-600 md:flex-row md:pt-4 md:justify-between">
            <!-- below md -->
            <span class="md:hidden self-stretch text-surface-950 dark:text-white text-xl font-semibold">¥{{ data.transaction_amount }}</span>
            <div class="flex items-center gap-3 self-stretch">
                <div class="w-9 h-9 rounded-full overflow-hidden grow-0 shrink-0">
                    <DefaultProfilePhoto />
                </div>
                <div class="flex flex-col items-start flex-grow">
                    <span class="self-stretch overflow-hidden text-surface-950 dark:text-white text-ellipsis text-sm font-medium">{{ data.user.name }}</span>
                    <span class="self-stretch overflow-hidden text-surface-500 text-ellipsis text-xs">{{ data.user.email }}</span>
                </div>
            </div>
            <!-- above md -->
            <span class="hidden md:block w-[180px] text-surface-950 dark:text-white text-right text-xl font-semibold">¥{{ data.transaction_amount }}</span>
        </div>

        <div class="flex flex-col items-center py-4 gap-3 self-stretch border-b border-gray-200 dark:border-surface-600">
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-surface-500 text-xs">{{ $t('public.txn_no') }}</span>
                <span class="self-stretch text-surface-950 dark:text-white text-sm font-medium">{{ data.transaction_number }}</span>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-surface-500 text-xs">{{ $t('public.date') }}</span>
                <span class="self-stretch text-surface-950 dark:text-white text-sm font-medium">{{ dayjs(data.approval_at).format('YYYY/MM/DD HH:mm:ss') }}</span>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-surface-500 text-xs">{{ $t('public.to') }}</span>
                <span class="self-stretch text-surface-950 dark:text-white text-sm font-medium">{{ $t(`public.${data.to_wallet.type}`) }}</span>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-surface-500 text-xs">{{ $t('public.status') }}</span>
                <Tag
                    :value="$t(`public.${data.status}`)"
                    :severity="getSeverity(data.status)"
                />
            </div>
        </div>

        <div class="flex flex-col items-center py-4 gap-3 self-stretch border-b border-gray-200 dark:border-surface-600">
            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs">
                    {{ $t('public.bank_name') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ data.to_payment_platform_name }}
                </div>
            </div>
            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs">
                    {{ $t('public.receiver_name') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ data.to_payment_account_name }}
                </div>
            </div>
            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs">
                    {{ $t('public.account_number') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ data.to_payment_account_no }}
                </div>
            </div>
        </div>

        <div class="flex flex-col items-center py-4 gap-3 self-stretch">
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-surface-500 text-xs">{{ $t('public.remarks') }}</span>
                <span class="self-stretch text-surface-950 dark:text-white text-sm font-medium">{{ data.remarks ?? '-' }}</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs">
                    {{ $t('public.payment_slip') }}
                </div>
                <div class="flex gap-2 items-center self-stretch">
                    <Image
                        :src="data.media[0].original_url"
                        alt="Image"
                        imageClass="max-w-full h-9 object-contain rounded"
                        preview
                    />
                    <span class="text-sm">{{ data.media[0].file_name }}</span>
                </div>
            </div>
        </div>

    </Dialog>
</template>
