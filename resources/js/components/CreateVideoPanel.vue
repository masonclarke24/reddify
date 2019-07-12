<template>
  <div class="rounded border-grey shadow p-4">
    <h1 class="mb-4">Options</h1>
    <div>
      <div>
        <label for="language">Select language:</label>
        <select v-model="selectedLanguage" name="language" id="language" @change="languageChange($event)">
          <option v-for="lang in languages" v-bind:key="lang">{{lang}}</option>
        </select>
      </div>

      <div>
        <label for="voice">Select voice:</label>
        <select v-model="selectedVoice" name="voice" id="voice" @change="voiceChange($event)">
          <option v-for="voice in voices" v-bind:key="voice">{{voice}}</option>
        </select>
      </div>
      <div>
        <label for="gender">Select gender:</label>
        <select v-model="selectedGender" name="gender" id="gender">
          <option v-for="gender in genders" v-bind:key="gender">{{gender}}</option>
        </select>
        <button type="button" class="btn w-32" @click="previewClick">Preview</button>
      </div>
      <audio controls class="mb-1" :key="audioSource">
        <source :src="audioSource" type="audio/mp3"/> />Your browser does not support the audio element.
      </audio>
    </div>
    <button
      type="submit"
      class="btn w-full"
      @click="createVideo"
    >Create Video</button>
    <div v-if="progress > 0">
      <div class="rounded-sm border border-black h-5 overflow-hidden">
        <div class="bg-green-300 rounded-sm h-5" :style="progressWidth"></div>
      </div>
      <p class="text-xs">{{progressMessage}}</p>
    </div>

    <div v-if="downloadReady">
      <a :href="downloadLink" class="text-blue-700 text-xl">download</a>
    </div>
  </div>
</template>

<script>
import Axios from "axios";
export default {
  name: "CreateVideoPanel",
  props: ["downloadLink", "downloadReady", "progress", "audioSource"],
  data() {
    return {
      voiceInfo: [],
      voices: [],
      languages: [],
      genders: ['Male', 'Female'],
      selectedLanguage: 'en-US',
      selectedVoice: 'Standard-A',
      selectedGender: 'Male'
    };
  },
  computed: {
    progressWidth() {
      return "width: " + this.progress + "%";
    },
    progressMessage() {
      return this.progress == 100 && !this.downloadReady
        ? "Preparing download, please wait."
        : this.downloadReady
        ? "Download ready"
        : "Processing: " + this.progress + "% complete.";
    }
  },
  methods: {
    filterVoices(language) {
      var voiceNames = new Set();

      //Extract just the voice name from the voices
      this.voiceInfo.forEach(element => {
        //check if this voice is avaliable in the selected language
        if (element.name.startsWith(language)) {
          //The voice name only is everything after the language code, which is always 5 character long, the 6'th characher is to remove
          //the extra - between the language code and the name
          var voiceName = element.name.substring(6, element.name.length);

          voiceName = voiceName.startsWith("-")
            ? voiceName.substring(1)
            : voiceName;

          //Extract only unique names / languages
          if (!voiceNames.has(voiceName)) voiceNames.add(voiceName);
        }
      });
      this.voices = Array.from(voiceNames);
      //Select a vocie if the filter removed the previously selected voice
      if(!this.voices.includes(this.selectedVoice))
        this.selectedVoice = this.voices[0];
    },
    filterGenders(voice){
      var avaliableGenders = new Set();
      var voiceInfoForSelectedVoice = this.voiceInfo.filter(v=> v.name == this.selectedLanguage + '-' + voice);
      
      voiceInfoForSelectedVoice.forEach(item => {
        if(!avaliableGenders.has(item.gender == 1 ? 'Male' : 'Female')) avaliableGenders.add(item.gender == 1 ? 'Male' : 'Female');
      });

      this.genders = Array.from(avaliableGenders);
      if(!this.genders.includes(this.selectedGender))
        this.selectedGender = this.genders[0];
    },
    //Show only voices that are avaliable for the selected language and select the first one
    languageChange(e) {
      this.filterVoices(e.target.value);
      this.filterGenders(this.selectedVoice);
      
    },
    voiceChange(e){
      this.filterGenders(e.target.value); 
      
    },
    //Retrieve the slected voice language and gender (in that order) and send it up to the parent to generate a preview
    previewClick(){

      this.$emit('preview-click', [this.selectedLanguage +'-' + this.selectedVoice, this.selectedLanguage, this.selectedGender]);
    },
    //Notify the parent that a video needs to be created. Pass on the requested audio parameters
    createVideo(){
      this.$emit('create-video', {name: this.selectedLanguage + '-' + this.selectedVoice, language: this.selectedLanguage, gender: this.selectedGender});
    }
  },
  created() {
    Axios.post("voices")
      .then(resp => {
        this.voiceInfo = resp.data;

        var voiceNames = new Set();
        var languageNames = new Set();
        //Extract just the voice name from the voices
        this.voiceInfo.forEach(element => {
          //The voice name only is everything after the language code, which is always 5 character long, the 6'th characher is to remove
          //the extra - between the language code and the name
          var voiceName = element.name.substring(6, element.name.length);

          var language = element.languageCodes[0];

          voiceName = voiceName.startsWith("-")
            ? voiceName.substring(1)
            : voiceName;

          //Extract only unique names / languages
          if (!voiceNames.has(voiceName)) voiceNames.add(voiceName);
          if (!languageNames.has(language)) languageNames.add(language);
        });

        this.filterVoices("");
        this.languages = Array.from(languageNames);
        this.filterVoices(this.selectedLanguage);
        this.selectedVoice = this.voices[0];
      })
      .catch(err => console.log(err));
  }
};
</script>

<style scoped>
  .btn{
    @apply bg-indigo-300;
    @apply rounded;
    @apply mb-4;
    @apply cursor-default;
  }
  .btn:hover{
    @apply bg-blue-300;
  }
</style>


