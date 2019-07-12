<template>
    <div class="rounded border-grey shadow p-4">
        <p class="text-xs">{{replyItem.author}}</p>
        <p>{{replyItem.content}}</p>
    </div>
</template>

<script>
export default {
    name: 'RepyItem',
    props: ['replyItem']
}
</script>

<style>

</style>
