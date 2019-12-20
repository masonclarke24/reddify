# reddify
The purpose of this project is to demonstrate the use of laravel and vue.js in a web application. This project creates a narrated video from the top 25 replies of the most recent submission in a given subreddit. This solution was designed to work for [r/AskReddit](https://www.Reddit.com/r/AskReddit) but will work for any subreddit that does not have images for a submission. The video narration is provided by Google's Speech Api.

# Usage Example
Creating a video:
![Alt-text](https://github.com/masonclarke24/reddify/blob/master/reddify.png)
Video demonstrating the output of an AskReddit submission:
[![Output sample](https://img.youtube.com/vi/rIr2_R13LJ8/0.jpg)](https://www.youtube.com/watch?v=rIr2_R13LJ8)

# Development Setup
This project has not been deployed. If you wish to use this sytem you will need to build and run it yourself.
## Prerequisites
* Laravel
* npm
* Web server eg. IIS - do not use php's built-in development server
* API key for Google [Text-to-Speech](https://cloud.google.com/text-to-speech/)

## Development installation instructions
1. Clone this repository
2. Rename the .env.example file to .env
3. In the .env file, provide the path to your GCLOUD_CREDENTIALS .json file
4. Run `npm install` and `npm run watch`
5. Videos take a long time to make so edit your php.ini file and set the max_execution_time to 500
6. Place this project in the appropriate directory for your web server or run the command `php artisan serve`(not recomended)
