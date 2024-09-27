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

const visible = ref(false);
const loadingUsers = ref(false);
const users = ref([]);
const selectedUser = ref();
const total_group_members = ref(0);
const colorHex = ref('');

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

watch(selectedUser, () => {
    total_group_members.value = selectedUser.value.total_group_members;
})

const form = useForm({
    name: '',
    color: '',
    leader: ''
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
        <form class="flex flex-col gap-6 items-center self-stretch">
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
                    {{ $t('public.create') }}
                </Button>
            </div>
        </form>
    </Dialog>
</template>
