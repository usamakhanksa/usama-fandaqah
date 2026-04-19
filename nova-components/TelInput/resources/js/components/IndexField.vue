<template>
    <span style="display: inline-block; direction: ltr">{{ field.value }}</span>
</template>

<script>
export default {
    props: ['resourceName', 'field'],
}
</script>
