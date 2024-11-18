<script setup>
import Button from "@/Components/Button.vue";
import Dialog from "primevue/dialog";
import {ref, watch} from "vue";
import {useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import InputText from "primevue/inputtext";
import ColorPicker from "primevue/colorpicker";
import Select from "primevue/select";
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import StepItem from 'primevue/stepitem';
import Step from 'primevue/step';
import StepPanel from 'primevue/steppanel';
import {
    IconArrowNarrowRight,
    IconArrowNarrowLeft,
} from "@tabler/icons-vue"
import DataTable from 'primevue/datatable';
import Column from 'primevue/column'
import InputNumber from "primevue/inputnumber";

const visible = ref(false);
const loadingUsers = ref(false);
const loadingRanks = ref(false);
const users = ref([]);
const selectedUser = ref();
const total_group_members = ref(0);
const colorHex = ref('');
const ranks = ref([]);

const getRandomColorHex = () => {
    const letters = '0123456789ABCDEF';
    let color = '';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
};

const openDialog = () => {
    visible.value = true;
    colorHex.value = getRandomColorHex();
    getAvailableLeader();
    getSettingRanks();
}

const getAvailableLeader = async () => {
    loadingUsers.value = true;
    try {
        const response = await axios.get('/getAvailableLeader');
        users.value = response.data;
    } catch (error) {
        console.error('Error fetching users:', error);
    } finally {
        loadingUsers.value = false;
    }
};

const getSettingRanks = async () => {
    loadingRanks.value = true;
    try {
        const response = await axios.get('/getSettingRanks');
        ranks.value = response.data;
        form.rank_settings = response.data.reduce((acc, rank) => {
            acc[rank.id] = { ...rank };
            return acc;
        }, {});
    } catch (error) {
        console.error('Error fetching users:', error);
    } finally {
        loadingRanks.value = false;
    }
};

const onCellEditComplete = (event) => {
    let { data, newValue, field } = event;

    switch (field) {
        case 'lot_rebate_amount':
            data[field] = newValue;
            break;

        case 'min_group_sales':
            data[field] = newValue;
            break;

        default:
            if (newValue.trim().length > 0) data[field] = newValue;
            else event.preventDefault();
            break;
    }

    form.rank_settings[data.id] = { ...data };
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}

watch(selectedUser, () => {
    total_group_members.value = selectedUser.value.total_group_members;
})

const form = useForm({
    name: '',
    color: '',
    leader: '',
    rank_settings: {}
})

const submitForm = () => {
    form.color = colorHex.value;
    form.leader = selectedUser.value;
    form.post(route('group.addGroup'), {
        onSuccess: (values) => {
            closeDialog();
            form.reset();
        }
    })
}

const closeDialog = () => {
    visible.value = false;
    form.reset();
}
</script>

<template>
    <Button
        type="button"
        variant="primary-flat"
        @click="openDialog"
    >
        {{ $t('public.create_group') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_group')"
        class="dialog-xs md:dialog-md"
    >
        <Stepper value="1" linear>
            <StepList>
                <Step value="1">{{ $t('public.basics') }}</Step>
                <Step value="2">{{ $t('public.settings') }}</Step>
            </StepList>
            <StepPanels>
                <StepPanel v-slot="{ activateCallback }" value="1">
                    <div class="flex flex-col gap-6 items-center self-stretch">
                        <div class="flex flex-col gap-3 items-center self-stretch">
                            <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.basics') }}</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                                <div class="flex flex-col items-start gap-1 self-stretch">
                                    <InputLabel
                                        for="name"
                                        :value="$t('public.name')"
                                        :invalid="!!form.errors.name"
                                    />
                                    <InputText
                                        id="name"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.name"
                                        :placeholder="$t('public.group_name_placeholder')"
                                        :invalid="!!form.errors.name"
                                        autofocus
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="flex flex-col items-start gap-1 self-stretch">
                                    <InputLabel
                                        for="color"
                                        :value="$t('public.color')"
                                        :invalid="!!form.errors.color"
                                    />
                                    <div class="flex items-center gap-2 w-full">
                                        <ColorPicker v-model="colorHex" id="color" />
                                        <InputText
                                            id="color"
                                            type="text"
                                            class="block w-full uppercase"
                                            v-model="colorHex"
                                            :placeholder="$t('public.hex_color_placeholder')"
                                            :invalid="!!form.errors.color"
                                            autocomplete="off"
                                        />
                                    </div>

                                    <InputError :message="form.errors.color" />
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 items-center self-stretch">
                            <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.leader') }}</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                                <div class="flex flex-col items-start gap-1 self-stretch">
                                    <InputLabel
                                        for="leader"
                                        :value="$t('public.leader')"
                                        :invalid="!!form.errors.leader"
                                    />
                                    <Select
                                        v-model="selectedUser"
                                        :options="users"
                                        filter
                                        optionLabel="name"
                                        :placeholder="$t('public.select_leader')"
                                        class="w-full"
                                        :loading="loadingUsers"
                                        :invalid="!!form.errors.leader"
                                    >
                                        <template #value="slotProps">
                                            <div v-if="slotProps.value" class="flex items-center">
                                                <div>{{ slotProps.value.name }}</div>
                                            </div>
                                            <span v-else>{{ slotProps.placeholder }}</span>
                                        </template>
                                        <template #option="slotProps">
                                            <div class="flex items-center gap-1 max-w-[220px] truncate">
                                                <span>{{ slotProps.option.name }}</span>
                                                <span class="text-xs text-gray-500">@{{ slotProps.option.username }}</span>
                                            </div>
                                        </template>
                                    </Select>
                                    <InputError :message="form.errors.leader" />
                                </div>

                                <div class="flex flex-col items-start gap-1 self-stretch">
                                    <InputLabel
                                        for="total_group_members"
                                    >
                                        {{ $t('public.total_group_members') }}
                                    </InputLabel>
                                    <InputText
                                        id="name"
                                        type="text"
                                        class="block w-full"
                                        disabled
                                        v-model="total_group_members"
                                        :placeholder="$t('public.total_group_members')"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex pt-6 justify-end">
                        <Button
                            type="button"
                            variant="gray-tonal"
                            :disabled="form.processing"
                            @click="activateCallback('2')"
                        >
                            {{ $t('public.next') }}
                            <IconArrowNarrowRight size="20" stroke-witdth="1.5" />
                        </Button>
                    </div>
                </StepPanel>
                <StepPanel v-slot="{ activateCallback }" value="2">
                    <DataTable
                        :value="ranks"
                        editMode="cell"
                        @cell-edit-complete="onCellEditComplete"
                    >
                        <Column field="rank_name" :header="$t('public.rank')"></Column>
                        <Column
                            field="lot_rebate_amount"
                            :header="$t('public.rebate')"
                        >
                            <template #body="{ data, field }">
                                {{ formatCurrency(data[field]) }}
                            </template>
                            <template #editor="{ data, field }">
                                <InputNumber
                                    v-model="data[field]"
                                    autofocus
                                    fluid
                                    class="max-w-[100px]"
                                    mode="currency"
                                    currency="USD"
                                    size="sm"
                                />
                            </template>
                        </Column>
                        <Column
                            field="min_group_sales"
                            :header="$t('public.group_sales')"
                        >
                            <template #body="{ data, field }">
                                {{ formatCurrency(data[field]) }}
                            </template>
                            <template #editor="{ data, field }">
                                <InputNumber
                                    v-model="data[field]"
                                    autofocus
                                    fluid
                                    class="max-w-[150px]"
                                    mode="currency"
                                    currency="USD"
                                />
                            </template>
                        </Column>
                    </DataTable>
                    <div class="text-xs mt-1.5 text-gray-500 dark:text-gray-400">
                        {{ $t('public.click_to_edit_note') }}
                    </div>
                    <div class="pt-8 flex justify-between items-center self-stretch w-full">
                        <Button
                            type="button"
                            variant="gray-tonal"
                            :disabled="form.processing"
                            @click="activateCallback('1')"
                        >
                            <IconArrowNarrowLeft size="20" stroke-witdth="1.5" />
                            {{ $t('public.back') }}
                        </Button>

                        <Button
                            variant="primary-flat"
                            @click="submitForm"
                            :disabled="form.processing"
                            class="w-full md:w-auto"
                        >
                            {{ $t('public.create') }}
                        </Button>
                    </div>
                </StepPanel>
            </StepPanels>
        </Stepper>


<!--        <form class="flex flex-col gap-6 items-center self-stretch">-->

<!--            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">-->
<!--                <Button-->
<!--                    type="button"-->
<!--                    variant="gray-tonal"-->
<!--                    @click="closeDialog"-->
<!--                    :disabled="form.processing"-->
<!--                    class="w-full md:w-auto"-->
<!--                >-->
<!--                    {{ $t('public.cancel') }}-->
<!--                </Button>-->
<!--                <Button-->
<!--                    variant="primary-flat"-->
<!--                    @click="submitForm"-->
<!--                    :disabled="form.processing"-->
<!--                    class="w-full md:w-auto"-->
<!--                >-->
<!--                    {{ $t('public.create') }}-->
<!--                </Button>-->
<!--            </div>-->
<!--        </form>-->
    </Dialog>
</template>
