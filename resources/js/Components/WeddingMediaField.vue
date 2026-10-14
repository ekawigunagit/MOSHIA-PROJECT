<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
const props = defineProps({ modelValue: [Number, String], label: String, fieldId: String, kind: { type: String, default: 'image' }, media: Array, workspaceId: Number, limitKb: Number, disabled: Boolean });
const emit = defineEmits(['update:modelValue', 'uploading']);
const input = ref(null);
const form = useForm({ kind: props.kind, file: null });
const choices = computed(() => props.media.filter(m => m.kind === props.kind));
const chosen = computed(() => choices.value.find(m => m.id === props.modelValue));
const accept = computed(() => props.kind === 'video' ? 'video/mp4,video/webm' : props.kind === 'music' ? 'audio/mpeg,.mp3' : 'image/jpeg,image/png,image/webp');
function upload() {
    if (!form.file || props.disabled || form.processing) return;
    form.kind = props.kind;
    form.post(route('wedding.media.store', props.workspaceId), {
        forceFormData: true, preserveState: true, preserveScroll: true,
        onStart: () => emit('uploading', true),
        onFinish: () => emit('uploading', false),
        onSuccess: page => {
            const id = page.props.uploadedMediaId;
            if (page.props.media.some(m => m.id === id && m.kind === props.kind)) emit('update:modelValue', id);
            form.reset('file'); if (input.value) input.value.value = '';
        },
    });
}
</script>
<template>
<div class="studio-upload">
<label :for="fieldId + '-file'" class="moshia-label">{{ label }}</label>
<input :id="fieldId + '-file'" ref="input" type="file" :accept="accept" :disabled="disabled || form.processing" @change="form.file = $event.target.files[0] ?? null; form.clearErrors()" />
<p class="studio-help">{{ kind === 'video' ? 'MP4 / WebM' : kind === 'music' ? 'MP3' : 'JPG / PNG / WebP' }} · maksimal {{ limitKb / 1024 }} MB. Upload lalu simpan draft.</p>
<InputError v-for="(error, key) in form.errors" :key="key" :message="error" />
<progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full" aria-label="Progres upload" />
<button type="button" class="button button-outline mt-3" :disabled="disabled || form.processing || !form.file" @click="upload">{{ form.processing ? 'Mengunggah…' : 'Unggah file' }}</button>
<label :for="fieldId + '-select'" class="moshia-label mt-4">Atau pilih file yang sudah diunggah</label>
<select :id="fieldId + '-select'" :value="modelValue ?? ''" :disabled="disabled || form.processing" class="moshia-input w-full" @change="emit('update:modelValue', $event.target.value ? Number($event.target.value) : null)"><option value="">Tanpa file</option><option v-for="m in choices" :key="m.id" :value="m.id">#{{ m.id }} · {{ Math.round(m.size / 1024) }} KB</option></select>
<img v-if="chosen && kind === 'image'" :src="chosen.url" :alt="label" class="studio-section-photo mt-4" />
<video v-if="chosen && kind === 'video'" :src="chosen.url" controls preload="metadata" class="studio-section-photo mt-4" />
<audio v-if="chosen && kind === 'music'" :src="chosen.url" controls preload="none" class="mt-4 w-full" />
</div>
</template>
