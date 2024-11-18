<script setup>
import Tabs from "primevue/tabs";
import TabPanel from "primevue/tabpanel";
import Tab from "primevue/tab";
import TabPanels from "primevue/tabpanels";
import TabList from "primevue/tablist";
import {h, ref} from "vue";
import JoiningReport from "@/Pages/Master/MasterReport/JoiningReport.vue";

const props = defineProps({
    master: Object
})

const tabs = ref([
    {
        title: 'joining',
        value: '0',
        count: props.master.ongoing_subscriptions_count,
        component: h(JoiningReport, {master: props.master})
    },
    {
        title: 'revoked',
        value: '1',
        count: 0
    },
]);
</script>

<template>
    <Tabs value="0">
        <TabList>
            <Tab v-for="tab in tabs" :key="tab.title" :value="tab.value">
                {{ $t(`public.${tab.title}`) }} ({{ tab.count }})
            </Tab>
        </TabList>
        <TabPanels>
            <TabPanel v-for="tab in tabs" :key="tab.content" :value="tab.value">
                <component :is="tab.component" />
            </TabPanel>
        </TabPanels>
    </Tabs>
</template>
