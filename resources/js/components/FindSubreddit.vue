<template>
    <div>
        <form @submit="findSubreddit" class="flex justify-between items-baseline">
            <input type="text" v-model="title" v-on:keyup="checkInput" 
            v-bind:class="{'border-red border' : this.isError}" name="title" placeholder="Subreddit name e.g AskReddit" class="w-2/4 mr-2 rounded">
            <input type="submit" value="Submit" class="bg-indigo-300 hover:bg-blue-300 rounded mb-4 w-2/4 ml-2" @click="$emit('clear-download-link')">
        </form>
        <div class='error'>{{this.errorMessage}}</div>
    </div>
</template>

<script>
const snoowrap = require('../snoowrap-v1');
import axios from "axios";
export default {
    name: "FindSubreddit",
    data(){
        return{
            title: '',
            errorMessage : '',
            isError : false
        }
    }, methods:{
        //Finds the subreddit that matches the title, extracts the top post and 25 replies
        findSubreddit(e){

            e.preventDefault();
            //Don't try and find a subreddit if the entry is invalid

            axios.post("createVideo", [this.title, [1,2,3]]).then(resp => console.log(resp.data)).catch(err => console.log(err));
            return;
            if(this.isError || this.title.length < 2)
                return;
            
            var reddit = new window.snoowrap({userAgent: process.env.USER_AGENT, clientId: process.env.CLIENT_ID, clientSecret: process.env.CLIENT_SECRET, 
            username: process.env.REDDIT_USERNAME, password: process.env.REDDIT_PASSWORD});

            //Fetch the top 25 posts from the specified subreddit
            reddit.getTop(this.title.match(/\w+/)[0]).then(resp => 
            //Extract the replies from this submission, 25 replies with 2 comments each
            resp[0].expandReplies({limit: 2, depth: 1}).then(submission => submission.comments.slice(0,25)))
            //Extract the relevant information from each comment and construct the replies object
            .then(comments => {
                var replies = [];

                for(var i = 0; i < comments.length; i++){
                    replies.push({id: i, author: comments[i].author.name, content: comments[i].body});
                }

                this.$emit('find-subreddit', replies);
            }).catch(err=> console.log("Too many requests, please try again later"));
        },
        checkInput(){
            if(!this.title.match(/\w+$/) && this.title.length > 2){
                this.errorMessage = "Invalid input"
                this.isError = true;
                console.log("error");
            }
            else{
                this.errorMessage = '';
                this.isError = false;
            }
        }
    }
}
</script>

<style scoped>
    input[type="text"]{
        text-overflow: ellipsis;
    }
</style>

