<script setup>
import Button from "@/Components/Button.vue";
import {IconArrowNarrowLeft, IconArrowNarrowRight, IconPlus} from "@tabler/icons-vue";
import {ref} from "vue";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import Step from 'primevue/step';
import StepPanel from 'primevue/steppanel';
import {useForm, usePage} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import MultiSelect from 'primevue/multiselect';
import ManagementFeeSetting from "@/Pages/Master/Partials/ManagementFeeSetting.vue";

const visible = ref(false);
const activeStep = ref(1);
const totalSteps = 3;
const loadingUsers = ref(false);
const users = ref([]);
const selectedUser = ref();
const managementFee = ref();

const openDialog = () => {
    visible.value = true;
    getUsers();
    getGroupLeaders();
}

const getUsers = async () => {
    loadingUsers.value = true;
    try {
        const response = await axios.get('/getUsers');
        users.value = response.data;
    } catch (error) {
        console.error('Error fetching users:', error);
    } finally {
        loadingUsers.value = false;
    }
};

const initialFormState = {
    step: '',
    master_name: '',
    owner: '',
    type: '',
    estimated_monthly_return: '',
    estimated_lot_size: '',
    max_drawdown: '',
    min_investment: null,
    sharing_profit: '',
    investment_period: null,
    investment_period_type: null,
    settlement_period: null,
    settlement_period_type: null,
    visible_to: null,
    management_fee: '',
};

const form = useForm({ ...initialFormState });

const resetForm = () => {
    Object.keys(initialFormState).forEach(key => {
        form[key] = initialFormState[key];
    });
};

const handleContinue = () => {
    form.step = activeStep.value;
    form.owner = selectedUser.value;
    form.type = selectedType.value;
    form.investment_period_type = selectedInvestmentPeriods.value;
    form.settlement_period_type = selectedSettlementPeriod.value;
    form.visible_to = selectedVisibility.value;
    form.management_fee = managementFee.value;
    form.post(route('master.addMaster'), {
        onSuccess: () => {
            if (activeStep.value === totalSteps) {
                closeDialog();
            } else {
                activeStep.value += 1;
            }
        }
    });
};

const closeDialog = () => {
    visible.value = false;
    resetForm();
    activeStep.value = 1;
};

const selectedType = ref('esg');
const masterTypes = ref([
    {name: 'esg', value: 'esg' },
    {name: 'standard',  value: 'standard' },
]);

const selectedInvestmentPeriods = ref();
const selectedSettlementPeriod = ref();
const investmentPeriods = ref([
    {name: 'day', value: 'day' },
    {name: 'month',  value: 'month' },
    {name: 'year',  value: 'year' },
]);

const groupLeaders = ref([]);
const selectedVisibility = ref();

const getGroupLeaders = async () => {
    loadingUsers.value = true;
    try {
        const response = await axios.get('/getGroupLeaders');
        groupLeaders.value = response.data;
    } catch (error) {
        console.error('Error fetching groupLeaders:', error);
    } finally {
        loadingUsers.value = false;
    }
};
</script>

