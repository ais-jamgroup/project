<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-sm font-medium text-[#FBE8A6]">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
