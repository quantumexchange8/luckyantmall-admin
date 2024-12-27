<script setup>
import Card from "primevue/card"
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Input from "@/Components/Input.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import Loading from "@/Components/Loading.vue";
import {onMounted, ref, watch, watchEffect} from "vue";
import {transactionFormat} from "@/Composables/index.js";
import {FilterMatchMode} from "@primevue/core/api";
import {usePage, router} from "@inertiajs/vue3";
import Button from "@/Components/Button.vue";
// import {
//     SlidersOneIcon,
//     CloudDownloadIcon,
//     XIcon,
//     SearchLgIcon
// } from "@/Components/Icons/outline.jsx"
import dayjs from "dayjs";
import Popover from "primevue/popover";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker"
import debounce from "lodash/debounce.js";
import {IconFileSearch, IconCircleXFilled, IconDownload, IconSearch} from "@tabler/icons-vue";
import Dialog from "primevue/dialog";
import Tag from "primevue/tag";
import RadioButton from "primevue/radiobutton";
import {useConfirm} from "primevue/useconfirm";
import { trans } from "laravel-vue-i18n";

const props = defineProps({
    selectedType: String
})

const isLoading = ref(false);
const dt = ref(null);
const orders = ref([]);
const expandedRows = ref({});
const exportTable = ref('no');
const {formatAmount} = transactionFormat();
const totalRecords = ref(0);
const first = ref(0);
// const totalAmount = ref();
// const successAmount = ref();
// const rejectedAmount = ref();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    // type: { value: props.selectedType, matchMode: FilterMatchMode.EQUALS },
    leader_id: { value: null, matchMode: FilterMatchMode.EQUALS },
    start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    // fund_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: props.selectedType, matchMode: FilterMatchMode.EQUALS },
});

const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value };

    try {
        setTimeout(async () => {
            const params = {
                page: JSON.stringify(event?.page + 1),
                sortField: event?.sortField,
                sortOrder: event?.sortOrder,
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value)
            };

            const url = route('order.getOrderHistory', params);
            const response = await fetch(url);
            const results = await response.json();

            orders.value = results?.data?.data;
            totalRecords.value = results?.data?.total;

            // totalAmount.value = results?.totalAmount;
            // successAmount.value = results?.successAmount;
            // rejectedAmount.value = results?.rejectedAmount;
            isLoading.value = false;
        }, 100);
    }  catch (e) {
        orders.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};
const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onSort = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onFilter = (event) => {
    lazyParams.value.filters = filters.value ;
    loadLazyData(event);
};

// const op = ref();
// const toggle = (event) => {
//     op.value.toggle(event);
//     getLeaders();
// }

// const leaders = ref();
// const loadingLeaders = ref(false);
const selectedDate = ref([]);

// const getLeaders = async () => {
//     loadingLeaders.value = true;
//     try {
//         const response = await axios.get('/getLeaders');
//         leaders.value = response.data;
//     } catch (error) {
//         console.error('Error fetching leaders:', error);
//     } finally {
//         loadingLeaders.value = false;
//     }
// };

const clearJoinDate = () => {
    selectedDate.value = [];
}

watch(selectedDate, (newDateRange) => {
    if (Array.isArray(newDateRange)) {
        const [startDate, endDate] = newDateRange;
        filters.value['start_date'].value = startDate;
        filters.value['end_date'].value = endDate;
        loadLazyData();
    } else {
        console.warn('Invalid date range format:', newDateRange);
    }
})

watch(selectedDate, () => {
    loadLazyData();
})

onMounted(() => {
    lazyParams.value = {
        first: dt.value.first,
        rows: dt.value.rows,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };

    loadLazyData();
});

watch(
    filters.value['global'],
    debounce(() => {
        loadLazyData();
    }, 300)
);

watch([filters.value['leader_id'], filters.value['status']], () => {
    loadLazyData()
});

