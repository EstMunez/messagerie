<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { User, Message } from '@/types';

const props = defineProps<{
    users: User[];
    messages: Message[];
    auth: { user: User };
}>();

const users = ref(props.users);
const messages = ref(props.messages);
const auth = props.auth;

const selectedUser = ref<User | null>(null);

const selectUser = (user: User) => {
    selectedUser.value = user;
};

// Filtrer les messages entre user connecté et user sélectionné
const filteredMessages = computed(() =>
    messages.value.filter(
        (msg) =>
            (msg.sender_id === auth.user.id && msg.receiver_id === selectedUser?.value?.id) ||
            (msg.receiver_id === auth.user.id && msg.sender_id === selectedUser?.value?.id)
    )
);

// Formulaire Inertia
const form = useForm({
    receiver_id: null as number | null,
    content: '',
});

const sendMessage = () => {
    if (!selectedUser.value || !form.content) return;

    form.receiver_id = selectedUser.value.id;

    form.post(route('messages.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('content');
            // Optionnel : ajouter le message localement sans reload
            messages.value.push({
                id: Date.now(),
                sender_id: auth.user.id,
                receiver_id: selectedUser.value!.id,
                content: form.content,
                created_at: new Date().toISOString(),
            } as Message);
        },
    });
};
</script>

<template>
    <div class="flex h-screen">
        <!-- Liste des utilisateurs -->
        <div class="w-1/4 border-r p-4 overflow-y-auto">
            <h2 class="font-bold mb-4">Utilisateurs</h2>
            <ul>
                <li
                    v-for="user in users"
                    :key="user.id"
                    @click="selectUser(user)"
                    :class="{
            'bg-gray-200': selectedUser && selectedUser.id === user.id,
            'cursor-pointer': true,
            'p-2 rounded': true
          }"
                >
                    {{ user.name }}
                </li>
            </ul>
        </div>

        <!-- Chat -->
        <div class="flex-1 flex flex-col p-4">
            <div class="flex-1 overflow-y-auto mb-4 space-y-2">
                <div
                    v-for="msg in filteredMessages"
                    :key="msg.id"
                    :class="{
            'text-right': msg.sender_id === auth.user.id,
            'text-left': msg.sender_id !== auth.user.id
          }"
                >
                    <div
                        :class="{
              'inline-block p-2 rounded': true,
              'bg-blue-500 text-white': msg.sender_id === auth.user.id,
              'bg-gray-300 text-black': msg.sender_id !== auth.user.id
            }"
                    >
                        {{ msg.content }}
                    </div>
                </div>
            </div>

            <!-- Formulaire d'envoi -->
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input
                    v-model="form.content"
                    type="text"
                    placeholder="Écrire un message..."
                    class="flex-1 border p-2 rounded"
                />
                <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 rounded"
                    :disabled="!selectedUser || !form.content"
                >
                    Envoyer
                </button>
            </form>
        </div>
    </div>
</template>




<style scoped>
/* Optionnel : scroll automatique */
.flex-1.overflow-y-auto {
    scrollbar-width: thin;
}
</style>
