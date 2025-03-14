<script setup>
import Button from "@/Components/Button.vue";
import TieredMenu from "primevue/tieredmenu";
import {
    IconDotsVertical,
    IconListSearch,
    IconPencilMinus,
} from "@tabler/icons-vue";
import {h, ref} from "vue";
import Dialog from "primevue/dialog";
import InvestmentReport from "@/Pages/Master/MasterReport/InvestmentReport.vue";

const props = defineProps({
    master: Object
});

const menu = ref();
const visible = ref(false)
const dialogType = ref('')

const items = ref([
    {
        label: 'investment_report',
        icon: h(IconListSearch),
        command: () => {
            visible.value = true;
            dialogType.value = 'investment_report';
        },
    },
    {
        label: 'edit',
        icon: h(IconPencilMinus),
        command: () => {
            visible.value = true;
            dialogType.value = 'edit';
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
                <component :is="item.icon" size="20" stroke-width="1.5" />
                <span class="font-medium">{{ $t(`public.${item.label}`) }}</span>
            </div>
        </template>
    </TieredMenu>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t(`public.${dialogType}`)"
        :class="{
            'dialog-xs md:dialog-md': dialogType !== 'investment_report',
            'dialog-xs md:dialog-lg': dialogType === 'investment_report'
        }"
    >
        <template v-if="dialogType === 'investment_report'">
            <InvestmentReport
                :master="master"
                @update:visible="visible = false"
            />
        </template>
    </Dialog>
</template>
