<template>
    <div>
        <div v-for="replyItem in replies" v-bind:key="replyItem.id"  class="p-4">
            <ReplyItem v-bind:replyItem="replyItem"></ReplyItem>
        </div>
    </div>
</template>

<script>

import ReplyItem from './ReplyItem'
export default {
    name: "Replies",
    props: ['replies'],
    components: {
        ReplyItem
    }
}
</script>

<style>

</style>
