<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
<link rel="stylesheet" href="css/app.css">
</head>

<body>
    <div id="app">
        <template>
            <div class="px-8">
                <div class="flex">
                    <aside class="w-auto pt-8 w-64">
                        <CreateVideoPanel v-on:create-video="createVideo" v-on:preview-click="createAudioSample" :download-link="downloadLink" :download-ready="downloadReady" :progress="progress" :audio-source="audioSource"></CreateVideoPanel>
                    </aside>
                    <div class="primary flex-1">
                        <FindSubreddit class="p-4" v-on:find-subreddit="addReplies" v-on:clear-download-link="clearDownloadLink"></FindSubreddit>
                        <Replies v-bind:replies="replies"></Replies>
                    </div>
                </div>
            </div>
        </template>
        
    </div>
    <script src='/js/app.js'></script>

</body>

</html>