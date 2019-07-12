<template>
  <div class="px-8">
    <div class="flex">
      <aside class="w-auto pt-8 w-64">
        <CreateVideoPanel
          v-on:create-video="createVideo"
          v-on:preview-click="createAudioSample"
          :download-link="this.downloadLink"
          :download-ready="this.downloadReady"
          :progress="this.progress"
          :audio-source="this.audioSource"
        ></CreateVideoPanel>
      </aside>
      <div class="primary flex-1">
        <FindSubreddit
          class="p-4"
          v-on:find-subreddit="addReplies"
          v-on:clear-download-link="clearDownloadLink"
        ></FindSubreddit>
        <Replies v-bind:replies="replies"></Replies>
      </div>
    </div>
  </div>
</template>

<script>
import FindSubreddit from "./FindSubreddit";
import Replies from "./Replies";
import CreateVideoPanel from "./CreateVideoPanel";
import axios from "axios";
import { setInterval, clearInterval } from 'timers';
export default {
  name: "App",
  components: {
    FindSubreddit,
    Replies,
    CreateVideoPanel
  },
  data() {
    return {
      replies: [
        {
          id: 0,
          content:
            "I hired a guy that was released after 17 years (circa 2005). A week after he started working he bought a phone. He had a child like wonder with push to talk and texting. A week later he was pissed off that he couldn't text his order in to Hungry Howies.",
          author: "super long"
        }
      ],
      downloadLink: "",
      downloadReady: false,
      progress: 0,
      audioSource: "audio.mp3"
    };
  },
  methods: {
    addReplies(resp) {
      this.replies = resp;
    },
    //Create a video using the requested voice
    createVideo(args) {
      axios
        .post("createVideo", [args, this.replies])
        .then(resp => {
          if (resp.status) {
            this.downloadReady = true;
            this.downloadLink = resp.data;
          }
        })
        .catch(err => console.log(err));

        //start a timer to update the progress
        var progressTimer = setInterval(() => {
           axios.get('progress').then(resp => this.progress = parseInt(resp.data * 100));
          if(this.progress == 100)
            clearInterval(progressTimer);
        }, 1000);

    },
    clearDownloadLink(e) {
      this.downloadReady = false;
      this.progress = 0;
    },
    createAudioSample(args){
      axios.post("voiceSample", [args[0], args[1], args[2], this.replies[0]['content']]).then(resp => this.audioSource = resp.data);
    }
  }
};
</script>

<style>
</style>