<template>
    <Button
        type="button"
        variant="primary-flat"
        class="w-full md:w-auto"
        @click="openDialog"
    >
        <IconPlus size="16" />
        {{ $t('public.add_master') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_master')"
        class="dialog-xs md:dialog-md"
    >
        <form @submit.prevent>
            <Stepper v-model:value="activeStep" linear>
                <StepList>
                    <Step :value="1">{{ $t('public.profile') }}</Step>
                    <Step :value="2">{{ $t('public.settings') }}</Step>
                    <Step :value="3">{{ $t('public.fee') }}</Step>
                </StepList>
                <StepPanels>
                    <StepPanel :value="1">
                        <div class="flex flex-col gap-6 items-center self-stretch">
                            <div class="flex flex-col gap-3 items-center self-stretch">
                                <span class="font-bold text-sm text-surface-950 dark:text-white w-full text-left">{{ $t('public.basics') }}</span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                                    <div class="flex flex-col items-start gap-1 self-stretch">
                                        <InputLabel
                                            for="master_name"
                                            :value="$t('public.master_name')"
                                            :invalid="!!form.errors.master_name"
                                        />
                                        <InputText
                                            id="master_name"
                                            type="text"
                                            class="block w-full"
                                            v-model="form.master_name"
                                            :placeholder="$t('public.master_name_placeholder')"
                                            :invalid="!!form.errors.master_name"
                                            autofocus
                                        />
                                        <InputError :message="form.errors.master_name" />
                                    </div>

                                    <div class="flex flex-col items-start gap-1 self-stretch">
                                        <InputLabel
                                            for="owner"
                                            :value="$t('public.owner')"
                                            :invalid="!!form.errors.owner"
                                        />
                                        <Select
                                            v-model="selectedUser"
                                            :options="users"
                                            filter
                                            optionLabel="name"
                                            :placeholder="$t('public.select_owner')"
                                            class="w-full"
                                            :loading="loadingUsers"
                                            :invalid="!!form.errors.owner"
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
                                            for="type"
                                            :value="$t('public.type')"
                                            :invalid="!!form.errors.type"
                                        />
                                        <Select
                                            v-model="selectedType"
                                            :options="masterTypes"
                                            optionLabel="name"
                                            optionValue="value"
                                            :placeholder="$t('public.select_type')"
                                            class="w-full"
                                        >
                                            <template #value="slotProps">
                                                <div v-if="slotProps.value" class="flex items-center">
                                                    <div>{{ $t(`public.${slotProps.value}`) }}</div>
                                                </div>
                                                <span v-else>{{ slotProps.placeholder }}</span>
                                            </template>
                                            <template #option="slotProps">
                                                <div class="flex items-center gap-1 max-w-[220px] truncate">
                                                    <span>{{ $t(`public.${slotProps.option.name}`) }}</span>
                                                </div>
                                            </template>
                                        </Select>
                                        <InputError :message="form.errors.type" />
                                    </div>

                                    <div class="flex flex-col items-start gap-1 self-stretch">
                                        <InputLabel
                                            for="estimated_monthly_return"
                                            :invalid="!!form.errors.estimated_monthly_return"
                                        >
                                            {{ $t('public.estimated_monthly_return') }}
                                        </InputLabel>
                                        <InputText
                                            id="estimated_monthly_return"
                                            type="text"
                                            class="block w-full"
                                            v-model="form.estimated_monthly_return"
                                            :placeholder="$t('public.estimated_monthly_return_placeholder')"
                                            :invalid="!!form.errors.estimated_monthly_return"
                                            autofocus
                                        />
                                        <InputError :message="form.errors.estimated_monthly_return" />
                                    </div>

                                    <div class="flex flex-col items-start gap-1 self-stretch">
                                        <InputLabel
                                            for="estimated_lot_size"
                                            :invalid="!!form.errors.estimated_lot_size"
                                        >
                                            {{ $t('public.estimated_lot_size') }}
                                        </InputLabel>
                                        <InputText
                                            id="estimated_lot_size"
                                            type="text"
                                            class="block w-full"
                                            v-model="form.estimated_lot_size"
                                            :placeholder="$t('public.estimated_lot_size_placeholder')"
                                            :invalid="!!form.errors.estimated_lot_size"
                                            autofocus
                                        />
                                        <InputError :message="form.errors.estimated_lot_size" />
                                    </div>

                                    <div class="flex flex-col items-start gap-1 self-stretch">
                                        <InputLabel
                                            for="max_drawdown"
                                            :invalid="!!form.errors.max_drawdown"
                                        >
                                            {{ $t('public.max_drawdown') }}
                                        </InputLabel>
                                        <InputText
                                            id="max_drawdown"
                                            type="text"
                                            class="block w-full"
                                            v-model="form.max_drawdown"
                                            :placeholder="$t('public.max_drawdown_placeholder')"
                                            :invalid="!!form.errors.max_drawdown"
                                            autofocus
                                        />
                                        <InputError :message="form.errors.max_drawdown" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 items-center self-stretch">
                                <span class="font-bold text-sm text-surface-950 dark:text-white w-full text-left">{{ $t('public.upload_image') }}</span>
                                <div class="flex flex-col items-start gap-3 self-stretch">
                                    <span class="text-xs text-gray-500">{{ $t('public.upload_image_caption') }}</span>
                                    <div class="flex flex-col gap-3">
                                        <Button
                                            type="button"
                                            variant="primary-tonal"
                                        >
                                            {{ $t('public.browse') }}
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex pt-6 justify-end">
                            <Button
                                type="button"
                                variant="gray-tonal"
                                @click="handleContinue"
                                :disabled="form.processing"
                            >
                                {{ $t('public.next') }}
                                <IconArrowNarrowRight size="20" stroke-witdth="1.5" />
                            </Button>
                        </div>
                    </StepPanel>

                    <!-- Step 2 -->
                    <StepPanel :value="2">
                        <div class="flex flex-col items-center gap-8 self-stretch">
                            <div class="flex flex-col items-center gap-3 self-stretch">
                                <span class="self-stretch text-surface-950 dark:text-white text-sm font-bold">{{ $t('public.joining_conditions') }}</span>
                                <div class="w-full grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                                        <InputLabel
                                            for="min_investment"
                                            :value="$t('public.min_investment')"
                                            :invalid="!!form.errors.min_investment"
                                        />
                                        <InputNumber
                                            v-model="form.min_investment"
                                            inputId="min_investment"
                                            class="w-full"
                                            :min="0"
                                            :step="100"
                                            fluid
                                            autofocus
                                            mode="currency"
                                            currency="USD"
                                            locale="en-US"
                                            :invalid="!!form.errors.min_investment"
                                        />
                                        <InputError :message="form.errors.min_investment" />
                                    </div>
                                    <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                                        <InputLabel
                                            for="sharing_profit"
                                            :value="$t('public.sharing_profit')"
                                            :invalid="!!form.errors.sharing_profit"
                                        />
                                        <InputText
                                            id="sharing_profit"
                                            type="number"
                                            class="block w-full"
                                            v-model="form.sharing_profit"
                                            :invalid="!!form.errors.sharing_profit"
                                            placeholder="0.00%"
                                        />
                                        <InputError :message="form.errors.sharing_profit" />
                                    </div>
                                    <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                                        <InputLabel
                                            for="investment_period"
                                            :invalid="!!form.errors.investment_period"
                                        >
                                            {{ $t('public.investment_period') }}
                                        </InputLabel>
                                        <div class="flex gap-2 items-center self-stretch">
                                            <InputNumber
                                                v-model="form.investment_period"
                                                inputId="investment_period"
                                                fluid
                                                :min="0"
                                                placeholder="0"
                                                :invalid="!!form.errors.investment_period"
                                            />
                                            <Select
                                                v-model="selectedInvestmentPeriods"
                                                :options="investmentPeriods"
                                                optionLabel="name"
                                                optionValue="value"
                                                :placeholder="$t('public.select_type')"
                                                class="w-full max-w-32"
                                                :invalid="!!form.errors.investment_period_type"
                                            >
                                                <template #value="slotProps">
                                                    <div v-if="slotProps.value" class="flex items-center">
                                                        <div>{{ $t(`public.${slotProps.value}`) }}</div>
                                                    </div>
                                                    <span v-else>{{ slotProps.placeholder }}</span>
                                                </template>
                                                <template #option="slotProps">
                                                    <div class="flex items-center gap-1 max-w-[220px] truncate">
                                                        <span>{{ $t(`public.${slotProps.option.name}`) }}</span>
                                                    </div>
                                                </template>
                                            </Select>
                                        </div>
                                        <InputError :message="form.errors.investment_period" />
                                    </div>

                                    <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                                        <InputLabel
                                            for="settlement_period"
                                            :value="$t('public.settlement_period')"
                                            :invalid="!!form.errors.settlement_period"
                                        />
                                        <div class="flex gap-2 items-center self-stretch">
                                            <InputNumber
                                                v-model="form.settlement_period"
                                                inputId="settlement_period"
                                                fluid
                                                :min="0"
                                                placeholder="0"
                                                :invalid="!!form.errors.settlement_period"
                                            />
                                            <Select
                                                v-model="selectedSettlementPeriod"
                                                :options="investmentPeriods"
                                                optionLabel="name"
                                                optionValue="value"
                                                :placeholder="$t('public.select_type')"
                                                class="w-full max-w-32"
                                                :invalid="!!form.errors.settlement_period_type"
                                            >
                                                <template #value="slotProps">
                                                    <div v-if="slotProps.value" class="flex items-center">
                                                        <div>{{ $t(`public.${slotProps.value}`) }}</div>
                                                    </div>
                                                    <span v-else>{{ slotProps.placeholder }}</span>
                                                </template>
                                                <template #option="slotProps">
                                                    <div class="flex items-center gap-1 max-w-[220px] truncate">
                                                        <span>{{ $t(`public.${slotProps.option.name}`) }}</span>
                                                    </div>
                                                </template>
                                            </Select>
                                        </div>
                                        <InputError :message="form.errors.settlement_period" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-center gap-3 self-stretch">
                                <div class="flex flex-col justify-center items-start gap-1 self-stretch">
                                    <span class="self-stretch text-surface-950 dark:text-white text-sm font-bold">{{ $t('public.master_settings') }}</span>
                                    <span class="self-stretch text-gray-500 text-xs">{{ $t('public.profit_information_message') }}</span>
                                </div>
                                <div class="w-full grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                                        <InputLabel
                                            for="visible_to"
                                            :value="$t('public.visible_to')"
                                            :invalid="!!form.errors.visible_to"
                                        />
                                        <MultiSelect
                                            v-model="selectedVisibility"
                                            :options="groupLeaders"
                                            optionLabel="name"
                                            filter
                                            :placeholder="$t('public.select_groups')"
                                            :maxSelectedLabels="3"
                                            class="w-full"
                                            :invalid="!!form.errors.visible_to"
                                        />
                                        <InputError :message="form.errors.visible_to" />
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="pt-6 flex justify-between items-center self-stretch w-full">
                            <Button
                                type="button"
                                variant="gray-tonal"
                                :disabled="form.processing"
                                @click="activeStep = 1"
                            >
                                <IconArrowNarrowLeft size="20" stroke-witdth="1.5" />
                                {{ $t('public.back') }}
                            </Button>

                            <Button
                                type="button"
                                variant="gray-tonal"
                                @click="handleContinue"
                                :disabled="form.processing"
                            >
                                {{ $t('public.next') }}
                                <IconArrowNarrowRight size="20" stroke-witdth="1.5" />
                            </Button>
                        </div>
                    </StepPanel>

                    <!-- Step 3 -->
                    <StepPanel :value="3">
                        <div class="flex flex-col items-center gap-3 self-stretch">
                            <span class="self-stretch text-surface-950 dark:text-white text-sm font-bold">{{ $t('public.management_fee') }}</span>

                            <ManagementFeeSetting
                                @get:management_fee="managementFee = $event"
                            />
                        </div>

                        <div class="pt-6 flex justify-between items-center self-stretch w-full">
                            <Button
                                type="button"
                                variant="gray-tonal"
                                :disabled="form.processing"
                                @click="activeStep = 1"
                            >
                                <IconArrowNarrowLeft size="20" stroke-witdth="1.5" />
                                {{ $t('public.back') }}
                            </Button>

                            <Button
                                variant="primary-flat"
                                @click="handleContinue"
                                :disabled="form.processing"
                                class="w-full md:w-auto"
                            >
                                {{ $t('public.create') }}
                            </Button>
                        </div>
                    </StepPanel>
                </StepPanels>
            </Stepper>
        </form>
    </Dialog>
</template>
