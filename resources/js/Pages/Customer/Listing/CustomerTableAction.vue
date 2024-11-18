<script setup>
import {h, ref} from "vue";
import {
    IconDotsVertical,
    IconId,
    IconUserUp,
    IconTrash,
    IconDeviceLaptop,
    IconChevronRight,
    IconUserCog,
    IconUserDollar,
} from "@tabler/icons-vue";
import TieredMenu from "primevue/tieredmenu";
import Dialog from "primevue/dialog";
import Button from "@/Components/Button.vue";
import UpgradeRank from "@/Pages/Customer/Listing/Partials/UpgradeRank.vue";
import UpgradeRole from "@/Pages/Customer/Listing/Partials/UpgradeRole.vue";

const props = defineProps({
    customer: Object,
})

const menu = ref();
const visible = ref(false);
const dialogType = ref('');

const items = ref([
    {
        label: 'customer_details',
        icon: h(IconId),
        command: () => {
            window.location.href = `/customer/detail/${props.customer.id_number}`;
        },
    },
    {
        label: 'access_portal',
        icon: h(IconDeviceLaptop),
        command: () => {
            window.open(route('customer.access_portal', props.customer.id), '_blank');
        },
    },
    {
        label: 'upgrade',
        icon: h(IconUserUp),
        items: [
            {
                label: 'rank',
                icon: h(IconUserCog),
                command: () => {
                    visible.value = true;
                    dialogType.value = 'upgrade_rank';
                },
            },
            {
                label: 'role',
                icon: h(IconUserDollar),
                command: () => {
                    visible.value = true;
                    dialogType.value = 'upgrade_role';
                },
            }
        ]
    },
    {
        separator: true,
    },
    {
        label: 'delete_member',
        icon: h(IconTrash),
        command: () => {
            // requireConfirmation('delete_member')
        },
    },
]);

const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <Button
        variant="gray-text"
        size="sm"
        type="button"
        iconOnly
        pill
        @click="toggle"
        aria-haspopup="true"
        aria-controls="overlay_tmenu"
    >
        <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
    </Button>
    <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup>
        <template #item="{ item, props, hasSubmenu }">
            <div
                class="flex items-center gap-3 self-stretch"
                v-bind="props.action"
            >
                <component :is="item.icon" size="20" stroke-width="1.25" :color="item.label === 'delete_member' ? '#F04438' : '#667085'" />
                <span class="font-medium" :class="{'text-error-500': item.label === 'delete_member'}">{{ $t(`public.${item.label}`) }}</span>
                <span v-if="hasSubmenu" class="ml-auto">
                        <IconChevronRight size="20" stroke-width="1.25" />
                    </span>
            </div>
        </template>
    </TieredMenu>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t(`public.${dialogType}`)"
        class="dialog-xs sm:dialog-sm"
    >
        <template v-if="dialogType === 'upgrade_rank'">
            <UpgradeRank
                :customer="customer"
                @update:visible="visible = false"
            />
        </template>

        <template v-if="dialogType === 'upgrade_role'">
            <UpgradeRole
                :customer="customer"
                @update:visible="visible = false"
            />
        </template>
    </Dialog>
</template>
