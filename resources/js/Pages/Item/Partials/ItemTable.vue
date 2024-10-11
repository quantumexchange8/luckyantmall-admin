<script setup>
import Card from "primevue/card";
import {onMounted, ref, watchEffect} from "vue";
import dayjs from "dayjs";
import {
    IconAdjustments,
    IconCircleXFilled,
    IconDownload,
    IconSearch
} from "@tabler/icons-vue";
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import Column from "primevue/column";
import ProgressSpinner from "primevue/progressspinner";
import Empty from "@/Components/Empty.vue";
import DataTable from "primevue/datatable";
import {FilterMatchMode} from "@primevue/core/api";
import {usePage} from "@inertiajs/vue3";
import ToggleItemStatus from "@/Pages/Item/Partials/ToggleItemStatus.vue";
import {useLangObserver} from "@/Composables/localeObserver.js";

const props = defineProps({
    itemCounts: Number,
})

const items = ref([]);
const isLoading = ref(false);
const { locale } = useLangObserver();

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/item/getItemsData');
        items.value = response.data.items;
    } catch (error) {
        console.error('Error fetching items:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    getResults();
});

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

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
                    :value="items"
                    :paginator="items?.length > 0"
                    removableSort
                    dataKey="id"
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    tableStyle="md:min-width: 50rem"
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    :currentPageReportTemplate="$t('public.paginator_caption')"
                    :globalFilterFields="[`name.${locale}`]"
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
                            :title="$t('public.empty_items_title')"
                            :message="$t('public.empty_items_message')"
                        />
                    </template>
                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $t('public.loading_items_caption') }}</span>
                        </div>
                    </template>
                    <template v-if="items.length">
                        <Column
                            field="name"
                            style="width: 50%"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.name') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ slotProps.data.name[locale] }}
                            </template>
                        </Column>
                        <Column
                            field="categories_count"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.categories') }}</span>
                            </template>
                            <template #body="slotProps">
                                <span class="lowercase">{{ slotProps.data.categories_count > 0 ?  `${slotProps.data.categories_count} ${$t('public.categories')}` : '-' }}</span>
                            </template>
                        </Column>
                        <Column
                            field="status"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="hidden md:block">{{ $t('public.status') }}</span>
                            </template>
                            <template #body="slotProps">
                                <ToggleItemStatus
                                    :item="slotProps.data"
                                />
                            </template>
                        </Column>
                        <Column
                            field="action"
                            header=""
                            style="width: 10%"
                            class="hidden md:table-cell"
                        >
                            <template #body="slotProps">

                            </template>
                        </Column>
<!--                        <Column class="md:hidden">-->
<!--                            <template #body="slotProps">-->
<!--                                <div class="flex flex-col items-start gap-1 self-stretch">-->
<!--                                    <div class="flex items-center gap-2 self-stretch w-full">-->
<!--                                        <div class="flex items-center gap-3 w-full">-->
<!--                                            <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">-->
<!--                                                <template v-if="slotProps.data.profile_photo">-->
<!--                                                    <img :src="slotProps.data.profile_photo"-->
<!--                                                         alt="profile_photo">-->
<!--                                                </template>-->
<!--                                                <template v-else>-->
<!--                                                    <DefaultProfilePhoto/>-->
<!--                                                </template>-->
<!--                                            </div>-->
<!--                                            <div class="flex flex-col items-start">-->
<!--                                                <div-->
<!--                                                    class="font-medium max-w-[120px] xxs:max-w-[140px] min-[390px]:max-w-[180px] xs:max-w-[220px] truncate">-->
<!--                                                    {{ slotProps.data.name }}-->
<!--                                                </div>-->
<!--                                                <div-->
<!--                                                    class="text-gray-500 text-xs max-w-[120px] xxs:max-w-[140px] min-[390px]:max-w-[180px] xs:max-w-[220px] truncate">-->
<!--                                                    {{ slotProps.data.email }}-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                        <div class="flex items-end">-->

<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="flex items-center gap-1 h-[26px]">-->
<!--                                        &lt;!&ndash;                                        <StatusBadge :value="slotProps.data.role">&ndash;&gt;-->
<!--                                        &lt;!&ndash;                                            {{ $t(`public.${slotProps.data.role}`) }}&ndash;&gt;-->
<!--                                        &lt;!&ndash;                                        </StatusBadge>&ndash;&gt;-->
<!--                                        <div class="flex items-center justify-center">-->
<!--                                            <div-->
<!--                                                v-if="slotProps.data.group_id"-->
<!--                                                class="flex items-center gap-2 rounded justify-center py-1 px-2"-->
<!--                                                :style="{ backgroundColor: formatRgbaColor(slotProps.data.group_color, 0.1) }"-->
<!--                                            >-->
<!--                                                <div-->
<!--                                                    class="w-1.5 h-1.5 grow-0 shrink-0 rounded-full"-->
<!--                                                    :style="{ backgroundColor: `#${slotProps.data.group_color}` }"-->
<!--                                                ></div>-->
<!--                                                <div-->
<!--                                                    class="text-xs font-semibold"-->
<!--                                                    :style="{ color: `#${slotProps.data.group_color}` }"-->
<!--                                                >-->
<!--                                                    {{ slotProps.data.group_name }}-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </template>-->
<!--                        </Column>-->
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>
</template>
