<script setup>
import Button from "@/Components/Button.vue"
import {
    IconEdit
} from "@tabler/icons-vue"
import Dialog from "primevue/dialog";
import {ref, watch} from "vue";
import {useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";

const props = defineProps({
    customerInfo: Object
})

const visible = ref(false);
const countries = ref([]);
const selectedCountry = ref();
const selectedPhoneCode = ref();
const loadingCountries = ref(false);

const openDialog = () => {
    visible.value = true;
    getCountries();
}

const getCountries = async () => {
    loadingCountries.value = true;
    try {
        const response = await axios.get('/getCountries');
        countries.value = response.data;
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingCountries.value = false;
    }
};

const form = useForm({
    user_id: '',
    name: '',
    username: '',
    country: '',
    dial_code: '',
    phone: '',
    phone_number: '',
});

watch(() => props.customerInfo, (value) => {
    form.user_id = value.id;
    form.name = value.name;
    form.username = value.username;
    form.phone = value.phone;
});

watch(countries, () => {
    selectedCountry.value = countries.value.find(country => country.id === props.customerInfo.country_id);
    selectedPhoneCode.value = countries.value.find(country => country.phone_code === props.customerInfo.dial_code);
});

const submitForm = () => {
    form.country = selectedCountry.value;
    form.dial_code = selectedPhoneCode.value;

    if (selectedPhoneCode.value) {
        form.phone_number = selectedPhoneCode.value.phone_code + form.phone;
    }

    form.post(route('customer.detail.updateCustomerProfile'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
        }
    })
}

const closeDialog = () => {
    visible.value = false;
}
</script>

<template>
    <Button
        type="button"
        variant="primary-text"
        icon-only
        pill
        size="sm"
        @click="openDialog"
        :disabled="!customerInfo"
    >
        <IconEdit size="20" stroke-width="1.5" />
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.contact_information')"
        class="dialog-xs md:dialog-md"
    >
        <form class="flex flex-col gap-5">
            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full"
            >
                <!-- Name -->
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel
                        for="name"
                        :value="$t('public.full_name')"
                        :invalid="!!form.errors.name"
                    />
                    <InputText
                        id="name"
                        type="text"
                        class="block w-full"
                        v-model="form.name"
                        :placeholder="$t('public.name')"
                        :invalid="!!form.errors.name"
                        autofocus
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Username -->
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel
                        for="username"
                        :value="$t('public.username')"
                        :invalid="!!form.errors.username"
                    />
                    <InputText
                        id="username"
                        type="text"
                        class="block w-full"
                        v-model="form.username"
                        :placeholder="$t('public.username')"
                        :invalid="!!form.errors.username"
                        autofocus
                    />
                    <InputError :message="form.errors.username" />
                </div>

                <!-- Country -->
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel
                        for="country"
                        :value="$t('public.country')"
                        :invalid="!!form.errors.country"
                    />
                    <Select
                        v-model="selectedCountry"
                        :options="countries"
                        filter
                        optionLabel="name"
                        :placeholder="$t('public.select_country')"
                        class="w-full"
                        :loading="loadingCountries"
                        :invalid="!!form.errors.country"
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center">
                                <div>{{ slotProps.value.name }}</div>
                            </div>
                            <span v-else>{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex items-center gap-1">
                                <div>{{ slotProps.option.emoji }}</div>
                                <div class="max-w-[200px] truncate">{{ slotProps.option.name }}</div>
                            </div>
                        </template>
                    </Select>
                    <InputError :message="form.errors.country" />
                </div>

                <!-- Phone number -->
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel
                        for="phone"
                        :value="$t('public.phone_number')"
                        :invalid="!!form.errors.phone"
                    />
                    <div class="flex gap-2 items-center self-stretch relative">
                        <Select
                            v-model="selectedPhoneCode"
                            :options="countries"
                            filter
                            :filterFields="['name', 'iso2', 'phone_code']"
                            optionLabel="name"
                            :placeholder="$t('public.phone_code')"
                            class="w-[100px]"
                            :loading="loadingCountries"
                            :invalid="!!form.errors.phone"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.phone_code }}</div>
                                </div>
                                <span v-else>
                                            {{ slotProps.placeholder }}
                                        </span>
                            </template>
                            <template #option="slotProps">
                                <div class="flex items-center gap-1">
                                    <div>{{ slotProps.option.emoji }}</div>
                                    <div>{{ slotProps.option.iso2 }}</div>
                                    <div class="max-w-[200px] truncate text-gray-500">({{ slotProps.option.phone_code }})</div>
                                </div>
                            </template>
                        </Select>

                        <InputText
                            id="phone"
                            type="text"
                            class="block w-full"
                            v-model="form.phone"
                            :placeholder="$t('public.phone_number')"
                            :invalid="!!form.errors.phone"
                        />
                    </div>
                    <InputError :message="form.errors.phone" />
                </div>
            </div>

            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
                <Button
                    type="button"
                    variant="gray-tonal"
                    @click="closeDialog"
                    :disabled="form.processing"
                    class="w-full md:w-auto"
                >
                    {{ $t('public.cancel') }}
                </Button>
                <Button
                    variant="primary-flat"
                    @click="submitForm"
                    :disabled="form.processing || loadingCountries"
                    class="w-full md:w-auto"
                >
                    {{ $t('public.save') }}
                </Button>
            </div>
        </form>
    </Dialog>
</template>
