<script setup>
import {onMounted, ref, watchEffect} from "vue";
import Empty from "@/Components/Empty.vue";
import Button from "@/Components/Button.vue";
import {generalFormat} from "@/Composables/format.js";
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import {
    IconSearch,
    IconCircleXFilled,
    IconAdjustments,
    IconDownload
} from "@tabler/icons-vue"
import DatePicker from "primevue/datepicker";
import Card from "primevue/card"
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import {FilterMatchMode} from "@primevue/core/api";
import InputText from "primevue/inputtext";
import Tag from "primevue/tag";
import dayjs from "dayjs";
import {usePage} from "@inertiajs/vue3";
import ProgressSpinner from 'primevue/progressspinner';
import CustomerTableAction from "@/Pages/Customer/Listing/CustomerTableAction.vue";

const props = defineProps({
    customerCounts: Number
})

const users = ref([]);
const total_users = ref(0);
const isLoading = ref(false);
const {formatRgbaColor} = generalFormat();

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/customer/getCustomersData');
        users.value = response.data.users;
        total_users.value = response.data.total_users;
    } catch (error) {
        console.error('Error fetching users:', error);
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

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getResults();
    }
});
</script>

<template>
    <Card class="w-full">
        <template #content>
            <div
                class="w-full"
            >
                <DataTable
                    v-model:filters="filters"
                    :value="users"
                    :paginator="users?.length > 0 && total_users > 0"
                    removableSort
                    dataKey="id"
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    tableStyle="md:min-width: 50rem"
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    :currentPageReportTemplate="$t('public.paginator_caption')"
                    :globalFilterFields="['name', 'email', 'username']"
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
                    <template v-if="users?.length > 0 && total_users > 0">
                        <Column
                            field="created_at"
                            sortable
                            class="hidden md:table-cell min-w-[120px]"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.joined') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ dayjs(slotProps.data.created_at).format('YYYY-MM-DD') }}
                            </template>
                        </Column>
                        <Column
                            field="name"
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
                                            {{ slotProps.data.name }}
                                        </div>
                                        <div class="text-gray-500 text-xs max-w-[150px] truncate">
                                            @{{ slotProps.data.username }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="email"
                            sortable
                            style="width: 15%"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.email') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ slotProps.data.email }}
                            </template>
                        </Column>
                        <Column
                            field="upline.name"
                            class="hidden md:table-cell min-w-[220px]"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.referer') }}</span>
                            </template>
                            <template #body="slotProps">
                                <div
                                    v-if="slotProps.data.upline"
                                    class="flex flex-col items-start"
                                >
                                    <div class="font-medium max-w-[180px] truncate">
                                        {{ slotProps.data.upline.name }}
                                    </div>
                                    <div class="text-gray-500 text-xs max-w-[180px] truncate">
                                        {{ slotProps.data.upline.email }}
                                    </div>
                                </div>
                                <div v-else>
                                    -
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="group"
                            class="hidden md:table-cell min-w-60"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.group') }}</span>
                            </template>
                            <template #body="slotProps">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center">
                                        <Tag
                                            v-if="slotProps.data.group"
                                            class="flex items-center gap-2 w-full rounded"
                                            :style="{ background: formatRgbaColor(slotProps.data.group.group.color, 0.1) }"
                                        >
                                            <div
                                                class="w-1.5 h-1.5 grow-0 shrink-0 rounded-full"
                                                :style="{ backgroundColor: `#${slotProps.data.group.group.color}` }"
                                            ></div>
                                            <div
                                                class="text-xs font-semibold text-nowrap"
                                                :style="{ color: `#${slotProps.data.group.group.color}` }"
                                            >
                                                {{ slotProps.data.group.group.name }}
                                            </div>
                                        </Tag>
                                        <div v-else>
                                            -
                                        </div>
                                    </div>
                                    <div class="text-xs truncate">
                                      {{ slotProps.data.group.group.group_leader.name }}
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="rank"
                            class="hidden md:table-cell min-w-28"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.rank') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ $t(`public.${slotProps.data.rank.rank_name}`) }}
                            </template>
                        </Column>
                        <Column
                            field="role"
                            class="hidden md:table-cell min-w-44"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.role') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ $t(`public.${slotProps.data.role}`) }}
                            </template>
                        </Column>
                        <Column
                            field="country_id"
                            sortable
                            class="hidden md:table-cell min-w-32"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.country') }}</span>
                            </template>
                            <template #body="slotProps">
                                <div class="flex items-center gap-1">
                                    <span>{{ slotProps.data.country.emoji }}</span>
                                    <span class="max-w-20 truncate">{{ slotProps.data.country.name }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="kyc_status"
                            sortable
                            style="width: 15%"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.status') }}</span>
                            </template>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.kyc_status"
                                     :severity="getSeverity(slotProps.data.kyc_status)"/>
                            </template>
                        </Column>
                        <Column
                            field="action"
                            frozen
                            alignFrozen="right"
                            header=""
                            style="width: 15%"
                            class="hidden md:table-cell"
                        >
                            <template #body="slotProps">
                                <CustomerTableAction
                                    :customer="slotProps.data"
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
</template>
