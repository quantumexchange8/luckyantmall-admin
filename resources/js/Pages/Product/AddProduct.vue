<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Card from "primevue/card";
import InputLabel from "@/Components/InputLabel.vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Textarea from 'primevue/textarea';
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import FileUpload from 'primevue/fileupload';
import Button from '@/Components/Button.vue';
import Image from 'primevue/image';
import ToggleSwitch from 'primevue/toggleswitch';
import {ref} from "vue";
import { usePrimeVue } from 'primevue/config';
import {
    IconPhotoPlus,
    IconX,
    IconUpload,
    IconPlus,
    IconCircleX,
} from "@tabler/icons-vue"
import {useLangObserver} from "@/Composables/localeObserver.js";
import {generalFormat} from "@/Composables/format.js";

const form = useForm({
    name: '',
    description: '',
    base_price: null,
    discount_type: null,
    discount_price: null,
    bundle_price: null,
    quantity: null,
    sku: '',
    images: null,
    category: '',
    master: '',
    required_delivery: '',
});

const $primevue = usePrimeVue();
const files = ref([]);
const finalPrice = ref();
const {formatAmount} = generalFormat();

const onRemoveTemplatingFile = (removeFileCallback, index) => {
    removeFileCallback(index);
};

const onSelectedFiles = (event) => {
    files.value = event.files;
};

const formatSize = (bytes) => {
    const k = 1024;
    const dm = 3;
    const sizes = $primevue.config.locale.fileSizeTypes;

    if (bytes === 0) {
        return `0 ${sizes[0]}`;
    }

    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const formattedSize = parseFloat((bytes / Math.pow(k, i)).toFixed(dm));

    return `${formattedSize} ${sizes[i]}`;
};

const selectedDiscountType = ref();
const discountTypes = ref([
    { name: 'bundle' },
    { name: 'percent' },
    { name: 'points' },
]);

const selectedCategories = ref();
const categories = ref();
const loadingCategories = ref(false);
const { locale } = useLangObserver();

const getCategories = async () => {
    loadingCategories.value = true;
    try {
        const response = await axios.get('/getCategories');
        categories.value = response.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    } finally {
        loadingCategories.value = false;
    }
};

getCategories();

const selectedMaster = ref();
const masters = ref();
const loadingMasters = ref(false);

const getMasters = async () => {
    loadingMasters.value = true;
    try {
        const response = await axios.get('/getMasters');
        masters.value = response.data;
    } catch (error) {
        console.error('Error fetching masters:', error);
    } finally {
        loadingMasters.value = false;
    }
};

getMasters();

const rows = ref([
    {},
]);

const addRow = () => {
    rows.value.push({});
};

const removeRow = (index) => {
    rows.value.splice(index, 1);
};

const submitForm = () => {
    form.discount_type = selectedDiscountType.value;
    form.base_price = finalPrice.value;
    form.images = files.value;
    form.category = selectedCategories.value;
    form.master = selectedMaster.value;

    // Filter out empty rows
    const nonEmptyRows = rows.value.filter(row => Object.keys(row).length > 0);

    if (nonEmptyRows.length > 0) {
        form.bundle_price = nonEmptyRows;
    }

    form.post(route('product.addProduct'), {
        onSuccess: () => {
            form.reset();
        }
    })
}
</script>

