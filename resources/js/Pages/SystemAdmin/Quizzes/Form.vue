<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    course: Object,
    quiz: { type: Object, default: null }
});

const isEditing = !!props.quiz;

const form = useForm({
    title: props.quiz?.title || '',
    duration_minutes: props.quiz?.duration_minutes || 30,
    pass_score: props.quiz?.pass_score || 70,
});

const submit = () => {
    if (isEditing) {
        form.put(route('system.quizzes.update', [props.course.id, props.quiz.id]));
    } else {
        form.post(route('system.quizzes.store', props.course.id));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Sửa bài kiểm tra' : 'Tạo bài kiểm tra'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ isEditing ? 'Sửa bài kiểm tra' : 'Tạo bài kiểm tra mới' }} - Khóa học: {{ course.name }}
                </h2>
                <Link :href="route('system.courses.show', course.id)" class="text-sm text-gray-500 hover:underline">
                    &larr; Quay lại khóa học
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="title" value="Tiêu đề bài kiểm tra *" />
                            <TextInput id="title" type="text" class="mt-1 block w-full" v-model="form.title" required />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="duration_minutes" value="Thời gian làm bài (Phút) *" />
                                <TextInput id="duration_minutes" type="number" min="1" class="mt-1 block w-full" v-model="form.duration_minutes" required />
                                <InputError class="mt-2" :message="form.errors.duration_minutes" />
                            </div>

                            <div>
                                <InputLabel for="pass_score" value="Điểm đậu (1-100) *" />
                                <TextInput id="pass_score" type="number" min="1" max="100" class="mt-1 block w-full" v-model="form.pass_score" required />
                                <InputError class="mt-2" :message="form.errors.pass_score" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ isEditing ? 'Cập nhật' : 'Tạo mới' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>