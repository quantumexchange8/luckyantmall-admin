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
    IconUsers,
    IconUserCircle,
    IconUsersGroup,
    IconTemplate,
    IconCategory,
    IconCoinMonero,
    IconHistory,
    IconClockDollar,
    IconAdjustmentsDollar,
    IconTag
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
            :title="$t('public.customer')"
        />

        <!-- Customer -->
        <SidebarCollapsible
            :title="$t('public.customer')"
            :active="route().current('customer.*')"
        >
            <template #icon>
                <IconUsers :size="20" stroke-width="1.5" />
            </template>

            <SidebarCollapsibleItem
                :title="$t('public.customer_listing')"
                :href="route('customer.listing')"
                :active="route().current('customer.listing') || route().current('customer.detail')"
            />

<!--            <SidebarCollapsibleItem-->
<!--                :title="$t('public.member_network')"-->
<!--                :href="route('member.network')"-->
<!--                :active="route().current('member.network')"-->
<!--            />-->

<!--            <SidebarCollapsibleItem-->
<!--                :title="$t('public.account_listing')"-->
<!--                :href="route('member.account_listing')"-->
<!--                :active="route().current('member.account_listing')"-->
<!--            />-->

        </SidebarCollapsible>

        <!-- Group -->
        <SidebarLink
            :title="$t('public.group')"
            :href="route('group')"
            :active="route().current('group')"
        >
            <template #icon>
                <IconUsersGroup :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCategoryLabel
            :title="$t('public.transaction')"
        />

        <!-- Pending -->
        <SidebarCollapsible
            :title="$t('public.pending')"
            :active="route().current('transaction.pending.*')"
        >
            <template #icon>
                <IconClockDollar :size="20" stroke-width="1.5" />
            </template>

            <SidebarCollapsibleItem
                :title="$t('public.deposit')"
                :href="route('transaction.pending.pending_deposit')"
                :active="route().current('transaction.pending.pending_deposit')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.withdrawal')"
                :href="route('customer.listing')"
                :active="route().current('customer.listing')"
            />

        </SidebarCollapsible>

        <!-- History -->
        <SidebarCollapsible
            :title="$t('public.history')"
            :active="route().current('transaction.history.*')"
        >
            <template #icon>
                <IconHistory :size="20" stroke-width="1.5" />
            </template>

            <SidebarCollapsibleItem
                :title="$t('public.deposit')"
                :href="route('transaction.history.deposit_history')"
                :active="route().current('transaction.history.deposit_history')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.withdrawal')"
                :href="route('customer.listing')"
                :active="route().current('customer.listing')"
            />

        </SidebarCollapsible>

        <SidebarCategoryLabel
            :title="$t('public.market')"
        />

        <!-- Product -->
        <SidebarCollapsible
            :title="$t('public.product')"
            :active="route().current('product') || route().current('product.*')"
        >
            <template #icon>
                <IconTag :size="20" stroke-width="1.5" />
            </template>

            <SidebarCollapsibleItem
                :title="$t('public.add_product')"
                :href="route('product.add_product')"
                :active="route().current('product.add_product')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.product_listing')"
                :href="route('product')"
                :active="route().current('product')"
            />

        </SidebarCollapsible>

        <!-- Master -->
        <SidebarLink
            :title="$t('public.master')"
            :href="route('master')"
            :active="route().current('master') || route().current('master.*')"
        >
            <template #icon>
                <IconCoinMonero :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCategoryLabel
            :title="$t('public.settings')"
        />

        <!-- Item -->
        <SidebarLink
            :title="$t('public.item')"
            :href="route('item.listing')"
            :active="route().current('item.listing')"
        >
            <template #icon>
                <IconTemplate :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Category -->
        <SidebarLink
            :title="$t('public.category')"
            :href="route('category.listing')"
            :active="route().current('category.listing')"
        >
            <template #icon>
                <IconCategory :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Deposit Profile -->
        <SidebarLink
            :title="$t('public.deposit_profile')"
            :href="route('deposit_profile')"
            :active="route().current('deposit_profile')"
        >
            <template #icon>
                <IconAdjustmentsDollar :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

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