<template>
    <AuthenticatedLayout :title="$t('public.add_product')">
        <div class="grid grid-cols-1 md:grid-cols-2 w-full gap-5">
            <div class="flex flex-col gap-5 items-center self-stretch w-full">
                <!-- General Info -->
                <Card class="w-full">
                    <template #content>
                        <div class="flex flex-col items-start gap-3">
                            <span class="text-surface-950 dark:text-white font-semibold">{{ $t('public.general_information') }}</span>

                            <div
                                class="flex flex-col gap-3 md:gap-5 w-full"
                            >
                                <!-- Name -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="name"
                                        :value="$t('public.product_name')"
                                        :invalid="!!form.errors.name"
                                    />
                                    <InputText
                                        id="name"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.name"
                                        :placeholder="$t('public.enter_product_name')"
                                        :invalid="!!form.errors.name"
                                        autofocus
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <!-- Description -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="description"
                                        :value="$t('public.description')"
                                        :invalid="!!form.errors.description"
                                    />
                                    <Textarea
                                        id="description"
                                        type="text"
                                        class="flex flex-1 self-stretch"
                                        v-model="form.description"
                                        :placeholder="$t('public.enter_description')"
                                        :invalid="!!form.errors.description"
                                        rows="5"
                                        cols="30"
                                    />
                                    <InputError :message="form.errors.description" />
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Price Info -->
                <Card class="w-full">
                    <template #content>
                        <div class="flex flex-col items-start gap-3">
                            <span class="text-surface-950 dark:text-white font-semibold">{{ $t('public.pricing') }}</span>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                                <!-- Base Price -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="base_price"
                                        :value="$t('public.base_price')"
                                        :invalid="!!form.errors.base_price"
                                    />
                                    <InputNumber
                                        v-model="finalPrice"
                                        inputId="base_price"
                                        mode="currency"
                                        currency="CNY"
                                        locale="zh-cn"
                                        placeholder="¥0.00"
                                        fluid
                                        :invalid="!!form.errors.base_price"
                                    />
                                    <InputError :message="form.errors.base_price" />
                                </div>

                                <!-- Discount Type -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="discount_type"
                                        :invalid="!!form.errors.discount_type"
                                    >
                                        {{ $t('public.discount_type') }}
                                    </InputLabel>
                                    <Select
                                        v-model="selectedDiscountType"
                                        :options="discountTypes"
                                        optionLabel="name"
                                        optionValue="name"
                                        :placeholder="$t('public.select_discount_type')"
                                        class="w-full"
                                        :invalid="!!form.errors.discount_type"
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
                                    <InputError :message="form.errors.discount_type" />
                                </div>

                                <div
                                    v-if="selectedDiscountType"
                                    class="col-span-1 md:col-span-2 w-full"
                                >
                                    <div
                                        v-if="selectedDiscountType !== 'bundle'"
                                        class="flex flex-col md:flex-row gap-3 md:gap-5 items-center self-stretch"
                                    >
                                        <!-- Percent/Point -->
                                        <div
                                            class="flex flex-col gap-1 items-start self-stretch w-full"
                                        >
                                            <InputLabel
                                                for="discount"
                                                :value="$t('public.discount')"
                                                :invalid="!!form.errors.discount"
                                            />
                                            <InputText
                                                id="discount"
                                                type="text"
                                                class="block w-full"
                                                v-model="form.discount"
                                                :placeholder="$t('public.discount')"
                                                :invalid="!!form.errors.discount"
                                            />
                                            <InputError :message="form.errors.discount" />
                                        </div>

                                        <!-- Final Price -->
                                        <div
                                            class="flex flex-col gap-1 items-start self-stretch w-full"
                                        >
                                            <InputLabel
                                                for="final_price"
                                                :value="$t('public.final_price')"
                                                :invalid="!!form.errors.final_price"
                                            />
                                            <InputText
                                                id="final_price"
                                                type="text"
                                                class="block w-full"
                                                v-model="form.final_price"
                                                :placeholder="$t('public.final_price')"
                                                :invalid="!!form.errors.final_price"
                                                disabled
                                                autofocus
                                            />
                                            <InputError :message="form.errors.final_price" />
                                        </div>
                                    </div>

                                    <!-- Bundle Price -->
                                    <div v-else class="flex flex-col md:col-span-2 gap-1 items-start self-stretch">
                                        <InputLabel
                                            for="bundle_price"
                                            :value="$t('public.bundle_price')"
                                            :invalid="!!form.errors.bundle_price"
                                        />
                                        <div class="flex flex-col items-center gap-3 self-stretch">
                                            <div class="flex justify-between items-center py-2 self-stretch border-b border-surface-200 bg-surface-100 dark:border-surface-700 dark:bg-surface-800">
                                                <div class="flex items-center px-2 w-full text-surface-950 dark:text-white text-xs font-semibold uppercase">
                                                    {{ $t('public.min_unit') }}
                                                </div>
                                                <div class="flex items-center px-2 w-full text-surface-950 dark:text-white text-xs font-semibold uppercase">
                                                    {{ $t('public.price_per_unit') }} (¥)
                                                </div>
                                            </div>

                                            <div class="flex flex-col items-center self-stretch max-h-[200px] overflow-y-auto">
                                                <div
                                                    v-for="(row, index) in rows"
                                                    :key="index"
                                                    class="flex justify-between gap-3 my-1 items-center self-stretch h-10"
                                                >
                                                    <div class="flex items-center gap-1 w-full">
                                                        <InputNumber
                                                            v-model="row.min_unit"
                                                            inputId="settlement_period"
                                                            fluid
                                                            :min="0"
                                                            :placeholder="$t('public.enter_unit')"
                                                            class="w-20"
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-1 w-full">
                                                        <InputNumber
                                                            v-model="row.price_per_unit"
                                                            :minFractionDigits="2"
                                                            fluid
                                                            placeholder="¥0.00"
                                                            class="w-20"
                                                            inputClass="w-20"
                                                        />
                                                        <Button
                                                            type="button"
                                                            variant="error-text"
                                                            pill
                                                            icon-only
                                                            size="sm"
                                                            @click="removeRow(index)"
                                                            :disabled="index === 0"
                                                        >
                                                            <IconCircleX size="16"/>
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>

                                            <Button
                                                type="button"
                                                variant="primary-outlined"
                                                size="base"
                                                class="w-full"
                                                @click="addRow"
                                            >
                                                <IconPlus size="16"/>
                                                {{ $t('public.add_bundle')}}
                                            </Button>
                                        </div>
                                        <InputError :message="form.errors.bundle_price" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Inventory Info -->
                <Card class="w-full">
                    <template #content>
                        <div class="flex flex-col items-start gap-3">
                            <span class="text-surface-950 dark:text-white font-semibold">{{ $t('public.inventory') }}</span>

                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full"
                            >
                                <!-- Quantity -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="quantity"
                                        :value="$t('public.quantity')"
                                        :invalid="!!form.errors.quantity"
                                    />
                                    <InputText
                                        id="quantity"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.quantity"
                                        :placeholder="$t('public.quantity')"
                                        :invalid="!!form.errors.quantity"
                                    />
                                    <InputError :message="form.errors.quantity" />
                                </div>

                                <!-- SKU -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="sku"
                                    >
                                        {{ $t('public.sku') }}
                                    </InputLabel>
                                    <InputText
                                        id="sku"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.sku"
                                        :placeholder="$t('public.sku_placeholder')"
                                        :invalid="!!form.errors.sku"
                                    />
                                    <InputError :message="form.errors.sku" />
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="flex flex-col gap-5 items-center self-stretch w-full">
                <!-- Images -->
                <Card class="w-full">
                    <template #content>
                        <div class="flex flex-col items-start gap-3">
                            <span class="text-surface-950 dark:text-white font-semibold">{{ $t('public.images') }}</span>

                            <div
                                class="flex flex-col gap-3 md:gap-5 w-full"
                            >
                                <!-- Name -->
                                <FileUpload
                                    name="demo[]"
                                    :multiple="true"
                                    accept="image/*"
                                    @select="onSelectedFiles"
                                >
                                    <template #header="{ chooseCallback, clearCallback, files }">
                                        <div class="flex flex-wrap justify-between items-center flex-1 gap-4">
                                            <div class="flex gap-2">
                                                <Button
                                                    type="button"
                                                    variant="gray-outlined"
                                                    size="sm"
                                                    @click="chooseCallback()"
                                                    pill
                                                    icon-only
                                                >
                                                    <IconPhotoPlus size="20" stroke-width="1.5" />
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="error-outlined"
                                                    size="sm"
                                                    @click="clearCallback()"
                                                    pill
                                                    icon-only
                                                    :disabled="!files || files.length === 0"
                                                >
                                                    <IconX size="20" stroke-width="1.5" />
                                                </Button>
                                            </div>
                                        </div>
                                    </template>
                                    <template #content="{ files, removeFileCallback }">
                                        <div class="flex flex-col gap-3">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $t('public.thumbnail_note') }}
                                            </div>

                                            <div v-if="files.length > 0">
                                                <div class="flex overflow-x-scroll gap-4">
                                                    <div
                                                        v-for="(file, index) of files" :key="file.name + file.type + file.size"
                                                        class="p-5 rounded-border w-full max-w-64 flex flex-col border border-surface items-center gap-4 relative"
                                                    >
                                                        <div class="absolute top-2 right-2">
                                                            <Button
                                                                type="button"
                                                                variant="error-text"
                                                                pill
                                                                icon-only
                                                                size="sm"
                                                                @click="onRemoveTemplatingFile(removeFileCallback, index)"
                                                            >
                                                                <IconX size="16" stroke-width="1.5" />
                                                            </Button>
                                                        </div>
                                                        <div class="max-h-20 mt-5">
                                                            <Image role="presentation" :alt="file.name" :src="file.objectURL" preview imageClass="w-48 object-contain h-20" />
                                                        </div>
                                                        <div class="flex flex-col gap-1 items-center self-stretch w-52">
                                                            <span class="font-semibold text-center text-xs truncate w-full max-w-52">{{ file.name }}</span>
                                                            <div class="text-xxs">{{ formatSize(file.size) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template #empty>
                                        <div class="flex items-center justify-center flex-col gap-3 mt-3">
                                            <div class="flex items-center justify-center p-3 text-surface-400 dark:text-surface-600 rounded-full border border-surface-400 dark:border-surface-600">
                                                <IconUpload size="24" stroke-width="1.5" />
                                            </div>
                                            <p class="text-sm">{{ $t('public.drag_and_drop_file') }}</p>
                                            <InputError :message="form.errors.images" />
                                        </div>
                                    </template>
                                </FileUpload>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Configuration -->
                <Card class="w-full">
                    <template #content>
                        <div class="flex flex-col items-start gap-3">
                            <span class="text-surface-950 dark:text-white font-semibold">{{ $t('public.configurations') }}</span>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                                <!-- Categories -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="category"
                                        :value="$t('public.category')"
                                        :invalid="!!form.errors.category"
                                    />
                                    <Select
                                        v-model="selectedCategories"
                                        :options="categories"
                                        :optionLabel="`name.${locale}`"
                                        :placeholder="$t('public.select_category')"
                                        class="w-full"
                                        :loading="loadingCategories"
                                        :invalid="!!form.errors.category"
                                    >
                                        <template #value="slotProps">
                                            <div v-if="slotProps.value" class="flex items-center">
                                                <div>{{ slotProps.value.name[locale] }}</div>
                                            </div>
                                            <span v-else>{{ slotProps.placeholder }}</span>
                                        </template>
                                        <template #option="slotProps">
                                            <div class="flex items-center gap-1 max-w-[220px] truncate">
                                                <span>{{ slotProps.option.name[locale] }}</span>
                                            </div>
                                        </template>
                                    </Select>
                                    <InputError :message="form.errors.category" />
                                </div>

                                <!-- Master -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="master"
                                        :invalid="!!form.errors.master"
                                    >
                                        {{ $t('public.master') }}
                                    </InputLabel>
                                    <Select
                                        v-model="selectedMaster"
                                        :options="masters"
                                        optionLabel="master_name"
                                        :placeholder="$t('public.select_master')"
                                        class="w-full"
                                        :loading="loadingMasters"
                                        :invalid="!!form.errors.master"
                                    >
                                        <template #value="slotProps">
                                            <div v-if="slotProps.value" class="flex items-center">
                                                <div>{{ slotProps.value.master_name }}</div>
                                            </div>
                                            <span v-else>{{ slotProps.placeholder }}</span>
                                        </template>
                                        <template #option="slotProps">
                                            <div class="flex items-center gap-1 max-w-[220px] truncate">
                                                <span>{{ slotProps.option.master_name }}</span>
                                            </div>
                                        </template>
                                    </Select>
                                    <InputError :message="form.errors.master" />
                                </div>

                                <!-- Delivery -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="required_delivery"
                                        :invalid="!!form.errors.required_delivery"
                                    >
                                        {{ $t('public.required_delivery') }}
                                    </InputLabel>
                                    <ToggleSwitch
                                        inputId="required_delivery"
                                        v-model="form.required_delivery"
                                    />
                                    <InputError :message="form.errors.required_delivery" />
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Submit -->
                <Card class="w-full">
                    <template #content>
                        <div class="flex flex-col items-start gap-3">
                            <span class="text-surface-950 dark:text-white font-semibold">{{ $t('public.submit_to_post') }}</span>
                            <div class="flex flex-col gap-1 items-start self-stretch w-full">
                                <div
                                    v-if="selectedMaster"
                                    class="flex justify-between items-start w-full text-sm"
                                >
                                    <div class="max-w-[100px] truncate text-surface-500">
                                        {{ $t('public.master') }}
                                    </div>
                                    <div class="font-semibold">
                                        {{ selectedMaster.master_name }}
                                    </div>
                                </div>
                                <div class="flex justify-between items-start w-full text-sm">
                                    <div class="max-w-[100px] truncate text-surface-500">
                                        {{ $t('public.final_price') }}
                                    </div>
                                    <div class="font-semibold">
                                        ¥{{ formatAmount(finalPrice ?? 0) }}
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 flex flex-col md:flex-row gap-3 justify-end items-center self-stretch w-full">
                                <Button
                                    type="button"
                                    variant="gray-flat"
                                    class="w-full md:w-auto"
                                    :disabled="form.processing"
                                >
                                    {{ $t('public.discard') }}
                                </Button>

                                <Button
                                    variant="primary-flat"
                                    :disabled="form.processing"
                                    class="w-full md:w-auto"
                                    @click="submitForm"
                                >
                                    {{ $t('public.add_product') }}
                                </Button>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
