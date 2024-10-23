<script setup>
import {h, ref} from "vue";
import {
    IconDotsVertical,
    IconId,
    IconUserUp,
    IconTrash,
    IconDeviceLaptop
} from "@tabler/icons-vue";
import TieredMenu from "primevue/tieredmenu";
import Button from "@/Components/Button.vue";

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
            </div>
        </template>
    </TieredMenu>
</template>
