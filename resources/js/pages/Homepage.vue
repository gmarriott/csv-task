<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    people?: Array<{
        title: string,
        first_name: string | null,
        initial: string | null,
        last_name: string
    }>;
}>();

const form = useForm<{ file: File | null }>({
    file: null,
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.file = target.files?.[0] ?? null;
};

const submit = () => {
    if (!form.file) return;

    form.post('/csv-upload', {
        forceFormData: true,
    });
};

</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#5ef5b8]"
    >
        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <input
                type="file"
                accept=".csv,text/csv"
                @change="handleFileChange"
                class="block"
            />

            <button
                type="submit"
                class="rounded-md bg-black px-4 py-2 text-white disabled:opacity-50"
                :disabled="!form.file || form.processing"
            >
                Upload CSV
            </button>

            <div v-if="form.errors.file" class="text-red-600 text-sm">
                {{ form.errors.file }}
            </div>
        </form>

        <div v-if="props.people && props.people.length" class="mt-6 w-full max-w-xl">
            <table class="w-full border">
                <thead>
                <tr class="bg-gray-200">
                    <th class="border px-2 py-1">Title</th>
                    <th class="border px-2 py-1">First Name</th>
                    <th class="border px-2 py-1">Initial</th>
                    <th class="border px-2 py-1">Last Name</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="(person, i) in props.people" :key="i">
                    <td class="border px-2 py-1">{{ person.title }}</td>
                    <td class="border px-2 py-1">{{ person.first_name }}</td>
                    <td class="border px-2 py-1">{{ person.initial }}</td>
                    <td class="border px-2 py-1">{{ person.last_name }}</td>
                </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>
