<script setup>
import {ref} from "vue";
import Empty from "@/Components/Empty.vue";
import Button from "@/Components/Button.vue";
import {generalFormat} from "@/Composables/format.js";
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import {
    IconUserFilled
} from "@tabler/icons-vue"
import DatePicker from "primevue/datepicker";
import CreateGroup from "@/Pages/Group/Partials/CreateGroup.vue";
import Card from "primevue/card"
import Skeleton from 'primevue/skeleton';

const props = defineProps({
    groupCount: Number
})

const groups = ref([]);
const isLoading = ref(false);
const currentPage = ref(1);
const rowsPerPage = ref(6);
const totalRecords = ref(0);
const {formatAmount} = generalFormat();
const dates = ref();

const getResults = async (page = 1, filterRowsPerPage = rowsPerPage.value) => {
    isLoading.value = true;

    try {
        let url = `/group/getGroupsData?page=${page}&paginate=${filterRowsPerPage}`;

        const response = await axios.get(url);
        groups.value = response.data.groups;
        totalRecords.value = response.data.totalRecords;
        currentPage.value = response.data.currentPage;
    } catch (error) {
        console.error('Error getting masters:', error);
    } finally {
        isLoading.value = false;
    }
}

getResults();
</script>

<template>
    <Card class="w-full">
        <template #content>
            <div class="flex flex-col items-center gap-5 self-stretch">
                <div class="flex justify-between items-center self-stretch">
                    <div class="flex flex-col md:flex-row items-center self-stretch gap-3">
                        <DatePicker
                            v-model="dates"
                            selectionMode="range"
                            :manualInput="false"
                        />
                        <Button
                            type="button"
                            class=""
                            variant="gray-outlined"
                        >
                            {{ $t('public.filter') }}
                        </Button>
                    </div>
                    <CreateGroup />
                </div>

                <div v-if="groupCount === 0 && !groups.length">
                    <Empty
                        :title="$t('public.no_groups_created_yet')"
                        :message="$t('public.no_groups_created_yet_caption')"
                    />
                </div>

                <div
                    v-else
                    class="w-full"
                    :class="{
                'grid md:grid-cols-2': isLoading
            }"
                >
                    <div
                        v-if="isLoading"
                        class="flex flex-col items-center self-stretch w-full shadow-toast"
                    >
                        <div
                            class="py-2 px-4 flex items-center gap-3 self-stretch bg-primary-500"
                        >
                            <div class="flex-1 text-white font-semibold">
                                <Skeleton width="9rem" class="my-1" borderRadius="2rem"></Skeleton>
                            </div>
                            <div class="flex items-center gap-2">
                                <IconUserFilled size="16" stroke-width="1.25" color="white" />
                            </div>
                        </div>
                        <div class="p-4 flex flex-col items-center gap-2 self-stretch dark:bg-surface-600/20">
                            <div class="min-w-[240px] pb-3 flex items-center gap-3 self-stretch border-b border-solid border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-7 h-7 rounded-full overflow-hidden">
                                        <DefaultProfilePhoto />
                                    </div>
                                    <div class="flex flex-col items-start flex-1">
                                        <div class="max-w-40 self-stretch overflow-hidden whitespace-nowrap text-gray-950 dark:text-white text-ellipsis text-sm font-medium md:max-w-[500px] xl:max-w-3xl">
                                            <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                                        </div>
                                        <div class="max-w-40 self-stretch overflow-hidden whitespace-nowrap text-gray-500 dark:text-gray-500 text-ellipsis text-xs md:max-w-[500px] xl:max-w-3xl">
                                            <Skeleton width="12rem" height="0.5rem" class="my-1" borderRadius="2rem"></Skeleton>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="min-w-[240px] pb-2 grid grid-cols-2 gap-2 self-stretch border-b border-solid border-gray-200 dark:border-gray-700">
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.total_deposit') }}:</span>
                                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.total_withdrawal') }}:</span>
                                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 self-stretch">
                                <div class="w-full flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.deposit') }}:</span>
                                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                                </div>
                                <div class="w-full flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.withdrawal') }}:</span>
                                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                                </div>
                                <div class="w-full flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.active_fund') }}:</span>
                                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                                </div>
                                <div class="w-full flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.profit') }}:</span>
                                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="!groups.length">
                        <Empty
                            :title="$t('public.no_groups_created_yet')"
                            :message="$t('public.no_groups_created_yet_caption')"
                        />
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-1 md:grid-cols-2 gap-5 self-stretch w-full"
                    >
                        <div
                            v-for="(group, index) in groups"
                            :key="index"
                            class="flex flex-col items-center self-stretch w-full shadow-toast"
                        >
                            <div
                                class="py-2 px-4 flex items-center gap-3 self-stretch"
                                :style="{'backgroundColor': `#${group.color}`}"
                            >
                                <div class="flex-1 text-white font-semibold">
                                    {{ group.name }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <IconUserFilled size="16" stroke-width="1.25" color="white" />
                                    <div class="text-white text-right text-sm font-medium">
                                        {{ formatAmount(group.member_count, 0) }}
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 flex flex-col items-center gap-2 self-stretch dark:bg-surface-600/20">
                                <div class="min-w-[240px] pb-3 flex items-center gap-3 self-stretch border-b border-solid border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="w-7 h-7 rounded-full overflow-hidden">
                                            <template v-if="group.profile_photo">
                                                <img :src="group.profile_photo" alt="group_leader_profile_photo">
                                            </template>
                                            <template v-else>
                                                <DefaultProfilePhoto />
                                            </template>
                                        </div>
                                        <div class="flex flex-col items-start flex-1">
                                            <div class="max-w-40 self-stretch overflow-hidden whitespace-nowrap text-gray-950 dark:text-white text-ellipsis text-sm font-medium md:max-w-[500px] xl:max-w-3xl">
                                                {{ group.group_leader.name }}
                                            </div>
                                            <div class="max-w-40 self-stretch overflow-hidden whitespace-nowrap text-gray-500 dark:text-gray-500 text-ellipsis text-xs md:max-w-[500px] xl:max-w-3xl">
                                                {{ group.group_leader.email }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="min-w-[240px] pb-2 grid grid-cols-2 gap-2 self-stretch border-b border-solid border-gray-200 dark:border-gray-700">
                                    <div class="w-full flex flex-col items-start gap-1">
                                        <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.total_deposit') }}:</span>
                                        <span class="text-gray-950 dark:text-white text-xs font-medium">$ {{ formatAmount(10000) }}</span>
                                    </div>
                                    <div class="w-full flex flex-col items-start gap-1">
                                        <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.total_withdrawal') }}:</span>
                                        <span class="text-gray-950 dark:text-white text-xs font-medium">$ {{ formatAmount(10000) }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 self-stretch">
                                    <div class="w-full flex items-center gap-1">
                                        <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.deposit') }}:</span>
                                        <span class="text-gray-950 dark:text-white text-xs font-medium">$ {{ formatAmount(10000) }}</span>
                                    </div>
                                    <div class="w-full flex items-center gap-1">
                                        <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.withdrawal') }}:</span>
                                        <span class="text-gray-950 dark:text-white text-xs font-medium">$ {{ formatAmount(10000) }}</span>
                                    </div>
                                    <div class="w-full flex items-center gap-1">
                                        <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.active_fund') }}:</span>
                                        <span class="text-gray-950 dark:text-white text-xs font-medium">$ {{ formatAmount(10000) }}</span>
                                    </div>
                                    <div class="w-full flex items-center gap-1">
                                        <span class="text-gray-500 dark:text-surface-400 text-xs">{{ $t('public.profit') }}:</span>
                                        <span class="text-gray-950 dark:text-white text-xs font-medium">$ {{ formatAmount(10000) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
