<script setup>
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import {
    IconUserPlus,
} from "@tabler/icons-vue"
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Password from "primevue/password";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import Select from 'primevue/select';

const visible = ref(false);
const countries = ref([]);
const selectedCountry = ref();
const selectedPhoneCode = ref();
const users = ref([]);
const selectedUser = ref();
const loadingCountries = ref(false);
const loadingUsers = ref(false);

const openDialog = () => {
    visible.value = true;
    getUsers();
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

const form = useForm({
    name: '',
    email: '',
    username: '',
    country: '',
    upline: '',
    dial_code: '',
    phone: '',
    phone_number: '',
    kyc_verification: '',
    password: '',
    password_confirmation: '',
})

const submitForm = () => {
    form.country = selectedCountry.value;
    form.dial_code = selectedPhoneCode.value;

    if (selectedUser.value) {
        form.upline = selectedUser.value;
    }

    if (selectedPhoneCode.value) {
        form.phone_number = selectedPhoneCode.value.phone_code + form.phone;
    }
    form.post(route('customer.addNewCustomer'), {
        onSuccess: (values) => {
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
        variant="primary-flat"
        class="w-full md:w-auto"
        @click="openDialog"
    >
        <IconUserPlus size="16" />
        {{ $t('public.add_customer') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_customer')"
        class="dialog-xs md:dialog-md"
    >
        <form class="flex flex-col gap-6 items-center self-stretch">
            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.basics') }}</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
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
                            :placeholder="$t('public.full_name_placeholder')"
                            :invalid="!!form.errors.name"
                            autofocus
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="email"
                            :value="$t('public.email')"
                            :invalid="!!form.errors.email"
                        />
                        <InputText
                            id="email"
                            type="email"
                            class="block w-full"
                            v-model="form.email"
                            placeholder="example@example.com"
                            :invalid="!!form.errors.email"
                        />
                        <InputError :message="form.errors.email" />
                    </div>
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
                            :placeholder="$t('public.enter_username')"
                            :invalid="!!form.errors.username"
                            autofocus
                        />
                        <InputError :message="form.errors.username" />
                    </div>
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="upline"
                        >
                            {{ $t('public.upline') }}
                        </InputLabel>
                        <Select
                            v-model="selectedUser"
                            :options="users"
                            filter
                            optionLabel="name"
                            :placeholder="$t('public.select_upline')"
                            class="w-full"
                            :loading="loadingUsers"
                            :invalid="!!form.errors.upline"
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
                        <InputError :message="form.errors.upline" />
                    </div>
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
            </div>

            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.credentials') }}</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="password"
                            :value="$t('public.password')"
                            :invalid="!!form.errors.password"
                        />
                        <Password
                            v-model="form.password"
                            toggleMask
                            :invalid="!!form.errors.password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="password_confirmation"
                            :value="$t('public.confirm_password')"
                            :invalid="!!form.errors.password"
                        />
                        <Password
                            v-model="form.password_confirmation"
                            toggleMask
                            :invalid="!!form.errors.password"
                        />
                    </div>
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
                    :disabled="form.processing"
                    class="w-full md:w-auto"
                >
                    {{ $t('public.save') }}
                </Button>
            </div>
        </form>
    </Dialog>
</template>
