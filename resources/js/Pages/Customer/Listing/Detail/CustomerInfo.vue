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
import EditContactInfo from "@/Pages/Customer/Listing/Detail/EditContactInfo.vue";

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
    <Card class="w-full self-stretch relative">
        <template #content>
            <div class="absolute top-3 right-3">
                <EditContactInfo
                    :customerInfo="userDetail"
                />
            </div>
            <div v-if="userDetail" class="flex flex-col gap-5 self-stretch">
                <div class="flex flex-col md:flex-row gap-3 md:gap-5 items-center self-stretch">
                    <div class="w-20 md:w-28 h-20 md:h-28 grow-0 shrink-0 rounded-full overflow-hidden bg-primary-200 dark:bg-surface-800">
                        <div v-if="userDetail">
                            <div v-if="userDetail.profile_photo">
                                <img :src="userDetail.profile_photo" alt="Profile Photo" class="w-full object-cover" />
                            </div>
                            <div v-else class="p-2 md:p-5">
                                <DefaultProfilePhoto />
                            </div>
                        </div>
                        <div v-else class="animate-pulse p-2 md:p-5">
                            <DefaultProfilePhoto />
                        </div>
                    </div>

                    <div
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
                        <div class="flex gap-2 flex-wrap items-center">
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
                            <Tag
                                v-if="userDetail.role !== 'user'"
                                :value="$t(`public.${userDetail.role}`)"
                                severity="secondary"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="flex flex-col justify-center items-center gap-5 self-stretch"
                >
                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- Email -->
                        <div class="flex flex-col justify-center items-start w-full">
                            <div class="flex gap-1 items-center w-full">
                                <span class="text-xs text-surface-500">{{ $t('public.email_address') }}</span>
                                <Tag
                                    class="text-xxs"
                                    :value="$t(`public.${userDetail.email_verified_at ? 'verified' : 'unverified'}`)"
                                    :severity="getSeverity(userDetail.email_verified_at ? 'active' : 'unverified')"
                                />
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium max-w-36 md:max-w-full">
                                {{ userDetail.email }}
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex flex-col justify-center gap-2 items-start w-full">
                            <div class="text-surface-500 text-xs w-full">
                                {{ $t('public.phone_number') }}
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                {{ userDetail.dial_code }} {{ userDetail.phone }}
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
                                <span>{{ userDetail.country.emoji}}</span>
                                <span class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">{{ userDetail.country.name}}</span>
                            </div>
                        </div>

                        <!-- Referer -->
                        <div class="flex flex-col justify-center items-start gap-1 w-full">
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
                    </div>

                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- Rank -->
                        <div class="flex flex-col justify-center items-start gap-2 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                {{ $t('public.rank') }}
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                {{ $t(`public.${userDetail.rank.rank_name}`) }}
                            </div>
                        </div>

                        <!-- Referer -->
                        <div class="flex flex-col justify-center items-start gap-2 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                {{ $t('public.total_referee') }}
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                {{ userDetail.total_direct_member ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading state -->
            <div v-else class="flex flex-col gap-5 self-stretch">
                <div class="flex flex-col md:flex-row gap-3 md:gap-5 items-center self-stretch">
                    <div class="w-20 md:w-28 h-20 md:h-28 grow-0 shrink-0 rounded-full overflow-hidden bg-primary-200 dark:bg-surface-800">
                        <div v-if="userDetail">
                            <div v-if="userDetail.profile_photo">
                                <img :src="userDetail.profile_photo" alt="Profile Photo" class="w-full object-cover" />
                            </div>
                            <div v-else class="p-2 md:p-5">
                                <DefaultProfilePhoto />
                            </div>
                        </div>
                        <div v-else class="animate-pulse p-2 md:p-5">
                            <DefaultProfilePhoto />
                        </div>
                    </div>
                    <div class="flex flex-col gap-5 w-full">
                        <div class="flex flex-col gap-1">
                            <div class="text-lg text-surface-950 dark:text-white">
                                <Skeleton width="10rem" height="1.5rem"></Skeleton>
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
                    </div>
                </div>
                <div class="flex flex-col justify-center items-center gap-5 self-stretch">
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
                                {{ $t('public.referer') }}
                            </div>
                            <Skeleton width="10rem" class="mb-1"></Skeleton>
                        </div>
                    </div>

                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- Country -->
                        <div class="flex flex-col justify-center items-start gap-2 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                {{ $t('public.rank') }}
                            </div>
                            <Skeleton width="10rem" class="mb-1"></Skeleton>
                        </div>

                        <!-- Referral Code -->
                        <div class="flex flex-col justify-center items-start gap-2 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                {{ $t('public.total_referee') }}
                            </div>
                            <Skeleton width="10rem" class="mb-1"></Skeleton>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
