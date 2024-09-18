<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import { sidebarState } from '@/Composables'
import {onMounted, ref, watchEffect} from "vue";
import {usePage} from "@inertiajs/vue3";
import {
    IconLayoutDashboard,
    IconComponents,
    IconUserCircle,
    IconUsersGroup,
    IconReceiptDollar,
    IconId,
    IconCoinMonero,
    IconBusinessplan,
    IconClockDollar,
    IconAward
} from '@tabler/icons-vue';
import SidebarCategoryLabel from "@/Components/Sidebar/SidebarCategoryLabel.vue";

const pendingWithdrawals = ref(0);
const pendingPammAllocate = ref(0);
const pendingBonusWithdrawal = ref(0);

const getPendingCounts = async () => {
    try {
        const response = await axios.get('/getPendingCounts');
        pendingWithdrawals.value = response.data.pendingWithdrawals
        pendingPammAllocate.value = response.data.pendingPammAllocate
        pendingBonusWithdrawal.value = response.data.pendingBonusWithdrawal
    } catch (error) {
        console.error('Error pending counts:', error);
    }
};

onMounted(() => {
    getPendingCounts();
})

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getPendingCounts();
    }
});
</script>

<template>
    <PerfectScrollbar
        tagname="nav"
        aria-label="main"
        class="relative flex flex-col flex-1 max-h-full gap-1 items-center"
        :class="{
            'p-3': sidebarState.isOpen || sidebarState.isHovered,
            'px-5 py-3': !sidebarState.isOpen && !sidebarState.isHovered,
        }"
    >
        <!-- Dashboard -->
        <SidebarLink
            :title="$t('public.dashboard')"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCategoryLabel
            :title="$t('public.customers')"
        />

        <SidebarCategoryLabel
            :title="$t('public.product')"
        />

        <!-- Pending -->
        <SidebarLink
            :title="$t('public.profile')"
            :href="route('profile.edit')"
            :active="route().current('profile.edit')"
        >
            <template #icon>
                <IconClockDollar :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCategoryLabel
            :title="$t('public.order')"
        />

        <!-- Components -->
<!--        <SidebarCollapsible-->
<!--            title="Components"-->
<!--            :active="route().current('components.*')"-->
<!--        >-->
<!--            <template #icon>-->
<!--                <IconComponents :size="20" stroke-width="1.25" />-->
<!--            </template>-->

<!--            <SidebarCollapsibleItem-->
<!--                title="Buttons"-->
<!--                :href="route('components.buttons')"-->
<!--                :active="route().current('components.buttons')"-->
<!--            />-->

<!--            <SidebarCollapsibleItem-->
<!--                title="Member Network"-->
<!--                :href="route('member.network')"-->
<!--                :active="route().current('member.network')"-->
<!--            />-->
<!--        </SidebarCollapsible>-->


        <!-- Profile -->
<!--        <SidebarLink-->
<!--            :title="$t('public.my_profile')"-->
<!--            :href="route('profile')"-->
<!--            :active="route().current('profile')"-->
<!--        >-->
<!--            <template #icon>-->
<!--                <IconUserCircle :size="20" stroke-width="1.25" />-->
<!--            </template>-->
<!--        </SidebarLink>-->

    </PerfectScrollbar>
</template>