const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['leader_id'].value = null;
    filters.value['start_date'].value = null;
    filters.value['end_date'].value = null;
};

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

// watchEffect(() => {
//     if (usePage().props.toast !== null) {
//         getResults();
//     }
// });

const visible = ref(false);
const detail = ref(null);

const openDialog = (data) => {
    visible.value = true;
    detail.value = data;
}

const getSeverity = (status) => {
    switch (status) {
        case 'processing':
            return 'info';

        case 'shipping':
            return 'info';

        case 'completed':
            return 'success';

        case 'pending_payment':
            return 'secondary';
        
        case 'cancelled':
            return 'danger';

        case 'return_refund':
            return 'danger';
    }
}

const proceedLabel = (orderStatus) => {
    switch (orderStatus) {
        case 'processing':
            return 'shipping';
        case 'shipping':
            return 'completed';
        case 'pending_payment':
            return 'cancelled';
        default:
            return 'unknown';
    }
};

// const emit = defineEmits(['update-totals']);

// watch([totalAmount, successAmount, rejectedAmount], () => {
//     emit('update-totals', {
//         totalAmount: totalAmount.value,
//         successAmount: successAmount.value,
//         rejectedAmount: rejectedAmount.value,
//     });
// });

const exportStatus = ref(false);

const exportReport = () => {
    exportStatus.value = true;
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value };

    const params = {
        page: JSON.stringify(event?.page + 1),
        sortField: event?.sortField,
        sortOrder: event?.sortOrder,
        include: [],
        lazyEvent: JSON.stringify(lazyParams.value),
        exportStatus: true,
    };

    const url = route('order.getOrderHistory', params);  // Construct the export URL

    try {
        // Send the request to the backend to trigger the export
        window.location.href = url;  // This will trigger the download directly
    } catch (e) {
        console.error('Error occurred during export:', e);  // Log the error if any
    } finally {
        isLoading.value = false;  // Reset loading state
        exportStatus.value = false;  // Reset export status
    }
};

const confirm = useConfirm();

// const requireConfirmation = (order) => {
//     const old_status = trans(`public.${order.status}`);
//     const new_status = "Test";
//     confirm.require({
//         group: 'headless-primary',
//         header: trans('public.update_status'),
//         // actionType: 'rebate',
//         text: trans('public.update_order_caption', { old_status, new_status }),
//         cancelButton: trans('public.cancel'),
//         acceptButton: trans('public.confirm'),
//         action: () => {
//             route.visit(route('order.updateOrderStatus', props.item.id), {
//                 method: 'patch',
//                 data: {
//                     id: props.item.id,
//                 },
//             })

//         }
//     });
// }

const requireConfirmation = (action_type, data) => {
    const old_status = trans(`public.${data.status}`);
    // const statusTransitions = {
    //     processing: 'shipping',
    //     shipping: 'completed',
    //     pending_payment: 'cancelled',
    // };
    // const new_status = trans(`public.${statusTransitions[data.status]}`) || trans('public.unknown');
    console.log(data.id)
    const next_status = proceedLabel(data.status)
    const new_status = trans(`public.${next_status}`);

    const messages = {
        update_order: {
            group: 'headless-primary',
            header: trans('public.update_order_status'),
            text: trans('public.update_order_caption', { old_status, new_status }),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('order.updateOrderStatus', data.id), {
                    method: 'patch',
                    data: {
                        id: data.id,
                        status: next_status,
                    },
                })

            }
        },
        update_order_item: {
            group: 'headless-primary',
            header: trans('public.update_item_status'),
            text: trans('public.update_order_caption', { old_status, new_status }),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('order.updateOrderItemStatus', data.id), {
                    method: 'patch',
                    data: {
                        id: data.id,
                        status: next_status,
                    },
                })

            }
        },
    };

    const { group, header, text, dynamicText, suffix, actionType, cancelButton, acceptButton, action } = messages[action_type];

    confirm.require({
        group,
        header,
        actionType,
        text,
        dynamicText,
        suffix,
        cancelButton,
        acceptButton,
        accept: action
    });
};
</script>

