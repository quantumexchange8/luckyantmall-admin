<script setup>
import Card from "primevue/card";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import Button from "@/Components/Button.vue";
import {
    IconEdit
} from "@tabler/icons-vue"
import Divider from "primevue/divider";
import Tag from "primevue/tag";
import Skeleton from "primevue/skeleton";
import {generalFormat} from "@/Composables/format.js";

const props = defineProps({
    userDetail: Object,
})

const {formatRgbaColor} = generalFormat();

const getSeverity = (status) => {
    switch (status) {
        case 'unverified':
            return 'danger';

        case 'active':
            return 'success';

        case 'pending':
            return 'info';

        case 'renewal':
            return null;
    }
}
</script>

<template>
    <Card class="w-full">
        <template #content>
            <div class="flex flex-col gap-5 self-stretch">
                <div class="flex gap-8 items-center self-stretch">
                    <div class="w-40 h-40 grow-0 shrink-0 rounded overflow-hidden bg-primary-200 dark:bg-surface-800">
                        <div v-if="userDetail">
                            <div v-if="userDetail.profile_photo">
                                <img :src="userDetail.profile_photo" alt="Profile Photo" class="w-full object-cover" />
                            </div>
                            <div v-else class="p-6">
                                <DefaultProfilePhoto />
                            </div>
                        </div>
                        <div v-else class="animate-pulse p-2">
                            <DefaultProfilePhoto />
                        </div>
                    </div>

                    <div
                        v-if="userDetail"
                        class="flex flex-col gap-5 w-full"
                    >
                        <div class="flex flex-col gap-1">
                            <div class="text-lg text-surface-950 dark:text-white">
                                {{ userDetail.name }}
                            </div>

                            <div class="flex items-center self-stretch text-sm text-surface-500 dark:text-surface-500">
                                <span>{{ userDetail.id_number }}</span>
                                <Divider layout="vertical" />
                                <span>{{ userDetail.username }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2 items-center">
                            <Tag
                                :value="$t(`public.${userDetail.status}`)"
                                :severity="getSeverity(userDetail.status)"
                            />
                            <div class="flex items-center">
                                <Tag
                                    v-if="userDetail.group"
                                    class="flex items-center gap-2 rounded"
                                    :style="{ background: formatRgbaColor(userDetail.group.group.color, 0.1) }"
                                >
                                    <div
                                        class="w-1.5 h-1.5 grow-0 shrink-0 rounded-full"
                                        :style="{ backgroundColor: `#${userDetail.group.group.color}` }"
                                    ></div>
                                    <div
                                        class="text-xs font-semibold"
                                        :style="{ color: `#${userDetail.group.group.color}` }"
                                    >
                                        {{ userDetail.group.group.name }}
                                    </div>
                                </Tag>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-5 w-full">
                            <div class="flex flex-col justify-center items-start gap-2">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.referer') }}
                                </div>
                                <div class="flex items-center gap-2 w-full">
                                    <div class="w-6 h-6 grow-0 shrink-0 rounded-full overflow-hidden">
                                        <div v-if="userDetail.upline_profile_photo">
                                            <img :src="userDetail.upline_profile_photo" alt="Profile Photo" />
                                        </div>
                                        <div v-else>
                                            <DefaultProfilePhoto />
                                        </div>
                                    </div>
                                    <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                        {{ userDetail.upline.name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-2">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.total_referee') }}
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ userDetail.total_direct_member ?? '-' }}
                                </div>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-2">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.referee_active_fund') }}
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ userDetail.total_direct_agent ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading state -->
                    <div v-else class="flex flex-col gap-5 w-full">
                        <div class="flex flex-col gap-1">
                            <div class="text-lg text-surface-950 dark:text-white">
                                <Skeleton width="10rem" height="2rem" class="mb-0.5"></Skeleton>
                            </div>

                            <div class="flex items-center self-stretch text-sm text-surface-500 dark:text-surface-500">
                                <Skeleton width="10rem"></Skeleton>
                                <Divider layout="vertical" />
                                <Skeleton width="10rem"></Skeleton>
                            </div>
                        </div>
                        <div class="flex gap-2 items-center">
                            <Skeleton width="10rem" height="1.25rem"></Skeleton>
                            <Skeleton width="10rem" height="1.25rem"></Skeleton>
                        </div>

                        <div class="grid grid-cols-3 gap-5 w-full">
                            <div class="flex flex-col justify-center items-start gap-2">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.referer') }}
                                </div>
                                <div class="flex items-center gap-2 w-full">
                                    <div class="w-6 h-6 grow-0 shrink-0 rounded-full overflow-hidden">
                                        <DefaultProfilePhoto />
                                    </div>
                                    <Skeleton width="10rem"></Skeleton>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-2">
                                <div class="text-surface-500 text-xs w-full truncate">{{ $t('public.total_referee') }}</div>
                                <Skeleton width="10rem"></Skeleton>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-2">
                                <div class="text-surface-500 text-xs w-full truncate">{{ $t('public.total_group_sales') }}</div>
                                <Skeleton width="10rem"></Skeleton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