<template>
    <Card class="w-full">
        <template #content>
            <div
                class="w-full"
            >
                <DataTable
                    :value="orders"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    lazy
                    paginator
                    removableSort
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    :currentPageReportTemplate="$t('public.paginator_caption')"
                    :first="first"
                    :rows="10"
                    v-model:filters="filters"
                    ref="dt"
                    dataKey="id"
                    :totalRecords="totalRecords"
                    :loading="isLoading"
                    @page="onPage($event)"
                    @sort="onSort($event)"
                    @filter="onFilter($event)"
                    :globalFilterFields="['user.name', 'user.email', 'meta_login', 'master_meta_login']"
                >
                    <template #header>
                        <div class="flex flex-col md:flex-row gap-3 items-center self-stretch md:pb-5">
                            <div class="relative w-full md:w-60">
                                <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                                    <IconSearch size="20" stroke-width="1.5"/>
                                </div>
                                <InputText
                                    v-model="filters['global'].value"
                                    :placeholder="$t('public.search')"
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
                            <div class="grid grid-cols-1 w-full gap-3">
                                <!-- <Button
                                    class="w-full md:w-28 flex gap-2"
                                    severity="secondary"
                                    outlined
                                    @click="toggle"
                                >
                                    <SlidersOneIcon class="w-4 h-4" />
                                    Filter
                                </Button> -->
                                <div class="w-full flex justify-end">
                                    <Button
                                        type="button"
                                        class="w-full md:w-auto"
                                        variant="primary-outlined"
                                        @click="exportReport"
                                        :disabled="exportTable==='yes'"
                                    >
                                        {{ $t('public.export') }}
                                        <IconDownload size="16" stroke-width="1.5"/>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #empty>
                        <div class="flex flex-col">
                            <span>No orders</span>
                        </div>
                    </template>
                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <Loading />
                            <span v-if="exportTable === 'no'" class="text-sm text-gray-700 dark:text-gray-300">Loading orders</span>
                            <span v-else class="text-sm text-gray-700 dark:text-gray-300">Exporting Report</span>
                        </div>
                    </template>
                    <template v-if="orders?.length > 0">
                        <Column
                            field="created_at"
                            sortable
                            frozen
                            class="table-cell min-w-36"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.date') }}</span>
                            </template>
                            <template #body="slotProps">
                                <span class="uppercase">{{ dayjs(slotProps.data.created_at).format('DD/MM/YYYY HH:mm:ss') }}</span>
                            </template>
                        </Column>
                        <Column
                            field="user.name"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.name') }}</span>
                            </template>
                            <template #body="slotProps">
                                <div class="flex items-center gap-2">
                                    <div v-if="slotProps.data.user" class="flex flex-col">
                                        <span class="font-semibold">{{ slotProps.data.user.name }}</span>
                                        <span class="text-gray-400">{{ slotProps.data.user.email }}</span>
                                    </div>
                                    <div v-else class="h-[37px] flex items-center self-stretch">
                                        -
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <!-- <Column
                            field="first_leader_id"
                            show-filter-menu
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.leader') }}</span>
                            </template>
                            <template #body="slotProps">
                                <div class="flex items-center gap-2">
                                    <div v-if="slotProps.data.first_leader_name" class="flex flex-col">
                                        <span class="font-semibold">{{ slotProps.data.first_leader_name }}</span>
                                        <span class="text-gray-400">{{ slotProps.data.first_leader_email }}</span>
                                    </div>
                                    <div v-else class="h-[37px] flex items-center self-stretch">
                                        -
                                    </div>
                                </div>
                            </template>
                        </Column> -->
                        <!-- <Column
                            field="to_wallet_id"
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.to') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ $t(`public.${slotProps.data.to_wallet.type}`) }}
                            </template>
                        </Column> -->
                        <Column
                            field="order_number"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.order_no') }}</span>
                            </template>
                            <template #body="slotProps">
                                <span class="font-semibold">{{ slotProps.data.order_number }}</span>
                            </template>
                        </Column>
                        <Column
                            field="total_price"
                            sortable
                            class="table-cell min-w-40"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.amount') }}</span>
                            </template>
                            <template #body="slotProps">
                                ¥ {{ formatAmount(slotProps.data.total_price ?? 0) }}
                            </template>
                        </Column>
                        <Column
                            field="status"
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.status') }}</span>
                            </template>
                            <template #body="slotProps">
                                <Tag
                                    :value="$t(`public.${slotProps.data.status}`)"
                                    :severity="getSeverity(slotProps.data.status)"
                                />
                            </template>
                        </Column>
                        <Column
                            field="action"
                            class="table-cell text-center"
                            frozen
                            align-frozen="right"
                        >
                            <template #body="{data}">
                                <Button
                                    v-if="['processing', 'shipping', 'pending_payment'].includes(data.status)"
                                    type="button"
                                    size="base"
                                    variant="primary-flat"
                                    class="!p-2"
                                    @click="openDialog(data)"
                                >
                                    {{ $t('public.update_status') }}
                                </Button>
                                <Button
                                    v-else
                                    type="button"
                                    pill
                                    size="base"
                                    variant="gray-outlined"
                                    class="!p-2"
                                    @click="openDialog(data)"
                                >
                                    <IconFileSearch size="14" />
                                </Button>
                            </template>
                        </Column>
                        <!-- <Column
                            field="action"
                            class="table-cell"
                            frozen
                            align-frozen="right"
                        >
                            <template #body="{data}">
                                <Button
                                    type="button"
                                    pill
                                    size="base"
                                    variant="gray-outlined"
                                    class="!p-2"
                                    @click="openDialog(data)"
                                >
                                    <IconFileSearch size="14" />
                                </Button>
                            </template>
                        </Column> -->
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>
<!-- 
    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 dark:text-white font-semibold">
                    {{ $t('public.filter_by_leaders') }}
                </div>
                <Select
                    v-model="filters['leader_id'].value"
                    :options="leaders"
                    optionLabel="name"
                    placeholder="Select a leader"
                    class="w-full"
                    filter
                    :filter-fields="['name', 'email']"
                    :isLoading="loadingLeaders"
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="flex items-center">
                            {{ slotProps.value.name }}
                        </div>
                        <span v-else>{{ slotProps.placeholder }}</span>
                    </template>
                    <template #option="slotProps">
                        <div class="flex flex-col max-w-[220px] truncate">
                            <div>{{ slotProps.option.name }}</div>
                            <div class="text-gray-400">{{ slotProps.option.email }}</div>
                        </div>
                    </template>
                </Select>
            </div>

            
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 dark:text-white font-semibold">
                    {{ $t('public.filter_date') }}
                </div>
                <div class="relative w-full">
                    <DatePicker
                        v-model="selectedDate"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        selectionMode="range"
                        placeholder="dd/mm/yyyy - dd/mm/yyyy"
                        tim
                    />
                    <div
                        v-if="selectedDate && selectedDate.length > 0"
                        class="absolute top-2/4 -mt-2 right-2 text-gray-400 select-none cursor-pointer bg-transparent"
                        @click="clearJoinDate"
                    >
                        <XIcon class="w-4 h-4" />
                    </div>
                </div>
            </div>

          
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 dark:text-white font-semibold">
                    Filter by fund
                </div>
                <div class="flex flex-col gap-1 self-stretch">
                    <div class="flex items-center gap-2 text-sm text-gray-950 dark:text-gray-300">
                        <RadioButton v-model="filters['fund_type'].value" inputId="demo_fund" value="DemoFund" class="w-4 h-4" />
                        <label for="demo_fund">Demo</label>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-950 dark:text-gray-300">
                        <RadioButton v-model="filters['fund_type'].value" inputId="real_fund" value="RealFund" class="w-4 h-4" />
                        <label for="real_fund">Real</label>
                    </div>
                </div>
            </div>

            
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 dark:text-white font-semibold">
                    Filter by status
                </div>
                <div class="flex flex-col gap-1 self-stretch">
                    <div class="flex items-center gap-2 text-sm text-gray-950 dark:text-gray-300">
                        <RadioButton v-model="filters['status'].value" inputId="status_success" value="Success" class="w-4 h-4" />
                        <label for="status_success">Success</label>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-950 dark:text-gray-300">
                        <RadioButton v-model="filters['status'].value" inputId="status_rejected" value="Rejected" class="w-4 h-4" />
                        <label for="status_rejected">Rejected</label>
                    </div>
                </div>
            </div>

            <Button
                type="button"
                severity="info"
                class="w-full"
                outlined
                @click="clearAll"
            >
                Clear All
            </Button>
        </div>
    </Popover> -->

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.view_details')"
        class="dialog-xs md:dialog-md"
    >
        <div class="flex flex-col items-center gap-4 divide-y dark:divide-gray-700 self-stretch">
            <div class="flex flex-col-reverse md:flex-row md:items-center gap-3 self-stretch w-full">
                <div class="flex flex-col items-start w-full">
                    <span class="text-gray-950 dark:text-white text-sm font-medium">{{ detail.user.name }}</span>
                    <span class="text-gray-500 text-xs">{{ detail.user.email }}</span>
                </div>
                <div class="min-w-[180px] text-gray-950 dark:text-white font-semibold text-xl md:text-right">
                    ¥ {{ formatAmount(detail.total_price ?? 0) }}
                </div>
            </div>

            <div class="flex flex-col gap-3 items-start w-full pt-4">
                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-gray-500 text-xs font-medium">
                        {{ $t('public.date') }}
                    </div>
                    <div class="text-gray-950 dark:text-white text-sm font-medium">
                        {{ dayjs(detail.created_at).format('DD/MM/YYYY HH:mm:ss') }}
                    </div>
                </div>
                <!-- <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-gray-500 text-xs font-medium">
                        {{ $t('public.to') }}
                    </div>
                    <div class="text-gray-950 dark:text-white text-sm font-medium">
                        {{ $t(`public.${detail.to_wallet.type}`) }}
                    </div>
                </div> -->
                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-gray-500 text-xs font-medium">
                        {{ $t('public.order_no') }}
                    </div>
                    <div class="text-gray-950 dark:text-white text-sm font-medium">
                        {{ detail.order_number }}
                    </div>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-gray-500 text-xs font-medium">
                        {{ $t('public.transaction_no') }}
                    </div>
                    <div class="text-gray-950 dark:text-white text-sm font-medium">
                        {{ detail.transaction.transaction_number }}
                    </div>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-gray-500 text-xs font-medium">
                        {{ $t('public.status') }}
                    </div>
                    <div class="text-gray-950 dark:text-white text-sm font-medium">
                        <Tag
                            :value="$t(`public.${detail.status}`)"
                            :severity="getSeverity(detail.status)"
                        />
                    </div>
                </div>
            </div>

            <!-- inner table for order items -->
            <div class="flex flex-col items-start pt-2 self-stretch md:pt-0">
                <DataTable
                    v-model:expandedRows="expandedRows"
                    :value="detail.orderItems"
                    dataKey="id"
                    removableSort
                    :pt="{
                        column: {
                            headercell: ({ context, props }) => ({
                                class: [
                                    'font-semibold',
                                    'text-xs',
                                    'uppercase',
                                    'box-border',

                                    // Position
                                    { 'sticky z-20 border-b': props.frozen || props.frozen === '' },

                                    { relative: context.resizable },

                                    // Shape
                                    { 'first:border-l border-y border-r': context?.showGridlines },
                                    'border-0 border-b border-solid',

                                    // Spacing
                                    context?.size === 'small' ? 'py-[0.375rem] px-2' : context?.size === 'large' ? 'py-[0.9375rem] px-5' : 'p-3',

                                    // Color
                                    (props.sortable === '' || props.sortable) && context.sorted ? 'bg-primary-100 text-primary-500' : 'bg-gray-100 text-gray-950',
                                    'border-gray-200 ',

                                    // States
                                    { 'hover:bg-gray-100': (props.sortable === '' || props.sortable) && !context?.sorted },
                                    'focus-visible:outline-none focus-visible:outline-offset-0 focus-visible:ring-1 focus-visible:ring-inset focus-visible:ring-primary-500 dark:focus-visible:ring-primary-400',

                                    // Transition
                                    { 'transition duration-200': props.sortable === '' || props.sortable },

                                    // Misc
                                    { 'cursor-pointer': props.sortable === '' || props.sortable },
                                    {
                                        'overflow-hidden space-nowrap border-y bg-clip-padding': context.resizable // Resizable
                                    },

                                    'hidden md:table-cell',
                                ]
                            }),
                            bodycell: ({ props, context, state, parent }) => ({
                                class: [
                                    // Font
                                    'text-sm font-semibold md:font-normal',

                                    // Alignment
                                    'text-left',

                                    // Spacing
                                    { 'py-2 px-3': context?.size !== 'large' && context?.size !== 'small' && !state['d_editing'] },

                                    // Border
                                    'border-0 border-b border-solid border-gray-200'
                                ]
                            }),
                        },
                    }"
                >
                    <!-- Row Expansion Column -->
                    <Column expander class="w-9 md:w-20 text-gray-500" />

                    <!-- Summary Columns -->
                    <Column sortable field="product_id" header="product" />
                    <Column field="price_per_unit" :header="`${$t('public.total_price')}&nbsp;(Ł)`" class="text-left hidden md:table-cell">
                        <template #body="slotProps">
                            {{ formatAmount(slotProps.data.price_per_unit * slotProps.data.quantity) }}
                        </template>
                    </Column>
                    <Column field="status" :header="`${$t('public.status')}&nbsp;($)`" class="text-left hidden md:table-cell">
                        <template #body="slotProps">
                            {{ formatAmount(slotProps.data.status) }}
                        </template>
                    </Column>
                    <Column field="status" header="`${$t('public.status')}&nbsp;($)`"  class="text-right md:hidden">
                        <template #body="slotProps">
                            $&nbsp;{{ formatAmount(slotProps.data.status) }}
                        </template>
                    </Column>

                    <!-- Row Expansion Content -->
                    <template #expansion="slotProps">
                    <!-- Display only details for each summary entry -->
                        <DataTable 
                            :value="slotProps.data.details" 
                            class="pl-9 md:pl-20"
                            unstyled
                            :pt="{
                                column: {
                                    headercell: ({ context, props }) => ({
                                        class: [
                                            'font-semibold',
                                            'text-xs',
                                            'uppercase',
                                            'box-border',

                                            // Position
                                            { 'sticky z-20 border-b': props.frozen || props.frozen === '' },

                                            { relative: context.resizable },

                                            // Shape
                                            { 'first:border-l border-y border-r': context?.showGridlines },
                                            'border-0 border-b border-solid',

                                            // Spacing
                                            context?.size === 'small' ? 'py-[0.375rem] px-2' : context?.size === 'large' ? 'py-[0.9375rem] px-5' : 'p-3',

                                            // Color
                                            (props.sortable === '' || props.sortable) && context.sorted ? 'bg-primary-50 text-primary-500' : 'bg-white text-gray-950',
                                            'border-gray-200 ',

                                            // States
                                            { 'hover:bg-gray-100': (props.sortable === '' || props.sortable) && !context?.sorted },
                                            'focus-visible:outline-none focus-visible:outline-offset-0 focus-visible:ring-1 focus-visible:ring-inset focus-visible:ring-primary-500 dark:focus-visible:ring-primary-400',

                                            // Transition
                                            { 'transition duration-200': props.sortable === '' || props.sortable },

                                            // Misc
                                            { 'cursor-pointer': props.sortable === '' || props.sortable },
                                            {
                                                'overflow-hidden space-nowrap border-y bg-clip-padding': context.resizable // Resizable
                                            },

                                            'hidden md:table-cell',
                                        ]
                                    }),
                                    bodycell: ({ props, context, state, parent }) => ({
                                        class: [
                                            'flex justify-between items-center md:justify-normal md:items-start',

                                            // Spacing
                                            { 'py-1 md:py-2 px-3': context?.size !== 'large' && context?.size !== 'small' && !state['d_editing'] },

                                            // Border
                                            { 'border-0 border-b border-solid border-gray-200': parent.props.rowIndex != slotProps.data.details.length - 1 }
                                        ]
                                    }),
                                },
                            }"
                        >
                            <Column field="name" :header="$t('public.product')" class="text-sm text-left hidden md:table-cell">
                                <template #body="slotProps">
                                    {{ $t('public.' + slotProps.data.name) }}
                                </template>
                            </Column>
                            <Column field="volume" :header="`${$t('public.volume')}&nbsp;(Ł)`" class="text-sm text-left hidden md:table-cell">
                                <template #body="slotProps">
                                    {{ formatAmount(slotProps.data.volume) }}
                                </template>
                            </Column>
                            <Column field="net_rebate" :header="`${$t('public.rebate')}&nbsp;/&nbsp;Ł&nbsp;($)`" class="text-sm text-left hidden md:table-cell">
                                <template #body="slotProps">
                                    {{ formatAmount(slotProps.data.net_rebate) }}
                                </template>
                            </Column>
                            <Column field="rebate" :header="`${$t('public.total')}&nbsp;($)`" class="text-sm text-left hidden md:table-cell">
                                <template #body="slotProps">
                                    {{ formatAmount(slotProps.data.rebate) }}
                                </template>
                            </Column>
                            <Column field="name" class="md:hidden">
                                <template #body="slotProps">
                                    <div class="flex flex-col items-start">
                                        <span class="overflow-hidden text-xs text-gray-950 text-right text-ellipsis font-semibold">{{ $t('public.' + slotProps.data.name) }}</span>
                                        <div class="flex items-center gap-2 self-stretch">
                                            <span class="text-gray-700 text-xs">{{ `${formatAmount(slotProps.data.volume)}&nbsp;Ł` }}</span>
                                            <span class="text-gray-700 text-sm">|</span>
                                            <span class="text-gray-700 text-xs">{{ `$&nbsp;${formatAmount(slotProps.data.net_rebate)}` }}</span>
                                        </div>
                                    </div>
                                    <span class="w-[100px] overflow-hidden text-sm text-gray-950 text-right text-ellipsis">$&nbsp;{{ formatAmount(slotProps.data.rebate) }}</span>
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </DataTable>
            </div>

            <div class="grid grid-cols-1 w-full gap-3 pt-4" v-if="['processing', 'shipping', 'pending_payment'].includes(detail.status)">
                <div class="w-full flex justify-end">
                    <Button
                        type="button"
                        class="w-full md:w-auto"
                        variant="primary-flat"
                        @click="requireConfirmation('update_order', detail)"
                    >
                        {{ $t('public.proceed_to') + $t(`public.${proceedLabel(detail.status)}`) }}
                    </Button>
                </div>
            </div>
        </div>
    </Dialog>
</template>
